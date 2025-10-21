<!-- /app/views/admin/sales-admin.php -->
<?php $pageTitle = "Ventas | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4">
        
        <!-- Header con Gradiente -->
        <div class="card bg-gradient-primary text-white mb-4 border-0 shadow">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1 fw-bold"><i class="fas fa-shopping-cart me-2"></i>Gestión de Ventas</h2>
                        <p class="mb-0 opacity-75">Sistema integral de registro y control de ventas</p>
                    </div>
                    <button class="btn btn-light btn-lg px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addSaleModal">
                        <i class="fas fa-plus-circle me-2"></i>Nueva Venta
                    </button>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Estadísticas -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Total Ventas</p>
                                <h3 class="mb-0 fw-bold" id="totalSales">0</h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-receipt text-primary fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Ingresos</p>
                                <h3 class="mb-0 fw-bold text-success" id="totalRevenue">$0</h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-dollar-sign text-success fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Pendiente</p>
                                <h3 class="mb-0 fw-bold text-warning" id="totalPending">$0</h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-clock text-warning fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-muted mb-1 small">Completadas</p>
                                <h3 class="mb-0 fw-bold text-info" id="completedSales">0</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded-3">
                                <i class="fas fa-check-circle text-info fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Ventas -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Listado de Ventas</h5>
                    </div>
                    <div class="col-auto">
                        <input type="text" id="searchInput" class="form-control" placeholder="🔍 Buscar...">
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">#</th>
                                <th>Cliente</th>
                                <th>Vendedor</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-end">Total</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="salesTableBody">
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="spinner-border text-primary"></div>
                                    <p class="text-muted mt-2 mb-0">Cargando ventas...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal: Nueva Venta -->
<div class="modal fade" id="addSaleModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="addSaleForm">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-cart-plus me-2"></i>Nueva Venta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Cliente <span class="text-danger">*</span></label>
                            <select class="form-select" name="cliente_ced" id="add_cliente" required>
                                <option value="">Seleccione...</option>
                            </select>
                            <div class="invalid-feedback">Seleccione un cliente</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Vendedor <span class="text-danger">*</span></label>
                            <select class="form-select" name="empleado_ced" id="add_empleado" required>
                                <option value="">Seleccione...</option>
                            </select>
                            <div class="invalid-feedback">Seleccione un vendedor</div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Tipo de Venta</label>
                            <select class="form-select" name="tipo_venta" required>
                                <option value="contado">Contado</option>
                                <option value="credito">Crédito</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Método de Pago</label>
                            <select class="form-select" name="metodo_pago_principal" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="tarjeta_debito">Tarjeta Débito</option>
                                <option value="tarjeta_credito">Tarjeta Crédito</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="pago_movil">Pago Móvil</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Descuento (%)</label>
                            <input type="number" class="form-control" name="descuento" id="add_descuento" 
                                   value="0" min="0" max="100" step="0.01">
                        </div>
                    </div>

                    <div class="card border-primary mb-3">
                        <div class="card-header bg-primary text-white d-flex justify-content-between">
                            <span><i class="fas fa-box me-2"></i>Productos</span>
                            <button type="button" class="btn btn-sm btn-light" id="btnAddProduct">
                                <i class="fas fa-plus me-1"></i>Agregar
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="productsContainer"></div>
                            <div class="alert alert-info mb-0" id="noProductsAlert">
                                <i class="fas fa-info-circle me-2"></i>No hay productos. Haga clic en "Agregar".
                            </div>
                        </div>
                    </div>

                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-calculator me-2"></i>Resumen
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span class="fw-bold" id="summary_subtotal">$0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Descuento:</span>
                                <span class="fw-bold text-danger" id="summary_discount">$0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fs-5 fw-bold">TOTAL:</span>
                                <span class="fs-4 fw-bold text-success" id="summary_total">$0.00</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label fw-bold">Observaciones</label>
                        <textarea class="form-control" name="observaciones" rows="2" maxlength="500"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Venta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Ver Detalles -->
<div class="modal fade" id="viewSaleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Detalles de Venta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="saleDetailsContent"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Agregar Pago -->
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addPaymentForm">
                <input type="hidden" name="venta_id" id="payment_venta_id">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i>Registrar Pago</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        Venta #<span id="payment_sale_number"></span><br>
                        Saldo: <strong id="payment_saldo_pendiente">$0</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Monto *</label>
                        <input type="number" step="0.01" class="form-control" name="monto" id="payment_monto" required>
                        <div class="invalid-feedback">Monto inválido</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Método *</label>
                        <select class="form-select" name="metodo_pago" id="payment_metodo" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="pago_movil">Pago Móvil</option>
                            <option value="tarjeta_debito">Tarjeta Débito</option>
                        </select>
                    </div>
                    <div class="mb-3" id="payment_ref_group" style="display:none;">
                        <label class="form-label fw-bold">Referencia *</label>
                        <input type="text" class="form-control" name="referencia" id="payment_referencia" pattern="[A-Za-z0-9\-]+">
                        <div class="invalid-feedback">Solo letras, números y guiones</div>
                    </div>
                    <div class="mb-3" id="payment_bank_group" style="display:none;">
                        <label class="form-label fw-bold">Banco</label>
                        <input type="text" class="form-control" name="banco" maxlength="50">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/BarkiOS/public/assets/js/sales-admin.js"></script>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.hover-lift {
    transition: transform 0.2s;
}
.hover-lift:hover {
    transform: translateY(-5px);
}
.product-row {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 10px;
    border-left: 3px solid #667eea;
}
</style>

</body>
</html>