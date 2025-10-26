<?php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo Compras
 *
 * Gestiona las compras de ropa exclusiva con múltiples prendas por compra.
 * Cada prenda tiene su precio individual y se puede agregar dinámicamente.
 */
class Purchase extends Database {
    /**
     * Obtiene todas las compras activas con información del proveedor.
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT c.*,
                       p.nombre_empresa as nombre_proveedor,
                       p.nombre_contacto,
                       COUNT(pc.prenda_comprada_id) as total_prendas
                FROM compras c
                JOIN proveedores p ON c.proveedor_rif = p.proveedor_rif
                LEFT JOIN prendas_compradas pc ON c.compra_id = pc.compra_id
                WHERE c.activo = 1
                GROUP BY c.compra_id
                ORDER BY c.fecha_compra DESC
            ");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log('Error en Purchase::getAll - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una compra específica por su ID.
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*,
                       p.nombre_empresa as nombre_proveedor,
                       p.nombre_contacto,
                       p.direccion as direccion_proveedor
                FROM compras c
                JOIN proveedores p ON c.proveedor_rif = p.proveedor_rif
                WHERE c.compra_id = :id AND c.activo = 1
            ");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log('Error en Purchase::getById - ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene las prendas de una compra específica.
     */
    public function getPrendasByCompraId($compraId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM prendas_compradas
                WHERE compra_id = :compra_id
                ORDER BY categoria, producto_nombre
            ");
            $stmt->execute([':compra_id' => $compraId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Error en Purchase::getPrendasByCompraId - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Agrega una nueva compra con sus prendas.
     */
    public function add($datos) {
        try {
            $this->db->beginTransaction();

            // Insertar compra principal
            $stmt = $this->db->prepare("
                INSERT INTO compras (proveedor_rif, factura_numero, fecha_compra, tracking, monto_total, pdf_generado)
                VALUES (:proveedor_rif, :factura_numero, :fecha_compra, :tracking, :monto_total, 0)
            ");

            $result = $stmt->execute([
                ':proveedor_rif' => $datos['proveedor_rif'],
                ':factura_numero' => $datos['factura_numero'],
                ':fecha_compra' => $datos['fecha_compra'],
                ':tracking' => $datos['tracking'] ?? '',
                ':monto_total' => $datos['monto_total']
            ]);

            if (!$result) {
                throw new Exception('Error al insertar la compra');
            }

            $compraId = $this->db->lastInsertId();

            // Insertar prendas si existen
            if (isset($datos['prendas']) && is_array($datos['prendas'])) {
                foreach ($datos['prendas'] as $prenda) {
                    if (!empty($prenda['producto_nombre']) && !empty($prenda['categoria']) && !empty($prenda['precio_costo'])) {
                        $stmt = $this->db->prepare("
                            INSERT INTO prendas_compradas (compra_id, producto_nombre, categoria, precio_costo)
                            VALUES (:compra_id, :producto_nombre, :categoria, :precio_costo)
                        ");

                        $stmt->execute([
                            ':compra_id' => $compraId,
                            ':producto_nombre' => $prenda['producto_nombre'],
                            ':categoria' => $prenda['categoria'],
                            ':precio_costo' => $prenda['precio_costo']
                        ]);
                    }
                }
            }

            $this->db->commit();
            return $compraId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('Error en Purchase::add - ' . $e->getMessage());
            throw new Exception('Error al agregar la compra: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza una compra existente.
     */
    public function update($id, $datos) {
        try {
            $this->db->beginTransaction();

            // Actualizar compra principal
            $stmt = $this->db->prepare("
                UPDATE compras
                SET proveedor_rif = :proveedor_rif,
                    factura_numero = :factura_numero,
                    fecha_compra = :fecha_compra,
                    tracking = :tracking,
                    monto_total = :monto_total,
                    updated_at = CURRENT_TIMESTAMP
                WHERE compra_id = :id
            ");

            $result = $stmt->execute([
                ':id' => $id,
                ':proveedor_rif' => $datos['proveedor_rif'],
                ':factura_numero' => $datos['factura_numero'],
                ':fecha_compra' => $datos['fecha_compra'],
                ':tracking' => $datos['tracking'] ?? '',
                ':monto_total' => $datos['monto_total']
            ]);

            if (!$result) {
                throw new Exception('Error al actualizar la compra');
            }

            // Eliminar prendas existentes
            $stmt = $this->db->prepare("DELETE FROM prendas_compradas WHERE compra_id = :compra_id");
            $stmt->execute([':compra_id' => $id]);

            // Insertar nuevas prendas
            if (isset($datos['prendas']) && is_array($datos['prendas'])) {
                foreach ($datos['prendas'] as $prenda) {
                    if (!empty($prenda['producto_nombre']) && !empty($prenda['categoria']) && !empty($prenda['precio_costo'])) {
                        $stmt = $this->db->prepare("
                            INSERT INTO prendas_compradas (compra_id, producto_nombre, categoria, precio_costo)
                            VALUES (:compra_id, :producto_nombre, :categoria, :precio_costo)
                        ");

                        $stmt->execute([
                            ':compra_id' => $id,
                            ':producto_nombre' => $prenda['producto_nombre'],
                            ':categoria' => $prenda['categoria'],
                            ':precio_costo' => $prenda['precio_costo']
                        ]);
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('Error en Purchase::update - ' . $e->getMessage());
            throw new Exception('Error al actualizar la compra: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una compra (soft delete).
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE compras SET activo = 0, updated_at = CURRENT_TIMESTAMP
                WHERE compra_id = :id
            ");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('Error en Purchase::delete - ' . $e->getMessage());
            throw new Exception('Error al eliminar la compra: ' . $e->getMessage());
        }
    }

    /**
     * Marca el PDF como generado.
     */
    public function markPdfGenerated($id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE compras SET pdf_generado = 1, updated_at = CURRENT_TIMESTAMP
                WHERE compra_id = :id
            ");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('Error en Purchase::markPdfGenerated - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene estadísticas de compras.
     */
    public function getEstadisticas() {
        try {
            $stmt = $this->db->query("CALL sp_estadisticas_compras()");
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Error en Purchase::getEstadisticas - ' . $e->getMessage());
            return [];
        }
    }
}