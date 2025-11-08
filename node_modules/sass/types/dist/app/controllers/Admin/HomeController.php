<?php
use Barkios\models\Dashboard;
use Barkios\helpers\PdfHelper;

require_once __DIR__ . '/LoginController.php';
checkAuth();

$dashboardModel = new Dashboard();

function index() {
    require __DIR__ . '/../../views/admin/home-admin.php';
}

handleRequest($dashboardModel);

// ============================================
// CORE REQUEST HANDLER
// ============================================

function handleRequest($model) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            
            $routes = [
                'GET_get_stats' => fn() => getStats($model),
                'GET_get_transactions' => fn() => getTransactions($model),
                'GET_export_report' => fn() => exportReport($model)
            ];

            $route = "{$_SERVER['REQUEST_METHOD']}_$action";
            
            if (isset($routes[$route])) {
                $routes[$route]();
            } else {
                jsonResponse(['success' => false, 'message' => 'Acción inválida'], 400);
            }
        } elseif ($action === 'generate_pdf_report') {
            generatePdfReport($model);
        }
    } catch (Exception $e) {
        handleError($e, $isAjax);
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

function handleError($e, $isAjax) {
    error_log("HomeController Error: " . $e->getMessage());
    
    if ($isAjax) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    } else {
        die("Error: " . $e->getMessage());
    }
}

