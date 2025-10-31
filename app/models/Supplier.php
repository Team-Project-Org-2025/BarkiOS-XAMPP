<?php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;
use PDOException;


class Supplier extends Database{

    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM proveedores WHERE activo = 1");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function supplierExists($proveedor_rif) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM proveedores WHERE proveedor_rif = :proveedor_rif");
        $stmt->execute([':proveedor_rif' => $proveedor_rif]);
        return $stmt->fetchColumn() > 0;
    }

    public function getById($proveedor_rif) {
        $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE proveedor_rif = :proveedor_rif");
        $stmt->execute([':proveedor_rif' => $proveedor_rif]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

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

    public function delete($proveedor_rif) {
        $stmt = $this->db->prepare("UPDATE proveedores SET activo = 0 WHERE proveedor_rif = :proveedor_rif");
        return $stmt->execute([':proveedor_rif' => $proveedor_rif]);
    }

    public function search($term) {
        try {
            $sql = "SELECT 
                        proveedor_rif AS rif,
                        nombre_empresa,
                        nombre_contacto
                    FROM proveedores
                    WHERE activo = 1
                      AND (
                          nombre_empresa LIKE :term
                          OR nombre_contacto LIKE :term
                          OR proveedor_rif LIKE :term
                      )
                    ORDER BY nombre_empresa ASC
                    LIMIT 10";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([':term' => "%$term%"]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('❌ Error en Supplier::search - ' . $e->getMessage());
            return [];
        }
    }
}
