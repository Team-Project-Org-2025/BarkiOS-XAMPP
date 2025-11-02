<?php
namespace Barkios\models;

use Barkios\core\Database;
use PDO;
use Exception;

class Sale extends Database
{
    private const IVA_DEFAULT = 16.00;


    public function getAll()
    {
        $sql = "
            SELECT v.*, 
                   c.nombre_cliente, 
                   e.nombre AS nombre_empleado,
                   COUNT(dv.detalle_venta_id) as total_prendas
            FROM ventas v
            LEFT JOIN clientes c ON v.cliente_ced = c.cliente_ced
            LEFT JOIN empleados e ON v.empleado_ced = e.empleado_ced
            LEFT JOIN detalle_venta dv ON v.venta_id = dv.venta_id
            WHERE v.estado_venta != 'cancelada'
            GROUP BY v.venta_id
            ORDER BY v.fecha DESC
        ";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT v.*, 
                   c.nombre_cliente, 
                   c.telefono as cliente_telefono,
                   c.correo as cliente_correo,
                   c.tipo as cliente_tipo,
                   e.nombre AS nombre_empleado,
                   e.cargo as empleado_cargo
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
            SELECT dv.*, 
                   p.codigo_prenda,
                   p.nombre AS nombre_prenda, 
                   p.tipo, 
                   p.categoria,
                   p.descripcion,
                   dv.precio_unitario as precio_con_iva,
                   ROUND(dv.precio_unitario / 1.16, 2) as precio_base,
                   ROUND(dv.precio_unitario - (dv.precio_unitario / 1.16), 2) as iva_producto
            FROM detalle_venta dv
            JOIN prendas p ON p.codigo_prenda = dv.codigo_prenda
            WHERE dv.venta_id = :id
        ");

        $stmt->execute([':id' => $id]);
        $venta['prendas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $venta['pagos'] = $this->getPaymentsBySale($id);
        $venta['total_pagado'] = array_sum(array_column($venta['pagos'], 'monto'));
        
        return $venta;
    }


public function addSale($data)
{
    try {
        $this->db->beginTransaction();

        $this->validateProductsAvailability($data['productos']);

        $subtotal = 0;
        foreach ($data['productos'] as $p) {
            $subtotal += floatval($p['precio_unitario']);
        }

        $subtotalSinIva = round($subtotal / 1.16, 2);
        $ivaPorcentaje = 16.00;
        $montoIva = round($subtotalSinIva * ($ivaPorcentaje / 100), 2);
        $montoTotal = round($subtotalSinIva + $montoIva, 2);

        $subtotal = $subtotalSinIva;

        if (!empty($data['referencia'])) {
            $referencia = trim($data['referencia']);
        } else {
            $referencia = $this->generateReference();
        }

        $stmt = $this->db->prepare("
            INSERT INTO ventas (
                referencia, empleado_ced, cliente_ced, tipo_venta, 
                estado_venta, monto_subtotal, iva_porcentaje, 
                monto_iva, monto_total, saldo_pendiente, observaciones
            ) VALUES (
                :ref, :empleado, :cliente, :tipo, :estado, 
                :subtotal, :iva_pct, :iva_monto, :total, :saldo, :obs
            )
        ");

        $estado = $data['tipo_venta'] === 'credito' ? 'pendiente' : 'completada';
        $saldo = $data['tipo_venta'] === 'credito' ? $montoTotal : 0.00;

        $stmt->execute([
            ':ref' => $referencia,
            ':empleado' => $data['empleado_ced'],
            ':cliente' => $data['cliente_ced'],
            ':tipo' => $data['tipo_venta'],
            ':estado' => $estado,
            ':subtotal' => $subtotal,
            ':iva_pct' => $ivaPorcentaje,
            ':iva_monto' => $montoIva,
            ':total' => $montoTotal,
            ':saldo' => $saldo,
            ':obs' => $data['observaciones'] ?? null
        ]);

        $ventaId = $this->db->lastInsertId();

        $this->addSaleDetails($ventaId, $data['productos']);

        if ($data['tipo_venta'] === 'credito') {
            $this->createCredit($ventaId, $referencia, $data['fecha_vencimiento']);
        }

        $this->db->commit();
        return $ventaId;

    } catch (Exception $e) {
        $this->db->rollBack();
        error_log("Sale::addSale - " . $e->getMessage());
        throw $e;
    }
}

    private function validateProductsAvailability($productos)
    {
        foreach ($productos as $p) {
            $codigo = $p['codigo_prenda'] ?? null;
            if (!$codigo) {
                throw new Exception("Código de prenda no especificado");
            }

            $stmt = $this->db->prepare("
                SELECT estado, nombre FROM prendas WHERE codigo_prenda = :codigo
            ");
            $stmt->execute([':codigo' => $codigo]);
            $prenda = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$prenda) {
                throw new Exception("Prenda con código {$codigo} no existe");
            }

            if ($prenda['estado'] !== 'DISPONIBLE') {
                throw new Exception("La prenda '{$prenda['nombre']}' (código: {$codigo}) no está disponible");
            }
        }
    }

    private function addSaleDetails($ventaId, $productos)
    {
        $stmtDet = $this->db->prepare("
            INSERT INTO detalle_venta (venta_id, prenda_id, codigo_prenda, precio_unitario)
            SELECT :venta_id, prenda_id, :codigo, :precio
            FROM prendas WHERE codigo_prenda = :codigo2
        ");
        
        $updPrenda = $this->db->prepare("
            UPDATE prendas SET estado = 'VENDIDA' WHERE codigo_prenda = :codigo
        ");

        foreach ($productos as $p) {
            $codigo = $p['codigo_prenda'];
            $stmtDet->execute([
                ':venta_id' => $ventaId,
                ':codigo' => $codigo,
                ':codigo2' => $codigo,
                ':precio' => $p['precio_unitario']
            ]);
            $updPrenda->execute([':codigo' => $codigo]);
        }
    }

private function createCredit($ventaId, $referencia, $fechaVencimiento = null)
{
    if (empty($fechaVencimiento)) {
        error_log("Fecha de vencimiento no proporcionada.");
    } else {
        error_log("Fecha de vencimiento: " . $fechaVencimiento);
    }


    $stmtCred = $this->db->prepare("
        INSERT INTO credito (venta_id, referencia_credito)
        VALUES (:venta_id, :ref)
    ");
    $stmtCred->execute([
        ':venta_id' => $ventaId,
        ':ref' => 'CRE-' . $referencia
    ]);

    $creditoId = $this->db->lastInsertId();

    $stmtCC = $this->db->prepare("
        INSERT INTO cuentas_cobrar (credito_id, estado, emision, vencimiento)
        VALUES (:credito_id, 'pendiente', NOW(), :vencimiento)
    ");
    $stmtCC->execute([
        ':credito_id' => $creditoId,
        ':vencimiento' => $fechaVencimiento
    ]);
}


    private function generateReference()
    {
        $fecha = date('Ymd');
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as total 
            FROM ventas 
            WHERE DATE(fecha) = CURDATE()
        ");
        $stmt->execute();
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['total'] + 1;
        
        return sprintf('VEN-%s-%03d', $fecha, $count);
    }



    public function addPayment($data)
    {
        try {
            $this->db->beginTransaction();

            // Validar monto
            $venta = $this->getById($data['venta_id']);
            if (!$venta) {
                throw new Exception("Venta no encontrada");
            }

            $monto = floatval($data['monto']);
            if ($monto <= 0 || $monto > floatval($venta['saldo_pendiente'])) {
                throw new Exception("Monto de pago inválido");
            }

            // Insertar pago
            $stmt = $this->db->prepare("
                INSERT INTO pagos (venta_id, monto, observaciones)
                VALUES (:venta_id, :monto, :obs)
            ");
            $stmt->execute([
                ':venta_id' => $data['venta_id'],
                ':monto' => $monto,
                ':obs' => $data['observaciones'] ?? null
            ]);

            // Actualizar saldo
            $this->db->prepare("
                UPDATE ventas 
                SET saldo_pendiente = GREATEST(saldo_pendiente - :monto, 0),
                    fec_actualizacion = NOW()
                WHERE venta_id = :id
            ")->execute([
                ':monto' => $monto,
                ':id' => $data['venta_id']
            ]);

            // Marcar como completada si saldo = 0
            $this->db->prepare("
                UPDATE ventas
                SET estado_venta = 'completada'
                WHERE venta_id = :id AND saldo_pendiente <= 0
            ")->execute([':id' => $data['venta_id']]);

            // Actualizar cuenta por cobrar
            $this->updateCreditStatus($data['venta_id']);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Sale::addPayment - " . $e->getMessage());
            throw $e;
        }
    }

    private function updateCreditStatus($ventaId)
    {
        $this->db->prepare("
            UPDATE cuentas_cobrar cc
            JOIN credito cr ON cc.credito_id = cr.credito_id
            JOIN ventas v ON cr.venta_id = v.venta_id
            SET cc.estado = IF(v.saldo_pendiente <= 0, 'pagado', 'pendiente')
            WHERE v.venta_id = :id
        ")->execute([':id' => $ventaId]);
    }

    public function getPaymentsBySale($ventaId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM pagos 
            WHERE venta_id = :id 
            ORDER BY fecha_pago DESC
        ");
        $stmt->execute([':id' => $ventaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function cancelSale($ventaId)
    {
        try {
            $this->db->beginTransaction();

            // Validar que la venta existe
            $venta = $this->getById($ventaId);
            if (!$venta) {
                throw new Exception("Venta no encontrada");
            }

            // Obtener códigos de prendas
            $stmt = $this->db->prepare("
                SELECT codigo_prenda FROM detalle_venta WHERE venta_id = :id
            ");
            $stmt->execute([':id' => $ventaId]);
            $codigos = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Liberar prendas
            if ($codigos) {
                $upd = $this->db->prepare("
                    UPDATE prendas SET estado = 'DISPONIBLE' WHERE codigo_prenda = :codigo
                ");
                foreach ($codigos as $codigo) {
                    $upd->execute([':codigo' => $codigo]);
                }
            }

            // Cancelar venta
            $this->db->prepare("
                UPDATE ventas 
                SET estado_venta = 'cancelada', 
                    saldo_pendiente = 0,
                    fec_actualizacion = NOW()
                WHERE venta_id = :id
            ")->execute([':id' => $ventaId]);

            // Marcar crédito como vencido
            $this->db->prepare("
                UPDATE cuentas_cobrar 
                SET estado = 'vencido'
                WHERE credito_id IN (
                    SELECT credito_id FROM credito WHERE venta_id = :id
                )
            ")->execute([':id' => $ventaId]);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Sale::cancelSale - " . $e->getMessage());
            throw $e;
        }
    }

    public function getClients()
    {
        $stmt = $this->db->query("
            SELECT cliente_ced, nombre_cliente, tipo, limite_credito, telefono
            FROM clientes 
            WHERE activo = 1 
            ORDER BY nombre_cliente
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEmployees()
    {
        $stmt = $this->db->query("
            SELECT empleado_ced, nombre, cargo
            FROM empleados 
            WHERE activo = 1 
            ORDER BY nombre
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProducts()
    {
        $stmt = $this->db->query("
            SELECT 
                prenda_id,
                codigo_prenda,
                nombre,
                categoria,
                tipo,
                precio,
                descripcion,
                precio_compra,
                (precio - COALESCE(precio_compra, 0)) as margen
            FROM prendas 
            WHERE estado = 'DISPONIBLE' AND activo = 1
            ORDER BY fecha_creacion DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductByCode($codigo)
    {
        $stmt = $this->db->prepare("
            SELECT 
                prenda_id,
                codigo_prenda,
                nombre,
                categoria,
                tipo,
                precio,
                descripcion,
                estado
            FROM prendas 
            WHERE codigo_prenda = :codigo
        ");
        $stmt->execute([':codigo' => $codigo]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
