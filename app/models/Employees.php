<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\models\Employees.php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;


class Employees extends Database {

    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT empleado_ced, nombre, telefono, cargo, fecha_ingreso 
                FROM empleados 
                WHERE activo = 1 
                ORDER BY nombre ASC
            ");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log("Error en getAll: " . $e->getMessage());
            return [];
        }
    }

    public function employeeExists($cedula) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM empleados WHERE empleado_ced = :empleado_ced");
        $stmt->execute([':empleado_ced' => $cedula]);
        return $stmt->fetchColumn() > 0;
    }


    public function getById($cedula) {
        $stmt = $this->db->prepare("
            SELECT empleado_ced, nombre, telefono, cargo, fecha_ingreso 
            FROM empleados 
            WHERE empleado_ced = :empleado_ced AND activo = 1
        ");
        $stmt->execute([':empleado_ced' => $cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function add($cedula, $nombre, $telefono, $cargo = 'Empleado') {
        if ($this->employeeExists($cedula)) {
            throw new Exception("Ya existe un empleado con esta cédula");
        }
        
        $fecha_ingreso = date('Y-m-d');
        
        $stmt = $this->db->prepare("
            INSERT INTO empleados (empleado_ced, nombre, telefono, cargo, fecha_ingreso, activo)
            VALUES (:empleado_ced, :nombre, :telefono, :cargo, :fecha_ingreso, 1)
        ");
        return $stmt->execute([
            ':empleado_ced' => $cedula,
            ':nombre' => $nombre,
            ':telefono' => $telefono,
            ':cargo' => $cargo,
            ':fecha_ingreso' => $fecha_ingreso
        ]);
    }


    public function update($cedula, $nombre, $telefono, $cargo = 'Empleado') {
        if (!$this->employeeExists($cedula)) {
            throw new Exception("No existe un empleado con esta cédula");
        }
        
        $stmt = $this->db->prepare("
            UPDATE empleados 
            SET nombre = :nombre, 
                telefono = :telefono,
                cargo = :cargo
            WHERE empleado_ced = :empleado_ced
        ");
        return $stmt->execute([
            ':empleado_ced' => $cedula,
            ':nombre' => $nombre,
            ':telefono' => $telefono,
            ':cargo' => $cargo
        ]);
    }

    public function delete($cedula) {
        $stmt = $this->db->prepare("UPDATE empleados SET activo = 0 WHERE empleado_ced = :empleado_ced");
        return $stmt->execute([':empleado_ced' => $cedula]);
    }

    public function searchEmployees($query) {
        try {
            $stmt = $this->db->prepare("
                SELECT empleado_ced, nombre, telefono, cargo
                FROM empleados 
                WHERE activo = 1 
                  AND nombre LIKE :query
                ORDER BY nombre ASC
                LIMIT 20
            ");
            $searchTerm = '%' . $query . '%';
            $stmt->execute([':query' => $searchTerm]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log("Error en searchEmployees: " . $e->getMessage());
            return [];
        }
    }
}