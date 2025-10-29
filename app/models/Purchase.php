<?php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo Compras - Solo lógica de negocio y BD
 */
class Purchase extends Database {
    
    /**
     * Verifica si una factura ya existe
     */
    public function facturaExiste($numeroFactura, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) FROM compras 
                    WHERE factura_numero = :factura_numero AND activo = 1";
            
            if ($excludeId) {
                $sql .= " AND compra_id != :exclude_id";
            }
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':factura_numero', $numeroFactura);
            
            if ($excludeId) {
                $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en facturaExiste: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un código de prenda ya existe
     */
    public function codigoPrendaExiste($codigo) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM prendas 
                WHERE codigo_prenda = :codigo AND activo = 1
            ");
            $stmt->execute([':codigo' => strtoupper(trim($codigo))]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en codigoPrendaExiste: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si un proveedor existe
     */
    public function proveedorExiste($proveedorRif) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM proveedores 
                WHERE proveedor_rif = :rif
            ");
            $stmt->execute([':rif' => $proveedorRif]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            error_log("Error en proveedorExiste: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el monto total actual de una compra
     */
    public function getMontoTotal($compraId) {
        try {
            $stmt = $this->db->prepare("
                SELECT monto_total 
                FROM compras 
                WHERE compra_id = :id AND activo = 1
            ");
            $stmt->execute([':id' => $compraId]);
            return floatval($stmt->fetchColumn());
        } catch (Exception $e) {
            error_log('Error en Purchase::getMontoTotal - ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene todas las compras con estado de pago
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    c.*,
                    p.nombre_empresa as nombre_proveedor,
                    p.nombre_contacto,
                    p.tipo_rif,
                    COUNT(DISTINCT pr.prenda_id) as total_prendas,
                    SUM(CASE WHEN pr.estado = 'DISPONIBLE' THEN 1 ELSE 0 END) as prendas_disponibles,
                    SUM(CASE WHEN pr.estado = 'VENDIDA' THEN 1 ELSE 0 END) as prendas_vendidas,
                    cp.cuenta_pagar_id,
                    cp.estado as estado_pago,
                    cp.fecha_vencimiento,
                    COALESCE(
                        (SELECT SUM(pc.monto) 
                         FROM pagos_compras pc 
                         WHERE pc.cuenta_pagar_id = cp.cuenta_pagar_id 
                         AND pc.estado_pago = 'CONFIRMADO'),
                        0
                    ) as total_pagado,
                    (c.monto_total - COALESCE(
                        (SELECT SUM(pc.monto) 
                         FROM pagos_compras pc 
                         WHERE pc.cuenta_pagar_id = cp.cuenta_pagar_id 
                         AND pc.estado_pago = 'CONFIRMADO'),
                        0
                    )) as saldo_pendiente,
                    CASE 
                        WHEN cp.fecha_vencimiento < CURDATE() AND cp.estado = 'pendiente' THEN 1
                        ELSE 0
                    END as vencida
                FROM compras c
                JOIN proveedores p ON c.proveedor_rif = p.proveedor_rif
                LEFT JOIN prendas pr ON c.compra_id = pr.compra_id AND pr.activo = 1
                LEFT JOIN cuentas_pagar cp ON c.compra_id = cp.compra_id
                WHERE c.activo = 1
                GROUP BY c.compra_id
                ORDER BY c.fecha_compra DESC, c.compra_id DESC
            ");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log('Error en Purchase::getAll - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una compra específica con cuenta por pagar
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    c.*,
                    p.nombre_empresa as nombre_proveedor,
                    p.nombre_contacto,
                    p.direccion as direccion_proveedor,
                    p.telefono as telefono_proveedor,
                    p.correo as correo_proveedor,
                    p.tipo_rif,
                    cp.cuenta_pagar_id,
                    cp.estado as estado_pago,
                    cp.fecha_vencimiento,
                    cp.observaciones as observaciones_pago,
                    COALESCE(
                        (SELECT SUM(pc.monto) 
                         FROM pagos_compras pc 
                         WHERE pc.cuenta_pagar_id = cp.cuenta_pagar_id 
                         AND pc.estado_pago = 'CONFIRMADO'),
                        0
                    ) as total_pagado,
                    (c.monto_total - COALESCE(
                        (SELECT SUM(pc.monto) 
                         FROM pagos_compras pc 
                         WHERE pc.cuenta_pagar_id = cp.cuenta_pagar_id 
                         AND pc.estado_pago = 'CONFIRMADO'),
                        0
                    )) as saldo_pendiente
                FROM compras c
                JOIN proveedores p ON c.proveedor_rif = p.proveedor_rif
                LEFT JOIN cuentas_pagar cp ON c.compra_id = cp.compra_id
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
     * Obtiene prendas de una compra
     */
    public function getPrendasByCompraId($compraId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    pr.prenda_id,
                    pr.codigo_prenda,
                    pr.nombre,
                    pr.categoria,
                    pr.tipo,
                    pr.precio_compra as precio_costo,
                    pr.precio as precio_venta,
                    pr.descripcion,
                    pr.estado,
                    pr.fecha_creacion,
                    dc.detalle_compra_id,
                    (pr.precio - pr.precio_compra) as margen_ganancia,
                    ((pr.precio - pr.precio_compra) / pr.precio_compra * 100) as porcentaje_ganancia
                FROM prendas pr
                LEFT JOIN detalle_compra dc ON pr.codigo_prenda = dc.codigo_prenda 
                    AND dc.compra_id = :compra_id
                WHERE pr.compra_id = :compra_id AND pr.activo = 1
                ORDER BY pr.categoria, pr.nombre
            ");
            $stmt->execute([':compra_id' => $compraId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Error en Purchase::getPrendasByCompraId - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Inserta una prenda en la base de datos
     */
    private function insertarPrenda($compraId, $prenda) {
        $codigoPrenda = strtoupper(trim($prenda['codigo_prenda']));
        
        // Verificar código único (regla de negocio)
        if ($this->codigoPrendaExiste($codigoPrenda)) {
            throw new Exception("El código '$codigoPrenda' ya existe en el inventario");
        }

        // Insertar en prendas
        $stmtPrenda = $this->db->prepare("
            INSERT INTO prendas (
                codigo_prenda, compra_id, nombre, categoria, tipo, 
                precio, precio_compra, descripcion, estado, activo
            )
            VALUES (
                :codigo_prenda, :compra_id, :nombre, :categoria, :tipo, 
                :precio, :precio_compra, :descripcion, 'DISPONIBLE', 1
            )
        ");

        $stmtPrenda->execute([
            ':codigo_prenda' => $codigoPrenda,
            ':compra_id' => $compraId,
            ':nombre' => $prenda['nombre'],
            ':categoria' => $prenda['categoria'],
            ':tipo' => $prenda['tipo'],
            ':precio' => $prenda['precio_venta'],
            ':precio_compra' => $prenda['precio_costo'],
            ':descripcion' => $prenda['descripcion']
        ]);

        // Insertar en detalle_compra
        $stmtDetalle = $this->db->prepare("
            INSERT INTO detalle_compra (
                compra_id, codigo_prenda, precio_compra
            )
            VALUES (
                :compra_id, :codigo_prenda, :precio_compra
            )
        ");

        $stmtDetalle->execute([
            ':compra_id' => $compraId,
            ':codigo_prenda' => $codigoPrenda,
            ':precio_compra' => $prenda['precio_costo']
        ]);

        return floatval($prenda['precio_costo']);
    }

    /**
     * Crea una cuenta por pagar para la compra
     */
    private function crearCuentaPorPagar($compraId, $datos) {
        $fechaVencimiento = $datos['fecha_vencimiento'] ?? date('Y-m-d', strtotime('+30 days'));
        
        $stmt = $this->db->prepare("
            INSERT INTO cuentas_pagar (
                compra_id, proveedor_rif, monto, fecha, 
                fecha_vencimiento, estado, observaciones
            )
            VALUES (
                :compra_id, :proveedor_rif, :monto, :fecha,
                :fecha_vencimiento, 'pendiente', :observaciones
            )
        ");

        $stmt->execute([
            ':compra_id' => $compraId,
            ':proveedor_rif' => $datos['proveedor_rif'],
            ':monto' => $datos['monto_total'],
            ':fecha' => $datos['fecha_compra'],
            ':fecha_vencimiento' => $fechaVencimiento,
            ':observaciones' => 'Cuenta generada automáticamente - Factura #' . $datos['factura_numero']
        ]);
    }

    /**
     * Agrega una nueva compra (validaciones de negocio)
     */
    public function add($datos) {
        try {
            // Validaciones de negocio
            if ($this->facturaExiste($datos['factura_numero'])) {
                throw new Exception('Ya existe una compra con este número de factura');
            }

            if (!$this->proveedorExiste($datos['proveedor_rif'])) {
                throw new Exception('El proveedor no existe');
            }

            if (empty($datos['prendas']) || !is_array($datos['prendas'])) {
                throw new Exception('Debe agregar al menos una prenda');
            }

            // Verificar códigos duplicados en el lote
            $codigos = array_map(function($p) {
                return strtoupper(trim($p['codigo_prenda']));
            }, $datos['prendas']);

            if (count($codigos) !== count(array_unique($codigos))) {
                throw new Exception('Hay códigos de prenda duplicados en el lote');
            }

            $this->db->beginTransaction();

            // Insertar compra
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

            $stmt->execute([
                ':proveedor_rif' => $datos['proveedor_rif'],
                ':factura_numero' => $datos['factura_numero'],
                ':fecha_compra' => $datos['fecha_compra'],
                ':tracking' => $datos['tracking'],
                ':monto_total' => $datos['monto_total'],
                ':observaciones' => $datos['observaciones']
            ]);

            $compraId = $this->db->lastInsertId();

            // Crear cuenta por pagar
            $this->crearCuentaPorPagar($compraId, $datos);

            // Insertar prendas
            foreach ($datos['prendas'] as $prenda) {
                $this->insertarPrenda($compraId, $prenda);
            }

            $this->db->commit();
            return $compraId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('Error en Purchase::add - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Actualiza datos generales de una compra
     */
    public function update($id, $datos) {
        try {
            // Validaciones de negocio
            if ($this->facturaExiste($datos['factura_numero'], $id)) {
                throw new Exception('Ya existe otra compra con este número de factura');
            }

            if (!$this->proveedorExiste($datos['proveedor_rif'])) {
                throw new Exception('El proveedor no existe');
            }

            $this->db->beginTransaction();

            // Actualizar compra
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

            $stmt->execute([
                ':id' => $id,
                ':proveedor_rif' => $datos['proveedor_rif'],
                ':factura_numero' => $datos['factura_numero'],
                ':fecha_compra' => $datos['fecha_compra'],
                ':tracking' => $datos['tracking'],
                ':monto_total' => $datos['monto_total'],
                ':observaciones' => $datos['observaciones']
            ]);

            // Actualizar cuenta por pagar
            $stmtCuenta = $this->db->prepare("
                UPDATE cuentas_pagar
                SET proveedor_rif = :proveedor_rif,
                    monto = :monto,
                    fec_actualizacion = CURRENT_TIMESTAMP
                WHERE compra_id = :compra_id
            ");

            $stmtCuenta->execute([
                ':compra_id' => $id,
                ':proveedor_rif' => $datos['proveedor_rif'],
                ':monto' => $datos['monto_total']
            ]);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('Error en Purchase::update - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Agrega nuevas prendas a una compra existente
     */
    public function addPrendasToCompra($compraId, $prendas) {
        try {
            if (empty($prendas) || !is_array($prendas)) {
                return 0;
            }

            // Verificar códigos duplicados en el lote
            $codigos = array_map(function($p) {
                return strtoupper(trim($p['codigo_prenda']));
            }, $prendas);

            if (count($codigos) !== count(array_unique($codigos))) {
                throw new Exception('Hay códigos de prenda duplicados en el lote');
            }

            $this->db->beginTransaction();

            $montoTotal = 0;
            foreach ($prendas as $prenda) {
                $montoTotal += $this->insertarPrenda($compraId, $prenda);
            }

            // Actualizar monto total de la compra
            $montoActual = $this->getMontoTotal($compraId);
            $nuevoMonto = $montoActual + $montoTotal;

            $stmt = $this->db->prepare("
                UPDATE compras 
                SET monto_total = :monto_total,
                    fec_actualizacion = CURRENT_TIMESTAMP
                WHERE compra_id = :id
            ");
            $stmt->execute([
                ':id' => $compraId,
                ':monto_total' => $nuevoMonto
            ]);

            // Actualizar cuenta por pagar
            $stmt = $this->db->prepare("
                UPDATE cuentas_pagar
                SET monto = :monto,
                    fec_actualizacion = CURRENT_TIMESTAMP
                WHERE compra_id = :compra_id
            ");
            $stmt->execute([
                ':compra_id' => $compraId,
                ':monto' => $nuevoMonto
            ]);

            $this->db->commit();
            return count($prendas);
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log('Error en Purchase::addPrendasToCompra - ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Elimina una compra (soft delete)
     */
    public function delete($id) {
        try {
            $this->db->beginTransaction();

            // Verificar prendas vendidas (regla de negocio)
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as vendidas
                FROM prendas
                WHERE compra_id = :id AND estado = 'VENDIDA' AND activo = 1
            ");
            $stmt->execute([':id' => $id]);
            $vendidas = $stmt->fetch(PDO::FETCH_ASSOC)['vendidas'];

            if ($vendidas > 0) {
                throw new Exception('No se puede eliminar: hay ' . $vendidas . ' prenda(s) vendida(s)');
            }

            // Verificar pagos (regla de negocio)
            $stmtPagos = $this->db->prepare("
                SELECT COUNT(*) as total_pagos
                FROM pagos_compras pc
                JOIN cuentas_pagar cp ON pc.cuenta_pagar_id = cp.cuenta_pagar_id
                WHERE cp.compra_id = :id AND pc.estado_pago = 'CONFIRMADO'
            ");
            $stmtPagos->execute([':id' => $id]);
            $totalPagos = $stmtPagos->fetch(PDO::FETCH_ASSOC)['total_pagos'];

            if ($totalPagos > 0) {
                throw new Exception('No se puede eliminar: la compra tiene pagos registrados');
            }

            // Desactivar compra
            $stmt = $this->db->prepare("
                UPDATE compras 
                SET activo = 0, fec_actualizacion = CURRENT_TIMESTAMP
                WHERE compra_id = :id
            ");
            $stmt->execute([':id' => $id]);

            // Anular cuenta por pagar
            $stmt = $this->db->prepare("
                UPDATE cuentas_pagar 
                SET estado = 'cancelado', fec_actualizacion = CURRENT_TIMESTAMP
                WHERE compra_id = :id
            ");
            $stmt->execute([':id' => $id]);

            // Desactivar prendas
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
            throw $e;
        }
    }

    /**
     * Obtiene pagos de una compra
     */
    public function getPagosByCompraId($compraId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    pc.*,
                    cp.cuenta_pagar_id
                FROM pagos_compras pc
                JOIN cuentas_pagar cp ON pc.cuenta_pagar_id = cp.cuenta_pagar_id
                WHERE cp.compra_id = :compra_id
                AND pc.estado_pago != 'ANULADO'
                ORDER BY pc.fecha_pago DESC
            ");
            $stmt->execute([':compra_id' => $compraId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Error en Purchase::getPagosByCompraId - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Marca PDF como generado
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
     * Obtiene estadísticas completas
     */
    public function getEstadisticas() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(DISTINCT c.compra_id) as total_compras,
                    SUM(c.monto_total) as monto_total_compras,
                    COUNT(DISTINCT c.proveedor_rif) as total_proveedores,
                    (SELECT COUNT(*) FROM prendas WHERE activo = 1 AND estado = 'DISPONIBLE') as prendas_disponibles,
                    (SELECT COUNT(*) FROM prendas WHERE activo = 1 AND estado = 'VENDIDA') as prendas_vendidas,
                    (SELECT SUM(precio_compra) FROM prendas WHERE activo = 1 AND estado = 'DISPONIBLE') as valor_inventario,
                    COALESCE((
                        SELECT SUM(c2.monto_total - COALESCE(
                            (SELECT SUM(pc.monto) 
                             FROM pagos_compras pc 
                             JOIN cuentas_pagar cp2 ON pc.cuenta_pagar_id = cp2.cuenta_pagar_id
                             WHERE cp2.compra_id = c2.compra_id 
                             AND pc.estado_pago = 'CONFIRMADO'),
                            0
                        ))
                        FROM compras c2
                        JOIN cuentas_pagar cp ON c2.compra_id = cp.compra_id
                        WHERE c2.activo = 1 AND cp.estado = 'pendiente'
                    ), 0) as saldo_pendiente_total,
                    (
                        SELECT COUNT(*)
                        FROM cuentas_pagar
                        WHERE estado = 'pendiente' 
                        AND fecha_vencimiento < CURDATE()
                    ) as cuentas_vencidas
                FROM compras c
                WHERE c.activo = 1
            ");
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('Error en Purchase::getEstadisticas - ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Valida si una compra puede editarse
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