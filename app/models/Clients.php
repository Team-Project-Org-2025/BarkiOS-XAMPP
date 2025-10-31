<?php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;

class Clients extends Database {

    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM clientes WHERE activo = 1 ORDER BY cliente_ced ASC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function clientExists($cedula) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM clientes WHERE cliente_ced = :cliente_ced");
        $stmt->execute([':cliente_ced' => $cedula]);
        return $stmt->fetchColumn() > 0;
    }


    public function getById($cedula) {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE cliente_ced = :cliente_ced");
        $stmt->execute([':cliente_ced' => $cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

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


    public function delete($cedula) {
        $stmt = $this->db->prepare("UPDATE clientes SET activo = 0 WHERE cliente_ced = :cliente_ced");
        return $stmt->execute([':cliente_ced' => $cedula]);
    }


    public function searchVipClients($query) {
        try {
            $stmt = $this->db->prepare("
                SELECT cliente_ced, nombre_cliente, telefono, correo, tipo
                FROM clientes 
                WHERE tipo = 'vip' 
                  AND activo = 1 
                  AND nombre_cliente LIKE :query
                ORDER BY nombre_cliente ASC
                LIMIT 20
            ");
            $searchTerm = $query . '%';
            $stmt->execute([':query' => $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("Error en searchVipClients: " . $e->getMessage());
            return [];
        }
    }
}