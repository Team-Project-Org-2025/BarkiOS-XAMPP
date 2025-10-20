<?php
// /app/controllers/admin/CreditnoteController.php
use Barkios\models\CreditNote;

/**
 * Controlador funcional de Notas de Crédito
 * Compatible con FrontController funcional (sin clases)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once ROOT_PATH . 'app/models/CreditNote.php';

/**
 * Función principal (equivalente a index())
 */
function index() {
    $creditNoteModel = new CreditNote();
    $basePath = '/BarkiOS';

    checkAuth($basePath);

    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            handleAjaxRequest($creditNoteModel, $action);
        } else {
            handleWebRequest($creditNoteModel, $action, $basePath);
        }
    } catch (Exception $e) {
        handleError($e, $isAjax);
    }
}

/**
 * Verifica autenticación
 */
function checkAuth($basePath) {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
        header("Location: {$basePath}/login");
        exit();
    }
}

/**
 * Manejo de solicitudes AJAX
 */
function handleAjaxRequest($creditNoteModel, $action) {
    header('Content-Type: application/json; charset=utf-8');

    switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
        case 'POST_add_ajax':
            handleAddEditAjax($creditNoteModel, 'add');
            break;
        case 'POST_edit_ajax':
            handleAddEditAjax($creditNoteModel, 'edit');
            break;
        case 'POST_delete_ajax':
            handleDeleteAjax($creditNoteModel);
            break;
        case 'GET_get_notes':
            getCreditNotesAjax($creditNoteModel);
            break;
        case 'GET_get_by_id':
            getNoteByIdAjax($creditNoteModel);
            break;
        case 'POST_check_client':
            checkClientAjax($creditNoteModel);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción AJAX inválida']);
            exit();
    }
}

/**
 * Manejo de solicitudes Web normales
 */
function handleWebRequest($creditNoteModel, $action, $basePath) {
    switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
        case 'POST_add':
            handleAddCreditNote($creditNoteModel, $basePath);
            break;
        case 'POST_edit':
            handleEditCreditNote($creditNoteModel, $basePath);
            break;
        case 'POST_delete':
            handleDeleteCreditNote($creditNoteModel, $basePath);
            break;
        default:
            require ROOT_PATH . 'app/views/admin/credit-notes-admin.php';
    }
}

/**
 * Manejo de errores
 */
function handleError(Exception $e, bool $isAjax) {
    if ($isAjax) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } else {
        echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
    exit();
}

# ==========================================================
# MÉTODOS AJAX
# ==========================================================

function handleAddEditAjax($creditNoteModel, string $mode) {
    $required = ['cliente_cedula', 'monto_total', 'motivo'];
    $data = [];

    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo $field es requerido");
        }
        $data[$field] = trim($_POST[$field]);
    }

    if (!preg_match('/^\d{7,10}$/', $data['cliente_cedula'])) {
        throw new Exception("La cédula debe tener entre 7 y 10 dígitos");
    }

    $monto = (float)$data['monto_total'];
    if ($monto <= 0) {
        throw new Exception("El monto debe ser mayor a cero");
    }

    if (strlen($data['motivo']) < 10) {
        throw new Exception("El motivo debe tener al menos 10 caracteres");
    }

    if (!$creditNoteModel->clienteExiste($data['cliente_cedula'])) {
        throw new Exception("El cliente con cédula {$data['cliente_cedula']} no existe");
    }

    if ($mode === 'add') {
        $success = $creditNoteModel->add($data['cliente_cedula'], $monto, $data['motivo']);
        $message = 'Nota de Crédito creada exitosamente';
    } else {
        $noteId = $_POST['nota_id'] ?? null;
        if (!$noteId) throw new Exception("ID de nota no proporcionado");

        $success = $creditNoteModel->update($noteId, $data['cliente_cedula'], $monto, $data['motivo']);
        $message = 'Nota de Crédito actualizada exitosamente';
    }

    if ($success) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        throw new Exception("Error al procesar la nota de crédito");
    }
    exit();
}

function handleDeleteAjax($creditNoteModel) {
    $noteId = $_POST['nota_id'] ?? null;
    if (!$noteId) throw new Exception("ID de nota no proporcionado");

    $note = $creditNoteModel->getById($noteId);
    if (!$note) throw new Exception("La nota de crédito no existe");
    if ($note['estado'] !== 'ACTIVA') throw new Exception("Solo se pueden cancelar notas activas");

    $success = $creditNoteModel->delete($noteId);
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Nota de Crédito cancelada exitosamente']);
    } else {
        throw new Exception("Error al cancelar la nota de crédito");
    }
    exit();
}

function getCreditNotesAjax($creditNoteModel) {
    $notes = $creditNoteModel->getAll();
    echo json_encode(['success' => true, 'notes' => $notes, 'count' => count($notes)]);
    exit();
}

function getNoteByIdAjax($creditNoteModel) {
    $noteId = $_GET['id'] ?? null;
    if (!$noteId) throw new Exception("ID no proporcionado");

    $note = $creditNoteModel->getById($noteId);
    if ($note) {
        echo json_encode(['success' => true, 'note' => $note]);
    } else {
        throw new Exception("Nota no encontrada");
    }
    exit();
}

function checkClientAjax($creditNoteModel) {
    $cedula = $_POST['cedula'] ?? null;
    if (!$cedula) throw new Exception("Cédula no proporcionada");

    $existe = $creditNoteModel->clienteExiste($cedula);
    echo json_encode([
        'success' => true,
        'existe' => $existe,
        'message' => $existe ? 'Cliente encontrado' : 'Cliente no encontrado'
    ]);
    exit();
}

# ==========================================================
# MÉTODOS WEB
# ==========================================================

function handleAddCreditNote($creditNoteModel, $basePath) {
    $required = ['cliente_cedula', 'monto_total', 'motivo'];
    foreach ($required as $f) {
        if (empty($_POST[$f])) {
            header("Location: {$basePath}/creditnote?error=missing_fields");
            exit();
        }
    }

    $cedula = trim($_POST['cliente_cedula']);
    $monto = (float)$_POST['monto_total'];
    $motivo = trim($_POST['motivo']);

    $success = $creditNoteModel->add($cedula, $monto, $motivo);

    header("Location: {$basePath}/creditnote?" . ($success ? "success=add" : "error=failed_add"));
    exit();
}

function handleEditCreditNote($creditNoteModel, $basePath) {
    $noteId = $_POST['nota_id'] ?? null;
    if (!$noteId) {
        header("Location: {$basePath}/creditnote?error=missing_id");
        exit();
    }

    $required = ['cliente_cedula', 'monto_total', 'motivo'];
    foreach ($required as $f) {
        if (empty($_POST[$f])) {
            header("Location: {$basePath}/creditnote?error=missing_fields");
            exit();
        }
    }

    $cedula = trim($_POST['cliente_cedula']);
    $monto = (float)$_POST['monto_total'];
    $motivo = trim($_POST['motivo']);

    $success = $creditNoteModel->update($noteId, $cedula, $monto, $motivo);

    header("Location: {$basePath}/creditnote?" . ($success ? "success=edit" : "error=failed_edit"));
    exit();
}

function handleDeleteCreditNote($creditNoteModel, $basePath) {
    $noteId = $_POST['nota_id'] ?? null;
    if (!$noteId) {
        header("Location: {$basePath}/creditnote?error=missing_id");
        exit();
    }

    $success = $creditNoteModel->delete($noteId);
    header("Location: {$basePath}/creditnote?" . ($success ? "success=delete" : "error=failed_delete"));
    exit();
}
