<?php
// filepath: app/controllers/Admin/PurchaseController.php

use Barkios\models\Purchase;
use Barkios\models\Supplier;
use Barkios\helpers\PdfHelper;
use Barkios\helpers\Validation;

require_once __DIR__ . '/LoginController.php';
checkAuth();

$purchaseModel = new Purchase();
$supplierModel = new Supplier();

function index() {
    require __DIR__ . '/../../views/admin/purchase-admin.php';
}

handleRequest($purchaseModel, $supplierModel);

// ============================================
// CORE REQUEST HANDLER
// ============================================

function handleRequest($purchaseModel, $supplierModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            routeAjax($purchaseModel, $supplierModel, $action);
        } elseif ($action === 'generate_pdf') {
            generatePdf($purchaseModel);
        }
    } catch (Exception $e) {
        handleError($e, $isAjax);
    }
}

function routeAjax($purchaseModel, $supplierModel, $action) {
    $method = $_SERVER['REQUEST_METHOD'];
    
    $routes = [
        'POST_add_ajax' => fn() => handleAdd($purchaseModel),
        'POST_edit_ajax' => fn() => handleEdit($purchaseModel),
        'POST_delete_ajax' => fn() => handleDelete($purchaseModel),
        'GET_get_purchases' => fn() => getPurchases($purchaseModel),
        'GET_get_purchase_detail' => fn() => getPurchaseDetail($purchaseModel),
        'GET_search_supplier' => fn() => searchSupplier($supplierModel),
        'GET_get_stats' => fn() => getStats($purchaseModel)
    ];

    $route = "{$method}_{$action}";
    
    if (isset($routes[$route])) {
        $routes[$route]();
    } else {
        jsonResponse(['success' => false, 'message' => 'Acción inválida'], 400);
    }
    
    exit();
}

// ============================================
// UTILITY FUNCTIONS (DRY)
// ============================================

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

function handleError($e, $isAjax) {
    error_log("PurchaseController Error: " . $e->getMessage());
    
    if ($isAjax) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    } else {
        die("Error: " . htmlspecialchars($e->getMessage()));
    }
}

// ============================================
// VALIDATION FUNCTIONS (REFACTORED)
// ============================================

function validatePurchaseData($datos) {
    $rules = [
        'proveedor_rif' => 'rif',
        'factura_numero' => 'factura',
        'fecha_compra' => ['type' => null, 'required' => true],
        'tracking' => ['type' => 'factura', 'required' => false]
    ];
    
    $validation = Validation::validate($datos, $rules);
    
    if (!$validation['valid']) {
        throw new Exception(implode(', ', $validation['errors']));
    }

    // Validar formato de fecha
    $dateValidation = Validation::validateDate($datos['fecha_compra']);
    if (!$dateValidation['valid']) {
        throw new Exception($dateValidation['message']);
    }
}

function validatePrendaData($prenda) {
    $rules = [
        'codigo_prenda' => 'codigo',
        'nombre' => 'nombrePrenda',
        'categoria' => 'nombre',
        'tipo' => 'nombre',
        'precio_costo' => 'precio'
    ];
    
    $validation = Validation::validate($prenda, $rules);
    
    if (!$validation['valid']) {
        throw new Exception(implode(', ', $validation['errors']));
    }
    
    // Validación de rango de precio
    $rangeValidation = Validation::validateRange($prenda['precio_costo'], 0.01, 10000);
    if (!$rangeValidation['valid']) {
        throw new Exception('El precio debe estar entre 0.01 y 10,000');
    }
}

function sanitizeAndValidatePrendas($rawPrendas) {
    if (empty($rawPrendas) || !is_array($rawPrendas)) {
        throw new Exception('Debe agregar al menos una prenda');
    }

    $prendas = [];
    $montoTotal = 0;

    foreach ($rawPrendas as $prenda) {
        $prendaData = [
            'codigo_prenda' => trim($prenda['codigo_prenda'] ?? ''),
            'nombre' => trim($prenda['nombre'] ?? ''),
            'categoria' => trim($prenda['categoria'] ?? ''),
            'tipo' => trim($prenda['tipo'] ?? ''),
            'precio_costo' => floatval($prenda['precio_costo'] ?? 0),
            'descripcion' => trim($prenda['descripcion'] ?? '')
        ];

        validatePrendaData($prendaData);

        $prendaData['precio_venta'] = isset($prenda['precio_venta']) && $prenda['precio_venta'] > 0
            ? floatval($prenda['precio_venta'])
            : 0;

        $prendas[] = $prendaData;
        $montoTotal += $prendaData['precio_costo'];
    }

    return ['prendas' => $prendas, 'monto_total' => $montoTotal];
}

// ============================================
// AJAX HANDLERS (SIMPLIFIED)
// ============================================

