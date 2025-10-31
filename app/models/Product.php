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


    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT * FROM prendas 
                WHERE activo = 1 AND estado IN ('DISPONIBLE', 'VENDIDA')
                ORDER BY prenda_id ASC
            ");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log("Error al obtener productos: " . $e->getMessage());
            return [];
        }
    }

    public function getDisponibles() {
        $stmt = $this->db->query("
            SELECT * FROM prendas
            WHERE activo = 1 AND estado = 'DISPONIBLE'
            ORDER BY prenda_id ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getById(int $id) {
        $stmt = $this->db->prepare("
            SELECT * FROM prendas
            WHERE prenda_id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }


    public function productExists(int $id): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM prendas WHERE prenda_id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetchColumn() > 0;
    }


    public function add(int $id, string $nombre, string $tipo, string $categoria, float $precio, ?string $imagen = null, ?string $descripcion = null) {
        // Verificar si existe por prenda_id
        if ($this->productExists($id)) {
            throw new Exception("Ya existe un producto con este código (prenda_id: $id).");
        }
        
        $stmtCheck = $this->db->prepare("SELECT COUNT(*) FROM prendas WHERE codigo_prenda = :codigo");
        $stmtCheck->execute([':codigo' => $id]);
        if ($stmtCheck->fetchColumn() > 0) {
            throw new Exception("Ya existe un producto con este código de prenda: $id");
        }

        $stmt = $this->db->prepare("
            INSERT INTO prendas (codigo_prenda, nombre, tipo, categoria, precio, imagen, descripcion, activo, estado)
            VALUES (:codigo_prenda, :nombre, :tipo, :categoria, :precio, :imagen, :descripcion, 1, 'DISPONIBLE')
        ");
        
        return $stmt->execute([
            ':codigo_prenda' => $id,
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':categoria' => $categoria,
            ':precio' => $precio,
            ':imagen' => $imagen,
            ':descripcion' => $descripcion
        ]);
    }

    public function update(int $id, string $nombre, string $tipo, string $categoria, float $precio, ?string $imagen = null, ?string $descripcion = null, bool $updateImage = false) {
        if (!$this->productExists($id)) {
            throw new Exception("No existe el producto con ID: $id");
        }

        $product = $this->getById($id);
        if ($product && $product['estado'] !== 'DISPONIBLE') {
            throw new Exception("No se puede editar una prenda vendida o eliminada");
        }

        if ($updateImage && $imagen !== null) {
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
                ':nombre' => $nombre,
                ':tipo' => $tipo,
                ':categoria' => $categoria,
                ':precio' => $precio,
                ':imagen' => $imagen,
                ':descripcion' => $descripcion
            ];
        } else {

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
                ':nombre' => $nombre,
                ':tipo' => $tipo,
                ':categoria' => $categoria,
                ':precio' => $precio,
                ':descripcion' => $descripcion
            ];
        }

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function marcarVendida($id) {
        return $this->db
            ->prepare("UPDATE prendas SET estado = 'VENDIDA' WHERE prenda_id = :prenda_id")
            ->execute([':prenda_id' => $id]);
    }


    public function liberarPrenda($id) {
        return $this->db
            ->prepare("UPDATE prendas SET estado = 'DISPONIBLE' WHERE prenda_id = :prenda_id")
            ->execute([':prenda_id' => $id]);
    }

    public function delete($id) {
        return $this->db
            ->prepare("UPDATE prendas SET activo = 0, estado = 'ELIMINADA' WHERE prenda_id = :prenda_id")
            ->execute([':prenda_id' => $id]);
    }


    public function deletePhysically(int $id) {
        $stmt = $this->db->prepare("DELETE FROM prendas WHERE prenda_id = :id");
        return $stmt->execute([':id' => $id]);
    }


    public function getImagePath(int $id): ?string {
        $product = $this->getById($id);
        return $product['imagen'] ?? null;
    }


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

    public function removeImage(int $id) {
        $stmt = $this->db->prepare("
            UPDATE prendas 
            SET imagen = NULL, fec_actualizacion = NOW() 
            WHERE prenda_id = :id
        ");
        return $stmt->execute([':id' => $id]);
    }
    public function getLatest(int $limit = 8)
{
    $stmt = $this->db->prepare("
        SELECT * FROM prendas
        WHERE activo = 1 AND estado = 'DISPONIBLE'
        ORDER BY fecha_creacion DESC
        LIMIT :limit
    ");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getByCategoria(string $categoria, ?int $limit = null)
{
    $sql = "SELECT * FROM prendas
            WHERE activo = 1 AND estado = 'DISPONIBLE'
            AND categoria = :categoria
            ORDER BY fecha_creacion DESC";
    if ($limit) $sql .= " LIMIT :limit";

    $stmt = $this->db->prepare($sql);
    $stmt->bindValue(':categoria', $categoria, PDO::PARAM_STR);
    if ($limit) $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}