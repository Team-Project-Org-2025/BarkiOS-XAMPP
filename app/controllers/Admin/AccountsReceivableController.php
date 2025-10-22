<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\controllers\Admin\AccountsReceivableController.php

use Barkios\models\AccountsReceivable;
use Barkios\models\Clients;

require_once __DIR__ . '/LoginController.php';


// ✅ Protege todo el módulo
checkAuth();

$accountsReceivableModel = new AccountsReceivable();
$clientsModel = new Clients();

function index() {
   return null;
}

handleRequest($accountsReceivableModel, $clientsModel);

function handleRequest($accountsReceivableModel, $clientsModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add_ajax':    handleAddEditAjax($accountsReceivableModel, $clientsModel, 'add'); break;
                case 'POST_edit_ajax':   handleAddEditAjax($accountsReceivableModel, $clientsModel, 'edit'); break;
                case 'POST_delete_ajax': handleDeleteAjax($accountsReceivableModel); break;
                case 'GET_get_accounts': getAccountsAjax($accountsReceivableModel); break;
                case 'GET_get_client_accounts': getClientAccountsAjax($accountsReceivableModel); break;
                case 'POST_register_payment': handleRegisterPayment($accountsReceivableModel); break;
                default:                 echo json_encode(['success'=>false,'message'=>'Acción inválida']); exit();
            }
        } else {
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add':    handleAddEdit($accountsReceivableModel, $clientsModel, 'add'); break;
                case 'POST_edit':   handleAddEdit($accountsReceivableModel, $clientsModel, 'edit'); break;
                case 'GET_delete':  handleDelete($accountsReceivableModel); break;
                default:            require __DIR__ . '/../../views/admin/accountsReceivable.php';
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

function handleAddEdit($accountsReceivableModel, $clientsModel, $mode) {
    $fields = ['cliente_ced', 'monto', 'fecha_vencimiento', 'descripcion'];
    
    // Validar campos requeridos
    foreach ($fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo " . str_replace('_', ' ', $field) . " es requerido");
        }
    }

    // Validar monto
    if (!is_numeric($_POST['monto']) || $_POST['monto'] <= 0) {
        throw new Exception("El monto debe ser un número mayor a cero");
    }

    // Validar fecha de vencimiento
    $fechaVencimiento = new DateTime($_POST['fecha_vencimiento']);
    $hoy = new DateTime();
    if ($fechaVencimiento < $hoy) {
        throw new Exception("La fecha de vencimiento no puede ser anterior a la fecha actual");
    }

    // Validar que el cliente exista
    if (!$clientsModel->getById($_POST['cliente_ced'])) {
        throw new Exception("El cliente seleccionado no existe");
    }

    $data = [
        'cliente_ced' => $_POST['cliente_ced'],
        'monto' => floatval($_POST['monto']),
        'fecha_vencimiento' => $_POST['fecha_vencimiento'],
        'descripcion' => $_POST['descripcion']
    ];

    if ($mode === 'edit') {
        if (empty($_POST['id'])) {
            throw new Exception("ID de cuenta por cobrar no proporcionado");
        }
        $data['estado'] = $_POST['estado'] ?? 'pendiente';
        $result = $accountsReceivableModel->update($_POST['id'], $data);
        $successMessage = 'Cuenta por cobrar actualizada exitosamente';
    } else {
        $result = $accountsReceivableModel->create($data);
        $successMessage = 'Cuenta por cobrar creada exitosamente';
    }

    if ($result['success']) {
        $_SESSION['success_message'] = $successMessage;
        header('Location: /admin/accounts-receivable');
    } else {
        throw new Exception($result['message']);
    }
    exit();
}

function handleDelete($accountsReceivableModel) {
    if (empty($_GET['id'])) {
        throw new Exception('ID de cuenta por cobrar no proporcionado');
    }
    $result = $accountsReceivableModel->delete($_GET['id']);
    
    if ($result['success']) {
        $_SESSION['success_message'] = 'Cuenta por cobrar eliminada exitosamente';
    } else {
        $_SESSION['error_message'] = $result['message'];
    }
    
    header('Location: /admin/accounts-receivable');
    exit();
}

