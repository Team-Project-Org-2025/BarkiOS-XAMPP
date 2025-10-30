<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\models\Employees.php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo Employees
 * 
 * Proporciona métodos para gestionar empleados en la base de datos,
 * incluyendo operaciones CRUD y utilidades de consulta.
 */
class Employees extends Database {
    /**
     * Obtiene todos los empleados activos registrados en la base de datos.
     * 
     * @return array Lista de empleados (cada empleado es un array asociativo).
     */
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

    /**
     * Verifica si un empleado existe por su cédula.
     * 
     * @param int|string $cedula Cédula del empleado.
     * @return bool True si existe, false si no.
     */
    public function employeeExists($cedula) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM empleados WHERE empleado_ced = :empleado_ced");
        $stmt->execute([':empleado_ced' => $cedula]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtiene un empleado por su cédula.
     * 
     * @param int|string $cedula Cédula del empleado.
     * @return array|null Array asociativo con los datos del empleado o null si no existe.
     */
    public function getById($cedula) {
        $stmt = $this->db->prepare("
            SELECT empleado_ced, nombre, telefono, cargo, fecha_ingreso 
            FROM empleados 
            WHERE empleado_ced = :empleado_ced AND activo = 1
        ");
        $stmt->execute([':empleado_ced' => $cedula]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Agrega un nuevo empleado a la base de datos.
     * 
     * @param int|string $cedula Cédula del empleado.
     * @param string $nombre Nombre completo del empleado.
     * @param string $telefono Teléfono del empleado.
     * @param string $cargo Cargo del empleado (por defecto: 'Empleado').
     * @return bool True si se insertó correctamente, false en caso contrario.
     * @throws Exception Si el empleado ya existe.
     */
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

    /**
     * Actualiza los datos de un empleado existente.
     * 
     * @param int|string $cedula Cédula del empleado.
     * @param string $nombre Nombre completo del empleado.
     * @param string $telefono Teléfono del empleado.
     * @param string $cargo Cargo del empleado.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     * @throws Exception Si el empleado no existe.
     */
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

    /**
     * Elimina lógicamente un empleado por su cédula (marcándolo como inactivo).
     * 
     * @param int|string $cedula Cédula del empleado a eliminar.
     * @return bool True si se eliminó correctamente, false en caso contrario.
     */
    public function delete($cedula) {
        $stmt = $this->db->prepare("UPDATE empleados SET activo = 0 WHERE empleado_ced = :empleado_ced");
        return $stmt->execute([':empleado_ced' => $cedula]);
    }

    /**
     * Busca empleados por nombre (autocompletado).
     * Realiza búsqueda incremental filtrando solo empleados activos.
     * 
     * @param string $query Texto de búsqueda para filtrar por nombre.
     * @return array Lista de empleados que coinciden con la búsqueda.
     */
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