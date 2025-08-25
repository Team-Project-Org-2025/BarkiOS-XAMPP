<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\models\Product.php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo Product
 * 
 * Proporciona métodos para gestionar prendas en la base de datos,
 * incluyendo operaciones CRUD y utilidades de consulta.
 */
class Product extends Database {
    /**
     * Obtiene todos los productos activos registrados en la base de datos.
     * 
     * @return array Lista de productos (cada producto es un array asociativo).
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM prendas WHERE activo = 1 ORDER BY prenda_id ASC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Verifica si un producto existe por su ID.
     * 
     * @param int $id ID del producto.
     * @return bool True si existe, false si no.
     */
    public function productExists($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM prendas WHERE prenda_id = :prenda_id");
        $stmt->execute([':prenda_id' => $id]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtiene un producto por su ID.
     * 
     * @param int $id ID del producto.
     * @return array|null Array asociativo con los datos del producto o null si no existe.
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM prendas WHERE prenda_id = :prenda_id");
        $stmt->execute([':prenda_id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Agrega un nuevo producto a la base de datos.
     * 
     * @param int $id ID del producto.
     * @param string $nombre Nombre del producto.
     * @param string $tipo Tipo de prenda.
     * @param string $categoria Categoría de la prenda.
     * @param float $precio Precio del producto.
     * @return bool True si se insertó correctamente, false en caso contrario.
     * @throws Exception Si el producto ya existe.
     */
    public function add($id, $nombre, $tipo, $categoria, $precio) {
        if ($this->productExists($id)) throw new Exception("Ya existe un producto con este ID");
        $stmt = $this->db->prepare("INSERT INTO prendas (prenda_id, nombre, tipo, categoria, precio) VALUES (:prenda_id, :nombre, :tipo, :categoria, :precio)");
        return $stmt->execute([
            ':prenda_id' => $id,
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':categoria' => $categoria,
            ':precio' => $precio
        ]);
    }

    /**
     * Actualiza un producto existente.
     * 
     * @param int $id ID del producto.
     * @param string $nombre Nombre del producto.
     * @param string $tipo Tipo de prenda.
     * @param string $categoria Categoría de la prenda.
     * @param float $precio Precio del producto.
     * @return bool True si se actualizó correctamente, false en caso contrario.
     * @throws Exception Si el producto no existe.
     */
    public function update($id, $nombre, $tipo, $categoria, $precio) {
        if (!$this->productExists($id)) throw new Exception("No existe un producto con este ID");
        $stmt = $this->db->prepare("UPDATE prendas SET nombre = :nombre, tipo = :tipo, categoria = :categoria, precio = :precio WHERE prenda_id = :prenda_id");
        return $stmt->execute([
            ':prenda_id' => $id,
            ':nombre' => $nombre,
            ':tipo' => $tipo,
            ':categoria' => $categoria,
            ':precio' => $precio
        ]);
    }

    /**
     * Elimina lógicamente un producto por su ID (marcándolo como inactivo).
     * 
     * @param int $id ID del producto a eliminar.
     * @return bool True si se eliminó correctamente, false en caso contrario.
     */
    public function delete($id) {
        $stmt = $this->db->prepare("UPDATE prendas SET activo = 0 WHERE prenda_id = :prenda_id");
        return $stmt->execute([':prenda_id' => $id]);
    }
    
}
