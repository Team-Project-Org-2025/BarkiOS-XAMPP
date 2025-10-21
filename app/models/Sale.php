<?php
namespace Barkios\models;

use Barkios\core\Database;
use PDO;

class Sale extends Database
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ==========================================================
       🔹 OBTENER TODAS LAS VENTAS
    ========================================================== */
    public function getAll()
    {
        try {
            $sql = "
                SELECT 
                    v.venta_id,
                    v.fecha,
                    v.tipo_venta,
                    v.estado_venta,
                    c.nombre_cliente,
                    e.nombre AS nombre_empleado,
                    COALESCE(SUM(p.monto), 0) AS total_pagado,
                    v.monto_total
                FROM ventas v
                LEFT JOIN clientes c ON v.cliente_ced = c.cliente_ced
                LEFT JOIN empleados e ON v.empleado_ced = e.empleado_ced
                LEFT JOIN pagos p ON v.venta_id = p.venta_id
                GROUP BY v.venta_id
                ORDER BY v.fecha DESC
            ";
            return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {
            error_log("Error al obtener ventas: " . $e->getMessage());
            return [];
        }
    }

    /* ==========================================================
       🔹 VALIDAR QUE UNA PRENDA ESTÉ DISPONIBLE
    ========================================================== */
    private function isProductAvailable($prendaId)
    {
        $stmt = $this->db->prepare("SELECT estado FROM prendas WHERE prenda_id = :id");
        $stmt->execute([':id' => $prendaId]);
        $estado = $stmt->fetchColumn();
        return $estado === 'DISPONIBLE';
    }

    /* ==========================================================
       🔹 AGREGAR NUEVA VENTA (Opción C)
    ========================================================== */
    public function addSale($data)
    {
        try {
            $this->db->beginTransaction();

            // Insertar encabezado
            $stmt = $this->db->prepare("
                INSERT INTO ventas (fecha, cliente_ced, empleado_ced, tipo_venta, estado_venta)
                VALUES (NOW(), :cliente, :empleado, :tipo_venta, :estado)
            ");
            $estadoVenta = $data['tipo_venta'] === 'credito' ? 'pendiente' : 'completada';
            $stmt->execute([
                ':cliente' => $data['cliente_ced'],
                ':empleado' => $data['empleado_ced'],
                ':tipo_venta' => $data['tipo_venta'],
                ':estado' => $estadoVenta
            ]);

            $ventaId = $this->db->lastInsertId();

            // Insertar detalle
            $stmtDetalle = $this->db->prepare("
                INSERT INTO detalle_venta (venta_id, prenda_id, precio_unitario)
                VALUES (:venta_id, :prenda_id, :precio)
            ");

            foreach ($data['productos'] as $prod) {
                // ✅ Validar que la prenda no esté vendida
                if (!$this->isProductAvailable($prod['prenda_id'])) {
                    throw new \Exception("La prenda '{$prod['nombre']}' ya fue vendida y no puede agregarse a la venta.");
                }

                // Insertar detalle
                $stmtDetalle->execute([
                    ':venta_id' => $ventaId,
                    ':prenda_id' => $prod['prenda_id'],
                    ':precio' => $prod['precio_unitario']
                ]);

                // Actualizar estado a 'VENDIDA'
                $this->db->prepare("
                    UPDATE prendas SET estado = 'VENDIDA' WHERE prenda_id = :id
                ")->execute([':id' => $prod['prenda_id']]);
            }

            // Registrar pago si es contado
            if ($data['tipo_venta'] === 'contado') {
                $this->addPayment([
                    'venta_id' => $ventaId,
                    'monto' => $data['monto_total'] ?? 0,
                    'estado_pago' => 'confirmado'
                ]);
            }

            $this->db->commit();
            return $ventaId;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log("Error al registrar venta: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /* ==========================================================
       🔹 ANULAR / ELIMINAR UNA VENTA
    ========================================================== */
    public function cancelSale($ventaId)
    {
        try {
            $this->db->beginTransaction();

            // Recuperar las prendas asociadas
            $stmt = $this->db->prepare("SELECT prenda_id FROM detalle_venta WHERE venta_id = :id");
            $stmt->execute([':id' => $ventaId]);
            $prendas = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Revertir estado de cada prenda
            foreach ($prendas as $pid) {
                $this->db->prepare("
                    UPDATE prendas SET estado = 'DISPONIBLE' WHERE prenda_id = :id
                ")->execute([':id' => $pid]);
            }

            // Cambiar estado de la venta
            $this->db->prepare("
                UPDATE ventas SET estado_venta = 'cancelada' WHERE venta_id = :id
            ")->execute([':id' => $ventaId]);

            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            error_log("Error al cancelar venta: " . $e->getMessage());
            return false;
        }
    }

    /* ==========================================================
       🔹 REGISTRAR PAGO
    ========================================================== */
    public function addPayment($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO pagos (venta_id, fecha_pago, monto, observaciones)
            VALUES (:venta, NOW(), :monto, 'Pago registrado automáticamente')
        ");
        return $stmt->execute([
            ':venta' => $data['venta_id'],
            ':monto' => $data['monto']
        ]);
    }

    /* ==========================================================
       🔹 OBTENER PRODUCTOS DISPONIBLES
    ========================================================== */
    public function getAvailableProducts()
    {
        $stmt = $this->db->query("
            SELECT prenda_id, nombre, tipo, categoria, precio
            FROM prendas
            WHERE activo = 1 AND estado = 'DISPONIBLE'
            ORDER BY nombre ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
