<?php
namespace Barkios\models;

use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo de Compras - Versión Simplificada
 * Gestiona compras de ropa exclusiva (cada prenda es única)
 */
class Purchase extends Database
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ==========================================================
       🔹 OBTENER TODAS LAS COMPRAS
    ========================================================== */
    public function getAll()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM vista_compras ORDER BY fecha_compra DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("Error al obtener compras: " . $e->getMessage());
            return [];
        }
    }

    /* ==========================================================
       🔹 OBTENER COMPRA POR ID CON PRENDAS
    ========================================================== */
    public function getById($compraId)
    {
        try {
            // Obtener información de la compra
            $stmt = $this->db->prepare("
                SELECT 
                    c.*,
                    p.nombre_empresa,
                    p.nombre_contacto,
                    p.direccion AS direccion_proveedor
                FROM compras c
                INNER JOIN proveedores p ON c.proveedor_id = p.id
                WHERE c.compra_id = :id AND c.activo = 1
            ");
            $stmt->execute([':id' => $compraId]);
            $compra = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$compra) {
                return null;
            }

            // Obtener prendas compradas
            $stmt = $this->db->prepare("
                SELECT * FROM prendas_compradas WHERE compra_id = :id ORDER BY categoria, producto_nombre
            ");
            $stmt->execute([':id' => $compraId]);
            $compra['prendas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $compra;
        } catch (\Throwable $e) {
            error_log("Error al obtener compra #$compraId: " . $e->getMessage());
            return null;
        }
    }

    /* ==========================================================
       🔹 OBTENER COMPRAS POR PROVEEDOR
    ========================================================== */
    public function getByProveedor($proveedorId)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM vista_compras 
                WHERE proveedor_id = :proveedor_id 
                ORDER BY fecha_compra DESC
            ");
            $stmt->execute([':proveedor_id' => $proveedorId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("Error al obtener compras del proveedor: " . $e->getMessage());
            return [];
        }
    }

    /* ==========================================================
       🔹 AGREGAR NUEVA COMPRA
    ========================================================== */
    public function add($data)
    {
        try {
            $this->db->beginTransaction();

            // Validar que exista el proveedor
            if (!$this->proveedorExists($data['proveedor_id'])) {
                throw new Exception("El proveedor no existe");
            }

            // Validar que la factura no esté duplicada
            if ($this->facturaExists($data['factura_numero'])) {
                throw new Exception("Ya existe una compra con este número de factura");
            }

            // Validar prendas
            if (empty($data['prendas']) || !is_array($data['prendas'])) {
                throw new Exception("Debe agregar al menos una prenda");
            }

            // Insertar compra (estructura completa con nuevos campos)
            $stmt = $this->db->prepare("
                INSERT INTO compras (
                    proveedor_id, factura_numero, fecha_compra, tracking, 
                    referencia, telefono, metodo_pago, direccion, monto_total
                ) VALUES (
                    :proveedor_id, :factura_numero, :fecha_compra, :tracking,
                    :referencia, :telefono, :metodo_pago, :direccion, :monto_total
                )
            ");

            $stmt->execute([
                ':proveedor_id' => $data['proveedor_id'],
                ':factura_numero' => $data['factura_numero'],
                ':fecha_compra' => $data['fecha_compra'] ?? date('Y-m-d'),
                ':tracking' => $data['tracking'] ?? null,
                ':referencia' => $data['referencia'] ?? null,
                ':telefono' => $data['telefono'] ?? null,
                ':metodo_pago' => $data['metodo_pago'] ?? null,
                ':direccion' => $data['direccion'] ?? null,
                ':monto_total' => $data['monto_total']
            ]);

            $compraId = $this->db->lastInsertId();

            // Insertar cada prenda única
            $stmtPrenda = $this->db->prepare("
                INSERT INTO prendas_compradas (
                    compra_id, producto_nombre, categoria, precio_costo
                ) VALUES (
                    :compra_id, :producto_nombre, :categoria, :precio_costo
                )
            ");

            foreach ($data['prendas'] as $prenda) {
                $stmtPrenda->execute([
                    ':compra_id' => $compraId,
                    ':producto_nombre' => $prenda['producto_nombre'],
                    ':categoria' => $prenda['categoria'],
                    ':precio_costo' => $prenda['precio_costo']
                ]);
            }

            $this->db->commit();
            return $compraId;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log("Error al agregar compra: " . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /* ==========================================================
       🔹 MARCAR PDF COMO GENERADO
    ========================================================== */
    public function markPdfGenerated($compraId)
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE compras SET
                    pdf_generado = 1,
                    updated_at = CURRENT_TIMESTAMP
                WHERE compra_id = :compra_id AND activo = 1
            ");

            return $stmt->execute([':compra_id' => $compraId]);
        } catch (\Throwable $e) {
            error_log("Error al marcar PDF: " . $e->getMessage());
            return false;
        }
    }

    /* ==========================================================
       🔹 CANCELAR COMPRA (eliminación lógica)
    ========================================================== */
    public function delete($compraId)
    {
        try {
            // Eliminación lógica
            $stmt = $this->db->prepare("
                UPDATE compras 
                SET activo = 0, updated_at = CURRENT_TIMESTAMP
                WHERE compra_id = :id
            ");
            
            return $stmt->execute([':id' => $compraId]);
        } catch (\Throwable $e) {
            error_log("Error al eliminar compra: " . $e->getMessage());
            return false;
        }
    }

    /* ==========================================================
       🔹 OBTENER ESTADÍSTICAS
    ========================================================== */
    public function getEstadisticas()
    {
        try {
            $stmt = $this->db->query("CALL sp_estadisticas_compras()");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }

    /* ==========================================================
       🔹 VERIFICAR SI EXISTE UN PROVEEDOR
    ========================================================== */
    private function proveedorExists($proveedorId)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM proveedores 
            WHERE id = :id AND activo = 1
        ");
        $stmt->execute([':id' => $proveedorId]);
        return $stmt->fetchColumn() > 0;
    }

    /* ==========================================================
       🔹 VERIFICAR SI EXISTE UNA FACTURA
    ========================================================== */
    private function facturaExists($facturaNumero)
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM compras 
            WHERE factura_numero = :factura AND activo = 1
        ");
        $stmt->execute([':factura' => $facturaNumero]);
        return $stmt->fetchColumn() > 0;
    }

    /* ==========================================================
       🔹 OBTENER CATEGORÍAS DE PRODUCTOS
    ========================================================== */
    public function getCategorias()
    {
        try {
            // Intentar obtener de tabla prendas si existe
            $stmt = $this->db->query("
                SELECT DISTINCT categoria 
                FROM prendas 
                WHERE activo = 1 
                ORDER BY categoria ASC
            ");
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            // Si no hay categorías, retornar algunas por defecto
            if (empty($result)) {
                return ['Pantalones', 'Camisas', 'Vestidos', 'Zapatos', 'Accesorios', 'Chaquetas', 'Faldas'];
            }
            
            return $result;
        } catch (\Throwable $e) {
            // Si la tabla no existe, retornar categorías por defecto
            return ['Pantalones', 'Camisas', 'Vestidos', 'Zapatos', 'Accesorios', 'Chaquetas', 'Faldas'];
        }
    }

    /* ==========================================================
       🔹 OBTENER TODOS LOS PROVEEDORES ACTIVOS
    ========================================================== */
    public function getAllProveedores()
    {
        try {
            $stmt = $this->db->query("
                SELECT id, nombre_empresa, nombre_contacto, direccion
                FROM proveedores 
                WHERE activo = 1 
                ORDER BY nombre_empresa ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("Error al obtener proveedores: " . $e->getMessage());
            return [];
        }
    }

    /* ==========================================================
       🔹 BUSCAR PROVEEDORES
    ========================================================== */
    public function searchProveedores($search)
    {
        try {
            $searchTerm = "%{$search}%";
            $stmt = $this->db->prepare("
                SELECT id, nombre_empresa, nombre_contacto, rif, direccion
                FROM proveedores 
                WHERE activo = 1 
                  AND (nombre_empresa LIKE :search 
                       OR nombre_contacto LIKE :search 
                       OR id LIKE :search)
                ORDER BY nombre_empresa ASC
                LIMIT 10
            ");
            $stmt->execute([':search' => $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("Error al buscar proveedores: " . $e->getMessage());
            return [];
        }
    }
}
