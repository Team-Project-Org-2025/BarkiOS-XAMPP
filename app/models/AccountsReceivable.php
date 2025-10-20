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
                SELECT cc.*, 
                        CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente
                FROM cuentas_cobrar cc
                JOIN clientes c ON cc.cliente_id = c.id
                WHERE cc.activo = 1 
                ORDER BY cc.id ASC
            ");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
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
            SELECT cc.*, 
                    CONCAT(c.nombre, ' ', c.apellido) as nombre_cliente
            FROM cuentas_cobrar cc
            JOIN clientes c ON cc.cliente_id = c.id
            WHERE cc.id = :id
        ");
        $stmt->execute([':cuenta_cobrar_id' => $cuentaPorCobrar]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Agrega un nuevo cliente a la base de datos.
     * 
     * @param int|string $cuentaPorCobrar Cédula del cliente.
     * @param string $nombre Nombre del cliente.
     * @param string $direccion Dirección del cliente.
     * @param string $telefono Teléfono del cliente.
     * @param string $membresia Tipo de membresía del cliente.
     * @return bool True si se insertó correctamente, false en caso contrario.
     * @throws Exception Si el cliente ya existe.
     */
    public function add($datos) {
        $stmt = $this->db->prepare("
            INSERT INTO cuentas_cobrar (
                cliente_id, factura_numero, fecha_emision, 
                fecha_vencimiento, monto_total, estado
            ) VALUES (
                :cliente_id, :factura_numero, :fecha_emision, 
                :fecha_vencimiento, :monto_total, :estado
            )
        ");

        return $stmt->execute([
            ':factura_numero' => $datos['factura_numero'],
            ':cliente_id' => $datos['cliente_id'],
            ':fecha_emision' => $datos['fecha_emision'],
            ':fecha_vencimiento' => $datos['fecha_vencimiento'],
            ':monto_total' => $datos['monto_total'],
            ':estado' => $datos['estado'] ?? 'Pendiente'
        ]);
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