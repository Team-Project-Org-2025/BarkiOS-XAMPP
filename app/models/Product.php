<?php
// app/models/Product.php
namespace Barkios\models;

use Barkios\core\Database;
use PDO;
use Exception;

class Product extends Database {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Obtiene todos los productos activos
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT prenda_id, nombre, tipo, categoria, precio, imagen, descripcion, activo 
                FROM prendas 
                WHERE activo = 1 
                ORDER BY fecha_creacion DESC
            ");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log("Error al obtener productos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un producto por su ID
     */
    public function getById(int $id) {
        $stmt = $this->db->prepare("
            SELECT prenda_id, nombre, tipo, categoria, precio, imagen, descripcion, activo 
            FROM prendas 
            WHERE prenda_id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Verifica si un producto existe
     */
    public function exists(int $id): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM prendas WHERE prenda_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Agrega un nuevo producto CON imagen
     */
    public function add(array $data) {
        if ($this->exists($data['prenda_id'])) {
            throw new Exception("Ya existe un producto con este código.");
        }

        $stmt = $this->db->prepare("
            INSERT INTO prendas (prenda_id, nombre, tipo, categoria, precio, imagen, descripcion)
            VALUES (:prenda_id, :nombre, :tipo, :categoria, :precio, :imagen, :descripcion)
        ");
        
        return $stmt->execute([
            ':prenda_id' => $data['prenda_id'],
            ':nombre' => $data['nombre'],
            ':tipo' => $data['tipo'],
            ':categoria' => $data['categoria'],
            ':precio' => $data['precio'],
            ':imagen' => $data['imagen'] ?? null,
            ':descripcion' => $data['descripcion'] ?? null
        ]);
    }

    /**
     * Actualiza un producto existente
     * Permite actualizar la imagen opcionalmente
     */
    public function update(int $id, array $data) {
        if (!$this->exists($id)) {
            throw new Exception("No existe el producto con ID: $id");
        }

        // Construir consulta dinámicamente según si hay nueva imagen
        if (isset($data['imagen'])) {
            $sql = "UPDATE prendas SET 
                    nombre = :nombre, 
                    tipo = :tipo, 
                    categoria = :categoria, 
                    precio = :precio,
                    imagen = :imagen,
                    descripcion = :descripcion,
                    fec_actualizacion = NOW()
                    WHERE prenda_id = :prenda_id";
            
            $params = [
                ':prenda_id' => $id,
                ':nombre' => $data['nombre'],
                ':tipo' => $data['tipo'],
                ':categoria' => $data['categoria'],
                ':precio' => $data['precio'],
                ':imagen' => $data['imagen'],
                ':descripcion' => $data['descripcion'] ?? null
            ];
        } else {
            // Si no hay nueva imagen, no actualizar el campo
            $sql = "UPDATE prendas SET 
                    nombre = :nombre, 
                    tipo = :tipo, 
                    categoria = :categoria, 
                    precio = :precio,
                    descripcion = :descripcion,
                    fec_actualizacion = NOW()
                    WHERE prenda_id = :prenda_id";
            
            $params = [
                ':prenda_id' => $id,
                ':nombre' => $data['nombre'],
                ':tipo' => $data['tipo'],
                ':categoria' => $data['categoria'],
                ':precio' => $data['precio'],
                ':descripcion' => $data['descripcion'] ?? null
            ];
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Elimina lógicamente un producto
     */
    public function delete(int $id) {
        $stmt = $this->db->prepare("UPDATE prendas SET activo = 0 WHERE prenda_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Elimina físicamente un producto (usar con precaución)
     */
    public function deletePhysically(int $id) {
        $stmt = $this->db->prepare("DELETE FROM prendas WHERE prenda_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Obtiene la ruta de la imagen de un producto
     */
    public function getImagePath(int $id): ?string {
        $product = $this->getById($id);
        return $product['imagen'] ?? null;
    }

    /**
     * Actualiza solo el campo imagen de un producto
     */
    public function updateImage(int $id, string $imagePath) {
        $stmt = $this->db->prepare("
            UPDATE prendas 
            SET imagen = :imagen, fec_actualizacion = NOW() 
            WHERE prenda_id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':imagen' => $imagePath
        ]);
    }

    /**
     * Elimina la referencia de imagen de un producto
     */
    public function removeImage(int $id) {
        $stmt = $this->db->prepare("
            UPDATE prendas 
            SET imagen = NULL, fec_actualizacion = NOW() 
            WHERE prenda_id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }
}