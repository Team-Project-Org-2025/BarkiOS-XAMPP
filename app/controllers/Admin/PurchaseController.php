<?php
// filepath: app/controllers/Admin/PurchaseController.php

use Barkios\models\Purchase;
use Barkios\models\Supplier;
use Barkios\helpers\PdfHelper;

require_once __DIR__ . '/LoginController.php';
checkAuth();

$purchaseModel = new Purchase();
$supplierModel = new Supplier();

function index() {
    require __DIR__ . '/../../views/admin/purchase-admin.php';
}

handleRequest($purchaseModel, $supplierModel);

// ============================================
// ENRUTAMIENTO PRINCIPAL
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
// UTILIDADES
// ============================================
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
}

function handleError($e, $isAjax) {
    error_log("PurchaseController Error: " . $e->getMessage());
    
    if ($isAjax) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    } else {
        die("Error: " . htmlspecialchars($e->getMessage()));
    }
    exit();
}

function sanitizeInput($data) {
    return array_map(function($value) {
        return is_string($value) ? trim($value) : $value;
    }, $data);
}

// ============================================
// VALIDACIONES
// ============================================
function validarCompra($datos) {
    $errores = [];

    if (empty($datos['proveedor_rif'])) {
        $errores[] = 'El proveedor es requerido';
    }

    if (empty($datos['factura_numero'])) {
        $errores[] = 'El número de factura es requerido';
    } elseif (!preg_match('/^\d{8}$/', $datos['factura_numero'])) {
        $errores[] = 'El número de factura debe tener 8 dígitos';
    }

    if (empty($datos['fecha_compra'])) {
        $errores[] = 'La fecha de compra es requerida';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha_compra'])) {
        $errores[] = 'Formato de fecha inválido';
    }

    if (!empty($datos['tracking']) && !preg_match('/^\d{8}$/', $datos['tracking'])) {
        $errores[] = 'El tracking debe tener 8 dígitos';
    }

    return $errores;
}

function validarPrenda($prenda) {
    $errores = [];

    if (empty($prenda['codigo_prenda'])) {
        $errores[] = 'El código de prenda es requerido';
    } elseif (!preg_match('/^[A-Z0-9\-]+$/i', $prenda['codigo_prenda'])) {
        $errores[] = "Código '{$prenda['codigo_prenda']}' contiene caracteres inválidos";
    }

    if (empty($prenda['nombre'])) {
        $errores[] = 'El nombre es requerido';
    } elseif (strlen($prenda['nombre']) < 3 || strlen($prenda['nombre']) > 150) {
        $errores[] = 'El nombre debe tener entre 3 y 150 caracteres';
    }

    if (empty($prenda['categoria'])) $errores[] = 'La categoría es requerida';
    if (empty($prenda['tipo'])) $errores[] = 'El tipo es requerido';

    if (!isset($prenda['precio_costo']) || floatval($prenda['precio_costo']) <= 0) {
        $errores[] = 'El precio de costo debe ser mayor a 0';
    }

    return $errores;
}

