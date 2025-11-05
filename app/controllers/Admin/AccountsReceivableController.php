<?php
use Barkios\models\AccountsReceivable;
use Barkios\helpers\Validation;

require_once __DIR__ . '/LoginController.php';
checkAuth();

$accountsReceivableModel = new AccountsReceivable();
handleRequest($accountsReceivableModel);


function handleRequest($model)
{
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            handleAjax($model, $action);
        } else {
            if (empty($action)) {
                return null; 
            } else {
                throw new Exception("Acción no válida");
            }
        }
    } catch (Exception $e) {
        error_log("AccountsReceivableController Error: " . $e->getMessage());
        if ($isAjax) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } else {
            echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
        exit();
    }
}


function handleAjax($model, $action)
{
    $method = $_SERVER['REQUEST_METHOD'];

    switch ("{$method}_{$action}") {
        
        case 'GET_get_accounts':
            getAccounts($model);
            break;

        case 'GET_get_account_details':
            getAccountDetails($model);
            break;

        case 'GET_get_by_client':
            getAccountsByClient($model);
            break;

        case 'GET_get_stats':
            getStats($model);
            break;

        case 'POST_register_payment':
            registerPayment($model);
            break;

        case 'POST_update_due_date':
            updateDueDate($model);
            break;

        case 'POST_delete':
            deleteAccount($model);
            break;

        case 'POST_process_expired':
            processExpired($model);
            break;

        default:
            throw new Exception("Petición no válida: {$method} {$action}");
    }

    exit();
}



function getAccounts($model)
{
    try {
        $accounts = $model->getAll();
        
        $formattedAccounts = array_map(function($acc) {
            return [
                'id' => $acc['cuenta_cobrar_id'],
                'referencia' => $acc['referencia'],
                'referencia_credito' => $acc['referencia_credito'],
                'cliente' => $acc['nombre_cliente'],
                'cliente_ced' => $acc['cliente_ced'],
                'telefono' => $acc['telefono'],
                'fecha_emision' => $acc['fecha_emision'],
                'fecha_vencimiento' => $acc['fecha_vencimiento'],
                'monto_total' => floatval($acc['monto_total']),
                'saldo_pendiente' => floatval($acc['saldo_pendiente']),
                'estado' => $acc['estado'],
                'estado_visual' => $acc['estado_visual'],
                'dias_restantes' => intval($acc['dias_restantes']),
                'venta_id' => $acc['venta_id']
            ];
        }, $accounts);

        echo json_encode([
            'success' => true,
            'accounts' => $formattedAccounts,
            'count' => count($formattedAccounts)
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}


function getAccountDetails($model)
{
    try {
        $id = intval($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            throw new Exception("ID de cuenta inválido");
        }

        $account = $model->getById($id);
        
        if (!$account) {
            throw new Exception("Cuenta por cobrar no encontrada");
        }

        echo json_encode([
            'success' => true,
            'account' => $account
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function getAccountsByClient($model)
{
    try {
        $cedula = trim($_GET['cedula'] ?? '');
        
        if (empty($cedula)) {
            throw new Exception("Cédula de cliente requerida");
        }

        $accounts = $model->getByClient($cedula);

        echo json_encode([
            'success' => true,
            'accounts' => $accounts,
            'count' => count($accounts)
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}


function getStats($model)
{
    try {
        $stats = $model->getStats();

        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}


function registerPayment($model)
{
    try {
        // Validar campos requeridos manualmente
        $required = ['cuenta_cobrar_id', 'monto'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Campo requerido: " . str_replace('_', ' ', $field));
            }
        }

        $monto = floatval($_POST['monto']);
        if ($monto <= 0) {
            throw new Exception("El monto debe ser mayor a cero");
        }

        // Validar referencia bancaria SOLO si existe
        if (!empty($_POST['referencia_bancaria'])) {
            $refValidation = Validation::validateField($_POST['referencia_bancaria'], 'referencia');
            if (!$refValidation['valid']) {
                throw new Exception('Referencia bancaria inválida (8-10 dígitos)');
            }
        }
        
        // Validar banco SOLO si existe
        if (!empty($_POST['banco'])) {
            $bancoValidation = Validation::validateField($_POST['banco'], 'banco');
            if (!$bancoValidation['valid']) {
                throw new Exception('Nombre del banco inválido');
            }
        }

        // Construir datos
        $paymentData = [
            'cuenta_cobrar_id' => intval($_POST['cuenta_cobrar_id']),
            'monto' => $monto,
            'tipo_pago' => !empty($_POST['tipo_pago']) ? trim($_POST['tipo_pago']) : 'EFECTIVO',
            'referencia_bancaria' => !empty($_POST['referencia_bancaria']) ? trim($_POST['referencia_bancaria']) : null,
            'banco' => !empty($_POST['banco']) ? trim($_POST['banco']) : null,
            'observaciones' => !empty($_POST['observaciones']) ? trim($_POST['observaciones']) : null
        ];

        $result = $model->registerPayment($paymentData);

        echo json_encode($result);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
function updateDueDate($model)
{
    try {
        $cuentaId = intval($_POST['cuenta_id'] ?? 0);
        $nuevaFecha = trim($_POST['nueva_fecha'] ?? '');

        if ($cuentaId <= 0) {
            throw new Exception("ID de cuenta inválido");
        }

        if (empty($nuevaFecha)) {
            throw new Exception("Nueva fecha de vencimiento requerida");
        }

        // Validar formato de fecha
        $fecha = \DateTime::createFromFormat('Y-m-d', $nuevaFecha);
        if (!$fecha) {
            throw new Exception("Formato de fecha inválido (use YYYY-MM-DD)");
        }

        $result = $model->updateDueDate($cuentaId, $nuevaFecha);

        echo json_encode($result);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function deleteAccount($model)
{
    try {
        $cuentaId = intval($_POST['cuenta_id'] ?? 0);

        if ($cuentaId <= 0) {
            throw new Exception("ID de cuenta inválido");
        }

        $confirmar = $_POST['confirmar'] ?? 'no';
        if ($confirmar !== 'si') {
            echo json_encode([
                'success' => false,
                'message' => 'Debe confirmar la eliminación',
                'require_confirmation' => true
            ]);
            return;
        }

        $result = $model->delete($cuentaId);

        echo json_encode($result);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function processExpired($model)
{
    try {
        if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
            throw new Exception("Solo administradores pueden ejecutar esta acción");
        }

        $result = $model->processExpiredAccounts();

        echo json_encode($result);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function index()
{
    $paths = [
        __DIR__ . '/../../views/admin/accounts-receivable-admin',
        dirname(__DIR__, 2) . '/views/admin/accounts-receivable-admin.php'
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require $path;
            return;
        }
    }

    throw new Exception("Vista no encontrada");
}