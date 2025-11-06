<?php
use Barkios\models\Sale;
use Barkios\helpers\PdfHelper;
use Barkios\helpers\Validation;
require_once __DIR__ . '/LoginController.php';
checkAuth();

if (session_status() === PHP_SESSION_NONE) session_start();

$saleModel = new Sale();
handleRequest($saleModel);

// ============================================
// CORE REQUEST HANDLER
// ============================================

function handleRequest($model) {
    $basePath = '/BarkiOS';

    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
        header("Location: {$basePath}/login");
        exit();
    }

    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            handleAjax($model, $action);
        } elseif ($action === 'generate_pdf') {
            generateSalePdf($model);
        } else {
            if (empty($action)) {
               return null;
            } else {
                throw new Exception("Acción no válida: " . $action);
            }
        }
    } catch (Exception $e) {
        handleError($e, $isAjax);
    }
}

function handleAjax($model, $action) {
    $method = $_SERVER['REQUEST_METHOD'];
    
    $routes = [
        'GET_get_sales' => fn() => getSales($model),
        'GET_get_by_id' => fn() => getSaleById($model),
        'GET_get_clients' => fn() => getResource($model, 'getClients', 'clients'),
        'GET_get_employees' => fn() => getResource($model, 'getEmployees', 'employees'),
        'GET_get_products' => fn() => getResourceWithCount($model, 'getProducts', 'products'),
        'GET_get_product_by_code' => fn() => getProductByCode($model),
        'GET_search_clients' => fn() => searchResource($model, 'searchClients'),
        'GET_search_employees' => fn() => searchResource($model, 'searchEmployees'),
        'GET_search_products' => fn() => searchResource($model, 'searchProducts'),
        'POST_add_sale' => fn() => addSale($model),
        'POST_add_payment' => fn() => addPayment($model),
        'POST_cancel_sale' => fn() => cancelSale($model)
    ];

    $route = "{$method}_{$action}";
    
    if (isset($routes[$route])) {
        $routes[$route]();
    } else {
        throw new Exception("Petición no válida: {$method} {$action}");
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
    error_log("SaleController Error: " . $e->getMessage());
    
    if ($isAjax) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    } else {
        echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
    }
}

// ============================================
// GENERIC RESOURCE HANDLERS (DRY)
// ============================================

