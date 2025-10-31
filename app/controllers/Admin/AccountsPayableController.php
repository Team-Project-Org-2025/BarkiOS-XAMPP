<?php

use Barkios\models\AccountsPayable;
use Barkios\models\Purchase;

require_once __DIR__ . '/LoginController.php';

checkAuth();

$accountsModel = new AccountsPayable();
$purchaseModel = new Purchase();

function index() {
    require __DIR__ . '/../../views/admin/accounts-payable-admin.php';
}

handleRequest($accountsModel, $purchaseModel);

function handleRequest($accountsModel, $purchaseModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'GET_get_accounts': 
                    getAccountsAjax($accountsModel); 
                    break;
                case 'GET_get_account_detail': 
                    getAccountDetailAjax($accountsModel, $purchaseModel); 
                    break;
                case 'POST_add_payment': 
                    addPaymentAjax($accountsModel); 
                    break;
                default: 
                    echo json_encode(['success'=>false,'message'=>'Acción inválida']); 
                    exit();
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

function getAccountsAjax($accountsModel) {
    try {
        $accounts = $accountsModel->getAll();
        echo json_encode([
            'success' => true,
            'data' => $accounts
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

function getAccountDetailAjax($accountsModel, $purchaseModel) {
    $id = isset($_GET['cuenta_pagar_id']) ? intval($_GET['cuenta_pagar_id']) : null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit();
    }

    try {
        $cuenta = $accountsModel->getById($id);
        if (!$cuenta) {
            echo json_encode(['success' => false, 'message' => 'Cuenta no encontrada']);
            exit();
        }

        $pagos = $accountsModel->getPagosByCuentaId($id);
        
        $prendas = [];
        if ($cuenta['compra_id']) {
            $prendas = $purchaseModel->getPrendasByCompraId($cuenta['compra_id']);
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'cuenta' => $cuenta,
                'pagos' => $pagos,
                'prendas' => $prendas
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

function addPaymentAjax($accountsModel) {
    $required = ['cuenta_pagar_id', 'monto', 'fecha_pago', 'tipo_pago', 'moneda_pago'];
    
    foreach ($required as $field) {
        if (empty($_POST[$field]) && $_POST[$field] !== '0') {
            echo json_encode(['success' => false, 'message' => "Campo requerido: $field"]);
            exit();
        }
    }

    $cuentaId = intval($_POST['cuenta_pagar_id']);
    $monto = floatval($_POST['monto']);
    
    if ($monto <= 0) {
        echo json_encode(['success' => false, 'message' => 'El monto debe ser mayor a 0']);
        exit();
    }

    // Verificar que no exceda el saldo
    $cuenta = $accountsModel->getById($cuentaId);
    if (!$cuenta) {
        echo json_encode(['success' => false, 'message' => 'Cuenta no encontrada']);
        exit();
    }

    $saldoPendiente = floatval($cuenta['saldo_pendiente']);
    
    // Permitir pequeña diferencia por conversión de moneda (±$0.50)
    if ($monto > ($saldoPendiente + 0.50)) {
        echo json_encode(['success' => false, 'message' => 'El monto excede el saldo pendiente']);
        exit();
    }
    
    // Si el pago es mayor o igual al saldo, ajustar al saldo exacto
    if ($monto >= $saldoPendiente) {
        $monto = $saldoPendiente;
    }

    $datos = [
        'cuenta_pagar_id' => $cuentaId,
        'compra_id' => $cuenta['compra_id'],
        'monto' => $monto,
        'fecha_pago' => $_POST['fecha_pago'],
        'tipo_pago' => $_POST['tipo_pago'],
        'moneda_pago' => $_POST['moneda_pago'],
        'referencia_bancaria' => $_POST['referencia_bancaria'] ?? null,
        'banco' => $_POST['banco'] ?? null,
        'observaciones' => $_POST['observaciones'] ?? null,
        'estado_pago' => 'CONFIRMADO'
    ];

    try {
        $pagoId = $accountsModel->addPago($datos);
        echo json_encode([
            'success' => true, 
            'message' => 'Pago registrado exitosamente',
            'pago_id' => $pagoId
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}