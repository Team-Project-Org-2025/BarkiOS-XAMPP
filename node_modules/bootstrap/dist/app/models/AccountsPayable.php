<?php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;


class AccountsPayable extends Database {
    
    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    cp.*,
                    c.factura_numero,
                    c.fecha_compra,
                    c.tracking,
                    p.nombre_empresa as nombre_proveedor,
                    p.nombre_contacto,
                    p.tipo_rif,
                    COALESCE(
                        (SELECT SUM(pc.monto) 
                         FROM pagos_compras pc 
                         WHERE pc.cuenta_pagar_id = cp.cuenta_pagar_id 
                         AND pc.estado_pago = 'CONFIRMADO'),
                        0
                    ) as total_pagado,
                    (cp.monto - COALESCE(
                        (SELECT SUM(pc.monto) 
                         FROM pagos_compras pc 
                         WHERE pc.cuenta_pagar_id = cp.cuenta_pagar_id 
                         AND pc.estado_pago = 'CONFIRMADO'),
                        0
                    )) as saldo_pendiente,
                    CASE 
                        WHEN cp.fecha_vencimiento < CURDATE() 
                            AND cp.estado = 'pendiente' THEN 1
                        ELSE 0
                    END as vencida,
                    c.monto_total
                FROM cuentas_pagar cp
                JOIN compras c ON cp.compra_id = c.compra_id
                JOIN proveedores p ON cp.proveedor_rif = p.proveedor_rif
                WHERE c.activo = 1
                ORDER BY 
                    CASE 
                        WHEN cp.fecha_vencimiento < CURDATE() AND cp.estado = 'pendiente' THEN 1
                        WHEN cp.estado = 'pendiente' THEN 2
                        ELSE 3
                    END,
                    cp.fecha_vencimiento ASC
            ");
            
            $cuentas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            

            foreach ($cuentas as &$cuenta) {
                if ($cuenta['vencida'] == 1 && $cuenta['estado'] == 'pendiente') {
                    $this->updateEstado($cuenta['cuenta_pagar_id'], 'vencido');
                    $cuenta['estado'] = 'vencido';
                }
                
                $saldo = floatval($cuenta['saldo_pendiente']);
                if ($saldo <= 0 && $cuenta['estado'] != 'pagado') {
                    $this->updateEstado($cuenta['cuenta_pagar_id'], 'pagado');
                    $cuenta['estado'] = 'pagado';
                }
            }
            