function handleAdd($purchaseModel) {
    try {
        $datos = [
            'proveedor_rif' => $_POST['proveedor_rif'] ?? '',
            'factura_numero' => $_POST['factura_numero'] ?? '',
            'fecha_compra' => $_POST['fecha_compra'] ?? '',
            'tracking' => $_POST['tracking'] ?? '',
            'observaciones' => $_POST['observaciones'] ?? '',
            'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? ''
        ];

        validatePurchaseData($datos);

        $prendasResult = sanitizeAndValidatePrendas($_POST['prendas'] ?? []);
        $datos['prendas'] = $prendasResult['prendas'];
        $datos['monto_total'] = $prendasResult['monto_total'];

        $compraId = $purchaseModel->add($datos);

        jsonResponse([
            'success' => true,
            'message' => 'Compra registrada exitosamente',
            'compra_id' => $compraId
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

function handleEdit($purchaseModel) {
    try {
        $id = intval($_POST['compra_id'] ?? 0);
        if (!$id) {
            throw new Exception('ID de compra inválido');
        }

        if (!$purchaseModel->canEdit($id)) {
            throw new Exception('No se puede editar: la compra tiene prendas vendidas');
        }

        $datos = [
            'proveedor_rif' => $_POST['proveedor_rif'] ?? '',
            'factura_numero' => $_POST['factura_numero'] ?? '',
            'fecha_compra' => $_POST['fecha_compra'] ?? '',
            'tracking' => $_POST['tracking'] ?? '',
            'observaciones' => $_POST['observaciones'] ?? ''
        ];

        validatePurchaseData($datos);

        $montoActual = $purchaseModel->getMontoTotal($id);
        $datos['monto_total'] = $montoActual;

        $purchaseModel->update($id, $datos);

        // Procesar nuevas prendas si existen
        $prendasAgregadas = 0;
        if (!empty($_POST['nuevas_prendas'])) {
            $prendasResult = sanitizeAndValidatePrendas($_POST['nuevas_prendas']);
            $prendasAgregadas = $purchaseModel->addPrendasToCompra($id, $prendasResult['prendas']);
        }

        $mensaje = $prendasAgregadas > 0
            ? "Compra actualizada y {$prendasAgregadas} prenda(s) agregada(s)"
            : 'Compra actualizada exitosamente';

        jsonResponse(['success' => true, 'message' => $mensaje]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

function handleDelete($purchaseModel) {
    try {
        $id = intval($_POST['compra_id'] ?? 0);
        if (!$id) {
            throw new Exception('ID inválido');
        }

        $purchaseModel->delete($id);

        jsonResponse(['success' => true, 'message' => 'Compra eliminada exitosamente']);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

function getPurchases($purchaseModel) {
    try {
        $purchases = $purchaseModel->getAll();
        jsonResponse(['success' => true, 'data' => $purchases]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

function getPurchaseDetail($purchaseModel) {
    try {
        $id = intval($_GET['compra_id'] ?? $_GET['id'] ?? 0);

        if (!$id) {
            throw new Exception('ID inválido');
        }

        $compra = $purchaseModel->getById($id);
        if (!$compra) {
            throw new Exception('Compra no encontrada');
        }

        $prendas = $purchaseModel->getPrendasByCompraId($id);
        $pagos = $purchaseModel->getPagosByCompraId($id);

        jsonResponse([
            'success' => true,
            'data' => [
                'compra' => $compra,
                'prendas' => $prendas,
                'pagos' => $pagos,
                'totales' => [
                    'total_prendas' => count($prendas),
                    'total_pagado' => $compra['total_pagado'] ?? 0,
                    'saldo_pendiente' => $compra['saldo_pendiente'] ?? 0
                ]
            ]
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

function searchSupplier($supplierModel) {
    try {
        $query = trim($_GET['search'] ?? $_GET['q'] ?? '');

        if ($query === '') {
            jsonResponse(['success' => true, 'results' => []]);
        }

        $results = method_exists($supplierModel, 'search')
            ? $supplierModel->search($query)
            : ($supplierModel->getById($query) ? [$supplierModel->getById($query)] : []);

        jsonResponse(['success' => true, 'results' => $results]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

function getStats($purchaseModel) {
    try {
        $stats = $purchaseModel->getEstadisticas();
        jsonResponse(['success' => true, 'stats' => $stats]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

// ============================================
// PDF GENERATION
// ============================================

function generatePdf($purchaseModel) {
    $id = intval($_GET['compra_id'] ?? $_GET['id'] ?? 0);
    $compra = $purchaseModel->getById($id);
    $prendas = $purchaseModel->getPrendasByCompraId($id);

    $html = buildPdfHtml($compra, $prendas);

    $pdfHelper = new PdfHelper();
    $pdf = $pdfHelper->fromHtml($html);

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="compra_'.$id.'.pdf"');
    echo $pdf;
    exit;
}

function buildPdfHtml($compra, $prendas) {
    $html = '<!doctype html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>Compra #' . htmlspecialchars($compra['compra_id']) . '</title>
      <style>
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f5f5f5; }
        .right { text-align: right; }
      </style>
    </head>
    <body>
      <div class="header">
        <h2>Detalle de Compra</h2>
        <div>Factura: ' . htmlspecialchars($compra['factura_numero']) .'</div>
        <div>Proveedor: ' . htmlspecialchars($compra['nombre_proveedor'] ?? $compra['proveedor_rif']) . '</div>
        <div>Fecha: ' . date('d/m/Y', strtotime($compra['fecha_compra'])) . '</div>
      </div>

      <h4>Prendas</h4>
      <table>
        <thead>
          <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Categoría</th>
            <th>Tipo</th>
            <th class="right">Precio costo</th>
          </tr>
        </thead>
        <tbody>';

    foreach ($prendas as $p) {
        $html .= '<tr>
            <td>' . htmlspecialchars($p['codigo_prenda']) . '</td>
            <td>' . htmlspecialchars($p['nombre']) . '</td>
            <td>' . htmlspecialchars($p['categoria']) . '</td>
            <td>' . htmlspecialchars($p['tipo']) . '</td>
            <td class="right">$' . number_format($p['precio_costo'], 2, '.', ',') . '</td>
        </tr>';
    }

    $html .= '</tbody></table>';
    $html .= '<div style="margin-top:10px;">
        <strong>Monto total:</strong> $' . number_format($compra['monto_total'], 2, '.', ',') . '
        <br><strong>Observaciones:</strong> ' . htmlspecialchars($compra['observaciones'] ?? '') . '
    </div>';
    $html .= '</body></html>';

    return $html;
}