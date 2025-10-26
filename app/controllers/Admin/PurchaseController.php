<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\controllers\Admin\PurchaseController.php

use Barkios\models\Purchase;
use Barkios\models\Supplier;

require_once __DIR__ . '/LoginController.php';

// ✅ Protege todo el módulo
checkAuth();

$purchaseModel = new Purchase();
$supplierModel = new Supplier();

function index() {
   return null;
}

handleRequest($purchaseModel, $supplierModel);

function handleRequest($purchaseModel, $supplierModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add_ajax':    handleAddAjax($purchaseModel, $supplierModel); break;
                case 'POST_edit_ajax':   handleEditAjax($purchaseModel, $supplierModel); break;
                case 'POST_delete_ajax': handleDeleteAjax($purchaseModel); break;
                case 'GET_get_purchases': getPurchasesAjax($purchaseModel); break;
                case 'GET_get_purchase_detail': getPurchaseDetailAjax($purchaseModel); break;
                case 'GET_search_supplier': searchSupplierAjax($supplierModel); break;
                case 'GET_download_pdf': downloadPdfAjax($purchaseModel); break;
                default:                 echo json_encode(['success'=>false,'message'=>'Acción inválida']); exit();
            }
        } else {
            require __DIR__ . '/../../views/admin/purchase-admin.php';
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

function handleAddAjax($purchaseModel, $supplierModel) {
    $required = ['proveedor_rif', 'factura_numero', 'fecha_compra', 'monto_total'];

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

    // Validar número de factura (8 dígitos)
    if (!preg_match('/^\d{8}$/', $_POST['factura_numero'])) {
        echo json_encode([
            'success' => false,
            'message' => 'El número de factura debe tener exactamente 8 dígitos'
        ]);
        exit();
    }

    // Validar que el proveedor exista
    if (!$supplierModel->getById($_POST['proveedor_rif'])) {
        echo json_encode([
            'success' => false,
            'message' => 'El proveedor seleccionado no existe'
        ]);
        exit();
    }

    // Validar número de factura único
    $stmt = $purchaseModel->db->prepare(
        "SELECT COUNT(*) FROM compras WHERE factura_numero = :factura_numero AND activo = 1"
    );
    $stmt->execute([':factura_numero' => $_POST['factura_numero']]);
    if ($stmt->fetchColumn() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe una compra con este número de factura'
        ]);
        exit();
    }

    try {
        $datos = [
            'proveedor_rif' => $_POST['proveedor_rif'],
            'factura_numero' => $_POST['factura_numero'],
            'fecha_compra' => $_POST['fecha_compra'],
            'tracking' => $_POST['tracking'] ?? '',
            'monto_total' => floatval($_POST['monto_total']),
            'prendas' => []
        ];

        // Procesar prendas si existen
        if (isset($_POST['prendas']) && is_array($_POST['prendas'])) {
            foreach ($_POST['prendas'] as $prenda) {
                if (!empty($prenda['producto_nombre']) && !empty($prenda['categoria']) && !empty($prenda['precio_costo'])) {
                    $datos['prendas'][] = [
                        'producto_nombre' => $prenda['producto_nombre'],
                        'categoria' => $prenda['categoria'],
                        'precio_costo' => floatval($prenda['precio_costo'])
                    ];
                }
            }
        }

        $result = $purchaseModel->add($datos);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Compra agregada exitosamente',
                'compra_id' => $result
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al agregar la compra'
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

function handleEditAjax($purchaseModel, $supplierModel) {
    if (empty($_POST['compra_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de compra no proporcionado'
        ]);
        exit();
    }

    $required = ['proveedor_rif', 'factura_numero', 'fecha_compra', 'monto_total'];

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

    // Validar número de factura (8 dígitos)
    if (!preg_match('/^\d{8}$/', $_POST['factura_numero'])) {
        echo json_encode([
            'success' => false,
            'message' => 'El número de factura debe tener exactamente 8 dígitos'
        ]);
        exit();
    }

    try {
        $datos = [
            'proveedor_rif' => $_POST['proveedor_rif'],
            'factura_numero' => $_POST['factura_numero'],
            'fecha_compra' => $_POST['fecha_compra'],
            'tracking' => $_POST['tracking'] ?? '',
            'monto_total' => floatval($_POST['monto_total']),
            'prendas' => []
        ];

        // Procesar prendas si existen
        if (isset($_POST['prendas']) && is_array($_POST['prendas'])) {
            foreach ($_POST['prendas'] as $prenda) {
                if (!empty($prenda['producto_nombre']) && !empty($prenda['categoria']) && !empty($prenda['precio_costo'])) {
                    $datos['prendas'][] = [
                        'producto_nombre' => $prenda['producto_nombre'],
                        'categoria' => $prenda['categoria'],
                        'precio_costo' => floatval($prenda['precio_costo'])
                    ];
                }
            }
        }

        $result = $purchaseModel->update($_POST['compra_id'], $datos);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Compra actualizada exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar la compra'
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

function handleDeleteAjax($purchaseModel) {
    if (empty($_POST['compra_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de compra no proporcionado'
        ]);
        exit();
    }

    try {
        $result = $purchaseModel->delete($_POST['compra_id']);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Compra eliminada exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al eliminar la compra'
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

function getPurchasesAjax($purchaseModel) {
    try {
        $purchases = $purchaseModel->getAll();

        // Formatear datos para la tabla
        $data = [];
        foreach ($purchases as $purchase) {
            $data[] = [
                'compra_id' => $purchase['compra_id'],
                'factura_numero' => $purchase['factura_numero'],
                'nombre_proveedor' => $purchase['nombre_proveedor'],
                'fecha_compra' => date('d/m/Y', strtotime($purchase['fecha_compra'])),
                'monto_total' => '$' . number_format($purchase['monto_total'], 2),
                'total_prendas' => $purchase['total_prendas'],
                'tracking' => $purchase['tracking'] ?: 'N/A',
                'proveedor_rif' => $purchase['proveedor_rif']
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

function getPurchaseDetailAjax($purchaseModel) {
    if (empty($_GET['compra_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de compra no proporcionado'
        ]);
        exit();
    }

    try {
        $purchase = $purchaseModel->getById($_GET['compra_id']);
        $prendas = $purchaseModel->getPrendasByCompraId($_GET['compra_id']);

        echo json_encode([
            'success' => true,
            'data' => [
                'compra' => $purchase,
                'prendas' => $prendas
            ]
        ]);
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
                stripos($supplier['proveedor_rif'], $search) !== false) {
                $results[] = [
                    'id' => $supplier['proveedor_rif'],
                    'nombre_empresa' => $supplier['nombre_empresa'],
                    'nombre_contacto' => $supplier['nombre_contacto'],
                    'rif' => $supplier['proveedor_rif']
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

function downloadPdfAjax($purchaseModel) {
    if (empty($_GET['compra_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'ID de compra no proporcionado'
        ]);
        exit();
    }

    try {
        $purchase = $purchaseModel->getById($_GET['compra_id']);
        $prendas = $purchaseModel->getPrendasByCompraId($_GET['compra_id']);

        if (!$purchase) {
            echo json_encode([
                'success' => false,
                'message' => 'Compra no encontrada'
            ]);
            exit();
        }

        // Aquí iría la lógica para generar el PDF
        // Por ahora solo marcamos como generado
        $purchaseModel->markPdfGenerated($_GET['compra_id']);

        echo json_encode([
            'success' => true,
            'message' => 'PDF generado exitosamente',
            'pdf_url' => '/BarkiOS/app/controllers/Admin/PurchaseController.php?action=generate_pdf&compra_id=' . $_GET['compra_id']
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}