<?php
// filepath: app/controllers/Admin/HomeController.php

use Barkios\models\Dashboard;

require_once __DIR__ . '/LoginController.php';
checkAuth();

$dashboardModel = new Dashboard();

function index() {
    require __DIR__ . '/../../views/admin/home-admin.php';
}

handleRequest($dashboardModel);

function handleRequest($model) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'GET_get_stats':
                    getStats($model);
                    break;
                    
                case 'GET_get_transactions':
                    getTransactions($model);
                    break;
                    
                case 'GET_export_report':
                    exportReport($model);
                    break;
                    
                default:
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Acción inválida'
                    ]);
                    exit();
            }
        }
    } catch (Exception $e) {
        error_log("HomeController Error: " . $e->getMessage());
        if ($isAjax) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        } else {
            die("Error: " . $e->getMessage());
        }
        exit();
    }
}

/**
 * Obtiene estadísticas del dashboard según el filtro de período
 */
function getStats($model) {
    try {
        $filter = $_GET['filter'] ?? 'today';
        $dateFrom = null;
        $dateTo = null;

        // Determinar rango de fechas según filtro
        switch ($filter) {
            case 'today':
                $dateFrom = $dateTo = date('Y-m-d');
                break;
            
            case 'week':
                $dateTo = date('Y-m-d');
                $dateFrom = date('Y-m-d', strtotime('-6 days'));
                break;
            
            case 'month':
                $dateTo = date('Y-m-d');
                $dateFrom = date('Y-m-01'); // Primer día del mes actual
                break;
            
            case 'year':
                $dateTo = date('Y-m-d');
                $dateFrom = date('Y-01-01'); // Primer día del año
                break;
            
            case 'custom':
                $dateFrom = $_GET['date_from'] ?? null;
                $dateTo = $_GET['date_to'] ?? null;
                
                if (!$dateFrom || !$dateTo) {
                    throw new Exception("Fechas personalizadas requeridas");
                }
                
                // Validar formato de fechas
                if (!validateDate($dateFrom) || !validateDate($dateTo)) {
                    throw new Exception("Formato de fecha inválido");
                }
                
                // Validar que dateFrom no sea mayor a dateTo
                if (strtotime($dateFrom) > strtotime($dateTo)) {
                    throw new Exception("La fecha inicial no puede ser mayor a la fecha final");
                }
                break;
            
            default:
                throw new Exception("Filtro inválido");
        }

        // Validar fechas
        if (!validateDate($dateFrom) || !validateDate($dateTo)) {
            throw new Exception("Formato de fecha inválido (usar YYYY-MM-DD)");
        }

        // Obtener estadísticas
        $stats = $model->getStats($dateFrom, $dateTo);
        
        // Obtener datos para gráfico de timeline
        $chartTimeline = $model->getChartTimeline($dateFrom, $dateTo, $filter);
        $stats['chart_timeline'] = $chartTimeline;

        echo json_encode([
            'success' => true,
            'data' => $stats,
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo,
                'filter' => $filter
            ]
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

/**
 * Obtiene últimas transacciones (ventas y compras)
 */
function getTransactions($model) {
    try {
        $filter = $_GET['filter'] ?? 'today';
        $dateFrom = null;
        $dateTo = null;

        // Determinar rango de fechas
        switch ($filter) {
            case 'today':
                $dateFrom = $dateTo = date('Y-m-d');
                break;
            case 'week':
                $dateTo = date('Y-m-d');
                $dateFrom = date('Y-m-d', strtotime('-6 days'));
                break;
            case 'month':
                $dateTo = date('Y-m-d');
                $dateFrom = date('Y-m-01');
                break;
            case 'year':
                $dateTo = date('Y-m-d');
                $dateFrom = date('Y-01-01');
                break;
            case 'custom':
                $dateFrom = $_GET['date_from'] ?? date('Y-m-d');
                $dateTo = $_GET['date_to'] ?? date('Y-m-d');
                break;
        }

        // Validar fechas
        if (!validateDate($dateFrom) || !validateDate($dateTo)) {
            throw new Exception("Formato de fecha inválido");
        }

        $transactions = $model->getTransactions($dateFrom, $dateTo);

        echo json_encode([
            'success' => true,
            'data' => $transactions,
            'count' => count($transactions)
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

/**
 * Exporta reporte en formato CSV
 */
function exportReport($model) {
    try {
        $filter = $_GET['filter'] ?? 'month';
        $dateFrom = null;
        $dateTo = null;

        // Determinar fechas según filtro
        switch ($filter) {
            case 'today':
                $dateFrom = $dateTo = date('Y-m-d');
                break;
            case 'week':
                $dateTo = date('Y-m-d');
                $dateFrom = date('Y-m-d', strtotime('-6 days'));
                break;
            case 'month':
                $dateTo = date('Y-m-d');
                $dateFrom = date('Y-m-01');
                break;
            case 'year':
                $dateTo = date('Y-m-d');
                $dateFrom = date('Y-01-01');
                break;
            case 'custom':
                $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
                $dateTo = $_GET['date_to'] ?? date('Y-m-d');
                break;
        }

        // Validar fechas
        if (!validateDate($dateFrom) || !validateDate($dateTo)) {
            throw new Exception("Formato de fecha inválido");
        }

        $stats = $model->getStats($dateFrom, $dateTo);
        $transactions = $model->getTransactions($dateFrom, $dateTo);

        // Configurar headers para descarga CSV
        $filename = "reporte_garage_barki_{$dateFrom}_al_{$dateTo}.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Encabezado del reporte
        fputcsv($output, ['=== REPORTE FINANCIERO - GARAGE BARKI ===']);
        fputcsv($output, ['Generado:', date('Y-m-d H:i:s')]);
        fputcsv($output, ['Período:', $dateFrom . ' al ' . $dateTo]);
        fputcsv($output, []);

        // Resumen Financiero
        fputcsv($output, ['=== RESUMEN GENERAL ===']);
        fputcsv($output, ['Concepto', 'Monto (USD)', 'Cantidad']);
        fputcsv($output, [
            'Total Ventas', 
            number_format($stats['ventas']['total'], 2), 
            $stats['ventas']['cantidad']
        ]);
        fputcsv($output, [
            'Total Compras', 
            number_format($stats['compras']['total'], 2), 
            $stats['compras']['cantidad']
        ]);
        
        $ganancia = $stats['ventas']['total'] - $stats['compras']['total'];
        $margen = $stats['ventas']['total'] > 0 
            ? (($ganancia / $stats['ventas']['total']) * 100) 
            : 0;
        
        fputcsv($output, [
            'Ganancia Neta', 
            number_format($ganancia, 2), 
            ''
        ]);
        fputcsv($output, [
            'Margen de Ganancia (%)', 
            number_format($margen, 2) . '%', 
            ''
        ]);
        fputcsv($output, []);

        // Cuentas
        fputcsv($output, ['=== CUENTAS ===']);
        fputcsv($output, [
            'Cuentas por Cobrar', 
            number_format($stats['cuentas_cobrar']['saldo_total'], 2), 
            $stats['cuentas_cobrar']['cantidad']
        ]);
        fputcsv($output, [
            '  - Vencidas', 
            '', 
            $stats['cuentas_cobrar']['vencidas']
        ]);
        fputcsv($output, [
            'Cuentas por Pagar', 
            number_format($stats['cuentas_pagar']['saldo_total'], 2), 
            $stats['cuentas_pagar']['cantidad']
        ]);
        fputcsv($output, [
            '  - Vencidas', 
            '', 
            $stats['cuentas_pagar']['vencidas']
        ]);
        fputcsv($output, []);

        // Inventario
        fputcsv($output, ['=== INVENTARIO ===']);
        fputcsv($output, [
            'Prendas Vendidas', 
            '', 
            $stats['inventario']['vendidas']
        ]);
        fputcsv($output, [
            'Prendas Disponibles', 
            '', 
            $stats['inventario']['disponibles']
        ]);
        fputcsv($output, []);

        // Transacciones Detalladas
        fputcsv($output, ['=== DETALLE DE TRANSACCIONES ===']);
        fputcsv($output, [
            'Fecha', 
            'Tipo', 
            'Referencia', 
            'Cliente/Proveedor', 
            'Monto (USD)', 
            'Estado'
        ]);
        
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
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit();
    }
}

/**
 * Valida formato de fecha YYYY-MM-DD
 */
function validateDate($date) {
    if (!$date) return false;
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}