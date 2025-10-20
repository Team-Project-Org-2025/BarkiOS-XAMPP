<?php
// /app/models/CreditNote.php
namespace Barkios\models;
use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo CreditNote
 * Gestiona las operaciones CRUD para las Notas de Crédito.
 */
class CreditNote extends Database {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Obtiene todas las notas de crédito activas.
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM notas_credito WHERE estado = 'ACTIVA' ORDER BY fecha DESC");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log("Error al obtener notas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Agrega una nueva Nota de Crédito.
     */
    public function add($cedula, $monto, $motivo) {
        $fecha = date('Y-m-d');
        $stmt = $this->db->prepare("
            INSERT INTO notas_credito (cliente_cedula, fecha, monto_total, motivo, estado) 
            VALUES (:cedula, :fecha, :monto, :motivo, 'ACTIVA')
        ");
        return $stmt->execute([
            ':cedula' => $cedula,
            ':fecha' => $fecha,
            ':monto' => $monto,
            ':motivo' => $motivo
        ]);
    }
    
    // (Puedes agregar métodos como getById, updateStatus, etc.)
}