// ============================================
// HANDLERS PRINCIPALES
// ============================================
function handleAdd($purchaseModel) {
    try {
        // Sanitizar datos
        $datos = sanitizeInput([
            'proveedor_rif' => $_POST['proveedor_rif'] ?? '',
            'factura_numero' => $_POST['factura_numero'] ?? '',
            'fecha_compra' => $_POST['fecha_compra'] ?? '',
            'tracking' => $_POST['tracking'] ?? '',
            'observaciones' => $_POST['observaciones'] ?? '',
            'fecha_vencimiento' => $_POST['fecha_vencimiento'] ?? ''
        ]);

        // Validar datos básicos
        $errores = validarCompra($datos);
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }

        // Validar prendas
        $rawPrendas = $_POST['prendas'] ?? [];
        if (empty($rawPrendas) || !is_array($rawPrendas)) {
            throw new Exception('Debe agregar al menos una prenda');
        }

        $prendas = [];
        $montoTotal = 0;

        foreach ($rawPrendas as $prenda) {
            $prendaData = sanitizeInput([
                'codigo_prenda' => $prenda['codigo_prenda'] ?? '',
                'nombre' => $prenda['nombre'] ?? '',
                'categoria' => $prenda['categoria'] ?? '',
                'tipo' => $prenda['tipo'] ?? '',
                'precio_costo' => floatval($prenda['precio_costo'] ?? 0),
                'descripcion' => $prenda['descripcion'] ?? ''
            ]);

            $erroresPrenda = validarPrenda($prendaData);
            if (!empty($erroresPrenda)) {
                throw new Exception(implode(', ', $erroresPrenda));
            }

            $prendaData['precio_venta'] = isset($prenda['precio_venta']) && $prenda['precio_venta'] > 0
                ? floatval($prenda['precio_venta'])
                : 0;

            $prendas[] = $prendaData;
            $montoTotal += $prendaData['precio_costo'];
        }

        $datos['prendas'] = $prendas;
        $datos['monto_total'] = $montoTotal;

        // Guardar en BD
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
        $id = isset($_POST['compra_id']) ? intval($_POST['compra_id']) : null;
        if (!$id) {
            throw new Exception('ID de compra inválido');
        }

        if (!$purchaseModel->canEdit($id)) {
            throw new Exception('No se puede editar: la compra tiene prendas vendidas');
        }

        // Datos generales
        $datos = sanitizeInput([
            'proveedor_rif' => $_POST['proveedor_rif'] ?? '',
            'factura_numero' => $_POST['factura_numero'] ?? '',
            'fecha_compra' => $_POST['fecha_compra'] ?? '',
            'tracking' => $_POST['tracking'] ?? '',
            'observaciones' => $_POST['observaciones'] ?? ''
        ]);

        $errores = validarCompra($datos);
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }

        $montoActual = $purchaseModel->getMontoTotal($id);
        $datos['monto_total'] = $montoActual;

        // Actualizar datos generales
        $purchaseModel->update($id, $datos);

        // Procesar nuevas prendas
        $nuevasPrendas = [];
        $rawNuevasPrendas = $_POST['nuevas_prendas'] ?? [];

        foreach ($rawNuevasPrendas as $prenda) {
            $prendaData = sanitizeInput([
                'codigo_prenda' => $prenda['codigo_prenda'] ?? '',
                'nombre' => $prenda['nombre'] ?? '',
                'categoria' => $prenda['categoria'] ?? '',
                'tipo' => $prenda['tipo'] ?? '',
                'precio_costo' => floatval($prenda['precio_costo'] ?? 0),
                'descripcion' => $prenda['descripcion'] ?? ''
            ]);

            $erroresPrenda = validarPrenda($prendaData);
            if (!empty($erroresPrenda)) {
                throw new Exception(implode(', ', $erroresPrenda));
            }

            $prendaData['precio_venta'] = isset($prenda['precio_venta']) && $prenda['precio_venta'] > 0
                ? floatval($prenda['precio_venta'])
                : 0;

            $nuevasPrendas[] = $prendaData;
        }

        $prendasAgregadas = 0;
        if (!empty($nuevasPrendas)) {
            $prendasAgregadas = $purchaseModel->addPrendasToCompra($id, $nuevasPrendas);
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
        $id = isset($_POST['compra_id']) ? intval($_POST['compra_id']) : null;
        if (!$id) {
            throw new Exception('ID inválido');
        }

        $purchaseModel->delete($id);

        jsonResponse(['success' => true, 'message' => 'Compra eliminada exitosamente']);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

// ============================================
// CONSULTAS
// ============================================
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
        $id = isset($_GET['compra_id']) ? intval($_GET['compra_id']) : (isset($_GET['id']) ? intval($_GET['id']) : null);

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
            return;
        }

        $results = [];

        if (method_exists($supplierModel, 'search')) {
            $results = $supplierModel->search($query);
        } elseif (method_exists($supplierModel, 'getById')) {
            $byId = $supplierModel->getById($query);
            if ($byId) $results = [$byId];
        }

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
// GENERACIÓN DE PDF
// ============================================
function generatePdf($purchaseModel) {
    $id = isset($_GET['compra_id']) ? intval($_GET['compra_id']) : (isset($_GET['id']) ? intval($_GET['id']) : null);
    $compra = $purchaseModel->getById($id);
    $prendas = $purchaseModel->getPrendasByCompraId($id);

    // 1) Construyo HTML en el controller
    $html = buildPdfHtml($compra, $prendas);

    // 2) PDF desde HTML
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