<?php
// filepath: app/controllers/Admin/PurchaseController.php

use Barkios\models\Purchase;
use Barkios\models\Supplier;
use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/LoginController.php';

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
                case 'GET_get_purchases': getPurchasesAjax($purchaseModel); break;
                case 'GET_get_purchase_detail': getPurchaseDetailAjax($purchaseModel); break;
                case 'GET_search_supplier': searchSupplierAjax($supplierModel); break;
                case 'GET_get_stats': getStatsAjax($purchaseModel); break;
                default: echo json_encode(['success'=>false,'message'=>'Acción inválida']); exit();
            }
        } elseif ($action === 'generate_pdf') {
            generatePdf($purchaseModel);
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

/**
 * Maneja la creación vía AJAX
 */
function handleAddAjax($purchaseModel, $supplierModel) {
    // campos requeridos
    $required = ['proveedor_rif', 'factura_numero', 'fecha_compra'];

    $missingFields = [];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $missingFields[] = str_replace('_', ' ', $field);
        }
    }

    if (!empty($missingFields)) {
        echo json_encode([
            'success' => false,
            'message' => 'Campos requeridos: ' . implode(', ', $missingFields)
        ]);
        exit();
    }

    // Validaciones básicas
    $factura_numero = trim($_POST['factura_numero']);
    $proveedor_rif = trim($_POST['proveedor_rif']);
    $fecha_compra = trim($_POST['fecha_compra']);
    $tracking = trim($_POST['tracking'] ?? '');
    $observaciones = trim($_POST['observaciones'] ?? '');
    $fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? trim($_POST['fecha_vencimiento']) : null;

    if (!preg_match('/^\d{8}$/', $factura_numero)) {
        echo json_encode(['success' => false, 'message' => 'El número de factura debe tener 8 dígitos']);
        exit();
    }

    if (!empty($tracking) && !preg_match('/^\d{8}$/', $tracking)) {
        echo json_encode(['success' => false, 'message' => 'El número de tracking debe tener 8 dígitos']);
        exit();
    }

    // Existe proveedor?
    if (!method_exists($supplierModel, 'getById') || !$supplierModel->getById($proveedor_rif)) {
        echo json_encode(['success' => false, 'message' => 'El proveedor no existe']);
        exit();
    }

    // Factura duplicada?
    if ($purchaseModel->facturaExiste($factura_numero)) {
        echo json_encode(['success' => false, 'message' => 'Ya existe una compra con este número de factura']);
        exit();
    }

    // Prendas
    $rawPrendas = $_POST['prendas'] ?? null;
    if (empty($rawPrendas) || !is_array($rawPrendas)) {
        echo json_encode(['success' => false, 'message' => 'Debe agregar al menos una prenda']);
        exit();
    }

    $montoTotal = 0;
    $prendasValidas = [];

    foreach ($rawPrendas as $prenda) {
        // campos obligatorios por prenda
        if (
            empty($prenda['codigo_prenda']) ||
            empty($prenda['nombre']) ||
            empty($prenda['categoria']) ||
            empty($prenda['tipo']) ||
            !isset($prenda['precio_costo'])
        ) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos de las prendas son obligatorios']);
            exit();
        }

        $codigoPrenda = strtoupper(trim($prenda['codigo_prenda']));
        $precioCosto = floatval($prenda['precio_costo']);

        if (!preg_match('/^[A-Z0-9\-]+$/', $codigoPrenda)) {
            echo json_encode(['success' => false, 'message' => "Código '$codigoPrenda' contiene caracteres inválidos"]);
            exit();
        }

        // evitar duplicados en el lote enviado
        foreach ($prendasValidas as $p) {
            if ($p['codigo_prenda'] === $codigoPrenda) {
                echo json_encode(['success' => false, 'message' => "El código '$codigoPrenda' está repetido"]);
                exit();
            }
        }

        if ($precioCosto <= 0) {
            echo json_encode(['success' => false, 'message' => 'El precio de costo debe ser mayor a cero']);
            exit();
        }

        // precio_venta opcional: si no viene, calculo un margen por defecto (ej. 50%)
        $precioVenta = isset($prenda['precio_venta']) && $prenda['precio_venta'] > 0
            ? floatval($prenda['precio_venta'])
            : round($precioCosto * 1.5, 2);

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

    // Armar datos para el modelo
    $datos = [
        'proveedor_rif' => $proveedor_rif,
        'factura_numero' => $factura_numero,
        'fecha_compra' => $fecha_compra,
        'tracking' => $tracking,
        'monto_total' => $montoTotal,
        'observaciones' => $observaciones,
        'fecha_vencimiento' => $fecha_vencimiento,
        'prendas' => $prendasValidas
    ];

    try {
        $compraId = $purchaseModel->add($datos);
        echo json_encode(['success' => true, 'message' => 'Compra registrada', 'compra_id' => $compraId]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

/**
 * Maneja la edición vía AJAX (solo datos generales, no prendas)
 */
function handleEditAjax($purchaseModel, $supplierModel) {
    $id = isset($_POST['compra_id']) ? intval($_POST['compra_id']) : null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID de compra inválido']);
        exit();
    }

    // Verificar si puede editarse
    if (!$purchaseModel->canEdit($id)) {
        echo json_encode(['success' => false, 'message' => 'No se puede editar: la compra tiene prendas vendidas']);
        exit();
    }

    $required = ['proveedor_rif', 'factura_numero', 'fecha_compra'];
    $missing = [];
    foreach ($required as $r) {
        if (empty($_POST[$r])) $missing[] = $r;
    }
    if (!empty($missing)) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos requeridos: ' . implode(', ', $missing)]);
        exit();
    }

    $proveedor_rif = trim($_POST['proveedor_rif']);
    $factura_numero = trim($_POST['factura_numero']);

    // validar proveedor
    if (!method_exists($supplierModel, 'getById') || !$supplierModel->getById($proveedor_rif)) {
        echo json_encode(['success' => false, 'message' => 'Proveedor inválido']);
        exit();
    }

    // validar factura unica (excluir id actual)
    if ($purchaseModel->facturaExiste($factura_numero, $id)) {
        echo json_encode(['success' => false, 'message' => 'Ya existe otra compra con ese número de factura']);
        exit();
    }

    $datos = [
        'proveedor_rif' => $proveedor_rif,
        'factura_numero' => $factura_numero,
        'fecha_compra' => trim($_POST['fecha_compra']),
        'tracking' => trim($_POST['tracking'] ?? ''),
        'monto_total' => floatval($_POST['monto_total'] ?? 0),
        'observaciones' => trim($_POST['observaciones'] ?? '')
    ];

    try {
        $purchaseModel->update($id, $datos);
        echo json_encode(['success' => true, 'message' => 'Compra actualizada']);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

/**
 * Maneja la eliminación vía AJAX (soft delete)
 */
function handleDeleteAjax($purchaseModel) {
    $id = isset($_POST['compra_id']) ? intval($_POST['compra_id']) : null;
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit();
    }

    try {
        $purchaseModel->delete($id);
        echo json_encode(['success' => true, 'message' => 'Compra eliminada']);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}


/**
 * Devuelve detalle de una compra (compra + prendas + pagos)
 */
function getPurchaseDetailAjax($purchaseModel) {
    // Aceptar tanto ?compra_id= como ?id=
    $id = isset($_GET['compra_id']) ? intval($_GET['compra_id']) : (isset($_GET['id']) ? intval($_GET['id']) : null);

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit();
    }

    try {
        $compra = $purchaseModel->getById($id);
        if (!$compra) {
            echo json_encode(['success' => false, 'message' => 'Compra no encontrada']);
            exit();
        }

        // Obtener prendas asociadas a la compra
        $prendas = $purchaseModel->getPrendasByCompraId($id);

        // Obtener pagos registrados (si aplica)
        $pagos = $purchaseModel->getPagosByCompraId($id);

        // 🧮 Calcular totales de apoyo (por si deseas mostrarlos)
        $totalPrendas = count($prendas);
        $totalPagos = array_sum(array_column($pagos, 'monto')) ?? 0;
        $saldoPendiente = max(0, $compra['monto_total'] - $totalPagos);

        // ✅ Estructura compatible con tu JS actual
        echo json_encode([
            'success' => true,
            'data' => [
                'compra' => $compra,
                'prendas' => $prendas,
                'pagos' => $pagos,
                'totales' => [
                    'total_prendas' => $totalPrendas,
                    'total_pagado' => $totalPagos,
                    'saldo_pendiente' => $saldoPendiente
                ]
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}


/**
 * Busca proveedor por término
 */
function searchSupplierAjax($supplierModel) {
    error_log("🔎 Búsqueda AJAX: " . json_encode($_GET));
    $q = trim($_GET['search'] ?? $_GET['q'] ?? '');
    if ($q === '') {
        echo json_encode(['success' => true, 'results' => []]);
        exit();
    }

    try {
        $results = [];

        // si existe método "search" en el modelo, lo usamos
        if (method_exists($supplierModel, 'search')) {
            $results = $supplierModel->search($q);
        } elseif (method_exists($supplierModel, 'getById')) {
            // fallback: buscar por rif exacto o por nombre (si getById soporta rif)
            $byId = $supplierModel->getById($q);
            if ($byId) $results = [$byId];
        }

        echo json_encode(['success' => true, 'results' => $results]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

/**
 * Estadísticas
 */
function getStatsAjax($purchaseModel) {
    try {
        $stats = $purchaseModel->getEstadisticas();
        echo json_encode(['success' => true, 'stats' => $stats]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

function getPurchasesAjax($purchaseModel) {
    $purchases = $purchaseModel->getAll();

    echo json_encode([
        'success' => true,
        'data' => $purchases
    ]);
    exit();
}



/**
 * Generar PDF (no-AJAX)
 * URL ejemplo: index.php?action=generate_pdf&id=123
 */
function generatePdf($purchaseModel) {
    $id = isset($_GET['compra_id']) ? intval($_GET['compra_id']) : (isset($_GET['id']) ? intval($_GET['id']) : null);
    if (!$id) {
        die('ID de compra inválido');
    }

    $compra = $purchaseModel->getById($id);
    if (!$compra) {
        die('Compra no encontrada');
    }

    $prendas = $purchaseModel->getPrendasByCompraId($id);

    // Construir HTML simple para el PDF
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
        <div>Factura: ' . htmlspecialchars($compra['factura_numero']) . ' | Compra ID: ' . htmlspecialchars($compra['compra_id']) . '</div>
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
            <td class="right">' . number_format($p['precio_costo'], 2, ',', '.') . '</td>
        </tr>';
    }

    $html .= '</tbody></table>';

    $html .= '<div style="margin-top:10px;">
        <strong>Monto total:</strong> ' . number_format($compra['monto_total'], 2, ',', '.') . '
        <br><strong>Observaciones:</strong> ' . htmlspecialchars($compra['observaciones'] ?? '') . '
    </div>';

    $html .= '</body></html>';

    // Configurar Dompdf
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Marcar PDF como generado
    try {
        $purchaseModel->markPdfGenerated($id);
    } catch (Exception $e) {
        // no detengo el flujo por un fallo al marcar, solo logueo
        error_log('No se pudo marcar pdf generado: ' . $e->getMessage());
    }

    // Enviar PDF al navegador (inline)
    $filename = 'compra_' . $compra['compra_id'] . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    echo $dompdf->output();
    exit();
}
