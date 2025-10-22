<?php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo Cuentas por Pagar
 * 
 * Proporciona métodos para gestionar cuentas_pagar en la base de datos.
 * Representa el crédito que los distribuidores/proveedores otorgan a la empresa.
 * Este módulo concatena información de compras y proveedores.
 */
class AccountsPayable extends Database {
    /**
     * Obtiene todas las cuentas por pagar activas con información del proveedor.
     * 
     * @return array Lista de cuentas por pagar (cada una es un array asociativo).
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT cp.*, 
                       p.nombre_empresa as nombre_proveedor,
                       p.nombre_contacto
                FROM cuentas_pagar cp
                JOIN proveedores p ON cp.proveedor_id = p.id
                WHERE cp.activo = 1 
                ORDER BY cp.fecha_vencimiento ASC
            ");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log('Error en AccountsPayable::getAll - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una cuenta por pagar específica por su ID.
     * 
     * @param int $id ID de la cuenta por pagar.
     * @return array|null Array asociativo con los datos o null si no existe.
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT cp.*, 
                       p.nombre_empresa as nombre_proveedor,
                       p.nombre_contacto
                FROM cuentas_pagar cp
                JOIN proveedores p ON cp.proveedor_id = p.id
                WHERE cp.id = :id AND cp.activo = 1
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('Error en AccountsPayable::getById - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene cuentas por pagar de un proveedor específico.
     * 
     * @param string $proveedorId ID del proveedor.
     * @return array Lista de cuentas por pagar del proveedor.
     */
    public function getByProveedor($proveedorId) {
        try {
            $stmt = $this->db->prepare("
                SELECT cp.*, 
                       p.nombre_empresa as nombre_proveedor
                FROM cuentas_pagar cp
                JOIN proveedores p ON cp.proveedor_id = p.id
                WHERE cp.proveedor_id = :proveedor_id 
                  AND cp.activo = 1
                ORDER BY cp.fecha_vencimiento ASC
            ");
            $stmt->execute([':proveedor_id' => $proveedorId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Error en AccountsPayable::getByProveedor - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Agrega una nueva cuenta por pagar.
     * 
     * @param array $datos Datos de la cuenta por pagar:
     *   - proveedor_id: ID del proveedor
     *   - factura_numero: Número de factura
     *   - fecha_emision: Fecha de emisión
     *   - fecha_vencimiento: Fecha de vencimiento
     *   - monto_total: Monto total a pagar
     *   - estado: Estado de la cuenta (Pendiente, Pagada, Vencida)
     * @return bool True si se insertó correctamente.
     */
    public function add($datos) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO cuentas_pagar (
                    proveedor_id, factura_numero, fecha_emision, 
                    fecha_vencimiento, monto_total, estado
                ) VALUES (
                    :proveedor_id, :factura_numero, :fecha_emision, 
                    :fecha_vencimiento, :monto_total, :estado
                )
            ");

            return $stmt->execute([
                ':proveedor_id' => $datos['proveedor_id'],
                ':factura_numero' => $datos['factura_numero'],
                ':fecha_emision' => $datos['fecha_emision'],
                ':fecha_vencimiento' => $datos['fecha_vencimiento'],
                ':monto_total' => $datos['monto_total'],
                ':estado' => $datos['estado'] ?? 'Pendiente'
            ]);
        } catch (\Throwable $e) {
            error_log('Error en AccountsPayable::add - ' . $e->getMessage());
            throw new Exception('Error al agregar cuenta por pagar: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza una cuenta por pagar existente.
     * 
     * @param int $id ID de la cuenta por pagar.
     * @param array $datos Datos a actualizar.
     * @return bool True si se actualizó correctamente.
     */
    public function update($id, $datos) {
        try {
            $stmt = $this->db->prepare("
                UPDATE cuentas_pagar SET 
                    proveedor_id = :proveedor_id,
                    factura_numero = :factura_numero,
                    fecha_emision = :fecha_emision,
                    fecha_vencimiento = :fecha_vencimiento,
                    monto_total = :monto_total,
                    estado = :estado
                WHERE id = :id AND activo = 1
            ");

            $datos['id'] = $id;
            return $stmt->execute($datos);
        } catch (\Throwable $e) {
            error_log('Error en AccountsPayable::update - ' . $e->getMessage());
            throw new Exception('Error al actualizar cuenta por pagar: ' . $e->getMessage());
        }
    }

    /**
     * Registra un pago para una cuenta por pagar.
     * 
     * @param int $id ID de la cuenta por pagar.
     * @param float $montoPagado Monto pagado.
     * @return bool True si se registró correctamente.
     */
    public function registrarPago($id, $montoPagado) {
        try {
            // Obtener cuenta actual
            $cuenta = $this->getById($id);
            if (!$cuenta) {
                throw new Exception('Cuenta por pagar no encontrada');
            }

            // Actualizar estado si el pago cubre el total
            $nuevoEstado = ($montoPagado >= $cuenta['monto_total']) ? 'Pagada' : 'Parcial';
            
            $stmt = $this->db->prepare("
                UPDATE cuentas_pagar 
                SET estado = :estado,
                    fecha_pago = CURRENT_TIMESTAMP
                WHERE id = :id
            ");

            return $stmt->execute([
                ':id' => $id,
                ':estado' => $nuevoEstado
            ]);
        } catch (\Throwable $e) {
            error_log('Error en AccountsPayable::registrarPago - ' . $e->getMessage());
            throw new Exception('Error al registrar pago: ' . $e->getMessage());
        }
    }

    /**
     * Elimina lógicamente una cuenta por pagar (marcándola como inactiva).
     * 
     * @param int $id ID de la cuenta por pagar a eliminar.
     * @return bool True si se eliminó correctamente.
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("UPDATE cuentas_pagar SET activo = 0 WHERE id = :id");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('Error en AccountsPayable::delete - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene estadísticas de cuentas por pagar.
     * 
     * @return array Estadísticas con totales por estado.
     */
    public function getEstadisticas() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total_cuentas,
                    SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'Pagada' THEN 1 ELSE 0 END) as pagadas,
                    SUM(CASE WHEN estado = 'Vencida' THEN 1 ELSE 0 END) as vencidas,
                    SUM(CASE WHEN estado = 'Pendiente' THEN monto_total ELSE 0 END) as total_pendiente,
                    SUM(CASE WHEN estado = 'Pagada' THEN monto_total ELSE 0 END) as total_pagado
                FROM cuentas_pagar
                WHERE activo = 1
            ");
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Error en AccountsPayable::getEstadisticas - ' . $e->getMessage());
            return [];
        }
    }
}