function validateDate($date) {
    if (!$date) return false;
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

// ============================================
// DATE RANGE CALCULATION
// ============================================

function calculateDateRange($filter, $dateFrom = null, $dateTo = null) {
    switch ($filter) {
        case 'today':
            return [date('Y-m-d'), date('Y-m-d')];
        case 'week':
            return [date('Y-m-d', strtotime('-6 days')), date('Y-m-d')];
        case 'month':
            return [date('Y-m-01'), date('Y-m-d')];
        case 'year':
            return [date('Y-01-01'), date('Y-m-d')];
        case 'custom':
            if (!$dateFrom || !$dateTo) {
                throw new Exception("Fechas personalizadas requeridas");
            }
            if (!validateDate($dateFrom) || !validateDate($dateTo)) {
                throw new Exception("Formato de fecha inválido");
            }
            if (strtotime($dateFrom) > strtotime($dateTo)) {
                throw new Exception("La fecha inicial no puede ser mayor a la fecha final");
            }
            return [$dateFrom, $dateTo];
        default:
            throw new Exception("Filtro inválido");
    }
}

// ============================================
// AJAX HANDLERS
// ============================================

function getStats($model) {
    try {
        $filter = $_GET['filter'] ?? 'today';
        $dateFrom = $_GET['date_from'] ?? null;
        $dateTo = $_GET['date_to'] ?? null;

        [$dateFrom, $dateTo] = calculateDateRange($filter, $dateFrom, $dateTo);

        $stats = $model->getStats($dateFrom, $dateTo);
        $chartTimeline = $model->getChartTimeline($dateFrom, $dateTo, $filter);
        $stats['chart_timeline'] = $chartTimeline;

        jsonResponse([
            'success' => true,
            'data' => $stats,
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
                'filter' => $filter
            ]
        ]);

    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

function getTransactions($model) {
    try {
        $filter = $_GET['filter'] ?? 'today';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-d');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');

        [$dateFrom, $dateTo] = calculateDateRange($filter, $dateFrom, $dateTo);

        $transactions = $model->getTransactions($dateFrom, $dateTo);

        jsonResponse([
            'success' => true,
            'data' => $transactions,
            'count' => count($transactions)
        ]);

    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

function exportReport($model) {
    try {
        $filter = $_GET['filter'] ?? 'month';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');

        [$dateFrom, $dateTo] = calculateDateRange($filter, $dateFrom, $dateTo);

        $stats = $model->getStats($dateFrom, $dateTo);
        $transactions = $model->getTransactions($dateFrom, $dateTo);

        $filename = "reporte_garage_barki_{$dateFrom}_al_{$dateTo}.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezado
        fputcsv($output, ['=== REPORTE FINANCIERO - GARAGE BARKI ===']);
        fputcsv($output, ['Generado:', date('Y-m-d H:i:s')]);
        fputcsv($output, ['Período:', $dateFrom . ' al ' . $dateTo]);
        fputcsv($output, []);

        // Resumen General
        fputcsv($output, ['=== RESUMEN GENERAL ===']);
        fputcsv($output, ['Concepto', 'Monto (USD)', 'Cantidad']);
        fputcsv($output, ['Total Ventas', number_format($stats['ventas']['total'], 2), $stats['ventas']['cantidad']]);
        fputcsv($output, ['Total Compras', number_format($stats['compras']['total'], 2), $stats['compras']['cantidad']]);
        
        $ganancia = $stats['ventas']['total'] - $stats['compras']['total'];
        $margen = $stats['ventas']['total'] > 0 ? (($ganancia / $stats['ventas']['total']) * 100) : 0;
        
        fputcsv($output, ['Ganancia Neta', number_format($ganancia, 2), '']);
        fputcsv($output, ['Margen de Ganancia (%)', number_format($margen, 2) . '%', '']);
        fputcsv($output, []);

        // Cuentas
        fputcsv($output, ['=== CUENTAS ===']);
        fputcsv($output, ['Cuentas por Cobrar', number_format($stats['cuentas_cobrar']['saldo_total'], 2), $stats['cuentas_cobrar']['cantidad']]);
        fputcsv($output, ['  - Vencidas', '', $stats['cuentas_cobrar']['vencidas']]);
        fputcsv($output, ['Cuentas por Pagar', number_format($stats['cuentas_pagar']['saldo_total'], 2), $stats['cuentas_pagar']['cantidad']]);
        fputcsv($output, ['  - Vencidas', '', $stats['cuentas_pagar']['vencidas']]);
        fputcsv($output, []);

        // Inventario
        fputcsv($output, ['=== INVENTARIO ===']);
        fputcsv($output, ['Prendas Vendidas', '', $stats['inventario']['vendidas']]);
        fputcsv($output, ['Prendas Disponibles', '', $stats['inventario']['disponibles']]);
        fputcsv($output, []);

        // Detalle de Transacciones
        fputcsv($output, ['=== DETALLE DE TRANSACCIONES ===']);
        fputcsv($output, ['Fecha', 'Tipo', 'Referencia', 'Cliente/Proveedor', 'Monto (USD)', 'Estado']);
        
        foreach ($transactions as $t) {
            fputcsv($output, [
                $t['fecha'],
                $t['tipo'],
                $t['referencia'] ?? 'N/A',
                $t['cliente_proveedor'] ?? '-',
                number_format($t['monto'], 2),
                $t['estado'] ?? 'N/A'
            ]);
        }

        fputcsv($output, []);
        fputcsv($output, ['--- Fin del Reporte ---']);
        
        fclose($output);
        exit();

    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

// ============================================
// PDF GENERATION
// ============================================

function generatePdfReport($model) {
    try {
        $filter = $_GET['filter'] ?? 'month';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');

        [$dateFrom, $dateTo] = calculateDateRange($filter, $dateFrom, $dateTo);

        $stats = $model->getStats($dateFrom, $dateTo);
        $transactions = $model->getTransactions($dateFrom, $dateTo);

        $html = buildReportPdfHtml($stats, $transactions, $dateFrom, $dateTo);

        $pdfHelper = new PdfHelper();
        $pdf = $pdfHelper->fromHtml($html);

        $filename = "reporte_dashboard_{$dateFrom}_al_{$dateTo}.pdf";
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        echo $pdf;
        exit();

    } catch (Exception $e) {
        die('Error al generar PDF: ' . htmlspecialchars($e->getMessage()));
    }
}

function buildReportPdfHtml($stats, $transactions, $dateFrom, $dateTo) {
    $ganancia = $stats['ventas']['total'] - $stats['compras']['total'];
    $margen = $stats['ventas']['total'] > 0 ? (($ganancia / $stats['ventas']['total']) * 100) : 0;

    $html = '<!doctype html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>Reporte Panel Administrtivo</title>
      <style>
        body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .info-section { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: bold; }
        .right { text-align: right; }
        .center { text-align: center; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
      </style>
    </head>
    <body>
      <div class="header">
        <h2>REPORTE FINANCIERO</h2>
        <p><strong>Período:</strong> ' . date('d/m/Y', strtotime($dateFrom)) . ' al ' . date('d/m/Y', strtotime($dateTo)) . '</p>
        <p><strong>Fecha:</strong> ' . date('d/m/Y H:i') . '</p>
      </div>

      <div class="info-section">
        <h4>Resumen General</h4>
        <table>
          <tr>
            <td><strong>Total Ventas</strong></td>
            <td class="right">$' . number_format($stats['ventas']['total'], 2, '.', ',') . '</td>
            <td class="center">' . $stats['ventas']['cantidad'] . ' ventas</td>
          </tr>
          <tr>
            <td><strong>Total Compras</strong></td>
            <td class="right">$' . number_format($stats['compras']['total'], 2, '.', ',') . '</td>
            <td class="center">' . $stats['compras']['cantidad'] . ' compras</td>
          </tr>
          <tr style="background: #f5f5f5;">
            <td><strong>Ganancia Neta</strong></td>
            <td class="right"><strong>$' . number_format($ganancia, 2, '.', ',') . '</strong></td>
            <td class="center">Margen: ' . number_format($margen, 2) . '%</td>
          </tr>
        </table>
      </div>

      <div class="info-section">
        <h4>Cuentas</h4>
        <table>
          <tr>
            <td><strong>Cuentas por Cobrar</strong></td>
            <td class="right">$' . number_format($stats['cuentas_cobrar']['saldo_total'], 2, '.', ',') . '</td>
            <td class="center">' . $stats['cuentas_cobrar']['cantidad'] . ' cuentas</td>
            <td class="center"><span class="badge badge-' . ($stats['cuentas_cobrar']['vencidas'] > 0 ? 'danger' : 'success') . '">' . $stats['cuentas_cobrar']['vencidas'] . ' vencidas</span></td>
          </tr>
          <tr>
            <td><strong>Cuentas por Pagar</strong></td>
            <td class="right">$' . number_format($stats['cuentas_pagar']['saldo_total'], 2, '.', ',') . '</td>
            <td class="center">' . $stats['cuentas_pagar']['cantidad'] . ' cuentas</td>
            <td class="center"><span class="badge badge-' . ($stats['cuentas_pagar']['vencidas'] > 0 ? 'danger' : 'success') . '">' . $stats['cuentas_pagar']['vencidas'] . ' vencidas</span></td>
          </tr>
        </table>
      </div>

      <div class="info-section">
        <h4>Inventario</h4>
        <table>
          <tr>
            <td><strong>Prendas Vendidas</strong></td>
            <td class="right">' . $stats['inventario']['vendidas'] . '</td>
          </tr>
          <tr>
            <td><strong>Prendas Disponibles</strong></td>
            <td class="right">' . $stats['inventario']['disponibles'] . '</td>
          </tr>
        </table>
      </div>

      <h4>Transacciones</h4>
      <table>
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Tipo</th>
            <th>Referencia</th>
            <th>Cliente/Proveedor</th>
            <th class="right">Monto</th>
            <th class="center">Estado</th>
          </tr>
        </thead>
        <tbody>';

    foreach ($transactions as $t) {
        $html .= '<tr>
            <td>' . date('d/m/Y', strtotime($t['fecha'])) . '</td>
            <td>' . htmlspecialchars($t['tipo']) . '</td>
            <td><code>' . htmlspecialchars($t['referencia'] ?? 'N/A') . '</code></td>
            <td>' . htmlspecialchars($t['cliente_proveedor'] ?? '-') . '</td>
            <td class="right">$' . number_format($t['monto'], 2, '.', ',') . '</td>
            <td class="center">' . htmlspecialchars($t['estado'] ?? 'N/A') . '</td>
        </tr>';
    }

    $html .= '</tbody>
      </table>

      <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
        <p>Documento generado automáticamente - Garage Barki</p>
        <p>' . date('d/m/Y H:i:s') . '</p>
      </div>
    </body>
    </html>';

    return $html;
}