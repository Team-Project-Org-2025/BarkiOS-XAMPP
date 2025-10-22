<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\models\Product.php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo Cuentas por cobrar
 * 
 * Proporciona métodos para gestionar cuentas_cobrar en la base de datos,
 * incluyendo operaciones CRUD y utilidades de consulta.
 */
class AccountsReceivable extends Database {
    /**
     * Obtiene todos los cuentas_cobrar activos registrados en la base de datos.
     * 
     * @return array Lista de cuentas_cobrar (cada cliente es un array asociativo).
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT cc.cuenta_cobrar_id, cc.credito_id, cc.emision, cc.vencimiento, cc.estado,
                       c.nombre_cliente, c.cliente_ced, v.monto_total
                FROM cuentas_cobrar cc
                LEFT JOIN credito cr ON cc.credito_id = cr.credito_id
                LEFT JOIN ventas v ON cr.venta_id = v.venta_id
                LEFT JOIN clientes c ON v.cliente_ced = c.cliente_ced
                ORDER BY cc.cuenta_cobrar_id DESC
            ");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log("Error en getAll AccountsReceivable: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si la cuenta por cobrar existe.
     * 
     * @param int|string $cuentaPorCobrar Cédula del cliente.
     * @return bool True si existe, false si no.
     */
    public function accountExists($cuentaPorCobrar) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM cuentas_cobrar WHERE cuenta_cobrar_id = :cuenta_cobrar_id");
        $stmt->execute([':cuenta_cobrar_id' => $cuentaPorCobrar]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtiene un cliente por su cédula.
     * 
     * @param int|string $cuentaPorCobrar Cédula del cliente.
     * @return array|null Array asociativo con los datos del cliente o null si no existe.
     */
    public function getById($cuentaPorCobrar) {
        $stmt = $this->db->prepare("
            SELECT cc.cuenta_cobrar_id, cc.credito_id, cc.emision, cc.vencimiento, cc.estado,
                   c.nombre_cliente, c.cliente_ced
            FROM cuentas_cobrar cc
            LEFT JOIN credito cr ON cc.credito_id = cr.credito_id
            LEFT JOIN ventas v ON cr.venta_id = v.venta_id
            LEFT JOIN clientes c ON v.cliente_ced = c.cliente_ced
            WHERE cc.cuenta_cobrar_id = :cuenta_cobrar_id
        ");
        $stmt->execute([':cuenta_cobrar_id' => $cuentaPorCobrar]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Crea un nuevo cliente en la base de datos.
     * 
     * @param array $datos Datos del cliente a crear.
     * @return array Resultado de la operación (éxito o error).
     */
    public function create($datos) {
        try {
            $this->db->beginTransaction();
            
            // 1. Crear venta
            $stmtVenta = $this->db->prepare("
                INSERT INTO ventas (fecha, monto_total, cliente_ced, observaciones)
                VALUES (:fecha, :monto_total, :cliente_ced, :observaciones)
            ");
            $stmtVenta->execute([
                ':fecha' => $datos['fecha_emision'],
                ':monto_total' => $datos['monto_total'],
                ':cliente_ced' => $datos['cliente_id'],
                ':observaciones' => 'Factura: ' . $datos['factura_numero']
            ]);
            $venta_id = $this->db->lastInsertId();
            
            // 2. Crear crédito
            $stmtCredito = $this->db->prepare("
                INSERT INTO credito (venta_id, cuenta_cobrar_id)
                VALUES (:venta_id, NULL)
            ");
            $stmtCredito->execute([':venta_id' => $venta_id]);
            $credito_id = $this->db->lastInsertId();
            
            // 3. Crear cuenta por cobrar
            $stmtCuenta = $this->db->prepare("
                INSERT INTO cuentas_cobrar (credito_id, emision, vencimiento, estado)
                VALUES (:credito_id, :emision, :vencimiento, :estado)
            ");
            $stmtCuenta->execute([
                ':credito_id' => $credito_id,
                ':emision' => $datos['fecha_emision'],
                ':vencimiento' => $datos['fecha_vencimiento'],
                ':estado' => $datos['estado'] ?? 'pendiente'
            ]);
            $cuenta_cobrar_id = $this->db->lastInsertId();
            
            // 4. Actualizar el crédito con el ID de cuenta por cobrar
            $stmtUpdateCredito = $this->db->prepare("
                UPDATE credito SET cuenta_cobrar_id = :cuenta_cobrar_id WHERE credito_id = :credito_id
            ");
            $stmtUpdateCredito->execute([
                ':cuenta_cobrar_id' => $cuenta_cobrar_id,
                ':credito_id' => $credito_id
            ]);
            
            $this->db->commit();
            return ['success' => true, 'id' => $cuenta_cobrar_id];
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Error en create AccountsReceivable: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Actualiza los datos de un cliente existente.
     * 
     * @param int|string $cuentaPorCobrar Cédula del cliente.
     * @param string $nombre Nombre del cliente.
     * @param string $direccion Dirección del cliente.
     * @param string $telefono Teléfono del cliente.
     * @param string $membresia Tipo de membresía del cliente.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     * @throws Exception Si el cliente no existe.
     */
    public function update($cuentaPorCobrar, $nombre, $direccion, $telefono, $membresia) {
        $stmt = $this->db->prepare("
            UPDATE cuentas_cobrar SET 
                cliente_id = :cliente_id,
                factura_numero = :factura_numero,
                fecha_emision = :fecha_emision,
                fecha_vencimiento = :fecha_vencimiento,
                monto_total = :monto_total,
                estado = :estado
            WHERE id = :id
        ");

        $datos['cuentaPorCobrar'] = $cuentaPorCobrar;
        return $stmt->execute($datos);
    }

    /**
     * Elimina lógicamente un cliente por su cédula (marcándolo como inactivo).
     * 
     * @param int|string $cuentaPorCobrar Cédula del cliente a eliminar.
     * @return bool True si se eliminó correctamente, false en caso contrario.
     */
    public function delete($cuentaPorCobrar) {
        $stmt = $this->db->prepare("UPDATE cuentas_cobrar SET activo = 0 WHERE id = :id");
        return $stmt->execute([':cuentaPorCobrar' => $cuentaPorCobrar]);
    }
}