            return $cuentas;
        } catch (\Throwable $e) {
            error_log('Error en AccountsPayable::getAll - ' . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    cp.*,
                    c.factura_numero,
                    c.fecha_compra,
                    c.tracking,
                    c.monto_total,
                    c.observaciones as observaciones_compra,
                    p.nombre_empresa as nombre_proveedor,
                    p.nombre_contacto,
                    p.direccion as direccion_proveedor,
                    p.telefono as telefono_proveedor,
                    p.correo as correo_proveedor,
                    p.tipo_rif,
                    COALESCE(
                        (SELECT SUM(pc.monto) 
                         FROM pagos_compras pc 
                         WHERE pc.cuenta_pagar_id = cp.cuenta_pagar_id 
                         AND pc.estado_pago = 'CONFIRMADO'),
                        0
                    ) as total_pagado,
                    (cp.monto - COALESCE(
                        (SELECT SUM(pc.monto) 
                         FROM pagos_compras pc 
                         WHERE pc.cuenta_pagar_id = cp.cuenta_pagar_id 
                         AND pc.estado_pago = 'CONFIRMADO'),
                        0
                    )) as saldo_pendiente
                FROM cuentas_pagar cp
                JOIN compras c ON cp.compra_id = c.compra_id
                JOIN proveedores p ON cp.proveedor_rif = p.proveedor_rif
                WHERE cp.cuenta_pagar_id = :id
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('Error en AccountsPayable::getById - ' . $e->getMessage());
            return null;
        }
    }

    public function getPagosByCuentaId($cuentaId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    pc.*
                FROM pagos_compras pc
                WHERE pc.cuenta_pagar_id = :cuenta_id
                AND pc.estado_pago != 'ANULADO'
                ORDER BY pc.fecha_pago DESC
            ");
            $stmt->execute([':cuenta_id' => $cuentaId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Error en AccountsPayable::getPagosByCuentaId - ' . $e->getMessage());
            return [];
        }
    }

    public function addPago($datos) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                INSERT INTO pagos_compras (
                    cuenta_pagar_id, compra_id, fecha_pago, monto, 
                    tipo_pago, moneda_pago, referencia_bancaria, 
                    banco, estado_pago, observaciones
                )
                VALUES (
                    :cuenta_pagar_id, :compra_id, :fecha_pago, :monto,
                    :tipo_pago, :moneda_pago, :referencia_bancaria,
                    :banco, :estado_pago, :observaciones
                )
            ");

            $result = $stmt->execute([
                ':cuenta_pagar_id' => $datos['cuenta_pagar_id'],
                ':compra_id' => $datos['compra_id'],
                ':fecha_pago' => $datos['fecha_pago'],
                ':monto' => $datos['monto'],
                ':tipo_pago' => $datos['tipo_pago'],
                ':moneda_pago' => $datos['moneda_pago'],
                ':referencia_bancaria' => $datos['referencia_bancaria'] ?? null,
                ':banco' => $datos['banco'] ?? null,
                ':estado_pago' => $datos['estado_pago'] ?? 'CONFIRMADO',
                ':observaciones' => $datos['observaciones'] ?? null
            ]);

            if (!$result) {
                throw new Exception('Error al insertar el pago');
            }

            $pagoId = $this->db->lastInsertId();

            $cuenta = $this->getById($datos['cuenta_pagar_id']);
            $saldoPendiente = floatval($cuenta['saldo_pendiente']) - floatval($datos['monto']);

            if ($saldoPendiente <= 0) {

                $this->updateEstado($datos['cuenta_pagar_id'], 'pagado');
            }

            $this->db->commit();
            return $pagoId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('Error en AccountsPayable::addPago - ' . $e->getMessage());
            throw new Exception('Error al registrar el pago: ' . $e->getMessage());
        }
    }

    public function updateEstado($cuentaId, $nuevoEstado) {
        try {
            $stmt = $this->db->prepare("
                UPDATE cuentas_pagar
                SET estado = :estado,
                    fec_actualizacion = CURRENT_TIMESTAMP
                WHERE cuenta_pagar_id = :id
            ");
            return $stmt->execute([
                ':id' => $cuentaId,
                ':estado' => $nuevoEstado
            ]);
        } catch (\Throwable $e) {
            error_log('Error en AccountsPayable::updateEstado - ' . $e->getMessage());
            return false;
        }
    }

    public function anularPago($pagoId) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("
                SELECT cuenta_pagar_id, monto 
                FROM pagos_compras 
                WHERE pago_compra_id = :id
            ");
            $stmt->execute([':id' => $pagoId]);
            $pago = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$pago) {
                throw new Exception('Pago no encontrado');
            }

            $stmt = $this->db->prepare("
                UPDATE pagos_compras
                SET estado_pago = 'ANULADO',
                    fec_actualizacion = CURRENT_TIMESTAMP
                WHERE pago_compra_id = :id
            ");
            $stmt->execute([':id' => $pagoId]);

            $cuenta = $this->getById($pago['cuenta_pagar_id']);
            $saldoPendiente = floatval($cuenta['saldo_pendiente']) + floatval($pago['monto']);

            if ($saldoPendiente > 0 && $cuenta['estado'] == 'pagado') {

                $estado = strtotime($cuenta['fecha_vencimiento']) < time() ? 'vencido' : 'pendiente';
                $this->updateEstado($pago['cuenta_pagar_id'], $estado);
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('Error en AccountsPayable::anularPago - ' . $e->getMessage());
            throw new Exception('Error al anular el pago: ' . $e->getMessage());
        }
    }

    public function getEstadisticas() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(DISTINCT cp.cuenta_pagar_id) as total_cuentas,
                    SUM(cp.monto) as deuda_original,
                    SUM(
                        cp.monto - COALESCE(
                            (SELECT SUM(pc.monto) 
                             FROM pagos_compras pc 
                             WHERE pc.cuenta_pagar_id = cp.cuenta_pagar_id 
                             AND pc.estado_pago = 'CONFIRMADO'),
                            0
                        )
                    ) as deuda_pendiente,
                    COUNT(DISTINCT CASE WHEN cp.estado = 'vencido' THEN cp.cuenta_pagar_id END) as cuentas_vencidas,
                    COUNT(DISTINCT CASE 
                        WHEN cp.estado = 'pendiente' 
                        AND cp.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                        THEN cp.cuenta_pagar_id 
                    END) as por_vencer_7dias
                FROM cuentas_pagar cp
                JOIN compras c ON cp.compra_id = c.compra_id
                WHERE c.activo = 1
            ");
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Error en AccountsPayable::getEstadisticas - ' . $e->getMessage());
            return [];
        }
    }
}