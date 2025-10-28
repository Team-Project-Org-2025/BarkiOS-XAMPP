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
                    <option value="pagado">Pagadas</option>
                    <option value="vencido">Vencidas</option>
                </select>
            </div>
        </div>

        <!-- Tabla de Cuentas por Pagar -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">N° Factura</th>
                                <th>Proveedor</th>
                                <th>Fecha Emisión</th>
                                <th>Vencimiento</th>
                                <th class="text-end">Monto Total</th>
                                <th class="text-end">Pagado</th>
                                <th class="text-end">Saldo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center" width="150">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="accountsTableBody">
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">Cargando cuentas...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: REGISTRAR PAGO -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="addPaymentForm" autocomplete="off">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-money-bill-wave me-2"></i>Registrar Pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="paymentCuentaId" name="cuenta_pagar_id">
                    
                    <!-- Información de la cuenta -->
                    <div class="alert alert-info">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Proveedor:</strong> <span id="paymentProveedor"></span></p>
                                <p class="mb-1"><strong>Factura:</strong> #<span id="paymentFactura"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Monto Total:</strong> $<span id="paymentMontoTotal"></span></p>
                                <p class="mb-1"><strong>Saldo Pendiente:</strong> <span class="text-danger fw-bold">$<span id="paymentSaldo"></span></span></p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Monto a Pagar <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="paymentMonto" 
                                       name="monto"
                                       min="0.01" 
                                       step="0.01" 
                                       required>
                            </div>
                        </div>

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

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Tipo de Pago <span class="text-danger">*</span></label>
                            <select class="form-select" id="paymentTipo" name="tipo_pago" required>
                                <option value="">Seleccionar...</option>
                                <option value="EFECTIVO">Efectivo</option>
                                <option value="TRANSFERENCIA">Transferencia</option>
                                <option value="PAGO_MOVIL">Pago Móvil</option>
                                <option value="ZELLE">Zelle</option>
                                <option value="CHEQUE">Cheque</option>
                                <option value="OTRO">Otro</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Moneda <span class="text-danger">*</span></label>
                            <select class="form-select" name="moneda_pago" required>
                                <option value="USD" selected>USD ($)</option>
                                <option value="BS">Bolívares (Bs)</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="referenciaField" style="display: none;">
                            <label class="form-label fw-bold">Referencia Bancaria</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="paymentReferencia" 
                                   name="referencia_bancaria"
                                   maxlength="50">
                        </div>

                        <div class="col-md-6" id="bancoField" style="display: none;">
                            <label class="form-label fw-bold">Banco</label>
                            <select class="form-select" id="paymentBanco" name="banco">
                                <option value="">Seleccionar...</option>
                                <option value="Banesco">Banesco</option>
                                <option value="Mercantil">Mercantil</option>
                                <option value="Venezuela">Venezuela</option>
                                <option value="Provincial">Provincial</option>
                                <option value="Bicentenario">Bicentenario</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Observaciones</label>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/BarkiOS/public/assets/js/accounts-payable-admin.js"></script>
<script src="/BarkiOS/public/assets/js/logout.js"></script>

</body>
</html>