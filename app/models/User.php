<?php
// app/models/User.php
namespace Barkios\models;

use Barkios\core\Database; 
use PDO; 
use Exception;

class User extends Database { 
    // ... (constructor y otros métodos)

    public function authenticate($email, $password) {
        try {
            // 1. Obtener el usuario
            $sql = "SELECT id, email, password_hash, nombre FROM users WHERE email = :email";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($user) {
                // 🚨 CÓDIGO INSEGURO: Se compara el texto plano ingresado
                // con el valor almacenado en la columna 'password_hash'.
                if ($password === $user['password_hash']) { 
                    
                    // 🚨 VERIFICA ESTO: Si tu columna es VARCHAR(255), 
                    // la comparación debe ser segura.

                    // Autenticación exitosa
                    unset($user['password_hash']); 
                    return $user;
                }
            }
            
            return null; // Autenticación fallida

        } catch (Exception $e) {
            error_log("Error de autenticación: " . $e->getMessage());
            return null;
        }
    }
}