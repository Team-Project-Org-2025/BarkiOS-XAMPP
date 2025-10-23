<?php
namespace Barkios\models;

use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo de Cuentas por Cobrar
 * Gestiona créditos de ventas, pagos y vencimientos
 */
class AccountsReceivable extends Database
{
    /* =====================================================
       CONSULTAS PRINCIPALES
    ===================================================== */

    /**
     * Obtiene todas las cuentas por cobrar activas (no eliminadas ni anuladas)
     */
    public function getAll()
    {
        try {
            $sql = "
                SELECT 
                    cc.cuenta_cobrar_id,
                    cc.emision as fecha_emision,
                    cc.vencimiento as fecha_vencimiento,
                    DATE_FORMAT(cc.vencimiento, '%Y-%m-%d') as fecha_vencimiento_formatted,
                    cc.estado,
                    v.venta_id,
                    v.referencia,
                    v.monto_total,
                    v.saldo_pendiente,
                    c.nombre_cliente,
                    c.cliente_ced,
                    c.telefono,
                    cr.credito_id,
                    cr.referencia_credito,
                    DATEDIFF(cc.vencimiento, NOW()) as dias_restantes,
                    CASE 
                        WHEN cc.estado = 'pagado' THEN 'Pagado'
                        WHEN cc.vencimiento < NOW() AND cc.estado = 'pendiente' THEN 'Vencido'
                        WHEN DATEDIFF(cc.vencimiento, NOW()) <= 3 AND DATEDIFF(cc.vencimiento, NOW()) >= 0 THEN 'Por vencer'
                        ELSE 'Vigente'
                    END as estado_visual
                FROM cuentas_cobrar cc
                INNER JOIN credito cr ON cc.credito_id = cr.credito_id
                INNER JOIN ventas v ON cr.venta_id = v.venta_id
                INNER JOIN clientes c ON v.cliente_ced = c.cliente_ced
                WHERE v.estado_venta != 'cancelada'
                  AND cc.estado != 'eliminado'
                ORDER BY cc.vencimiento ASC, cc.cuenta_cobrar_id DESC
            ";
            
            $stmt = $this->db->query($sql);
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log("Error en getAll AccountsReceivable: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una cuenta por cobrar específica con todos sus detalles
     */
    public function getById($cuentaId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    cc.*,
                    v.venta_id,
                    v.referencia,
                    v.monto_total,
                    v.monto_subtotal,
                    v.monto_iva,
                    v.saldo_pendiente,
                    v.fecha as fecha_venta,
                    v.observaciones as observaciones_venta,
                    c.nombre_cliente,
                    c.cliente_ced,
                    c.telefono,
                    c.correo,
                    c.tipo as tipo_cliente,
                    e.nombre as nombre_empleado,
                    cr.credito_id,
                    cr.referencia_credito,
                    DATEDIFF(cc.vencimiento, NOW()) as dias_restantes
                FROM cuentas_cobrar cc
                INNER JOIN credito cr ON cc.credito_id = cr.credito_id
                INNER JOIN ventas v ON cr.venta_id = v.venta_id
                INNER JOIN clientes c ON v.cliente_ced = c.cliente_ced
                LEFT JOIN empleados e ON v.empleado_ced = e.empleado_ced
                WHERE cc.cuenta_cobrar_id = :id
            ");
            
            $stmt->execute([':id' => $cuentaId]);
            $cuenta = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($cuenta) {
                // Obtener historial de pagos
                $cuenta['pagos'] = $this->getPaymentsByAccount($cuentaId);
                $cuenta['total_pagado'] = array_sum(array_column($cuenta['pagos'], 'monto'));
            }
            
            return $cuenta ?: null;
        } catch (Exception $e) {
            error_log("Error en getById AccountsReceivable: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene cuentas por cobrar de un cliente específico
     */
    public function getByClient($clienteCed)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    cc.*,
                    v.referencia,
                    v.monto_total,
                    v.saldo_pendiente,
                    DATEDIFF(cc.vencimiento, NOW()) as dias_restantes
                FROM cuentas_cobrar cc
                INNER JOIN credito cr ON cc.credito_id = cr.credito_id
                INNER JOIN ventas v ON cr.venta_id = v.venta_id
                WHERE v.cliente_ced = :cedula
                  AND v.estado_venta != 'cancelada'
                  AND cc.estado != 'eliminado'
                ORDER BY cc.vencimiento ASC
            ");
            
            $stmt->execute([':cedula' => $clienteCed]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getByClient: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene historial de pagos de una cuenta por cobrar
     */
    public function getPaymentsByAccount($cuentaId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.pago_id,
                    p.fecha_pago,
                    p.monto,
                    p.tipo_pago,
                    p.moneda_pago,
                    p.referencia_bancaria,
                    p.banco,
                    p.estado_pago,
                    p.observaciones
                FROM pagos p
                INNER JOIN credito cr ON p.credito_id = cr.credito_id
                INNER JOIN cuentas_cobrar cc ON cr.credito_id = cc.credito_id
                WHERE cc.cuenta_cobrar_id = :id
                  AND p.estado_pago = 'CONFIRMADO'
                ORDER BY p.fecha_pago DESC
            ");
            
            $stmt->execute([':id' => $cuentaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getPaymentsByAccount: " . $e->getMessage());
            return [];
        }
    }

    /* =====================================================
       REGISTRO DE PAGOS
    ===================================================== */

    /**
     * Registra un pago para una cuenta por cobrar
     */
    public function registerPayment($data)
    {
        try {
            $this->db->beginTransaction();

            // Validar cuenta
            $cuenta = $this->getById($data['cuenta_cobrar_id']);
            if (!$cuenta) {
                throw new Exception("Cuenta por cobrar no encontrada");
            }

            if ($cuenta['estado'] === 'pagado') {
                throw new Exception("Esta cuenta ya está completamente pagada");
            }

            if ($cuenta['estado'] === 'vencido') {
                throw new Exception("No se pueden registrar pagos en cuentas vencidas");
            }

            // Validar monto
            $monto = floatval($data['monto']);
            $saldoPendiente = floatval($cuenta['saldo_pendiente']);
            
            if ($monto <= 0) {
                throw new Exception("El monto debe ser mayor a cero");
            }

            if ($monto > $saldoPendiente) {
                throw new Exception("El monto ($" . number_format($monto, 2) . ") excede el saldo pendiente ($" . number_format($saldoPendiente, 2) . ")");
            }

            // Insertar pago
            $stmt = $this->db->prepare("
                INSERT INTO pagos (
                    venta_id, credito_id, monto, tipo_pago, moneda_pago,
                    referencia_bancaria, banco, estado_pago, observaciones
                ) VALUES (
                    :venta_id, :credito_id, :monto, :tipo_pago, :moneda,
                    :referencia, :banco, 'CONFIRMADO', :obs
                )
            ");
            
            $stmt->execute([
                ':venta_id' => $cuenta['venta_id'],
                ':credito_id' => $cuenta['credito_id'],
                ':monto' => $monto,
                ':tipo_pago' => $data['tipo_pago'] ?? 'EFECTIVO',
                ':moneda' => $data['moneda_pago'] ?? 'BS',
                ':referencia' => $data['referencia_bancaria'] ?? null,
                ':banco' => $data['banco'] ?? null,
                ':obs' => $data['observaciones'] ?? null
            ]);

            $pagoId = $this->db->lastInsertId();

            // Actualizar saldo en venta
            $nuevoSaldo = max(0, $saldoPendiente - $monto);
            $this->db->prepare("
                UPDATE ventas 
                SET saldo_pendiente = :saldo,
                    estado_venta = IF(:saldo <= 0, 'completada', estado_venta),
                    fec_actualizacion = NOW()
                WHERE venta_id = :venta_id
            ")->execute([
                ':saldo' => $nuevoSaldo,
                ':venta_id' => $cuenta['venta_id']
            ]);

            // Actualizar estado de cuenta por cobrar
            if ($nuevoSaldo <= 0) {
                $this->db->prepare("
                    UPDATE cuentas_cobrar 
                    SET estado = 'pagado',
                        fec_actualizacion = NOW()
                    WHERE cuenta_cobrar_id = :id
                ")->execute([':id' => $data['cuenta_cobrar_id']]);
            }

            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Pago registrado correctamente',
                'pago_id' => $pagoId,
                'nuevo_saldo' => $nuevoSaldo
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en registerPayment: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /* =====================================================
       GESTIÓN DE VENCIMIENTOS
    ===================================================== */

    /**
     * Actualiza el vencimiento de una cuenta por cobrar
     */
    public function updateDueDate($cuentaId, $nuevaFecha)
    {
        try {
            $this->db->beginTransaction();

            // Validar que la fecha sea futura
            $fecha = new \DateTime($nuevaFecha);
            $hoy = new \DateTime();
            
            if ($fecha <= $hoy) {
                throw new Exception("La fecha de vencimiento debe ser posterior a hoy");
            }

            $stmt = $this->db->prepare("
                UPDATE cuentas_cobrar 
                SET vencimiento = :fecha,
                    fec_actualizacion = NOW()
                WHERE cuenta_cobrar_id = :id
                  AND estado IN ('pendiente', 'vencido')
            ");
            
            $result = $stmt->execute([
                ':fecha' => $nuevaFecha,
                ':id' => $cuentaId
            ]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("No se pudo actualizar la fecha (verifica que la cuenta esté pendiente)");
            }

            // Si estaba vencida, cambiar estado a pendiente
            $this->db->prepare("
                UPDATE cuentas_cobrar 
                SET estado = 'pendiente'
                WHERE cuenta_cobrar_id = :id 
                  AND estado = 'vencido'
            ")->execute([':id' => $cuentaId]);

            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Fecha de vencimiento actualizada'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Procesa cuentas vencidas (debe ejecutarse diariamente)
     */
    public function processExpiredAccounts()
    {
        try {
            $this->db->beginTransaction();

            // Marcar cuentas como vencidas
            $this->db->query("
                UPDATE cuentas_cobrar 
                SET estado = 'vencido',
                    fec_actualizacion = NOW()
                WHERE vencimiento < NOW()
                  AND estado = 'pendiente'
            ");

            // Anular ventas con cuentas vencidas
            $this->db->query("
                UPDATE ventas v
                INNER JOIN credito cr ON v.venta_id = cr.venta_id
                INNER JOIN cuentas_cobrar cc ON cr.credito_id = cc.credito_id
                SET v.estado_venta = 'cancelada',
                    v.saldo_pendiente = 0,
                    v.fec_actualizacion = NOW()
                WHERE cc.estado = 'vencido'
                  AND v.estado_venta = 'pendiente'
            ");

            // Liberar prendas de ventas anuladas
            $this->db->query("
                UPDATE prendas p
                INNER JOIN detalle_venta dv ON p.codigo_prenda = dv.codigo_prenda
                INNER JOIN ventas v ON dv.venta_id = v.venta_id
                SET p.estado = 'DISPONIBLE'
                WHERE v.estado_venta = 'cancelada'
                  AND p.estado = 'VENDIDA'
            ");

            $affected = $this->db->query("
                SELECT COUNT(*) as total 
                FROM cuentas_cobrar 
                WHERE estado = 'vencido'
            ")->fetch(PDO::FETCH_ASSOC)['total'];

            $this->db->commit();
            
            return [
                'success' => true,
                'message' => "Se procesaron {$affected} cuentas vencidas",
                'affected' => $affected
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en processExpiredAccounts: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /* =====================================================
       ELIMINACIÓN LÓGICA
    ===================================================== */

    /**
     * Elimina lógicamente una cuenta por cobrar
     */
    public function delete($cuentaId)
    {
        try {
            $this->db->beginTransaction();

            // Verificar que la cuenta existe y está pendiente
            $cuenta = $this->getById($cuentaId);
            if (!$cuenta) {
                throw new Exception("Cuenta por cobrar no encontrada");
            }

            if ($cuenta['estado'] === 'pagado') {
                throw new Exception("No se puede eliminar una cuenta ya pagada");
            }

            // Marcar cuenta como eliminada
            $this->db->prepare("
                UPDATE cuentas_cobrar 
                SET estado = 'eliminado',
                    fec_actualizacion = NOW()
                WHERE cuenta_cobrar_id = :id
            ")->execute([':id' => $cuentaId]);

            // Anular venta relacionada
            $this->db->prepare("
                UPDATE ventas 
                SET estado_venta = 'cancelada',
                    saldo_pendiente = 0,
                    fec_actualizacion = NOW()
                WHERE venta_id = :venta_id
            ")->execute([':venta_id' => $cuenta['venta_id']]);

            // Liberar prendas
            $this->db->prepare("
                UPDATE prendas p
                INNER JOIN detalle_venta dv ON p.codigo_prenda = dv.codigo_prenda
                SET p.estado = 'DISPONIBLE'
                WHERE dv.venta_id = :venta_id
                  AND p.estado = 'VENDIDA'
            ")->execute([':venta_id' => $cuenta['venta_id']]);

            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Cuenta por cobrar eliminada correctamente'
            ];

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error en delete AccountsReceivable: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /* =====================================================
       ESTADÍSTICAS
    ===================================================== */

    /**
     * Obtiene estadísticas generales de cuentas por cobrar
     */
    public function getStats()
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total_cuentas,
                    SUM(CASE WHEN cc.estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN cc.estado = 'pagado' THEN 1 ELSE 0 END) as pagadas,
                    SUM(CASE WHEN cc.estado = 'vencido' THEN 1 ELSE 0 END) as vencidas,
                    SUM(v.saldo_pendiente) as saldo_total,
                    SUM(CASE WHEN DATEDIFF(cc.vencimiento, NOW()) <= 3 
                        AND cc.estado = 'pendiente' THEN v.saldo_pendiente ELSE 0 END) as por_vencer
                FROM cuentas_cobrar cc
                INNER JOIN credito cr ON cc.credito_id = cr.credito_id
                INNER JOIN ventas v ON cr.venta_id = v.venta_id
                WHERE v.estado_venta != 'cancelada'
                  AND cc.estado != 'eliminado'
            ");
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error en getStats: " . $e->getMessage());
            return null;
        }
    }
}