<?php
namespace Barkios\models;

use Barkios\core\Database;
use PDO;
use Exception;

/**
 * Modelo Dashboard
 * Gestiona estadísticas y reportes del sistema
 */
class Dashboard extends Database
{
    /**
     * Obtiene estadísticas generales del período
     */
    public function getStats($dateFrom, $dateTo)
    {
        try {
            $stats = [
                'ventas' => $this->getVentasStats($dateFrom, $dateTo),
                'compras' => $this->getComprasStats($dateFrom, $dateTo),
                'cuentas_cobrar' => $this->getCuentasCobrarStats(),
                'cuentas_pagar' => $this->getCuentasPagarStats(),
                'inventario' => $this->getInventarioStats($dateFrom, $dateTo),
                'productos' => $this->getProductosStats() // ✅ NUEVO: Estadísticas de productos
            ];

            return $stats;
        } catch (Exception $e) {
            error_log("Error en Dashboard::getStats - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Estadísticas de Ventas
     */
    private function getVentasStats($dateFrom, $dateTo)
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as cantidad,
                COALESCE(SUM(monto_total), 0) as total,
                COALESCE(AVG(monto_total), 0) as promedio
            FROM ventas
            WHERE DATE(fecha) BETWEEN :from AND :to
            AND estado_venta != 'cancelada'
        ");
        $stmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calcular período anterior para comparación
        $daysDiff = (strtotime($dateTo) - strtotime($dateFrom)) / 86400 + 1;
        $prevFrom = date('Y-m-d', strtotime($dateFrom . " -{$daysDiff} days"));
        $prevTo = date('Y-m-d', strtotime($dateTo . " -{$daysDiff} days"));

        $stmt->execute([':from' => $prevFrom, ':to' => $prevTo]);
        $previous = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calcular tendencia
        $tendencia = 0;
        if ($previous['total'] > 0) {
            $tendencia = (($current['total'] - $previous['total']) / $previous['total']) * 100;
        } elseif ($current['total'] > 0) {
            $tendencia = 100;
        }

        return [
            'cantidad' => (int)$current['cantidad'],
            'total' => (float)$current['total'],
            'promedio' => (float)$current['promedio'],
            'tendencia' => round($tendencia, 1)
        ];
    }

    /**
     * Estadísticas de Compras
     */
    private function getComprasStats($dateFrom, $dateTo)
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as cantidad,
                COALESCE(SUM(monto_total), 0) as total,
                COALESCE(AVG(monto_total), 0) as promedio
            FROM compras
            WHERE DATE(fecha_compra) BETWEEN :from AND :to
            AND activo = 1
        ");
        $stmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
        $current = $stmt->fetch(PDO::FETCH_ASSOC);

        // Período anterior
        $daysDiff = (strtotime($dateTo) - strtotime($dateFrom)) / 86400 + 1;
        $prevFrom = date('Y-m-d', strtotime($dateFrom . " -{$daysDiff} days"));
        $prevTo = date('Y-m-d', strtotime($dateTo . " -{$daysDiff} days"));

        $stmt->execute([':from' => $prevFrom, ':to' => $prevTo]);
        $previous = $stmt->fetch(PDO::FETCH_ASSOC);

        // Tendencia
        $tendencia = 0;
        if ($previous['total'] > 0) {
            $tendencia = (($current['total'] - $previous['total']) / $previous['total']) * 100;
        } elseif ($current['total'] > 0) {
            $tendencia = 100;
        }

        return [
            'cantidad' => (int)$current['cantidad'],
            'total' => (float)$current['total'],
            'promedio' => (float)$current['promedio'],
            'tendencia' => round($tendencia, 1)
        ];
    }

    /**
     * Estadísticas de Cuentas por Cobrar
     */
    private function getCuentasCobrarStats()
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(DISTINCT cc.cuenta_cobrar_id) as cantidad,
                COALESCE(SUM(v.saldo_pendiente), 0) as saldo_total,
                COUNT(DISTINCT CASE 
                    WHEN cc.estado = 'vencido' THEN cc.cuenta_cobrar_id 
                END) as vencidas,
                COUNT(DISTINCT CASE 
                    WHEN DATEDIFF(cc.vencimiento, NOW()) <= 3 
                    AND cc.estado = 'pendiente' 
                    THEN cc.cuenta_cobrar_id 
                END) as por_vencer
            FROM cuentas_cobrar cc
            INNER JOIN credito cr ON cc.credito_id = cr.credito_id
            INNER JOIN ventas v ON cr.venta_id = v.venta_id
            WHERE cc.estado IN ('pendiente', 'vencido')
            AND v.estado_venta != 'cancelada'
        ");

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'cantidad' => (int)$result['cantidad'],
            'saldo_total' => (float)$result['saldo_total'],
            'vencidas' => (int)$result['vencidas'],
            'por_vencer' => (int)$result['por_vencer']
        ];
    }

    /**
     * Estadísticas de Cuentas por Pagar
     */
    private function getCuentasPagarStats()
    {
        $stmt = $this->db->query("
            SELECT 
                COUNT(DISTINCT cp.cuenta_pagar_id) as cantidad,
                COALESCE(SUM(
                    cp.monto - COALESCE(
                        (SELECT SUM(pc.monto) 
                         FROM pagos_compras pc 
                         WHERE pc.cuenta_pagar_id = cp.cuenta_pagar_id 
                         AND pc.estado_pago = 'CONFIRMADO'),
                        0
                    )
                ), 0) as saldo_total,
                COUNT(DISTINCT CASE 
                    WHEN cp.estado = 'vencido' THEN cp.cuenta_pagar_id 
                END) as vencidas,
                COUNT(DISTINCT CASE 
                    WHEN DATEDIFF(cp.fecha_vencimiento, NOW()) <= 7 
                    AND cp.estado = 'pendiente' 
                    THEN cp.cuenta_pagar_id 
                END) as por_vencer
            FROM cuentas_pagar cp
            INNER JOIN compras c ON cp.compra_id = c.compra_id
            WHERE cp.estado IN ('pendiente', 'vencido')
            AND c.activo = 1
        ");

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return [
            'cantidad' => (int)$result['cantidad'],
            'saldo_total' => (float)$result['saldo_total'],
            'vencidas' => (int)$result['vencidas'],
            'por_vencer' => (int)$result['por_vencer']
        ];
    }

    /**
     * Estadísticas de Inventario (prendas vendidas y disponibles)
     */
    private function getInventarioStats($dateFrom, $dateTo)
    {
        // Prendas vendidas en el período
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT p.codigo_prenda) as vendidas
            FROM prendas p
            INNER JOIN detalle_venta dv ON p.codigo_prenda = dv.codigo_prenda
            INNER JOIN ventas v ON dv.venta_id = v.venta_id
            WHERE DATE(v.fecha) BETWEEN :from AND :to
            AND v.estado_venta != 'cancelada'
        ");
        $stmt->execute([':from' => $dateFrom, ':to' => $dateTo]);
        $vendidas = $stmt->fetch(PDO::FETCH_ASSOC);

        // Inventario disponible actual
        $stmt = $this->db->query("
            SELECT COUNT(*) as disponibles
            FROM prendas
            WHERE estado = 'DISPONIBLE' AND activo = 1
        ");
        $disponibles = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'vendidas' => (int)$vendidas['vendidas'],
            'disponibles' => (int)$disponibles['disponibles']
        ];
    }

    /**
     * ✅ NUEVO: Estadísticas de Productos Totales en el Sistema
     */
    private function getProductosStats()
    {
        try {
            // Total de productos activos (disponibles + vendidas)
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN estado = 'DISPONIBLE' THEN 1 END) as disponibles,
                    COUNT(CASE WHEN estado = 'VENDIDA' THEN 1 END) as vendidas
                FROM prendas
                WHERE activo = 1
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // Valor total del inventario disponible
            $stmtValor = $this->db->query("
                SELECT COALESCE(SUM(precio), 0) as valor_total
                FROM prendas
                WHERE estado = 'DISPONIBLE' AND activo = 1
            ");
            $valor = $stmtValor->fetch(PDO::FETCH_ASSOC);

            return [
                'total' => (int)$result['total'],
                'disponibles' => (int)$result['disponibles'],
                'vendidas' => (int)$result['vendidas'],
                'valor_inventario' => (float)$valor['valor_total']
            ];

        } catch (Exception $e) {
            error_log("Error en getProductosStats: " . $e->getMessage());
            return [
                'total' => 0,
                'disponibles' => 0,
                'vendidas' => 0,
                'valor_inventario' => 0
            ];
        }
    }

    /**
     * Obtiene datos para gráfico de línea temporal
     */
    public function getChartTimeline($dateFrom, $dateTo, $filter)
    {
        // Determinar agrupación según período
        $groupBy = 'DATE(fecha)';
        $dateFormat = '%Y-%m-%d';
        
        if ($filter === 'year') {
            $groupBy = 'DATE_FORMAT(fecha, "%Y-%m")';
            $dateFormat = '%Y-%m';
        } elseif ($filter === 'month') {
            $groupBy = 'DATE(fecha)';
            $dateFormat = '%Y-%m-%d';
        }

        // Ventas por período
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(fecha, :format) as periodo,
                COALESCE(SUM(monto_total), 0) as total
            FROM ventas
            WHERE DATE(fecha) BETWEEN :from AND :to
            AND estado_venta != 'cancelada'
            GROUP BY $groupBy
            ORDER BY fecha
        ");
        $stmt->execute([
            ':from' => $dateFrom,
            ':to' => $dateTo,
            ':format' => $dateFormat
        ]);
        $ventasData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compras por período
        $stmt = $this->db->prepare("
            SELECT 
                DATE_FORMAT(fecha_compra, :format) as periodo,
                COALESCE(SUM(monto_total), 0) as total
            FROM compras
            WHERE DATE(fecha_compra) BETWEEN :from AND :to
            AND activo = 1
            GROUP BY DATE_FORMAT(fecha_compra, :format)
            ORDER BY fecha_compra
        ");
        $stmt->execute([
            ':from' => $dateFrom,
            ':to' => $dateTo,
            ':format' => $dateFormat
        ]);
        $comprasData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Combinar datos
        $labels = [];
        $ventas = [];
        $compras = [];

        // Crear array de períodos
        $periodos = [];
        foreach ($ventasData as $v) {
            $periodos[$v['periodo']] = ['ventas' => $v['total'], 'compras' => 0];
        }
        foreach ($comprasData as $c) {
            if (!isset($periodos[$c['periodo']])) {
                $periodos[$c['periodo']] = ['ventas' => 0, 'compras' => 0];
            }
            $periodos[$c['periodo']]['compras'] = $c['total'];
        }

        // Ordenar y formatear
        ksort($periodos);
        foreach ($periodos as $periodo => $data) {
            $labels[] = $this->formatPeriodLabel($periodo, $filter);
            $ventas[] = (float)$data['ventas'];
            $compras[] = (float)$data['compras'];
        }

        return [
            'labels' => $labels,
            'ventas' => $ventas,
            'compras' => $compras
        ];
    }

    /**
     * Formatea etiqueta de período para gráfico
     */
    private function formatPeriodLabel($periodo, $filter)
    {
        if ($filter === 'year') {
            // Formato: "Ene 2024"
            $date = date_create_from_format('Y-m', $periodo);
            return $date ? $date->format('M Y') : $periodo;
        } elseif ($filter === 'month') {
            // Formato: "15 Ene"
            $date = date_create_from_format('Y-m-d', $periodo);
            return $date ? $date->format('d M') : $periodo;
        } else {
            // Formato: "15/01"
            $date = date_create_from_format('Y-m-d', $periodo);
            return $date ? $date->format('d/m') : $periodo;
        }
    }

    /**
     * Obtiene transacciones recientes (ventas y compras)
     */
    public function getTransactions($dateFrom, $dateTo, $limit = 50)
    {
        // Ventas
        $stmt = $this->db->prepare("
            SELECT 
                v.fecha,
                'VENTA' as tipo,
                v.referencia,
                c.nombre_cliente as cliente_proveedor,
                v.monto_total as monto,
                v.estado_venta as estado
            FROM ventas v
            INNER JOIN clientes c ON v.cliente_ced = c.cliente_ced
            WHERE DATE(v.fecha) BETWEEN :from AND :to
            ORDER BY v.fecha DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':from', $dateFrom);
        $stmt->bindValue(':to', $dateTo);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Compras
        $stmt = $this->db->prepare("
            SELECT 
                c.fecha_compra as fecha,
                'COMPRA' as tipo,
                c.factura_numero as referencia,
                p.nombre_empresa as cliente_proveedor,
                c.monto_total as monto,
                'completada' as estado
            FROM compras c
            INNER JOIN proveedores p ON c.proveedor_rif = p.proveedor_rif
            WHERE DATE(c.fecha_compra) BETWEEN :from AND :to
            AND c.activo = 1
            ORDER BY c.fecha_compra DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':from', $dateFrom);
        $stmt->bindValue(':to', $dateTo);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Combinar y ordenar por fecha
        $transactions = array_merge($ventas, $compras);
        usort($transactions, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        return array_slice($transactions, 0, $limit);
    }
}