function handleAddEditAjax($accountsReceivableModel, $clientsModel, $mode) {
    $fields = ['factura_numero', 'cliente_ced', 'fecha_emision', 'fecha_vencimiento', 'monto_total'];
    
    // Validar campos requeridos
    $missingFields = [];
    foreach ($fields as $field) {
        if (empty($_POST[$field])) {
            $missingFields[] = str_replace('_', ' ', $field);
        }
    }
    
    if (!empty($missingFields)) {
        echo json_encode([
            'success' => false,
            'message' => 'Los siguientes campos son requeridos: ' . implode(', ', $missingFields)
        ]);
        exit();
    }

    // Validar número de factura (exactamente 8 dígitos)
    if (!preg_match('/^[0-9]{8}$/', $_POST['factura_numero'])) {
        echo json_encode([
            'success' => false,
            'message' => 'El número de factura debe tener exactamente 8 dígitos numéricos'
        ]);
        exit();
    }

    // Validar monto
    if (!is_numeric($_POST['monto_total']) || $_POST['monto_total'] <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El monto debe ser un número mayor a cero'
        ]);
        exit();
    }

    // Validar fecha de vencimiento
    $fechaVencimiento = new DateTime($_POST['fecha_vencimiento']);
    $fechaEmision = new DateTime($_POST['fecha_emision']);
    if ($fechaVencimiento < $fechaEmision) {
        echo json_encode([
            'success' => false,
            'message' => 'La fecha de vencimiento no puede ser anterior a la fecha de emisión'
        ]);
        exit();
    }

    // Validar que el cliente exista y sea VIP
    $cliente = $clientsModel->getById($_POST['cliente_ced']);
    if (!$cliente) {
        echo json_encode([
            'success' => false,
            'message' => 'El cliente seleccionado no existe'
        ]);
        exit();
    }
    
    if ($cliente['tipo'] !== 'vip') {
        echo json_encode([
            'success' => false,
            'message' => 'Solo se pueden registrar cuentas por cobrar para clientes VIP'
        ]);
        exit();
    }

    $data = [
        'factura_numero' => trim($_POST['factura_numero']),
        'cliente_id' => $_POST['cliente_ced'],
        'fecha_emision' => $_POST['fecha_emision'],
        'fecha_vencimiento' => $_POST['fecha_vencimiento'],
        'monto_total' => floatval($_POST['monto_total']),
        'estado' => $_POST['estado'] ?? 'Pendiente'
    ];

    if ($mode === 'edit') {
        if (empty($_POST['id'])) {
            echo json_encode([
                'success' => false,
                'message' => 'ID de cuenta por cobrar no proporcionado'
            ]);
            exit();
        }
        $data['estado'] = $_POST['estado'] ?? 'pendiente';
        $result = $accountsReceivableModel->update($_POST['id'], $data);
        $successMessage = 'Cuenta por cobrar actualizada exitosamente';
    } else {
        $result = $accountsReceivableModel->create($data);
        $successMessage = 'Cuenta por cobrar creada exitosamente';
    }

    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => $successMessage,
            'id' => $result['id'] ?? $_POST['id'] ?? null
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
    exit();
}

function handleDeleteAjax($accountsReceivableModel) {
    if (empty($_POST['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de cuenta por cobrar no proporcionado'
        ]);
        exit();
    }
    
    $result = $accountsReceivableModel->delete($_POST['id']);
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Cuenta por cobrar eliminada exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['message']
        ]);
    }
    exit();
}

function getAccountsAjax($accountsReceivableModel) {
    $accounts = $accountsReceivableModel->getAll();
    
    // Formatear datos para el frontend
    $data = [];
    foreach ($accounts as $account) {
        $data[] = [
            'id' => $account['cuenta_cobrar_id'] ?? 'N/A',
            'factura_numero' => $account['cuenta_cobrar_id'] ?? 'N/A',
            'nombre_cliente' => $account['nombre_cliente'] ?? 'Sin cliente',
            'fecha_emision' => $account['emision'] ? date('Y-m-d', strtotime($account['emision'])) : 'N/A',
            'monto_total' => $account['monto_total'] ?? 0,
            'fecha_vencimiento' => $account['vencimiento'] ? date('Y-m-d', strtotime($account['vencimiento'])) : 'N/A',
            'estado' => ucfirst($account['estado'] ?? 'pendiente')
        ];
    }
    
    echo json_encode([
        'success' => true,
        'accounts' => $data,
        'count' => count($data)
    ]);
    exit();
}

function getClientAccountsAjax($accountsReceivableModel) {
    if (empty($_GET['cliente_ced'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Cédula de cliente no proporcionada'
        ]);
        exit();
    }
    
    $accounts = $accountsReceivableModel->getByCliente($_GET['cliente_ced']);
    
    // Formatear datos para la vista
    $data = [];
    foreach ($accounts as $account) {
        $data[] = [
            'id' => $account['id'],
            'monto' => number_format($account['monto'], 2, ',', '.'),
            'fecha_emision' => date('d/m/Y', strtotime($account['fecha_emision'])),
            'fecha_vencimiento' => date('d/m/Y', strtotime($account['fecha_vencimiento'])),
            'estado' => ucfirst($account['estado']),
            'descripcion' => $account['descripcion']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $data
    ]);
    exit();
}