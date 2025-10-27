<?php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo Compras
 * 
 * Gestiona las compras y crea productos en la tabla prendas
 */
class Purchase extends Database {
    
    public function facturaExiste($numeroFactura)
    {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM compras 
                WHERE factura_numero = :factura_numero 
                  AND activo = 1
            ");
            $stmt->execute([':factura_numero' => $numeroFactura]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en facturaExiste: " . $e->getMessage());
            return false;
        }
    }


    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT c.*,
                       p.nombre_empresa as nombre_proveedor,
                       p.nombre_contacto,
                       COUNT(pr.prenda_id) as total_prendas
                FROM compras c
                JOIN proveedores p ON c.proveedor_rif = p.proveedor_rif
                LEFT JOIN prendas pr ON c.compra_id = pr.compra_id AND pr.activo = 1
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
     * Obtiene una compra específica por su ID
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
     * Obtiene las prendas de una compra específica
     */
    public function getPrendasByCompraId($compraId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    prenda_id,
                    codigo_prenda,
                    nombre,
                    categoria,
                    tipo,
                    precio_compra as precio_costo,
                    precio as precio_venta,
                    descripcion,
                    estado,
                    fecha_creacion
                FROM prendas
                WHERE compra_id = :compra_id AND activo = 1
                ORDER BY categoria, nombre
            ");
            $stmt->execute([':compra_id' => $compraId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Error en Purchase::getPrendasByCompraId - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Agrega una nueva compra con sus prendas
     */
    public function add($datos) {
        try {
            $this->db->beginTransaction();

            // Insertar compra principal
            $stmt = $this->db->prepare("
                INSERT INTO compras (
                    proveedor_rif, factura_numero, fecha_compra, 
                    tracking, monto_total, observaciones, pdf_generado, activo
                )
                VALUES (
                    :proveedor_rif, :factura_numero, :fecha_compra, 
                    :tracking, :monto_total, :observaciones, 0, 1
                )
            ");

            $result = $stmt->execute([
                ':proveedor_rif' => $datos['proveedor_rif'],
                ':factura_numero' => $datos['factura_numero'],
                ':fecha_compra' => $datos['fecha_compra'],
                ':tracking' => $datos['tracking'] ?? '',
                ':monto_total' => $datos['monto_total'],
                ':observaciones' => $datos['observaciones'] ?? ''
            ]);

            if (!$result) {
                throw new Exception('Error al insertar la compra');
            }

            $compraId = $this->db->lastInsertId();

            // Insertar prendas en la tabla prendas
            if (isset($datos['prendas']) && is_array($datos['prendas'])) {
                foreach ($datos['prendas'] as $index => $prenda) {
                    // Generar código único para la prenda
                    // Usar el código proporcionado o generar uno si no existe
                $codigoPrenda = !empty($prenda['codigo_prenda'])
                    ? strtoupper(trim($prenda['codigo_prenda']))
                    : $this->generateCodigoPrenda($compraId, $index);
                                

                    $stmt = $this->db->prepare("
                        INSERT INTO prendas (
                            codigo_prenda, compra_id, nombre, categoria, tipo, 
                            precio, precio_compra, descripcion, estado, activo
                        )
                        VALUES (
                            :codigo_prenda, :compra_id, :nombre, :categoria, :tipo, 
                            :precio, :precio_compra, :descripcion, 'DISPONIBLE', 1
                        )
                    ");

                    $stmt->execute([
                        ':codigo_prenda' => $codigoPrenda,
                        ':compra_id' => $compraId,
                        ':nombre' => $prenda['nombre'],
                        ':categoria' => $prenda['categoria'],
                        ':tipo' => $prenda['tipo'],
                        ':precio' => $prenda['precio_venta'],
                        ':precio_compra' => $prenda['precio_costo'],
                        ':descripcion' => $prenda['descripcion'] ?? ''
                    ]);
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
     * Actualiza una compra existente
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
                    observaciones = :observaciones,
                    fec_actualizacion = CURRENT_TIMESTAMP
                WHERE compra_id = :id
            ");

            $result = $stmt->execute([
                ':id' => $id,
                ':proveedor_rif' => $datos['proveedor_rif'],
                ':factura_numero' => $datos['factura_numero'],
                ':fecha_compra' => $datos['fecha_compra'],
                ':tracking' => $datos['tracking'] ?? '',
                ':monto_total' => $datos['monto_total'],
                ':observaciones' => $datos['observaciones'] ?? ''
            ]);

            if (!$result) {
                throw new Exception('Error al actualizar la compra');
            }

            // Desactivar prendas existentes (soft delete)
            $stmt = $this->db->prepare("
                UPDATE prendas 
                SET activo = 0 
                WHERE compra_id = :compra_id AND estado = 'DISPONIBLE'
            ");
            $stmt->execute([':compra_id' => $id]);

            // Insertar nuevas prendas
            if (isset($datos['prendas']) && is_array($datos['prendas'])) {
                foreach ($datos['prendas'] as $index => $prenda) {
                    $codigoPrenda = !empty($prenda['codigo_prenda'])
                    ? strtoupper(trim($prenda['codigo_prenda']))
                    : $this->generateCodigoPrenda($id, $index);


                    $stmt = $this->db->prepare("
                        INSERT INTO prendas (
                            codigo_prenda, compra_id, nombre, categoria, tipo, 
                            precio, precio_compra, descripcion, estado, activo
                        )
                        VALUES (
                            :codigo_prenda, :compra_id, :nombre, :categoria, :tipo, 
                            :precio, :precio_compra, :descripcion, 'DISPONIBLE', 1
                        )
                    ");

                    $stmt->execute([
                        ':codigo_prenda' => $codigoPrenda,
                        ':compra_id' => $id,
                        ':nombre' => $prenda['nombre'],
                        ':categoria' => $prenda['categoria'],
                        ':tipo' => $prenda['tipo'],
                        ':precio' => $prenda['precio_venta'],
                        ':precio_compra' => $prenda['precio_costo'],
                        ':descripcion' => $prenda['descripcion'] ?? ''
                    ]);
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
     * Elimina una compra (soft delete)
     * Solo se pueden eliminar compras donde todas las prendas están DISPONIBLES
     */
    public function delete($id) {
        try {
            $this->db->beginTransaction();

            // Verificar si hay prendas vendidas
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as vendidas
                FROM prendas
                WHERE compra_id = :id AND estado = 'VENDIDA' AND activo = 1
            ");
            $stmt->execute([':id' => $id]);
            $vendidas = $stmt->fetch(PDO::FETCH_ASSOC)['vendidas'];

            if ($vendidas > 0) {
                throw new Exception('No se puede eliminar esta compra porque tiene prendas vendidas');
            }

            // Desactivar compra
            $stmt = $this->db->prepare("
                UPDATE compras 
                SET activo = 0, fec_actualizacion = CURRENT_TIMESTAMP
                WHERE compra_id = :id
            ");
            $stmt->execute([':id' => $id]);

            // Desactivar prendas asociadas
            $stmt = $this->db->prepare("
                UPDATE prendas 
                SET activo = 0, estado = 'ELIMINADA'
                WHERE compra_id = :id
            ");
            $stmt->execute([':id' => $id]);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('Error en Purchase::delete - ' . $e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Marca el PDF como generado
     */
    public function markPdfGenerated($id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE compras 
                SET pdf_generado = 1, fec_actualizacion = CURRENT_TIMESTAMP
                WHERE compra_id = :id
            ");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log('Error en Purchase::markPdfGenerated - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Genera un código único para cada prenda
     * Formato: PRD-YYYYMMDD-COMPRAID-INDEX
     */
    private function generateCodigoPrenda($compraId, $index) {
        $fecha = date('Ymd');
        $codigo = sprintf('PRD-%s-%04d-%03d', $fecha, $compraId, $index + 1);
        
        // Verificar si el código ya existe
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM prendas WHERE codigo_prenda = :codigo
        ");
        $stmt->execute([':codigo' => $codigo]);
        
        // Si existe, agregar un sufijo aleatorio
        if ($stmt->fetchColumn() > 0) {
            $codigo .= '-' . strtoupper(substr(md5(uniqid()), 0, 4));
        }
        
        return $codigo;
    }

    /**
     * Obtiene estadísticas de compras
     */
    public function getEstadisticas() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total_compras,
                    SUM(monto_total) as monto_total_compras,
                    COUNT(DISTINCT proveedor_rif) as total_proveedores,
                    (SELECT COUNT(*) FROM prendas WHERE activo = 1 AND estado = 'DISPONIBLE') as prendas_disponibles,
                    (SELECT COUNT(*) FROM prendas WHERE activo = 1 AND estado = 'VENDIDA') as prendas_vendidas
                FROM compras 
                WHERE activo = 1
            ");
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Error en Purchase::getEstadisticas - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el historial de precios de un producto similar
     */
    public function getPrecioHistorico($nombre, $categoria) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    p.nombre,
                    p.precio_compra,
                    p.precio as precio_venta,
                    c.fecha_compra,
                    pr.nombre_empresa as proveedor
                FROM prendas p
                JOIN compras c ON p.compra_id = c.compra_id
                JOIN proveedores pr ON c.proveedor_rif = pr.proveedor_rif
                WHERE p.categoria = :categoria
                AND p.nombre LIKE :nombre
                AND p.activo = 1
                ORDER BY c.fecha_compra DESC
                LIMIT 10
            ");
            $stmt->execute([
                ':categoria' => $categoria,
                ':nombre' => '%' . $nombre . '%'
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Error en Purchase::getPrecioHistorico - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el margen de ganancia promedio por categoría
     */
    public function getMargenPorCategoria() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    categoria,
                    COUNT(*) as total_prendas,
                    AVG(precio_compra) as precio_compra_promedio,
                    AVG(precio) as precio_venta_promedio,
                    AVG(precio - precio_compra) as margen_promedio,
                    AVG(((precio - precio_compra) / precio_compra) * 100) as porcentaje_ganancia
                FROM prendas
                WHERE activo = 1 AND precio_compra IS NOT NULL AND precio_compra > 0
                GROUP BY categoria
                ORDER BY margen_promedio DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Error en Purchase::getMargenPorCategoria - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Valida que una compra pueda ser editada
     */
    public function canEdit($compraId) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as vendidas
                FROM prendas
                WHERE compra_id = :id AND estado = 'VENDIDA' AND activo = 1
            ");
            $stmt->execute([':id' => $compraId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['vendidas'] == 0;
        } catch (\Throwable $e) {
            error_log('Error en Purchase::canEdit - ' . $e->getMessage());
            return false;
        }
    }
}