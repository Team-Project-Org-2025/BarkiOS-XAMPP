<?php 
namespace Barkios\models;

use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo de Cuentas por Cobrar (optimizado)
 */
class AccountsReceivable extends Database
{
    /** =====================================================
     *  MÉTODOS BASE
     ===================================================== */
    private function run(string $sql, array $params = [], bool $fetchAll = true)
    {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $fetchAll ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("DB Error: " . $e->getMessage());
            return $fetchAll ? [] : null;
        }
    }

    private function execute(string $sql, array $params = []): bool
    {
        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("DB Exec Error: " . $e->getMessage());
            return false;
        }
    }

    /** =====================================================
     *  CONSULTAS PRINCIPALES
     ===================================================== */

    public function getAll()
    {
        $sql = "
            SELECT 
                cc.cuenta_cobrar_id, cc.emision AS fecha_emision,
                cc.vencimiento AS fecha_vencimiento,
                DATE_FORMAT(cc.vencimiento, '%Y-%m-%d') AS fecha_vencimiento_formatted,
                cc.estado, v.venta_id, v.referencia, v.monto_total, v.saldo_pendiente,
                c.nombre_cliente, c.cliente_ced, c.telefono,
                cr.credito_id, cr.referencia_credito,
                DATEDIFF(cc.vencimiento, NOW()) AS dias_restantes,
                CASE 
                    WHEN cc.estado = 'pagado' THEN 'Pagado'
                    WHEN cc.vencimiento < NOW() AND cc.estado = 'pendiente' THEN 'Vencido'
                    WHEN DATEDIFF(cc.vencimiento, NOW()) BETWEEN 0 AND 3 THEN 'Por vencer'
                    ELSE 'Vigente'
                END AS estado_visual
            FROM cuentas_cobrar cc
            INNER JOIN credito cr ON cc.credito_id = cr.credito_id
            INNER JOIN ventas v ON cr.venta_id = v.venta_id
            INNER JOIN clientes c ON v.cliente_ced = c.cliente_ced
            WHERE v.estado_venta != 'cancelada' AND cc.estado != 'eliminado'
            ORDER BY cc.vencimiento ASC, cc.cuenta_cobrar_id DESC";
        return $this->run($sql);
    }

    public function getById($id)
    {
        $sql = "
            SELECT 
                cc.*, v.venta_id, v.referencia, v.monto_total, v.monto_subtotal,
                v.monto_iva, v.saldo_pendiente, v.fecha AS fecha_venta,
                v.observaciones AS observaciones_venta,
                c.nombre_cliente, c.cliente_ced, c.telefono, c.correo, c.tipo AS tipo_cliente,
                e.nombre AS nombre_empleado,
                cr.credito_id, cr.referencia_credito,
                DATEDIFF(cc.vencimiento, NOW()) AS dias_restantes
            FROM cuentas_cobrar cc
            INNER JOIN credito cr ON cc.credito_id = cr.credito_id
            INNER JOIN ventas v ON cr.venta_id = v.venta_id
            INNER JOIN clientes c ON v.cliente_ced = c.cliente_ced
            LEFT JOIN empleados e ON v.empleado_ced = e.empleado_ced
            WHERE cc.cuenta_cobrar_id = :id";
        
        $cuenta = $this->run($sql, [':id' => $id], false);
        if (!$cuenta) return null;

        $pagos = $this->getPaymentsByAccount($id);
        $cuenta['pagos'] = $pagos;
        $cuenta['total_pagado'] = array_sum(array_column($pagos, 'monto')) ?? 0;
        return $cuenta;
    }

    public function getByClient($cedula)
    {
        $sql = "
            SELECT 
                cc.*, v.referencia, v.monto_total, v.saldo_pendiente,
                DATEDIFF(cc.vencimiento, NOW()) AS dias_restantes
            FROM cuentas_cobrar cc
            INNER JOIN credito cr ON cc.credito_id = cr.credito_id
            INNER JOIN ventas v ON cr.venta_id = v.venta_id
            WHERE v.cliente_ced = :cedula
              AND v.estado_venta != 'cancelada'
              AND cc.estado != 'eliminado'
            ORDER BY cc.vencimiento ASC";
        return $this->run($sql, [':cedula' => $cedula]);
    }

    public function getPaymentsByAccount($id)
    {
        $sql = "
            SELECT p.pago_id, p.fecha_pago, p.monto, p.tipo_pago, 
                   p.referencia_bancaria, p.banco, p.estado_pago, p.observaciones
            FROM pagos p
            INNER JOIN credito cr ON p.credito_id = cr.credito_id
            INNER JOIN cuentas_cobrar cc ON cr.credito_id = cc.credito_id
            WHERE cc.cuenta_cobrar_id = :id AND p.estado_pago = 'CONFIRMADO'
            ORDER BY p.fecha_pago DESC";
        return $this->run($sql, [':id' => $id]);
    }

    /** =====================================================
     *  OPERACIONES CRUD
     ===================================================== */

    public function registerPayment(array $data)
    {
        try {
            $this->db->beginTransaction();

            $cuenta = $this->getById($data['cuenta_cobrar_id']);
            if (!$cuenta) throw new Exception("Cuenta por cobrar no encontrada");
            if ($cuenta['estado'] === 'pagado') throw new Exception("Cuenta ya pagada");
            if ($cuenta['estado'] === 'vencido') throw new Exception("Cuenta vencida");

            $monto = (float) $data['monto'];
            $saldo = (float) $cuenta['saldo_pendiente'];
            if ($monto <= 0) throw new Exception("Monto inválido");
            if ($monto > $saldo) throw new Exception("Monto excede el saldo pendiente");

            $insertPago = "
                INSERT INTO pagos (venta_id, credito_id, monto, tipo_pago, referencia_bancaria, banco, estado_pago, observaciones)
                VALUES (:venta_id, :credito_id, :monto, :tipo_pago, :referencia, :banco, 'CONFIRMADO', :obs)";
            $this->execute($insertPago, [
                ':venta_id'   => $cuenta['venta_id'],
                ':credito_id' => $cuenta['credito_id'],
                ':monto'      => $monto,
                ':tipo_pago'  => $data['tipo_pago'] ?? 'EFECTIVO',
                ':referencia' => $data['referencia_bancaria'] ?? null,
                ':banco'      => $data['banco'] ?? null,
                ':obs'        => $data['observaciones'] ?? null
            ]);

            $nuevoSaldo = max(0, $saldo - $monto);

            $this->execute("
                UPDATE ventas 
                SET saldo_pendiente = :saldo,
                    estado_venta = IF(:saldo <= 0, 'completada', estado_venta),
                    fec_actualizacion = NOW()
                WHERE venta_id = :venta_id", 
                [':saldo' => $nuevoSaldo, ':venta_id' => $cuenta['venta_id']]
            );

            if ($nuevoSaldo <= 0)
                $this->execute("UPDATE cuentas_cobrar SET estado = 'pagado', fec_actualizacion = NOW() WHERE cuenta_cobrar_id = :id",
                    [':id' => $data['cuenta_cobrar_id']]);

            $this->db->commit();
            return ['success' => true, 'message' => 'Pago registrado correctamente', 'nuevo_saldo' => $nuevoSaldo];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateDueDate($id, $nuevaFecha)
    {
        try {
            $fecha = new \DateTime($nuevaFecha);
            if ($fecha <= new \DateTime()) throw new Exception("La fecha debe ser futura");

            $this->db->beginTransaction();

            $updated = $this->execute("
                UPDATE cuentas_cobrar 
                SET vencimiento = :fecha, fec_actualizacion = NOW()
                WHERE cuenta_cobrar_id = :id AND estado IN ('pendiente', 'vencido')", 
                [':fecha' => $nuevaFecha, ':id' => $id]
            );

            if (!$updated) throw new Exception("No se pudo actualizar la fecha");

            $this->execute("UPDATE cuentas_cobrar SET estado = 'pendiente' WHERE cuenta_cobrar_id = :id AND estado = 'vencido'", [':id' => $id]);
            $this->db->commit();
            return ['success' => true, 'message' => 'Fecha actualizada'];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function processExpiredAccounts()
    {
        try {
            $this->db->beginTransaction();

            $this->execute("UPDATE cuentas_cobrar SET estado='vencido', fec_actualizacion=NOW() WHERE vencimiento < NOW() AND estado='pendiente'");
            $this->execute("
                UPDATE ventas v
                INNER JOIN credito cr ON v.venta_id = cr.venta_id
                INNER JOIN cuentas_cobrar cc ON cr.credito_id = cc.credito_id
                SET v.estado_venta='cancelada', v.saldo_pendiente=0, v.fec_actualizacion=NOW()
                WHERE cc.estado='vencido' AND v.estado_venta='pendiente'");
            $this->execute("
                UPDATE prendas p
                INNER JOIN detalle_venta dv ON p.codigo_prenda=dv.codigo_prenda
                INNER JOIN ventas v ON dv.venta_id=v.venta_id
                SET p.estado='DISPONIBLE'
                WHERE v.estado_venta='cancelada' AND p.estado='VENDIDA'");

            $affected = $this->db->query("SELECT COUNT(*) total FROM cuentas_cobrar WHERE estado='vencido'")->fetchColumn();
            $this->db->commit();
            return ['success' => true, 'message' => "Se procesaron $affected cuentas vencidas", 'affected' => $affected];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getStats()
    {
        $sql = "
            SELECT 
                COUNT(*) total_cuentas,
                SUM(cc.estado='pendiente') pendientes,
                SUM(cc.estado='pagado') pagadas,
                SUM(cc.estado='vencido') vencidas,
                SUM(v.saldo_pendiente) saldo_total,
                SUM(CASE WHEN DATEDIFF(cc.vencimiento,NOW())<=3 AND cc.estado='pendiente' THEN v.saldo_pendiente ELSE 0 END) por_vencer
            FROM cuentas_cobrar cc
            INNER JOIN credito cr ON cc.credito_id=cr.credito_id
            INNER JOIN ventas v ON cr.venta_id=v.venta_id
            WHERE v.estado_venta!='cancelada' AND cc.estado!='eliminado'";
        return $this->run($sql, [], false);
    }
}
