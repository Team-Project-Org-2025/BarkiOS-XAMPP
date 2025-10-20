<?php
// /app/controllers/admin/CreditNoteController.php
namespace Barkios\controllers\admin; 

use Barkios\models\CreditNote;
use Exception;
// Importamos la CLASE LoginController para usar su método estático de seguridad
use Barkios\controllers\admin\LoginController; 

// =================================================================
// 🚨 CÓDIGO DE INICIALIZACIÓN Y SEGURIDAD 🚨
// =================================================================

// 1. Fallback de ROOT_PATH (debe estar definido en index.php)
if (!defined('ROOT_PATH')) {
    // Si no se definió en index.php, calculamos la ruta del proyecto
    define('ROOT_PATH', dirname(__DIR__, 3) . '/');
}
$basePath = '/BarkiOS'; 

// 2. Inicializar Modelo
$creditNoteModel = new CreditNote();

// 3. Aplicar Seguridad antes de cualquier acción
// Usamos el FQN del LoginController para llamar su método estático checkAuth()
LoginController::checkAuth(); 

// 4. Ejecutar el enrutador principal
handleRequest($creditNoteModel, $basePath);

// =================================================================
// 🚨 FUNCIONES DE ENRUTAMIENTO Y LÓGICA 🚨
// =================================================================

/**
 * handleRequest (Enrutador principal)
 */
function handleRequest($creditNoteModel, $basePath) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add_ajax':   handleAddCreditNoteAjax($creditNoteModel); break;
                case 'GET_get_notes':   getCreditNotesAjax($creditNoteModel); break;
                default:                 echo json_encode(['success'=>false,'message'=>'Acción AJAX inválida']); exit();
            }
        } else {
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add':    handleAddCreditNote($creditNoteModel, $basePath); break;
                default:   
                    // 🚨 RUTA CORREGIDA: Carga la vista principal usando ROOT_PATH
                    require ROOT_PATH . 'app/views/admin/credit-notes-admin.php';
            }
        }
    } catch (Exception $e) {
        if ($isAjax) {
            http_response_code(500);
            echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
        } else {
            die("Error: " . $e->getMessage());
        }
        exit();
    }
}

/**
 * handleAddCreditNote (Maneja la adición por formulario web)
 */
function handleAddCreditNote($creditNoteModel, $basePath) { 
    $required = ['cliente_cedula', 'monto_total', 'motivo'];
    foreach ($required as $f) {
        if (empty($_POST[$f])) throw new Exception("El campo $f es requerido");
    }
    
    $cedula = trim($_POST['cliente_cedula']);
    $monto = (float)$_POST['monto_total'];
    $motivo = trim($_POST['motivo']);

    $success = $creditNoteModel->add($cedula, $monto, $motivo);
    
    // 🚨 Redirección corregida (URL)
    if ($success) {
        header("Location: {$basePath}/credit-notes?success=add"); 
    } else {
        header("Location: {$basePath}/credit-notes?error=failed_add"); 
    }
    exit();
}

/**
 * handleAddCreditNoteAjax (Maneja la adición por AJAX)
 */
function handleAddCreditNoteAjax($creditNoteModel) {
    $required = ['cliente_cedula', 'monto_total', 'motivo'];
    $data = [];
    foreach ($required as $f) {
        if (empty($_POST[$f])) throw new Exception("El campo $f es requerido");
        $data[$f] = trim($_POST[$f]);
    }
    
    $monto = (float)$data['monto_total'];
    
    if ($monto <= 0) throw new Exception("El monto debe ser positivo.");

    $success = $creditNoteModel->add($data['cliente_cedula'], $monto, $data['motivo']);
    
    if ($success) {
        echo json_encode(['success'=>true, 'message'=>'Nota de Crédito agregada']);
    } else {
        throw new Exception("Error al crear la nota en la DB.");
    }
    exit();
}

/**
 * getCreditNotesAjax (Devuelve el listado de notas)
 */
function getCreditNotesAjax($creditNoteModel) {
    $notes = $creditNoteModel->getAll();
    echo json_encode(['success'=>true, 'notes'=>$notes, 'count'=>count($notes)]); exit();
}

// Nota: Puedes agregar funciones adicionales (edit, delete) si es necesario.