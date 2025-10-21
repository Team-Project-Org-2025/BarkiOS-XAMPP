<?php
// app/models/User.php
namespace Barkios\models;

use Barkios\core\Database; 
use PDO; 
use Exception;

class User extends Database { 
    
    public function __construct() {
        // Llama al constructor de la clase padre (Database)
        parent::__construct(); 
    }

    // Función que asume que existe en la clase Database (necesaria para el controlador)
    public function getLastInsertId(): ?int {
        try {
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            return null;
        }
    }


    // =============================================================
    // LÓGICA DE AUTENTICACIÓN (Se mantiene la lógica de texto plano)
    // =============================================================

    public function authenticate($email, $password) {
        try {
            $sql = "SELECT id, email, password_hash, nombre FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user) {
                // 🚨 CÓDIGO INSEGURO: Comparación de texto plano (según tu lógica actual)
                if ($password === $user['password_hash']) { 
                    unset($user['password_hash']); 
                    return $user;
                }
            }
            return null;
        } catch (Exception $e) {
            error_log("Error de autenticación: " . $e->getMessage());
            return null;
        }
    }

    // =============================================================
    // 🚨 NUEVOS MÉTODOS CRUD PARA GESTIÓN DE EMPLEADOS (USUARIOS) 🚨
    // =============================================================
    
    /**
     * Obtiene todos los usuarios/empleados registrados.
     */
    public function getAll() {
        try {
            // Asumimos que no hay campo 'activo', si lo hay, agregarlo aquí
            $stmt = $this->db->query("SELECT id, email, nombre FROM users ORDER BY id ASC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
             // Es importante loguear el error para depurar problemas de DB
             error_log("Error al obtener todos los usuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si un usuario existe por su ID o Email.
     */
    public function userExists(int $id = null, string $email = null): bool {
        if ($id !== null) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
        } elseif ($email !== null) {
               $stmt = $this->db->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
        } else {
            return false;
        }
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtiene un usuario por su ID.
     */
    public function getById(int $id) {
        $stmt = $this->db->prepare("SELECT id, email, nombre FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Agrega un nuevo usuario/empleado.
     */
    public function add(string $nombre, string $email, string $password) {
        if ($this->userExists(null, $email)) {
            throw new Exception("Ya existe un usuario con este email.");
        }
        
        // 🚨 IMPORTANTE: Insertamos la contraseña en TEXTO PLANO 
        // para que coincida con tu lógica INSEGURA actual.
        // Se recomienda ALTAMENTE usar password_hash() aquí.
        $stmt = $this->db->prepare("
            INSERT INTO users (nombre, email, password_hash)
            VALUES (:nombre, :email, :password_hash)
        ");
        return $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password_hash' => $password // Insertando texto plano
        ]);
    }

    /**
     * Actualiza los datos de un usuario existente.
     * La contraseña solo se actualiza si se proporciona.
     */
    public function update(int $id, string $nombre, string $email, string $password = null) {
        if (!$this->userExists($id)) {
            throw new Exception("No existe el usuario con ID: $id");
        }
        
        $sql = "UPDATE users SET nombre = :nombre, email = :email WHERE id = :id";
        $params = [
            ':id' => $id,
            ':nombre' => $nombre,
            ':email' => $email
        ];

        if ($password) {
            // Si se proporciona una nueva contraseña, la actualizamos
            $sql = "UPDATE users SET nombre = :nombre, email = :email, password_hash = :password_hash WHERE id = :id";
            $params[':password_hash'] = $password; // Texto plano
        }
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Elimina lógicamente un usuario por su ID.
     */
    public function delete(int $id) {
        // Asumimos que haces una ELIMINACIÓN FÍSICA de la base de datos
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id"); 
        return $stmt->execute([':id' => $id]);
    }
}
