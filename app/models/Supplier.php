<?php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;
use PDOException;

/**
 * Modelo Supplier
 * 
 * Proporciona métodos para gestionar proveedores en la base de datos,
 * incluyendo operaciones CRUD y utilidades de consulta.
 */
class Supplier extends Database{

    /**
     * Obtiene todos los proveedores activos.
     * 
     * @return array Lista de proveedores activos.
     */
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM proveedores WHERE activo = 1");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verifica si un proveedor existe por su RIF.
     * 
     * @param int|string $proveedor_rif RIF del proveedor.
     * @return bool True si existe, false si no.
     */
    public function supplierExists($proveedor_rif) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM proveedores WHERE proveedor_rif = :proveedor_rif");
        $stmt->execute([':proveedor_rif' => $proveedor_rif]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtiene un proveedor por su RIF.
     * 
     * @param int|string $proveedor_rif RIF del proveedor.
     * @return array|null Array asociativo con los datos del proveedor o null si no existe.
     */
    public function getById($proveedor_rif) {
        $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE proveedor_rif = :proveedor_rif");
        $stmt->execute([':proveedor_rif' => $proveedor_rif]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Agrega un nuevo proveedor a la base de datos.
     * 
     * @param int|string $proveedor_rif RIF del proveedor.
     * @param string $nombre_contacto Nombre del contacto.
     * @param string $nombre_empresa Nombre de la empresa.
     * @param string $direccion Dirección del proveedor.
     * @param string $tipo_rif Tipo de RIF.
     * @return bool True si se insertó correctamente, false en caso contrario.
     * @throws Exception Si el proveedor ya existe.
     */
    public function add($proveedor_rif, $nombre_contacto, $nombre_empresa, $direccion, $tipo_rif) {
        if ($this->supplierExists($proveedor_rif)) {
            throw new Exception("Ya existe un proveedor con este RIF");
        }
        try {
            $stmt = $this->db->prepare("
                INSERT INTO proveedores (proveedor_rif, nombre_empresa, nombre_contacto, direccion, tipo_rif)
                VALUES (:proveedor_rif, :nombre_empresa, :nombre_contacto, :direccion, :tipo_rif)
            ");
            return $stmt->execute([
                ':proveedor_rif' => $proveedor_rif,
                ':nombre_contacto' => $nombre_contacto,
                ':nombre_empresa' => $nombre_empresa,
                ':direccion' => $direccion,
                ':tipo_rif' => $tipo_rif
            ]);
        } catch (PDOException $e) {
            error_log('Error al agregar proveedor: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza los datos de un proveedor existente.
     * 
     * @param int|string $proveedor_rif RIF del proveedor.
     * @param string $nombre_contacto Nombre del contacto.
     * @param string $nombre_empresa Nombre de la empresa.
     * @param string $direccion Dirección del proveedor.
     * @param string $tipo_rif Tipo de RIF.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     * @throws Exception Si el proveedor no existe.
     */
    public function update($proveedor_rif, $nombre_contacto, $nombre_empresa, $direccion, $tipo_rif) {
        if (!$this->supplierExists($proveedor_rif)) {
            throw new Exception("No existe un proveedor con este RIF");
        }
        $stmt = $this->db->prepare("
            UPDATE proveedores
            SET nombre_contacto = :nombre_contacto,
                nombre_empresa = :nombre_empresa,
                direccion = :direccion,
                tipo_rif = :tipo_rif
            WHERE proveedor_rif = :proveedor_rif
        ");
        return $stmt->execute([
            ':proveedor_rif' => $proveedor_rif,
            ':nombre_contacto' => $nombre_contacto,
            ':nombre_empresa' => $nombre_empresa,
            ':direccion' => $direccion,
            ':tipo_rif' => $tipo_rif
        ]);
    }

    /**
     * Elimina lógicamente un proveedor (marcándolo como inactivo).
     * 
     * @param int|string $proveedor_rif RIF del proveedor a eliminar.
     * @return bool True si se eliminó correctamente, false en caso contrario.
     */
    public function delete($proveedor_rif) {
        $stmt = $this->db->prepare("UPDATE proveedores SET activo = 0 WHERE proveedor_rif = :proveedor_rif");
        return $stmt->execute([':proveedor_rif' => $proveedor_rif]);
    }
}
