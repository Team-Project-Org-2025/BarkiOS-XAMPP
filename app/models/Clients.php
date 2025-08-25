<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\models\Product.php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo Clients
 * 
 * Proporciona métodos para gestionar clientes en la base de datos,
 * incluyendo operaciones CRUD y utilidades de consulta.
 */
class Clients extends Database {
    /**
     * Obtiene todos los clientes activos registrados en la base de datos.
     * 
     * @return array Lista de clientes (cada cliente es un array asociativo).
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM clientes WHERE activo = 1 ORDER BY cliente_ced ASC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Verifica si un cliente existe por su cédula.
     * 
     * @param int|string $cedula Cédula del cliente.
     * @return bool True si existe, false si no.
     */
    public function clientExists($cedula) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM clientes WHERE cliente_ced = :cliente_ced");
        $stmt->execute([':cliente_ced' => $cedula]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtiene un cliente por su cédula.
     * 
     * @param int|string $cedula Cédula del cliente.
     * @return array|null Array asociativo con los datos del cliente o null si no existe.
     */
    public function getById($cedula) {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE cliente_ced = :cliente_ced");
        $stmt->execute([':cliente_ced' => $cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Agrega un nuevo cliente a la base de datos.
     * 
     * @param int|string $cedula Cédula del cliente.
     * @param string $nombre Nombre del cliente.
     * @param string $direccion Dirección del cliente.
     * @param string $telefono Teléfono del cliente.
     * @param string $membresia Tipo de membresía del cliente.
     * @return bool True si se insertó correctamente, false en caso contrario.
     * @throws Exception Si el cliente ya existe.
     */
    public function add($cedula, $nombre, $direccion, $telefono, $membresia) {
        if ($this->clientExists($cedula)) {
            throw new Exception("Ya existe un cliente con esta cédula");
        }
        $stmt = $this->db->prepare("
            INSERT INTO clientes (cliente_ced, nombre_cliente, direccion, telefono, tipo)
            VALUES (:cliente_ced, :nombre_cliente, :direccion, :telefono, :tipo)
        ");
        return $stmt->execute([
            ':cliente_ced' => $cedula,
            ':nombre_cliente' => $nombre,
            ':direccion' => $direccion,
            ':telefono' => $telefono,
            ':tipo' => $membresia
        ]);
    }

    /**
     * Actualiza los datos de un cliente existente.
     * 
     * @param int|string $cedula Cédula del cliente.
     * @param string $nombre Nombre del cliente.
     * @param string $direccion Dirección del cliente.
     * @param string $telefono Teléfono del cliente.
     * @param string $membresia Tipo de membresía del cliente.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     * @throws Exception Si el cliente no existe.
     */
    public function update($cedula, $nombre, $direccion, $telefono, $membresia) {
        if (!$this->clientExists($cedula)) throw new Exception("No existe un cliente con esta cedula");
        $stmt = $this->db->prepare("UPDATE clientes SET nombre_cliente = :nombre_cliente, direccion = :direccion, telefono = :telefono, tipo = :tipo WHERE cliente_ced = :cliente_ced");
        return $stmt->execute([
            ':cliente_ced' => $cedula,
            ':nombre_cliente' => $nombre,
            ':direccion' => $direccion,
            ':telefono' => $telefono,
            ':tipo' => $membresia
        ]);
    }

    /**
     * Elimina lógicamente un cliente por su cédula (marcándolo como inactivo).
     * 
     * @param int|string $cedula Cédula del cliente a eliminar.
     * @return bool True si se eliminó correctamente, false en caso contrario.
     */
    public function delete($cedula) {
        $stmt = $this->db->prepare("UPDATE clientes SET activo = 0 WHERE cliente_ced = :cliente_ced");
        return $stmt->execute([':cliente_ced' => $cedula]);
    }
}