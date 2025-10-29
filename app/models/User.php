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

    // =============================================================
    // ✅ AUTENTICACIÓN SEGURA CON PASSWORD_VERIFY
    // =============================================================

    /**
     * Autentica un usuario verificando email y contraseña hasheada
     * @param string $email
     * @param string $password Contraseña en texto plano
     * @return array|null Datos del usuario si es válido, null si no
     */
    public function authenticate($email, $password) {
        try {
            $sql = "SELECT id, email, password_hash, nombre FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user) {
                // ✅ VERIFICACIÓN SEGURA: Compara la contraseña con el hash
                if (password_verify($password, $user['password_hash'])) {
                    // Si el hash usa un algoritmo antiguo, lo actualizamos
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

    /**
     * Actualiza el hash de contraseña (para rehashing automático)
     * @param int $userId
     * @param string $plainPassword
     */
    private function updatePasswordHash(int $userId, string $plainPassword): void {
        try {
            $newHash = password_hash($plainPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
            $stmt->execute([':hash' => $newHash, ':id' => $userId]);
        } catch (\Throwable $e) {
            error_log("Error actualizando hash: " . $e->getMessage());
        }
    }

    // =============================================================
    // ✅ MÉTODOS CRUD CON HASHING SEGURO
    // =============================================================
    
    /**
     * Obtiene todos los usuarios/empleados registrados.
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT id, email, nombre FROM users ORDER BY id ASC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log("Error al obtener todos los usuarios: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica si un usuario existe por su ID o Email.
     */
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

    /**
     * Obtiene un usuario por su ID.
     */
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

    /**
     * ✅ Agrega un nuevo usuario con contraseña hasheada
     * @param string $nombre
     * @param string $email
     * @param string $password Contraseña en texto plano (se hasheará automáticamente)
     * @throws Exception
     */
    public function add(string $nombre, string $email, string $password) {
        if ($this->userExists(null, $email)) {
            throw new Exception("Ya existe un usuario con este email.");
        }
        
        // ✅ HASHEAR LA CONTRASEÑA DE FORMA SEGURA
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

    /**
     * ✅ Actualiza los datos de un usuario existente con contraseña hasheada
     * @param int $id
     * @param string $nombre
     * @param string $email
     * @param string|null $password Contraseña en texto plano (opcional)
     * @throws Exception
     */
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
            // ✅ HASHEAR LA NUEVA CONTRASEÑA
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

    /**
     * Elimina físicamente un usuario por su ID.
     */
    public function delete(int $id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id"); 
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log("Error en delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ Verifica si una contraseña cumple con los requisitos de seguridad
     * @param string $password
     * @return bool
     */
    public function isPasswordStrong(string $password): bool {
        // Mínimo 8 caracteres, mayúsculas, minúsculas, números y símbolos
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&._-])[A-Za-z\d@$!%*?&._-]{8,}$/', $password) === 1;
    }
}