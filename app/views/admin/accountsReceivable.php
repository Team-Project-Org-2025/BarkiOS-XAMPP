<?php $pageTitle = "Cuentas por Cobrar | Garage Barki"; ?>
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
.badge-vigente { background-color: #28a745; }
.badge-por-vencer { background-color: #ffc107; }
.badge-vencido { background-color: #dc3545; }
.badge-pagado { background-color: #17a2b8; }
</style>

<div class="main-content">
    <div class="container-fluid">
        
        <!-- Header con título y botón -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">Cuentas por Cobrar</h1>
            <div class="d-flex gap-2">
                <button class="btn btn-warning btn-sm rounded-pill px-4" 
                        onclick="processExpiredAccounts()" 
                        title="Procesar vencimientos">
                    <i class="fas fa-sync-alt me-1"></i> Procesar Vencidos
                </button>
            </div>
        </div>

        <!-- Tabla de Cuentas por Cobrar -->
        <div class="card mt-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle text-center">
                        <thead>
                            <tr>
                                <th>Nº Factura</th>
                                <th>Cliente</th>
                                <th>Emisión</th>
                                <th>Monto</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="accountsTableBody">
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Detalles de Cuenta -->
<div class="modal fade" id="viewAccountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>
                    Detalle de Cuenta por Cobrar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="accountDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Cargando detalles...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Registrar Pago -->
<div class="modal fade" id="registerPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="registerPaymentForm">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-money-bill-wave me-2"></i>
                        Registrar Pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="payment_cuenta_id" name="cuenta_cobrar_id">
                    
                    <!-- Info de la cuenta -->
                    <div class="alert alert-info mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Cliente:</strong> <span id="payment_cliente"></span></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><strong>Saldo pendiente:</strong></span>
                            <strong class="text-danger" id="payment_saldo">$0.00</strong>
                        </div>
                    </div>

                    <!-- Monto -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Monto a pagar <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" name="monto" 
                                   step="0.01" min="0.01" required>
                        </div>
                        <small class="text-muted">Ingrese el monto en USD</small>
                    </div>

                    <!-- Tipo de pago -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipo de pago</label>
                        <select class="form-select" name="tipo_pago">
                            <option value="EFECTIVO">Efectivo</option>
                            <option value="TRANSFERENCIA">Transferencia</option>
                            <option value="PAGO_MOVIL">Pago Móvil</option>
                            <option value="ZELLE">Zelle</option>
                            <option value="PUNTO">Punto de Venta</option>
                            <option value="CHEQUE">Cheque</option>
                        </select>
                    </div>

                    <!-- Moneda -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Moneda</label>
                        <select class="form-select" name="moneda_pago">
                            <option value="USD">USD ($)</option>
                            <option value="BS">Bolívares (Bs)</option>
                        </select>
                    </div>

                    <!-- Referencia bancaria -->
                    <div class="mb-3" id="refBancariaGroup">
                        <label class="form-label fw-bold">Referencia bancaria</label>
                        <input type="text" class="form-control" name="referencia_bancaria" 
                               placeholder="Ej: 123456789">
                    </div>

                    <!-- Banco -->
                    <div class="mb-3" id="bancoGroup">
                        <label class="form-label fw-bold">Banco</label>
                        <input type="text" class="form-control" name="banco" 
                               placeholder="Ej: Banesco">
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Registrar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Extender Vencimiento -->
<div class="modal fade" id="extendDateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="extendDateForm">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-calendar-plus me-2"></i>
                        Extender Fecha de Vencimiento
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="extend_cuenta_id" name="cuenta_id">
                    
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Cliente:</strong> <span id="extend_cliente"></span><br>
                        <strong>Vencimiento actual:</strong> <span id="extend_fecha_actual"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Nueva fecha de vencimiento <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" name="nueva_fecha" 
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        <small class="text-muted">La fecha debe ser posterior a hoy</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-calendar-check me-1"></i> Actualizar Fecha
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 para alertas bonitas -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/BarkiOS/public/assets/js/accountsReceivable.js"></script>
<<<<<<<<< Temporary merge branch 1
<script src="/BarkiOS/public/assets/js/logout.js"></script>
=========

>>>>>>>>> Temporary merge branch 2
</body>
</html>