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
     * @return array Lista de notas de crédito
     */
    public function getAll() {
        try {
            $stmt = $this->db->query("
                SELECT * FROM notas_credito 
                WHERE estado = 'ACTIVA' 
                ORDER BY fecha DESC, nota_id DESC
            ");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\Throwable $e) {
            error_log("Error al obtener notas de crédito: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una nota de crédito por ID.
     * @param int $id ID de la nota
     * @return array|null Datos de la nota o null si no existe
     */
    public function getById($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM notas_credito 
                WHERE nota_id = :id
            ");
            $stmt->execute([':id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\Throwable $e) {
            error_log("Error al obtener nota por ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Agrega una nueva Nota de Crédito.
     * @param string $cedula Cédula del cliente
     * @param float $monto Monto total de la nota
     * @param string $motivo Motivo de la nota
     * @return bool True si se agregó correctamente
     */
    public function add($cedula, $monto, $motivo) {
        try {
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
        } catch (\Throwable $e) {
            error_log("Error al agregar nota de crédito: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una nota de crédito existente.
     * @param int $id ID de la nota
     * @param string $cedula Cédula del cliente
     * @param float $monto Monto total
     * @param string $motivo Motivo de la nota
     * @return bool True si se actualizó correctamente
     */
    public function update($id, $cedula, $monto, $motivo) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notas_credito 
                SET cliente_cedula = :cedula, 
                    monto_total = :monto, 
                    motivo = :motivo
                WHERE nota_id = :id
            ");
            return $stmt->execute([
                ':id' => $id,
                ':cedula' => $cedula,
                ':monto' => $monto,
                ':motivo' => $motivo
            ]);
        } catch (\Throwable $e) {
            error_log("Error al actualizar nota de crédito: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cambia el estado de una nota de crédito.
     * @param int $id ID de la nota
     * @param string $estado Nuevo estado (ACTIVA, USADA, CANCELADA)
     * @return bool True si se actualizó correctamente
     */
    public function updateStatus($id, $estado) {
        try {
            $validStates = ['ACTIVA', 'USADA', 'CANCELADA'];
            if (!in_array($estado, $validStates)) {
                throw new Exception("Estado inválido");
            }

            $stmt = $this->db->prepare("
                UPDATE notas_credito 
                SET estado = :estado 
                WHERE nota_id = :id
            ");
            return $stmt->execute([
                ':id' => $id,
                ':estado' => $estado
            ]);
        } catch (\Throwable $e) {
            error_log("Error al actualizar estado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina (desactiva) una nota de crédito.
     * @param int $id ID de la nota
     * @return bool True si se eliminó correctamente
     */
    public function delete($id) {
        try {
            $stmt = $this->db->prepare("
                UPDATE notas_credito 
                SET estado = 'CANCELADA' 
                WHERE nota_id = :id
            ");
            return $stmt->execute([':id' => $id]);
        } catch (\Throwable $e) {
            error_log("Error al eliminar nota de crédito: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si existe un cliente por cédula.
     * @param string $cedula Cédula del cliente
     * @return bool True si existe
     */
    public function clienteExiste($cedula) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count 
                FROM clientes 
                WHERE cedula = :cedula
            ");
            $stmt->execute([':cedula' => $cedula]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result && $result['count'] > 0;
        } catch (\Throwable $e) {
            error_log("Error al verificar cliente: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el saldo disponible de notas de crédito para un cliente.
     * @param string $cedula Cédula del cliente
     * @return float Saldo total disponible
     */
    public function getSaldoCliente($cedula) {
        try {
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(monto_total), 0) as saldo 
                FROM notas_credito 
                WHERE cliente_cedula = :cedula 
                AND estado = 'ACTIVA'
            ");
            $stmt->execute([':cedula' => $cedula]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (float)$result['saldo'] : 0.0;
        } catch (\Throwable $e) {
            error_log("Error al obtener saldo: " . $e->getMessage());
            return 0.0;
        }
    }
}