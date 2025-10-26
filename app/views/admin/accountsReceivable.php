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


<!--  MODAL REGISTRAR PAGO -->
<div class="modal fade" id="registerPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-money-bill-wave me-2"></i> Registrar Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="registerPaymentForm">
            <input type="hidden" name="monto">

                <input type="hidden" name="cuenta_cobrar_id" id="payment_cuenta_id">

                <div class="modal-body">

                    <!--ALERTA CLIENTE / SALDO-->
                    <div class="alert alert-info mb-3"> 
                        <div class="d-flex justify-content-between">
                            <span><strong>Cliente:</strong> <span id="payment_cliente"></span></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><strong>Saldo pendiente:</strong></span>
                            <strong class="text-danger" id="payment_saldo">$0.00</strong>
                        </div>
                    </div>
                    <!-- ✅ FIN ALERTA -->

                    <!-- Moneda -->
                    <div class="mb-3">
                        <label class="form-label">Moneda</label>
                        <select name="moneda_pago" id="payment_moneda" class="form-select" required>
                            <option value="USD" selected>USD</option>
                            <option value="BS">Bolívares (Bs)</option>
                        </select>
                    </div>
                    <!-- Input único de monto -->
                    <div class="mb-3" id="group_monto_general">
                        <label class="form-label">Monto</label>
                        <input type="number" name="monto_general" step="0.01" class="form-control" min="0" placeholder="0.00" required>
                        <small id="equiv_info" class="text-muted" style="display:none;"></small>
                    </div>

                    <!-- Tipo de pago -->
                    <div class="mb-3">
                        <label class="form-label">Tipo de Pago</label>
                        <select name="tipo_pago" class="form-select" required></select>
                    </div>

                    <!-- Referencias bancarias -->
                    <div class="mb-3" id="refBancariaGroup">
                        <label class="form-label">Referencia Bancaria</label>
                        <input type="text" name="referencia_bancaria" class="form-control">
                    </div>

                    <div class="mb-3" id="bancoGroup">
                        <label class="form-label">Banco</label>
                        <input type="text" name="banco" class="form-control">
                    </div>

                    <!-- Observaciones -->
                    <div class="mb-3">
                        <label class="form-label">Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="2"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Registrar Pago
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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
                    <input type="hidden" id="extend_cuenta_id" name="cuenta_cobrar_id">
                    
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

<?php 
if (!function_exists('getDolarRate')) {
    require_once __DIR__ . '/../../core/AdminContext.php';
}
?>

<script>
    const DOLAR_BCV_RATE = <?php echo getDolarRate(); ?>;
</script>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/BarkiOS/public/assets/js/accountsReceivable.js"></script>

<script src="/BarkiOS/public/assets/js/logout.js"></script>

</body>
</html>