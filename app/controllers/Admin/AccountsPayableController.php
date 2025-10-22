<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\controllers\Admin\AccountsPayableController.php

use Barkios\models\AccountsPayable;
use Barkios\models\Supplier;

$accountsPayableModel = new AccountsPayable();
$supplierModel = new Supplier();

function index() {
   return null;
}

handleRequest($accountsPayableModel, $supplierModel);

function handleRequest($accountsPayableModel, $supplierModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add_ajax':    handleAddAjax($accountsPayableModel, $supplierModel); break;
                case 'POST_edit_ajax':   handleEditAjax($accountsPayableModel, $supplierModel); break;
                case 'POST_delete_ajax': handleDeleteAjax($accountsPayableModel); break;
                case 'GET_get_accounts': getAccountsAjax($accountsPayableModel); break;
                case 'GET_get_supplier_accounts': getSupplierAccountsAjax($accountsPayableModel); break;
                case 'POST_register_payment': handleRegisterPayment($accountsPayableModel); break;
                case 'GET_search_supplier': searchSupplierAjax($supplierModel); break;
                default:                 echo json_encode(['success'=>false,'message'=>'Acción inválida']); exit();
            }
        } else {
            require __DIR__ . '/../../views/admin/accountsPayable.php';
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

function handleAddAjax($accountsPayableModel, $supplierModel) {
    $required = ['proveedor_id', 'factura_numero', 'fecha_emision', 'fecha_vencimiento', 'monto_total'];
    
    // Validar campos requeridos
    $missingFields = [];
    foreach ($required as $field) {
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

    // Validar monto
    if (!is_numeric($_POST['monto_total']) || $_POST['monto_total'] <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El monto debe ser un número mayor a cero'
        ]);
        exit();
    }

    // Validar que el proveedor exista
    if (!$supplierModel->getById($_POST['proveedor_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'El proveedor seleccionado no existe'
        ]);
        exit();
    }

    // Validar número de factura único
    $stmt = $accountsPayableModel->db->prepare(
        "SELECT COUNT(*) FROM cuentas_pagar WHERE factura_numero = :factura_numero AND activo = 1"
    );
    $stmt->execute([':factura_numero' => $_POST['factura_numero']]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe una cuenta con este número de factura'
        ]);
        exit();
    }

    try {
        $datos = [
            'proveedor_id' => $_POST['proveedor_id'],
            'factura_numero' => $_POST['factura_numero'],
            'fecha_emision' => $_POST['fecha_emision'],
            'fecha_vencimiento' => $_POST['fecha_vencimiento'],
            'monto_total' => floatval($_POST['monto_total']),
            'estado' => $_POST['estado'] ?? 'Pendiente'
        ];

        $result = $accountsPayableModel->add($datos);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Cuenta por pagar agregada exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al agregar la cuenta por pagar'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

function handleEditAjax($accountsPayableModel, $supplierModel) {
    if (empty($_POST['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de cuenta por pagar no proporcionado'
        ]);
        exit();
    }

    $required = ['proveedor_id', 'factura_numero', 'fecha_emision', 'fecha_vencimiento', 'monto_total', 'estado'];
    
    // Validar campos requeridos
    $missingFields = [];
    foreach ($required as $field) {
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

    // Validar monto
    if (!is_numeric($_POST['monto_total']) || $_POST['monto_total'] <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'El monto debe ser un número mayor a cero'
        ]);
        exit();
    }

    try {
        $datos = [
            'proveedor_id' => $_POST['proveedor_id'],
            'factura_numero' => $_POST['factura_numero'],
            'fecha_emision' => $_POST['fecha_emision'],
            'fecha_vencimiento' => $_POST['fecha_vencimiento'],
            'monto_total' => floatval($_POST['monto_total']),
            'estado' => $_POST['estado']
        ];

        $result = $accountsPayableModel->update($_POST['id'], $datos);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Cuenta por pagar actualizada exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar la cuenta por pagar'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

function handleDeleteAjax($accountsPayableModel) {
    if (empty($_POST['id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de cuenta por pagar no proporcionado'
        ]);
        exit();
    }
    
    try {
        $result = $accountsPayableModel->delete($_POST['id']);
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Cuenta por pagar eliminada exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar la cuenta por pagar'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

function getAccountsAjax($accountsPayableModel) {
    try {
        $accounts = $accountsPayableModel->getAll();
        
        // Formatear datos para la tabla
        $data = [];
        foreach ($accounts as $account) {
            $data[] = [
                'id' => $account['id'],
                'factura_numero' => $account['factura_numero'],
                'nombre_proveedor' => $account['nombre_proveedor'],
                'fecha_emision' => date('d/m/Y', strtotime($account['fecha_emision'])),
                'monto_total' => '$' . number_format($account['monto_total'], 2),
                'fecha_vencimiento' => date('d/m/Y', strtotime($account['fecha_vencimiento'])),
                'estado' => $account['estado'],
                'proveedor_id' => $account['proveedor_id']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

function getSupplierAccountsAjax($accountsPayableModel) {
    if (empty($_GET['proveedor_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de proveedor no proporcionado'
        ]);
        exit();
    }
    
    try {
        $accounts = $accountsPayableModel->getByProveedor($_GET['proveedor_id']);
        
        // Formatear datos para la vista
        $data = [];
        foreach ($accounts as $account) {
            $data[] = [
                'id' => $account['id'],
                'factura_numero' => $account['factura_numero'],
                'monto_total' => number_format($account['monto_total'], 2, ',', '.'),
                'fecha_emision' => date('d/m/Y', strtotime($account['fecha_emision'])),
                'fecha_vencimiento' => date('d/m/Y', strtotime($account['fecha_vencimiento'])),
                'estado' => ucfirst($account['estado'])
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

function handleRegisterPayment($accountsPayableModel) {
    if (empty($_POST['id']) || empty($_POST['monto_pagado'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Datos incompletos para registrar el pago'
        ]);
        exit();
    }

    try {
        $result = $accountsPayableModel->registrarPago(
            $_POST['id'], 
            floatval($_POST['monto_pagado'])
        );
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Pago registrado exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al registrar el pago'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

function searchSupplierAjax($supplierModel) {
    $search = $_GET['search'] ?? '';
    
    if (strlen($search) < 2) {
        echo json_encode(['success' => true, 'data' => []]);
        exit();
    }
    
    try {
        $suppliers = $supplierModel->getAll();
        $results = [];
        
        foreach ($suppliers as $supplier) {
            // Búsqueda case-insensitive en nombre de empresa y nombre de contacto
            if (stripos($supplier['nombre_empresa'], $search) !== false || 
                stripos($supplier['nombre_contacto'], $search) !== false ||
                stripos($supplier['id'], $search) !== false) {
                $results[] = [
                    'id' => $supplier['id'],
                    'nombre_empresa' => $supplier['nombre_empresa'],
                    'nombre_contacto' => $supplier['nombre_contacto'],
                    'rif' => $supplier['id']
                ];
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $results
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}