function getResource($model, $method, $key) {
    try {
        $data = $model->$method();
        jsonResponse(['success' => true, $key => $data]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

function getResourceWithCount($model, $method, $key) {
    try {
        $data = $model->$method();
        jsonResponse(['success' => true, $key => $data, 'count' => count($data)]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

function searchResource($model, $method) {
    try {
        $query = trim($_GET['search'] ?? $_GET['q'] ?? '');
        
        if (strlen($query) < 2) {
            jsonResponse(['success' => true, 'results' => []]);
        }

        $results = $model->$method($query);
        jsonResponse(['success' => true, 'results' => $results]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

// ============================================
// SPECIFIC HANDLERS
// ============================================

function getSales($model) {
    try {
        $sales = $model->getAll();
        jsonResponse(['success' => true, 'sales' => $sales, 'count' => count($sales)]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

function getSaleById($model) {
    try {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            throw new Exception("ID de venta inválido");
        }

        $venta = $model->getSaleWithDetails($id);
        jsonResponse([
            'success' => !!$venta, 
            'venta' => $venta,
            'message' => $venta ? 'Venta encontrada' : 'Venta no encontrada'
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

function getProductByCode($model) {
    try {
        $codigo = trim($_GET['codigo'] ?? '');
        if (empty($codigo)) {
            throw new Exception("Código de prenda requerido");
        }

        $product = $model->getProductByCode($codigo);
        jsonResponse([
            'success' => !!$product,
            'product' => $product,
            'message' => $product ? 'Producto encontrado' : 'Producto no encontrado'
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

// ============================================
// VALIDATION
// ============================================

function validateSaleData($data) {
    $rules = [
        'cliente_ced' => 'cedula',
        'empleado_ced' => 'cedula',
        'tipo_venta' => ['type' => null, 'required' => true],
        'productos' => ['type' => null, 'required' => true]
    ];

    $validation = Validation::validate($data, $rules);
    if (!$validation['valid']) {
        throw new Exception(implode(', ', $validation['errors']));
    }

    // Validar tipo de venta
    $tipoVenta = Validation::validateTipoVenta($data['tipo_venta']);
    if (!$tipoVenta['valid']) {
        throw new Exception($tipoVenta['message']);
    }

    // Validar productos
    $productosVal = Validation::validateProductos($data['productos']);
    if (!$productosVal['valid']) {
        throw new Exception($productosVal['message']);
    }
}

function validateCreditoFechaVencimiento($tipo, $fechaVencimiento) {
    if (strtolower($tipo) !== 'credito') {
        return null;
    }

    if (empty($fechaVencimiento)) {
        throw new Exception("Debe seleccionar una fecha de vencimiento para ventas a crédito");
    }

    $valFecha = Validation::validateDate($fechaVencimiento);
    if (!$valFecha['valid']) {
        throw new Exception($valFecha['message']);
    }

    if ($fechaVencimiento <= date('Y-m-d')) {
        throw new Exception("La fecha de vencimiento debe ser posterior a hoy");
    }

    return $fechaVencimiento;
}

// ============================================
// SALE OPERATIONS
// ============================================

function addSale($model) {
    try {
        validateSaleData($_POST);

        $tipo = strtolower($_POST['tipo_venta']);
        $fechaVencimiento = validateCreditoFechaVencimiento($tipo, $_POST['fecha_vencimiento'] ?? null);

        $ventaData = [
            'cliente_ced' => trim($_POST['cliente_ced']),
            'empleado_ced' => trim($_POST['empleado_ced']),
            'tipo_venta' => $tipo,
            'productos' => json_decode($_POST['productos'], true),
            'observaciones' => $_POST['observaciones'] ?? null,
            'iva_porcentaje' => floatval($_POST['iva_porcentaje'] ?? 16.00),
            'referencia' => $_POST['referencia'] ?? null,
            'fecha_vencimiento' => $fechaVencimiento
        ];

        $ventaId = $model->addSale($ventaData);

        if (!$ventaId) {
            throw new Exception("Error al registrar la venta");
        }

        $venta = $model->getById($ventaId);
        jsonResponse([
            'success' => true,
            'message' => 'Venta registrada correctamente',
            'venta_id' => $ventaId,
            'referencia' => $venta['referencia'] ?? null
        ]);

    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

function addPayment($model) {
    try {
        $ventaId = intval($_POST['venta_id'] ?? 0);
        $monto = floatval($_POST['monto'] ?? 0);

        if ($ventaId <= 0) {
            throw new Exception("ID de venta inválido");
        }

        if ($monto <= 0) {
            throw new Exception("Monto de pago inválido");
        }

        $success = $model->addPayment([
            'venta_id' => $ventaId,
            'monto' => $monto,
            'observaciones' => $_POST['observaciones'] ?? null
        ]);

        jsonResponse([
            'success' => $success,
            'message' => $success ? 'Pago registrado correctamente' : 'Error al registrar el pago'
        ]);

    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

function cancelSale($model) {
    try {
        $id = intval($_POST['venta_id'] ?? 0);
        if ($id <= 0) {
            throw new Exception("ID de venta inválido");
        }

        $success = $model->cancelSale($id);
        jsonResponse([
            'success' => $success,
            'message' => $success ? 'Venta anulada correctamente' : 'Error al anular venta'
        ]);

    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

// ============================================
// PDF GENERATION
// ============================================

function index() {
    $paths = [
        __DIR__ . '/../../views/admin/sale-admin.php',
        dirname(__DIR__, 2) . '/views/admin/sale-admin.php'
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require $path;
            return;
        }
    }

    throw new Exception("Vista no encontrada");
}

function generateSalePdf($model) {
    try {
        $id = intval($_GET['venta_id'] ?? $_GET['id'] ?? 0);
        
        if ($id <= 0) {
            throw new Exception("ID de venta inválido");
        }

        $venta = $model->getSaleWithDetails($id);
        
        if (!$venta) {
            throw new Exception("Venta no encontrada");
        }

        $html = buildSalePdfHtml($venta);

        $pdfHelper = new PdfHelper();
        $pdf = $pdfHelper->fromHtml($html);

        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="venta_' . $id . '.pdf"');
        echo $pdf;
        exit();
        
    } catch (Exception $e) {
        die('Error al generar PDF: ' . htmlspecialchars($e->getMessage()));
    }
}

function buildSalePdfHtml($venta) {
    $html = '<!doctype html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>Venta #' . htmlspecialchars($venta['venta_id']) . '</title>
      <style>
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .info-section { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        .right { text-align: right; }
        .total-section { margin-top: 20px; border-top: 2px solid #333; padding-top: 10px; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
      </style>
    </head>
    <body>
      <div class="header">
        <h2>COMPROBANTE DE VENTA</h2>
        <p><strong>Referencia:</strong> ' . htmlspecialchars($venta['referencia'] ?? 'N/A') . '</p>
        <p><strong>Fecha:</strong> ' . date('d/m/Y H:i', strtotime($venta['fecha'])) . '</p>
      </div>

      <div class="info-section">
        <h4>Información del Cliente</h4>
        <p><strong>Nombre:</strong> ' . htmlspecialchars($venta['nombre_cliente']) . '</p>
        <p><strong>Cédula:</strong> ' . htmlspecialchars($venta['cliente_ced']) . '</p>
        ' . (isset($venta['cliente_telefono']) ? '<p><strong>Teléfono:</strong> ' . htmlspecialchars($venta['cliente_telefono']) . '</p>' : '') . '
      </div>

      <div class="info-section">
        <h4>Información de la Venta</h4>
        <p><strong>Vendedor:</strong> ' . htmlspecialchars($venta['nombre_empleado']) . '</p>
        <p><strong>Tipo de Venta:</strong> <span class="badge badge-info">' . strtoupper(htmlspecialchars($venta['tipo_venta'])) . '</span></p>
        <p><strong>Estado:</strong> <span class="badge badge-' . (strtolower($venta['estado_venta']) === 'completada' ? 'success' : 'warning') . '">' . strtoupper(htmlspecialchars($venta['estado_venta'])) . '</span></p>
      </div>

      <h4>Productos</h4>
      <table>
        <thead>
          <tr>
            <th>Código</th>
            <th>Producto</th>
            <th>Tipo</th>
            <th>Categoría</th>
            <th class="right">Precio</th>
          </tr>
        </thead>
        <tbody>';

    foreach (($venta['prendas'] ?? $venta['items'] ?? []) as $item) {
        $html .= '<tr>
            <td><code>' . htmlspecialchars($item['codigo_prenda'] ?? $item['codigo'] ?? 'N/A') . '</code></td>
            <td>' . htmlspecialchars($item['nombre_prenda'] ?? $item['nombre'] ?? '') . '</td>
            <td>' . htmlspecialchars($item['tipo'] ?? $item['tipo_prenda'] ?? '') . '</td>
            <td>' . htmlspecialchars($item['categoria'] ?? $item['categoria_prenda'] ?? '') . '</td>
            <td class="right">$' . number_format(floatval($item['precio_unitario'] ?? $item['subtotal'] ?? $item['precio'] ?? 0), 2, '.', ',') . '</td>
        </tr>';
    }

    $html .= '</tbody></table>';

    $subtotal = floatval($venta['monto_subtotal'] ?? $venta['subtotal'] ?? 0);
    $iva = floatval($venta['monto_iva'] ?? $venta['iva'] ?? 0);
    $total = floatval($venta['monto_total'] ?? $venta['total'] ?? 0);
    $pagado = floatval($venta['total_pagado'] ?? $venta['pagado'] ?? 0);
    $saldo = floatval($venta['saldo_pendiente'] ?? $venta['saldo'] ?? 0);

    $html .= '<div class="total-section">
        <table style="width: 50%; margin-left: auto;">
          <tr><td>Subtotal:</td><td class="right">$' . number_format($subtotal, 2, '.', ',') . '</td></tr>
          <tr><td>IVA (' . ($venta['iva_porcentaje'] ?? 16) . '%):</td><td class="right">$' . number_format($iva, 2, '.', ',') . '</td></tr>
          <tr style="font-weight: bold; background: #f5f5f5;">
            <td>TOTAL:</td>
            <td class="right">$' . number_format($total, 2, '.', ',') . '</td>
          </tr>';

    if ($pagado > 0) {
        $html .= '<tr style="color: #155724;">
            <td>Pagado:</td>
            <td class="right">$' . number_format($pagado, 2, '.', ',') . '</td>
          </tr>';
    }

    if ($saldo > 0) {
        $html .= '<tr style="color: #856404; font-weight: bold;">
            <td>Saldo Pendiente:</td>
            <td class="right">$' . number_format($saldo, 2, '.', ',') . '</td>
          </tr>';
    }

    $html .= '</table></div>';

    if (!empty($venta['observaciones'])) {
        $html .= '<div style="margin-top: 20px; padding: 10px; background: #f8f9fa; border-left: 4px solid #007bff;">
            <strong>Observaciones:</strong><br>
            ' . nl2br(htmlspecialchars($venta['observaciones'])) . '
        </div>';
    }

    $html .= '<div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
        <p>Documento generado automáticamente - Garage Barki</p>
        <p>' . date('d/m/Y H:i:s') . '</p>
    </div>';

    $html .= '</body></html>';

    return $html;
}