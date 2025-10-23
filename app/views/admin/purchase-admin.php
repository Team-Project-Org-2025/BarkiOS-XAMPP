<!-- /app/views/admin/purchase-admin.php - VERSIÓN SIMPLIFICADA -->
<?php $pageTitle = "Compras | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<?php require_once __DIR__ . '/../partials/navbar-admin.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4">
        
        <!-- Header con Gradiente -->
        <div class="card bg-gradient-purple text-white mb-4 border-0 shadow">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2 class="mb-1 fw-bold"><i class="fas fa-shopping-bag me-2"></i>Gestión de Compras</h2>
                        <p class="mb-0 opacity-75">Registro de compras de ropa exclusiva a comercios internacionales</p>
                    </div>
                    <button class="btn btn-light btn-lg px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
                        <i class="fas fa-plus-circle me-2"></i>Nueva Compra
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de Compras -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Listado de Compras</h5>
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
                                <th>N° Factura</th>
                                <th>Tracking</th>
                                <th>Comercio</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-end">Monto Total</th>
                                <th class="text-center">PDF</th>
                            </tr>
                        </thead>
                        <tbody id="purchasesTableBody">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-purple"></div>
                                    <p class="text-muted mt-2 mb-0">Cargando compras...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal: Nueva Compra -->
<div class="modal fade" id="addPurchaseModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="addPurchaseForm">
                <div class="modal-header bg-purple text-white">
                    <h5 class="modal-title"><i class="fas fa-cart-plus me-2"></i>Nueva Compra</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Información General -->
                    <h6 class="text-purple fw-bold mb-3"><i class="fas fa-file-invoice me-2"></i>Información de Factura</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Comercio/Proveedor <span class="text-danger">*</span></label>
                            <div class="position-relative">
                                <input type="text" class="form-control" id="searchSupplier" 
                                       placeholder="Buscar comercio..." autocomplete="off">
                                <input type="hidden" name="proveedor_id" id="proveedorId" required>
                                <div id="supplierResults" class="list-group position-absolute w-100" 
                                     style="display: none; z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
                            </div>
                            <small class="text-muted">Escriba mínimo 2 caracteres para buscar</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">N° de Factura <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="factura_numero" 
                                   placeholder="Ej: FACT-USA-001" required maxlength="50">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Fecha de Compra <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="fecha_compra" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tracking / Guía</label>
                            <input type="text" class="form-control" name="tracking" 
                                   placeholder="Ej: TRACK123456" maxlength="100">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Referencia de Pago</label>
                            <input type="text" class="form-control" name="referencia" 
                                   placeholder="Ej: REF-2024-001" maxlength="100">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Teléfono de Contacto</label>
                            <input type="tel" class="form-control" name="telefono" 
                                   placeholder="Ej: +1-555-1234" maxlength="20">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Método de Pago</label>
                            <select class="form-select" name="metodo_pago">
                                <option value="">Seleccione...</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta">Tarjeta de Crédito</option>
                                <option value="paypal">PayPal</option>
                                <option value="zelle">Zelle</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="otro">Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Dirección de Envío / Comercio</label>
                            <textarea class="form-control" name="direccion" rows="2" 
                                      placeholder="Ej: 123 Main Street, New York, NY 10001, USA" maxlength="255"></textarea>
                        </div>
                    </div>

                    <hr>

                    <!-- Prendas -->
                    <div class="card border-purple mb-3">
                        <div class="card-header bg-purple text-white d-flex justify-content-between">
                            <span><i class="fas fa-tshirt me-2"></i>Prendas Compradas (cada una es única)</span>
                            <button type="button" class="btn btn-sm btn-light" id="btnAddPrenda">
                                <i class="fas fa-plus me-1"></i>Agregar Prenda
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="prendasContainer"></div>
                            <div class="alert alert-info mb-0" id="noPrendasAlert">
                                <i class="fas fa-info-circle me-2"></i>No hay prendas agregadas. Haga clic en "Agregar Prenda".
                                <br><small class="text-muted">Recuerda: Cada prenda es única con su propio precio. Si compraste 20 pantalones, debes agregar 20 prendas individuales.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen -->
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-calculator me-2"></i>Resumen de Compra
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total de Prendas:</span>
                                <span class="fw-bold" id="summary_cantidad">0</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fs-5 fw-bold">MONTO TOTAL A PAGAR:</span>
                                <span class="fs-4 fw-bold text-success" id="summary_total">$0.00</span>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-purple" id="btnGuardar">
                        <span class="btn-text">Guardar Compra y Generar PDF</span>
                        <span class="spinner-border spinner-border-sm d-none ms-2" role="status"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/BarkiOS/public/assets/js/purchase-admin.js"></script>
<script src="/BarkiOS/public/assets/js/logout.js"></script>
<style>
.bg-gradient-purple {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
.prenda-row {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 12px;
    border-left: 4px solid #667eea;
}
.bg-purple {
    background-color: #667eea !important;
}
.text-purple {
    color: #667eea !important;
}
.btn-purple {
    background-color: #667eea;
    border-color: #667eea;
    color: white;
}
.btn-purple:hover {
    background-color: #5568d3;
    border-color: #5568d3;
    color: white;
}

/* Evitar duplicación de elementos */
.table-responsive {
    position: relative;
    z-index: 1;
}

#purchasesTableBody {
    position: relative;
    z-index: 1;
}

/* Asegurar que no haya elementos duplicados */
.card .card {
    margin-bottom: 0 !important;
}

.table .table {
    margin-bottom: 0 !important;
}
