<?php
// app/models/User.php
namespace Barkios\models;

use Barkios\core\Database; 
use PDO; 
use Exception;

class User extends Database { 
    
    public function __construct() {
        parent::__construct(); 
    }

    public function getLastInsertId(): ?int {
        try {
            return (int)$this->db->lastInsertId();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function authenticate($email, $password) {
        try {
            $sql = "SELECT id, email, password_hash, nombre FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user) {
                if (password_verify($password, $user['password_hash'])) {

                    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
                        $this->updatePasswordHash($user['id'], $password);
                    }
                    
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

    private function updatePasswordHash(int $userId, string $plainPassword): void {
        try {
            $newHash = password_hash($plainPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            $stmt->execute([':hash' => $newHash, ':id' => $userId]);
        } catch (\Throwable $e) {
            error_log("Error actualizando hash: " . $e->getMessage());
        }
    }

    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT id, email, nombre FROM users ORDER BY id ASC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log("Error al obtener todos los usuarios: " . $e->getMessage());
            return [];
        }
    }


    public function userExists(int $id = null, string $email = null): bool {
        try {
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
        } catch (\Throwable $e) {
            error_log("Error en userExists: " . $e->getMessage());
            return false;
        }
    }


    public function getById(int $id) {
        try {
            $stmt = $this->db->prepare("SELECT id, email, nombre FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\Throwable $e) {
            error_log("Error en getById: " . $e->getMessage());
            return null;
        }
    }

    public function add(string $nombre, string $email, string $password) {
        if ($this->userExists(null, $email)) {
            throw new Exception("Ya existe un usuario con este email.");
        }
        
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        if ($passwordHash === false) {
            throw new Exception("Error al hashear la contraseña");
        }
        
        $stmt = $this->db->prepare("
            INSERT INTO users (nombre, email, password_hash)
            VALUES (:nombre, :email, :password_hash)
        ");
        
        return $stmt->execute([
            ':nombre' => $nombre,
            ':email' => $email,
            ':password_hash' => $passwordHash
        ]);
    }


    public function update(int $id, string $nombre, string $email, string $password = null) {
        if (!$this->userExists($id)) {
            throw new Exception("No existe el usuario con ID: $id");
        }
        
        $sql = "UPDATE users SET nombre = :nombre, email = :email";
        $params = [
            ':id' => $id,
            ':nombre' => $nombre,
            ':email' => $email
        ];

        if ($password !== null && $password !== '') {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            
            if ($passwordHash === false) {
                throw new Exception("Error al hashear la contraseña");
            }
            
            $sql .= ", password_hash = :password_hash";
            $params[':password_hash'] = $passwordHash;
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }


    public function delete(int $id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id"); 
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log("Error en delete: " . $e->getMessage());
            return false;
        }
    }

    public function isPasswordStrong(string $password): bool {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&._-])[A-Za-z\d@$!%*?&._-]{8,}$/', $password) === 1;
    }
}