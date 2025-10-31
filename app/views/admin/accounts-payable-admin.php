<?php $pageTitle = "Cuentas por Pagar | Garage Barki"; ?>
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

.modal-dialog-scrollable .modal-body {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

.stat-card {
    transition: transform 0.2s, box-shadow 0.2s;
    border-left: 4px solid;
    cursor: pointer;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15) !important;
}

.stat-card.primary { border-left-color: #0d6efd; }
.stat-card.success { border-left-color: #198754; }
.stat-card.warning { border-left-color: #ffc107; }
.stat-card.danger { border-left-color: #dc3545; }

.cuenta-row {
    transition: all 0.2s ease;
}

.cuenta-row:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
}

.cuenta-row.vencida {
    border-left: 4px solid #dc3545;
}

.cuenta-row.por-vencer {
    border-left: 4px solid #ffc107;
}

.cuenta-row.al-dia {
    border-left: 4px solid #198754;
}

.badge-estado {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

.pago-item {
    background: #f8f9fa;
    border-left: 3px solid #0d6efd;
    padding: 10px;
    margin-bottom: 8px;
    border-radius: 4px;
}

::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <div>
                <h3 class="mb-1"><i class="fas fa-file-invoice-dollar me-2 text-danger"></i>Cuentas por Pagar</h3>
                <p class="text-muted mb-0 small">Gestione las deudas con proveedores y registre pagos</p>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 stat-card primary">
                    <div class="card-body text-center">
                        <i class="fas fa-file-invoice fa-2x text-primary mb-2"></i>
                        <h6 class="text-muted small mb-1">Total Cuentas</h6>
                        <h4 class="mb-0" id="statTotalCuentas">0</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 stat-card danger">
                    <div class="card-body text-center">
                        <i class="fas fa-exclamation-circle fa-2x text-danger mb-2"></i>
                        <h6 class="text-muted small mb-1">Deuda Total</h6>
                        <h4 class="mb-0 text-danger" id="statDeudaTotal">$0.00</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 stat-card warning">
                    <div class="card-body text-center">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <h6 class="text-muted small mb-1">Por Vencer (7 días)</h6>
                        <h4 class="mb-0 text-warning" id="statPorVencer">0</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 stat-card danger">
                    <div class="card-body text-center">
                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                        <h6 class="text-muted small mb-1">Vencidas</h6>
                        <h4 class="mb-0 text-danger" id="statVencidas">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0" 
                           id="searchInput" 
                           placeholder="Buscar por proveedor, factura...">
                </div>
            </div>
            <div class="col-md-6">
                <select class="form-select" id="filterEstado">
                    <option value="">Todos los estados</option>
                    <option value="pendiente">Pendientes</option>
                    <option value="vencido">Vencidas</option>
                </select>
            </div>
        </div>

        <!-- Tabla de Cuentas por Pagar -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="accountsTable" class="table table-hover align-middle mb-0 w-100">
                        <thead class="table-light">
                            <tr>
                                <th>N° Factura</th>
                                <th>Proveedor</th>
                                <th>Fecha Emisión</th>
                                <th>Vencimiento</th>
                                <th class="text-end">Monto Total</th>
                                <th class="text-end">Pagado</th>
                                <th class="text-end">Saldo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: REGISTRAR PAGO - VERSIÓN MEJORADA -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="addPaymentForm" autocomplete="off">
                <!-- ✅ Input oculto para enviar monto en USD -->
                <input type="hidden" id="paymentMonto" name="monto">
                <input type="hidden" id="paymentCuentaId" name="cuenta_pagar_id">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-money-bill-wave me-2"></i>Registrar Pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <!-- Información de la cuenta -->
                    <div class="alert alert-info mb-3">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Proveedor:</strong> <span id="paymentProveedor"></span></p>
                                <p class="mb-1"><strong>Factura:</strong> #<span id="paymentFactura"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Monto Total:</strong> $<span id="paymentMontoTotal"></span></p>
                                <p class="mb-1"><strong>Saldo Pendiente:</strong> <span class="text-danger fw-bold" id="paymentSaldo"></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <!-- Moneda -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Moneda <span class="text-danger">*</span></label>
                            <select id="paymentMoneda" name="moneda_pago" class="form-select" required>
                                <option value="USD" selected>Dólares (USD)</option>
                                <option value="BS">Bolívares (Bs)</option>
                            </select>
                        </div>

                        <!-- Fecha de Pago -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha de Pago <span class="text-danger">*</span></label>
                            <input type="date" 
                                   class="form-control" 
                                   id="paymentFecha" 
                                   name="fecha_pago"
                                   value="<?= date('Y-m-d') ?>" 
                                   max="<?= date('Y-m-d') ?>"
                                   required>
                        </div>

                        <!-- Monto con validación visual -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Monto a Pagar <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text" id="currency-symbol">$</span>
                                <input type="number" 
                                       id="paymentMontoGeneral"
                                       step="0.01" 
                                       class="form-control"
                                       min="0.01" 
                                       placeholder="0.00" 
                                       required
                                       aria-describedby="equivInfo">
                            </div>
                            <!-- ✅ Área de conversión/equivalencia -->
                            <div id="equivInfo" class="mt-2" style="display: none;"></div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Tipo de pago -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de Pago <span class="text-danger">*</span></label>
                            <select class="form-select" id="paymentTipo" name="tipo_pago" required>
                                <option value="">Seleccionar...</option>
                            </select>
                            <div class="invalid-feedback">Seleccione un método de pago</div>
                        </div>

                        <!-- Referencia Bancaria (oculto inicialmente) -->
                        <div class="col-md-6" id="referenciaField" style="display: none;">
                            <label class="form-label fw-bold">Referencia Bancaria <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="paymentReferencia" 
                                   name="referencia_bancaria"
                                   maxlength="10"
                                   pattern="\d{8,10}"
                                   placeholder="8-10 dígitos">
                            <small class="form-text text-muted">8 a 10 dígitos numéricos</small>
                            <div class="invalid-feedback">Ingrese una referencia válida (8-10 dígitos)</div>
                        </div>

                        <!-- Banco (oculto inicialmente) -->
                        <div class="col-md-6" id="bancoField" style="display: none;">
                            <label class="form-label fw-bold">Banco <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control" 
                                   id="paymentBanco" 
                                   name="banco"
                                   maxlength="30"
                                   placeholder="Ej: Banco Provincial">
                            <small class="form-text text-muted">Hasta 30 caracteres</small>
                            <div class="invalid-feedback">Ingrese el nombre del banco (3-30 caracteres)</div>
                        </div>

                        <!-- Observaciones -->
                        <div class="col-12">
                            <label class="form-label fw-bold">Observaciones (opcional)</label>
                            <textarea class="form-control" 
                                      name="observaciones" 
                                      rows="2" 
                                      maxlength="500"
                                      placeholder="Detalles adicionales del pago..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="btnGuardarPago">
                        <span class="spinner-border spinner-border-sm d-none me-2"></span>
                        <span class="btn-text">
                            <i class="fas fa-check me-1"></i>Registrar Pago
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: VER DETALLE DE CUENTA -->
<div class="modal fade" id="viewAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Detalle de Cuenta por Pagar</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewAccountContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Cargando...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php 
if (!function_exists('getDolarRate')) {
    require_once __DIR__ . '/../../core/AdminContext.php';
}
?>

<script>
    const DOLAR_BCV_RATE = <?php echo getDolarRate(); ?>;
</script>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.3/dist/sweetalert2.all.min.js"></script>
<script type="module" src="/BarkiOS/public/assets/js/admin/accounts-payable-admin.js"></script>
<script src="/BarkiOS/public/assets/js/admin/logout.js"></script>

</body>
</html>