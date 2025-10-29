<?php
use Barkios\models\Sale;
use Barkios\helpers\PdfHelper;

// Proteger el módulo (requiere autenticación)
require_once __DIR__ . '/LoginController.php';
checkAuth();

if (session_status() === PHP_SESSION_NONE) session_start();

$saleModel = new Sale();
handleRequest($saleModel);

/**
 * Función principal de enrutamiento
 */

function handleRequest($model)
{
    $basePath = '/BarkiOS';

    // Validación de sesión
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
            // ✅ AGREGAR ESTA LÍNEA
            generateSalePdf($model);
        } else {
            if (empty($action)) {
               return null;
            } else {
                throw new Exception("Acción no válida: " . $action);
            }
        }
    } catch (Exception $e) {
        error_log("SaleController Error: " . $e->getMessage());
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

/**
 * Manejador de peticiones AJAX
 */
function handleAjax($model, $action)
{
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ("{$method}_{$action}") {

        case 'GET_get_sales':
            getSales($model);
            break;

        case 'GET_get_by_id':
            getSaleById($model);
            break;

        case 'GET_get_clients':
            getClients($model);
            break;

        case 'GET_get_employees':
            getEmployees($model);
            break;

        case 'GET_get_products':
            getProducts($model);
            break;

        case 'GET_get_product_by_code':
            getProductByCode($model);
            break;

        case 'POST_add_sale':
            addSale($model);
            break;

        case 'POST_add_payment':
            addPayment($model);
            break;

        case 'POST_cancel_sale':
            cancelSale($model);
            break;

        default:
            throw new Exception("Petición no válida: {$method} {$action}");
    }

    exit();
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
}

function sanitizeInput($data) {
    return array_map(function($value) {
        return is_string($value) ? trim($value) : $value;
    }, $data);
}

function validarVenta($datos) {
    $errores = [];

    if (empty($datos['cliente_ced'])) {
        $errores[] = 'El cliente es requerido';
    }

    if (empty($datos['empleado_ced'])) {
        $errores[] = 'El empleado es requerido';
    }

    if (empty($datos['tipo_venta']) || !in_array($datos['tipo_venta'], ['contado', 'credito'])) {
        $errores[] = 'Tipo de venta inválido';
    }

    // Validar fecha de vencimiento para crédito
    if ($datos['tipo_venta'] === 'credito') {
        if (empty($datos['fecha_vencimiento'])) {
            $errores[] = 'Fecha de vencimiento requerida para crédito';
        } else {
            $fechaHoy = date('Y-m-d');
            if ($datos['fecha_vencimiento'] <= $fechaHoy) {
                $errores[] = 'La fecha de vencimiento debe ser posterior a hoy';
            }
        }
    }

    return $errores;
}

/* ============================================================
   ENDPOINTS GET
============================================================ */

function getSales($model)
{
    try {
        $sales = $model->getAll();
        echo json_encode([
            'success' => true, 
            'sales' => $sales,
            'count' => count($sales)
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getSaleById($model)
{
    try {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            throw new Exception("ID de venta inválido");
        }

        $venta = $model->getSaleWithDetails($id);
        echo json_encode([
            'success' => !!$venta, 
            'venta' => $venta,
            'message' => $venta ? 'Venta encontrada' : 'Venta no encontrada'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getClients($model)
{
    try {
        $clients = $model->getClients();
        echo json_encode([
            'success' => true, 
            'clients' => $clients
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getEmployees($model)
{
    try {
        $employees = $model->getEmployees();
        echo json_encode([
            'success' => true, 
            'employees' => $employees
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getProducts($model)
{
    try {
        $products = $model->getProducts();
        echo json_encode([
            'success' => true, 
            'products' => $products,
            'count' => count($products)
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getProductByCode($model)
{
    try {
        $codigo = trim($_GET['codigo'] ?? '');
        if (empty($codigo)) {
            throw new Exception("Código de prenda requerido");
        }

        $product = $model->getProductByCode($codigo);
        echo json_encode([
            'success' => !!$product,
            'product' => $product,
            'message' => $product ? 'Producto encontrado' : 'Producto no encontrado'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

/* ============================================================
   ENDPOINTS POST
============================================================ */

function addSale($model)
{
    try {
        // Validar campos requeridos
        $required = ['cliente_ced', 'empleado_ced', 'tipo_venta', 'productos'];
        foreach ($required as $f) {
            if (empty($_POST[$f])) {
                throw new Exception("Campo requerido: $f");
            }
        }

        $cliente = trim($_POST['cliente_ced']);
        $empleado = trim($_POST['empleado_ced']);
        $tipo = strtolower($_POST['tipo_venta']);

        // Decodificar productos
        $productos = json_decode($_POST['productos'], true);
        if (!$productos || !is_array($productos) || count($productos) === 0) {
            throw new Exception("Debe agregar al menos un producto válido");
        }

        // Validar que cada producto tenga código_prenda
        foreach ($productos as $p) {
            if (empty($p['codigo_prenda']) || empty($p['precio_unitario'])) {
                throw new Exception("Productos inválidos: falta código o precio");
            }
        }

        // Si es venta a crédito, obtener la fecha de vencimiento
        $fechaVencimiento = null;
        if ($tipo === 'credito') {
            // Validar que la fecha de vencimiento esté presente
            if (empty($_POST['fecha_vencimiento'])) {
                throw new Exception("Debe seleccionar una fecha de vencimiento para ventas a crédito");
            }
            $fechaVencimiento = $_POST['fecha_vencimiento'];
            
            // Validar que la fecha de vencimiento sea posterior a la fecha actual
            $fechaHoy = date('Y-m-d');
            if ($fechaVencimiento <= $fechaHoy) {
                throw new Exception("La fecha de vencimiento debe ser posterior a hoy");
            }
        }

        // Preparar datos de venta
        $ventaData = [
            'cliente_ced' => $cliente,
            'empleado_ced' => $empleado,
            'tipo_venta' => $tipo,
            'productos' => $productos,
            'observaciones' => $_POST['observaciones'] ?? null,
            'iva_porcentaje' => floatval($_POST['iva_porcentaje'] ?? 16.00),
            'referencia' => $_POST['referencia'] ?? null,
            'fecha_vencimiento' => $fechaVencimiento // Agregar la fecha de vencimiento
        ];

        // Registrar venta
        $ventaId = $model->addSale($ventaData);

        if ($ventaId) {
            // Obtener referencia generada
            $venta = $model->getById($ventaId);
            echo json_encode([
                'success' => true, 
                'message' => 'Venta registrada correctamente', 
                'venta_id' => $ventaId,
                'referencia' => $venta['referencia'] ?? null
            ]);
        } else {
            throw new Exception("Error al registrar la venta");
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
}


function addPayment($model)
{
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

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Pago registrado correctamente' : 'Error al registrar el pago'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
}

function cancelSale($model)
{
    try {
        $id = intval($_POST['venta_id'] ?? 0);
        if ($id <= 0) {
            throw new Exception("ID de venta inválido");
        }

        $success = $model->cancelSale($id);
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Venta anulada correctamente' : 'Error al anular venta'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
}

/* ============================================================
   MOSTRAR VISTA
============================================================ */

function index()
{
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


/* ============================================================
   GENERACIÓN DE PDF
============================================================ */

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

        // Construir HTML del PDF
        $html = buildSalePdfHtml($venta);

        // Generar PDF usando el helper
        $pdfHelper = new PdfHelper();
        $pdf = $pdfHelper->fromHtml($html);

        // Enviar PDF al navegador
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
        .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
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

    // Totales
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

    // Observaciones
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