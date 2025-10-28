<?php 
require_once __DIR__ . '/../../core/AdminContext.php';
$pageTitle = "Dashboard | Garage Barki"; 
?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?>

<style>
html, body {
    height: 100%;
    overflow-y: auto;
}

.main-content {
    overflow-y: auto;
    max-height: calc(100vh - 80px);
    padding-bottom: 2rem;
}

.stat-card {
    transition: all 0.3s ease;
    border-left: 4px solid;
    height: 100%;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.15) !important;
}

.stat-card.ventas { border-left-color: #28a745; }
.stat-card.compras { border-left-color: #dc3545; }
.stat-card.cuentas-cobrar { border-left-color: #ffc107; }
.stat-card.cuentas-pagar { border-left-color: #17a2b8; }
.stat-card.inventario { border-left-color: #6f42c1; }

.chart-container {
    position: relative;
    height: 320px;
}

.filter-btn-group .btn {
    border-radius: 20px;
    padding: 0.4rem 1rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.filter-btn-group .btn.active {
    transform: scale(1.05);
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.metric-icon {
    font-size: 2.5rem;
    opacity: 0.2;
    position: absolute;
    right: 15px;
    top: 15px;
}

.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255,255,255,0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: 0.375rem;
}

.trend-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
}

.transaction-row {
    transition: background-color 0.2s;
}

.transaction-row:hover {
    background-color: #f8f9fa;
}

.period-indicator {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: #e9ecef;
    border-radius: 15px;
    font-size: 0.875rem;
    font-weight: 500;
}

/* Widget de Tasa BCV */
.bcv-widget {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 1rem 1.5rem;
    color: white;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: transform 0.2s;
}

.bcv-widget:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0,0,0,0.15);
}

.bcv-rate {
    font-size: 1.75rem;
    font-weight: 700;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

@media (max-width: 768px) {
    .filter-btn-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .filter-btn-group .btn {
        flex: 1 1 auto;
        min-width: calc(50% - 0.25rem);
    }
    
    .bcv-widget {
        margin-top: 1rem;
    }
}
</style>

<div class="main-content">
    <div class="container-fluid py-4">
        <div class="row mb-4 align-items-center">
            <div class="col-lg-5 mb-3 mb-lg-0">
                <h3 class="mb-2">
                    <i class="fas fa-chart-line me-2 text-primary"></i>
                    Dashboard Financiero
                </h3>
                <p class="text-muted mb-0">
                    <i class="fas fa-calendar-alt me-1"></i>
                    <span id="currentPeriod" class="period-indicator">Hoy</span>
                </p>
            </div>
            
            <!-- Filtros de período -->
            <div class="col-lg-4">
                <div class="d-flex justify-content-lg-end">
                    <div class="btn-group filter-btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-filter="today">
                            Hoy
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-filter="week">
                            Semana
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-filter="month">
                            Mes
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-filter="year">
                            Año
                        </button>
                        <button type="button" class="btn btn-outline-primary" data-filter="custom">
                            <i class="fas fa-calendar-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="ml-auto widget-container">
            <?php include __DIR__ . '/../partials/exchange-rate-widget.php'; ?>
        </div>

        <!-- Rango de fechas personalizado -->
        <div class="row mb-3 d-none" id="customDateRange">
            <div class="col-12">
                <div class="card border-primary">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Fecha Desde:</label>
                                <input type="date" class="form-control" id="dateFrom">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Fecha Hasta:</label>
                                <input type="date" class="form-control" id="dateTo">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-primary w-100" id="applyCustomDates">
                                    <i class="fas fa-check me-1"></i> Aplicar Filtro
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas de métricas principales -->
        <div class="row g-3 mb-4">
            <!-- Ventas -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm stat-card ventas" onclick="window.location.href='/BarkiOS/admin/sales'">
                    <div class="card-body position-relative">
                        <i class="fas fa-shopping-cart metric-icon text-success"></i>
                        <h6 class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            Ventas
                        </h6>
                        <h3 class="mb-1 fw-bold text-success" id="statVentas">
                            <span class="spinner-border spinner-border-sm"></span>
                        </h3>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted" id="statVentasCount">
                                <i class="fas fa-receipt me-1"></i>0 ventas
                            </small>
                            <span class="badge trend-badge" id="statVentasTrend"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compras -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm stat-card compras" onclick="window.location.href='/BarkiOS/admin/purchases'">
                    <div class="card-body position-relative">
                        <i class="fas fa-box metric-icon text-danger"></i>
                        <h6 class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            Compras
                        </h6>
                        <h3 class="mb-1 fw-bold text-danger" id="statCompras">
                            <span class="spinner-border spinner-border-sm"></span>
                        </h3>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted" id="statComprasCount">
                                <i class="fas fa-truck me-1"></i>0 compras
                            </small>
                            <span class="badge trend-badge" id="statComprasTrend"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cuentas por Cobrar -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm stat-card cuentas-cobrar" onclick="window.location.href='/BarkiOS/admin/accounts-receivable'">
                    <div class="card-body position-relative">
                        <i class="fas fa-hand-holding-usd metric-icon text-warning"></i>
                        <h6 class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            Por Cobrar
                        </h6>
                        <h3 class="mb-1 fw-bold text-warning" id="statCobrar">
                            <span class="spinner-border spinner-border-sm"></span>
                        </h3>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted" id="statCobrarCount">
                                <i class="fas fa-file-invoice me-1"></i>0 cuentas
                            </small>
                            <span class="badge bg-danger" id="statCobrarVencidas"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cuentas por Pagar -->
            <div class="col-lg-3 col-md-6">
                <div class="card border-0 shadow-sm stat-card cuentas-pagar" onclick="window.location.href='/BarkiOS/admin/accounts-payable'">
                    <div class="card-body position-relative">
                        <i class="fas fa-file-invoice-dollar metric-icon text-info"></i>
                        <h6 class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            Por Pagar
                        </h6>
                        <h3 class="mb-1 fw-bold text-info" id="statPagar">
                            <span class="spinner-border spinner-border-sm"></span>
                        </h3>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted" id="statPagarCount">
                                <i class="fas fa-file-invoice-dollar me-1"></i>0 cuentas
                            </small>
                            <span class="badge bg-danger" id="statPagarVencidas"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen Financiero e Inventario -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center border-end">
                                <h6 class="text-muted mb-2 small">Ganancia Neta</h6>
                                <h2 class="mb-0 fw-bold" id="statGananciaNeta">
                                    <span class="spinner-border spinner-border-sm"></span>
                                </h2>
                            </div>
                            <div class="col-md-3 text-center border-end">
                                <h6 class="text-muted mb-2 small">Margen (%)</h6>
                                <h4 class="mb-0 fw-bold" id="statMargen">
                                    <span class="spinner-border spinner-border-sm"></span>
                                </h4>
                            </div>
                            <div class="col-md-3 text-center border-end">
                                <h6 class="text-muted mb-2 small">Prendas Vendidas</h6>
                                <h4 class="mb-0 fw-bold text-primary" id="statPrendasVendidas">
                                    <span class="spinner-border spinner-border-sm"></span>
                                </h4>
                            </div>
                            <div class="col-md-3 text-center">
                                <h6 class="text-muted mb-2 small">Inventario Disponible</h6>
                                <h4 class="mb-0 fw-bold text-success" id="statInventario">
                                    <span class="spinner-border spinner-border-sm"></span>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tarjeta de Inventario Total -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm stat-card inventario" onclick="window.location.href='/BarkiOS/admin/products'">
                    <div class="card-body position-relative">
                        <i class="fas fa-warehouse metric-icon" style="color: #6f42c1;"></i>
                        <h6 class="text-muted mb-2 text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                            Productos en Inventario
                        </h6>
                        <h3 class="mb-1 fw-bold" style="color: #6f42c1;" id="statTotalProductos">
                            <span class="spinner-border spinner-border-sm"></span>
                        </h3>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">
                                <i class="fas fa-boxes me-1"></i>Total en sistema
                            </small>
                            <span class="badge bg-purple" id="statProductosStatus"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="row g-3 mb-4">
            <!-- Gráfico de Ventas vs Compras -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold">
                                <i class="fas fa-chart-bar me-2 text-primary"></i>
                                Ventas vs Compras
                            </h6>
                            <button class="btn btn-sm btn-outline-secondary" onclick="refreshCharts()">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body position-relative">
                        <div class="loading-overlay d-none" id="loadingChart1">
                            <div class="spinner-border text-primary"></div>
                        </div>
                        <div class="chart-container">
                            <canvas id="ventasComprasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Estado de Cuentas -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fas fa-chart-pie me-2 text-primary"></i>
                            Balance de Cuentas
                        </h6>
                    </div>
                    <div class="card-body position-relative">
                        <div class="loading-overlay d-none" id="loadingChart2">
                            <div class="spinner-border text-primary"></div>
                        </div>
                        <div class="chart-container">
                            <canvas id="cuentasChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Acciones Rápidas y Transacciones -->
        <div class="row g-3">
            <!-- Acciones Rápidas -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fas fa-bolt me-2 text-warning"></i>
                            Acciones Rápidas
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="/BarkiOS/admin/sales" class="btn btn-outline-success btn-sm text-start">
                                <i class="fas fa-plus-circle me-2"></i>
                                Nueva Venta
                            </a>
                            <a href="/BarkiOS/admin/purchases" class="btn btn-outline-danger btn-sm text-start">
                                <i class="fas fa-shopping-cart me-2"></i>
                                Nueva Compra
                            </a>
                            <a href="/BarkiOS/admin/products" class="btn btn-outline-primary btn-sm text-start">
                                <i class="fas fa-box me-2"></i>
                                Ver Inventario
                            </a>
                            <a href="/BarkiOS/admin/accounts-receivable" class="btn btn-outline-warning btn-sm text-start">
                                <i class="fas fa-hand-holding-usd me-2"></i>
                                Cuentas por Cobrar
                            </a>
                            <a href="/BarkiOS/admin/accounts-payable" class="btn btn-outline-info btn-sm text-start">
                                <i class="fas fa-file-invoice-dollar me-2"></i>
                                Cuentas por Pagar
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Alertas -->
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="mb-0 fw-semibold">
                            <i class="fas fa-bell me-2 text-danger"></i>
                            Alertas
                        </h6>
                    </div>
                    <div class="card-body" id="alertsContainer">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-muted"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Últimas Transacciones -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-bottom py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold">
                                <i class="fas fa-history me-2 text-primary"></i>
                                Últimas Transacciones
                            </h6>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary active" data-trans-type="all">Todas</button>
                                <button class="btn btn-outline-success" data-trans-type="ventas">Ventas</button>
                                <button class="btn btn-outline-danger" data-trans-type="compras">Compras</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="px-3">Fecha</th>
                                        <th>Tipo</th>
                                        <th>Referencia</th>
                                        <th>Cliente/Proveedor</th>
                                        <th class="text-end">Monto</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="transactionsTableBody">
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="spinner-border text-primary"></div>
                                            <p class="mt-2 text-muted small">Cargando transacciones...</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php 
// Variable JavaScript para la tasa del dólar
$dolarRate = getDolarRate();
?>
<script>
    // Variable global para uso en JavaScript
    const DOLAR_BCV_RATE = <?php echo $dolarRate; ?>;
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/BarkiOS/public/assets/js/home-admin.js"></script>
<script src="/BarkiOS/public/assets/js/logout.js"></script>

</body>
</html>