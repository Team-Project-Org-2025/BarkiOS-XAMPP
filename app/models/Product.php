<?php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;

class Product extends Database {

    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT * FROM prendas 
                WHERE activo = 1 AND estado_prenda = 'disponible'
                ORDER BY prenda_id ASC
            ");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function productExists($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM prendas WHERE prenda_id = :prenda_id");
        $stmt->execute([':prenda_id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM prendas WHERE prenda_id = :prenda_id");
        $stmt->execute([':prenda_id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function add($id, $nombre, $tipo, $categoria, $precio) {
        if ($this->productExists($id)) throw new Exception("Ya existe un producto con este ID");

        $stmt = $this->db->prepare("
            INSERT INTO prendas (prenda_id, nombre, tipo, categoria, precio, activo, estado_prenda)
            VALUES (:prenda_id, :nombre, :tipo, :categoria, :precio, 1, 'disponible')
        ");
        return $stmt->execute([
            ':prenda_id' => $id,
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':categoria' => $categoria,
            ':precio' => $precio
        ]);
    }

    public function update($id, $nombre, $tipo, $categoria, $precio) {
        $product = $this->getById($id);

        if (!$product) throw new Exception("No existe un producto con este ID");
        if ($product['estado_prenda'] !== 'disponible')
            throw new Exception("No se puede editar una prenda vendida o eliminada");

        $stmt = $this->db->prepare("
            UPDATE prendas 
            SET nombre = :nombre, tipo = :tipo, categoria = :categoria, precio = :precio
            WHERE prenda_id = :prenda_id
        ");

        return $stmt->execute([
            ':prenda_id' => $id,
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':categoria' => $categoria,
            ':precio' => $precio
        ]);
    }

    public function delete($id) {
        return $this->db
            ->prepare("UPDATE prendas SET activo = 0, estado_prenda = 'eliminada' WHERE prenda_id = :prenda_id")
            ->execute([':prenda_id' => $id]);
    }
}
