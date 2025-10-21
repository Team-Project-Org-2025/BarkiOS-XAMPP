<?php
namespace Barkios\models;

use Barkios\core\Database;
use PDO;
use Exception;

class Sale extends Database
{
    /* =====================================================
       OBTENER DATOS
    ===================================================== */

    public function getAll()
    {
        $sql = "
            SELECT v.*, 
                   c.nombre_cliente, 
                   e.nombre AS nombre_empleado
            FROM ventas v
            LEFT JOIN clientes c ON v.cliente_ced = c.cliente_ced
            LEFT JOIN empleados e ON v.empleado_ced = e.empleado_ced
            ORDER BY v.fecha DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT v.*, c.nombre_cliente, e.nombre AS nombre_empleado
            FROM ventas v
            LEFT JOIN clientes c ON v.cliente_ced = c.cliente_ced
            LEFT JOIN empleados e ON v.empleado_ced = e.empleado_ced
            WHERE v.venta_id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getSaleWithDetails($id)
    {
        $venta = $this->getById($id);
        if (!$venta) return null;

        $stmt = $this->db->prepare("
            SELECT dv.*, p.nombre AS nombre_prenda, p.tipo, p.categoria
            FROM detalle_venta dv
            JOIN prendas p ON p.prenda_id = dv.prenda_id
            WHERE dv.venta_id = :id
        ");
        $stmt->execute([':id' => $id]);
        $venta['detalles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $venta['pagos'] = $this->getPaymentsBySale($id);
        return $venta;
    }

    /* =====================================================
       CREAR VENTA
    ===================================================== */

    public function addSale($data)
    {
        try {
            $this->db->beginTransaction();

            // Insertar cabecera de venta
            $stmt = $this->db->prepare("
                INSERT INTO ventas (
                    empleado_ced, cliente_ced, tipo_venta, estado_venta,
                    monto_total, saldo_pendiente, observaciones
                ) VALUES (
                    :empleado, :cliente, :tipo, :estado, :total, :saldo, :obs
                )
            ");

            $estado = $data['tipo_venta'] === 'credito' ? 'pendiente' : 'completada';
            $saldo = $data['tipo_venta'] === 'credito' ? $data['monto_total'] : 0.00;

            $stmt->execute([
                ':empleado' => $data['empleado_ced'],
                ':cliente' => $data['cliente_ced'],
                ':tipo' => $data['tipo_venta'],
                ':estado' => $estado,
                ':total' => $data['monto_total'],
                ':saldo' => $saldo,
                ':obs' => $data['observaciones'] ?? null
            ]);

            $ventaId = $this->db->lastInsertId();

            // Insertar detalle_venta
            $stmtDet = $this->db->prepare("
                INSERT INTO detalle_venta (venta_id, prenda_id, precio_unitario)
                VALUES (:venta_id, :prenda_id, :precio)
            ");
            $updPrenda = $this->db->prepare("
                UPDATE prendas SET estado = 'VENDIDA' WHERE prenda_id = :id
            ");

            foreach ($data['productos'] as $p) {
                $stmtDet->execute([
                    ':venta_id' => $ventaId,
                    ':prenda_id' => $p['prenda_id'],
                    ':precio' => $p['precio_unitario']
                ]);
                $updPrenda->execute([':id' => $p['prenda_id']]);
            }

            // Si es venta a crédito → registrar en credito + cuentas_cobrar
            if ($data['tipo_venta'] === 'credito') {
                $stmtCred = $this->db->prepare("
                    INSERT INTO credito (venta_id) VALUES (:venta_id)
                ");
                $stmtCred->execute([':venta_id' => $ventaId]);
                $creditoId = $this->db->lastInsertId();

                $stmtCC = $this->db->prepare("
                    INSERT INTO cuentas_cobrar (credito_id, estado)
                    VALUES (:credito_id, 'pendiente')
                ");
                $stmtCC->execute([':credito_id' => $creditoId]);

                $cuentaId = $this->db->lastInsertId();

                // Enlazar cuenta al crédito
                $this->db->prepare("
                    UPDATE credito SET cuenta_cobrar_id = :cuenta WHERE credito_id = :id
                ")->execute([
                    ':cuenta' => $cuentaId,
                    ':id' => $creditoId
                ]);
            }

            $this->db->commit();
            return $ventaId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Sale::addSale - " . $e->getMessage());
            return false;
        }
    }

    /* =====================================================
       PAGOS
    ===================================================== */

    public function addPayment($data)
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO pagos (venta_id, monto, observaciones)
                VALUES (:venta_id, :monto, :obs)
            ");
            $stmt->execute([
                ':venta_id' => $data['venta_id'],
                ':monto' => $data['monto'],
                ':obs' => $data['observaciones'] ?? null
            ]);

            // Actualizar saldo en venta
            $this->db->prepare("
                UPDATE ventas 
                SET saldo_pendiente = GREATEST(saldo_pendiente - :monto, 0)
                WHERE venta_id = :id
            ")->execute([
                ':monto' => $data['monto'],
                ':id' => $data['venta_id']
            ]);

            // Si la venta ya no tiene saldo pendiente, marcar completada
            $this->db->query("
                UPDATE ventas
                SET estado_venta = 'completada'
                WHERE venta_id = {$data['venta_id']} AND saldo_pendiente <= 0
            ");

            return true;
        } catch (Exception $e) {
            error_log("Sale::addPayment - " . $e->getMessage());
            return false;
        }
    }

    public function getPaymentsBySale($venta_id)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM pagos WHERE venta_id = :id ORDER BY fecha_pago DESC
        ");
        $stmt->execute([':id' => $venta_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =====================================================
       CANCELAR VENTA
    ===================================================== */

    public function cancelSale($ventaId)
    {
        try {
            $this->db->beginTransaction();

            // Liberar prendas
            $stmt = $this->db->prepare("SELECT prenda_id FROM detalle_venta WHERE venta_id = :id");
            $stmt->execute([':id' => $ventaId]);
            $prendas = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if ($prendas) {
                $upd = $this->db->prepare("UPDATE prendas SET estado = 'DISPONIBLE' WHERE prenda_id = :id");
                foreach ($prendas as $p) $upd->execute([':id' => $p]);
            }

            // Marcar venta como cancelada
            $this->db->prepare("
                UPDATE ventas SET estado_venta = 'cancelada', saldo_pendiente = 0 WHERE venta_id = :id
            ")->execute([':id' => $ventaId]);

            // Marcar cuentas asociadas como canceladas
            $this->db->query("
                UPDATE cuentas_cobrar 
                SET estado = 'vencido'
                WHERE credito_id IN (SELECT credito_id FROM credito WHERE venta_id = $ventaId)
            ");

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Sale::cancelSale - " . $e->getMessage());
            return false;
        }
    }
}
