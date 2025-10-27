<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\controllers\Admin\PurchaseController.php

use Barkios\models\Purchase;
use Barkios\models\Supplier;

require_once __DIR__ . '/LoginController.php';

// Protege todo el módulo
checkAuth();

$purchaseModel = new Purchase();
$supplierModel = new Supplier();

function index() {
    require __DIR__ . '/../../views/admin/purchase-admin.php';
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
                case 'GET_generate_pdf': generatePdf($purchaseModel); break;
                case 'GET_get_purchases': getPurchasesAjax($purchaseModel); break;
                case 'GET_get_purchase_detail': getPurchaseDetailAjax($purchaseModel); break;
                case 'GET_search_supplier': searchSupplierAjax($supplierModel); break;
                default: echo json_encode(['success'=>false,'message'=>'Acción inválida']); exit();
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

function handleAddAjax($purchaseModel, $supplierModel) {
    $required = ['proveedor_rif', 'factura_numero', 'fecha_compra'];

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

    // Validar número de factura (8 dígitos)
    if (!preg_match('/^\d{8}$/', $_POST['factura_numero'])) {
        echo json_encode([
            'success' => false,
            'message' => 'El número de factura debe tener exactamente 8 dígitos'
        ]);
        exit();
    }

    // Validar tracking si existe (8 dígitos)
    if (!empty($_POST['tracking']) && !preg_match('/^\d{8}$/', $_POST['tracking'])) {
        echo json_encode([
            'success' => false,
            'message' => 'El número de tracking debe tener exactamente 8 dígitos'
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
// Validar número de factura único
    if ($purchaseModel->facturaExiste($_POST['factura_numero'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe una compra con este número de factura'
        ]);
        exit();
    }


    // Validar que haya al menos una prenda
    if (empty($_POST['prendas']) || !is_array($_POST['prendas'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Debe agregar al menos una prenda a la compra'
        ]);
        exit();
    }

    // Validar y calcular monto total
    $montoTotal = 0;
    $prendasValidas = [];

    foreach ($_POST['prendas'] as $prenda) {
        // Validar campos requeridos
        if (
            empty($prenda['codigo_prenda']) ||
            empty($prenda['nombre']) ||
            empty($prenda['categoria']) ||
            empty($prenda['tipo']) ||
            empty($prenda['precio_costo']) ||
            empty($prenda['precio_venta'])
        ) {
            echo json_encode([
                'success' => false,
                'message' => 'Todos los campos de las prendas son obligatorios, incluyendo el código de prenda'
            ]);
            exit();
        }

        $codigoPrenda = strtoupper(trim($prenda['codigo_prenda']));
        $precioCosto = floatval($prenda['precio_costo']);
        $precioVenta = floatval($prenda['precio_venta']);

        // Validar formato del código (solo letras, números o guiones)
        if (!preg_match('/^[A-Z0-9\-]+$/', $codigoPrenda)) {
            echo json_encode([
                'success' => false,
                'message' => "El código de prenda '$codigoPrenda' contiene caracteres no válidos (solo letras, números y guiones)"
            ]);
            exit();
        }

        // Validar código duplicado en la misma compra
        foreach ($prendasValidas as $p) {
            if ($p['codigo_prenda'] === $codigoPrenda) {
                echo json_encode([
                    'success' => false,
                    'message' => "El código de prenda '$codigoPrenda' está repetido en la lista"
                ]);
                exit();
            }
        }

        // Validar precios
        if ($precioCosto <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'El precio de costo debe ser mayor a cero'
            ]);
            exit();
        }

        if ($precioVenta <= $precioCosto) {
            echo json_encode([
                'success' => false,
                'message' => 'El precio de venta debe ser mayor al de costo'
            ]);
            exit();
        }

        // Agregar prenda válida
        $prendasValidas[] = [
            'codigo_prenda' => $codigoPrenda,
            'nombre' => trim($prenda['nombre']),
            'categoria' => $prenda['categoria'],
            'tipo' => $prenda['tipo'],
            'precio_costo' => $precioCosto,
            'precio_venta' => $precioVenta,
            'descripcion' => trim($prenda['descripcion'] ?? '')
        ];

        $montoTotal += $precioCosto;
    }

    // Insertar compra
    try {
        $datos = [
            'proveedor_rif' => $_POST['proveedor_rif'],
            'factura_numero' => $_POST['factura_numero'],
            'fecha_compra' => $_POST['fecha_compra'],
            'tracking' => $_POST['tracking'] ?? '',
            'monto_total' => $montoTotal,
            'observaciones' => trim($_POST['observaciones'] ?? ''),
            'prendas' => $prendasValidas
        ];

        $result = $purchaseModel->add($datos);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Compra agregada exitosamente. Se registraron ' . count($prendasValidas) . ' prendas.',
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
        echo json_encode(['success'=>false,'message'=>'ID de compra no proporcionado']);
        exit();
    }

    // Validaciones base (como en add pero sin prendas)
    $required = ['proveedor_rif', 'factura_numero', 'fecha_compra'];
    $missing = [];
    foreach ($required as $f) {
        if (empty($_POST[$f])) $missing[] = str_replace('_',' ',$f);
    }
    if ($missing) {
        echo json_encode(['success'=>false,'message'=>'Los siguientes campos son requeridos: '.implode(', ',$missing)]);
        exit();
    }

    if (!preg_match('/^\d{8}$/', $_POST['factura_numero'])) {
        echo json_encode(['success'=>false,'message'=>'El número de factura debe tener exactamente 8 dígitos']);
        exit();
    }

    if (!empty($_POST['tracking']) && !preg_match('/^\d{8}$/', $_POST['tracking'])) {
        echo json_encode(['success'=>false,'message'=>'El número de tracking debe tener exactamente 8 dígitos']);
        exit();
    }

    // Recalcular monto desde DB (no desde POST)
    $prendas = $purchaseModel->getPrendasByCompraId($_POST['compra_id']);
    $montoTotal = 0;
    foreach ($prendas as $p) {
        $montoTotal += floatval($p['precio_costo']);
    }

    try {
        $datos = [
            'proveedor_rif'   => $_POST['proveedor_rif'],
            'factura_numero'  => $_POST['factura_numero'],
            'fecha_compra'    => $_POST['fecha_compra'],
            'tracking'        => $_POST['tracking'] ?? '',
            'monto_total'     => $montoTotal,
            'observaciones'   => trim($_POST['observaciones'] ?? '')
            // NO SE PASAN PRENDAS
        ];

        $result = $purchaseModel->update($_POST['compra_id'], $datos);

        echo json_encode([
            'success' => $result,
            'message' => $result ? 'Compra actualizada exitosamente' : 'Error al actualizar la compra'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
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
                'proveedor_rif' => $purchase['proveedor_rif'],
                'pdf_generado' => $purchase['pdf_generado']
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

use Dompdf\Dompdf;
use Dompdf\Options;

function generatePdf($purchaseModel) {

    if (empty($_GET['compra_id'])) {
        die("ID de compra no proporcionado");
    }

    $id = $_GET['compra_id'];
    $purchase = $purchaseModel->getById($id);
    $prendas  = $purchaseModel->getPrendasByCompraId($id);

    if (!$purchase) {
        die("Compra no encontrada");
    }

    require __DIR__ . '/../../../vendor/autoload.php';

    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    // HTML simple (luego lo estilizamos)
    $html = "<h2>Factura #{$purchase['factura_numero']}</h2>";
    $html .= "<p><b>Proveedor:</b> {$purchase['nombre_proveedor']}</p>";
    $html .= "<p><b>Fecha:</b> {$purchase['fecha_compra']}</p>";
    $html .= "<p><b>Total:</b> {$purchase['monto_total']}</p>";

    $html .= "<hr><h3>Prendas</h3>";
    $html .= "<table width='100%' border='1' cellspacing='0' cellpadding='4'>
        <tr><th>Código</th><th>Nombre</th><th>Costo</th></tr>";
    foreach ($prendas as $p) {
        $html .= "<tr>
            <td>{$p['codigo_prenda']}</td>
            <td>{$p['nombre']}</td>
            <td>{$p['precio_costo']}</td>
        </tr>";
    }
    $html .= "</table>";

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $dompdf->stream("compra_$id.pdf", ["Attachment" => true]);
    exit;
}
