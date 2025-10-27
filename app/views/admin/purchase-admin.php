<?php $pageTitle = "Compras | Garage Barki"; ?>
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
.stat-card.info { border-left-color: #0dcaf0; }

.prenda-row {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.prenda-row:hover {
    border-left-color: #0d6efd;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}

#supplierResults, #editSupplierResults {
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
    border-radius: 0.375rem;
}

.supplier-item:hover {
    background-color: #f8f9fa;
}

.purchase-row {
    transition: all 0.2s ease;
}

.purchase-row:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
}

.badge-pago {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}

#prendasContainer, #editPrendasContainer {
    max-height: 500px;
    overflow-y: auto;
    padding-right: 10px;
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
                <h3 class="mb-1"><i class="fas fa-shopping-bag me-2 text-primary"></i>Gestión de Compras</h3>
                <p class="text-muted mb-0 small">Registre compras a proveedores y gestione el inventario</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
                <i class="fas fa-plus me-2"></i>Nueva Compra
            </button>
        </div>

        <!-- Estadísticas -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 stat-card primary">
                    <div class="card-body text-center">
                        <i class="fas fa-shopping-cart fa-2x text-primary mb-2"></i>
                        <h6 class="text-muted small mb-1">Total Compras</h6>
                        <h4 class="mb-0" id="statTotalCompras">0</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 stat-card success">
                    <div class="card-body text-center">
                        <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
                        <h6 class="text-muted small mb-1">Monto Total</h6>
                        <h5 class="mb-0 text-success" id="statMontoTotal">$0.00</h5>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 stat-card info">
                    <div class="card-body text-center">
                        <i class="fas fa-boxes fa-2x text-info mb-2"></i>
                        <h6 class="text-muted small mb-1">Prendas en Inventario</h6>
                        <h4 class="mb-0 text-info" id="statPrendasDisponibles">0</h4>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card shadow-sm border-0 stat-card primary">
                    <div class="card-body text-center">
                        <i class="fas fa-warehouse fa-2x text-primary mb-2"></i>
                        <h6 class="text-muted small mb-1">Valor Inventario</h6>
                        <h5 class="mb-0 text-primary" id="statValorInventario">$0.00</h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="row mb-4">
            <div class="col-12 col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0" 
                           id="searchInput" 
                           placeholder="Buscar por factura, proveedor...">
                </div>
            </div>
        </div>

        <!-- Tabla de Compras -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-4">N° Factura</th>
                                <th>Proveedor</th>
                                <th>Fecha</th>
                                <th class="text-end">Monto</th>
                                <th class="text-center">Prendas</th>
                                <th class="text-center" width="200">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseTableBody">
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 text-muted">Cargando compras...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: AGREGAR COMPRA -->
<div class="modal fade" id="addPurchaseModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form id="addPurchaseForm" autocomplete="off">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-shopping-bag me-2"></i>Registrar Nueva Compra
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="addPurchaseErrors" class="alert alert-danger d-none"></div>

                    <!-- Información General -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información de la Factura</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">
                                        N° Factura <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="facturaNumero" 
                                           name="factura_numero"
                                           maxlength="8" 
                                           pattern="\d{8}" 
                                           placeholder="12345678" 
                                           required>
                                    <div class="form-text">8 dígitos exactos</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">
                                        Fecha de Compra <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="fechaCompra" 
                                           name="fecha_compra"
                                           value="<?= date('Y-m-d') ?>" 
                                           max="<?= date('Y-m-d') ?>"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">
                                        N° Tracking <small class="text-muted">(opcional)</small>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="tracking" 
                                           name="tracking"
                                           placeholder="12345678" 
                                           maxlength="8" 
                                           pattern="\d{8}">
                                    <div class="form-text">Para envíos con seguimiento</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Proveedor -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-truck me-2"></i>Proveedor</h6>
                        </div>
                        <div class="card-body">
                            <label class="form-label fw-bold">
                                Buscar Proveedor <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="searchSupplier"
                                   placeholder="Escriba el nombre, empresa o RIF..." 
                                   autocomplete="off">
                            <input type="hidden" id="proveedorId" name="proveedor_rif" required>
                            <div id="supplierResults" 
                                 class="list-group mt-2 position-absolute w-100" 
                                 style="z-index: 1050; display: none;">
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Mínimo 2 caracteres
                            </div>
                        </div>
                    </div>

                    <!-- Cuenta por Pagar -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Cuenta por Pagar</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Nota:</strong> Esta compra generará automáticamente una cuenta por pagar a crédito.
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">
                                        Fecha de Vencimiento
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="fechaVencimiento" 
                                           name="fecha_vencimiento"
                                           value="<?= date('Y-m-d', strtotime('+30 days')) ?>"
                                           min="<?= date('Y-m-d') ?>">
                                    <div class="form-text">Por defecto: +30 días</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Observaciones</label>
                                    <textarea class="form-control" 
                                              name="observaciones" 
                                              rows="1" 
                                              maxlength="500"
                                              placeholder="Notas adicionales..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="card mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-box me-2"></i>Productos Comprados</h6>
                            <button type="button" 
                                    class="btn btn-sm btn-primary" 
                                    id="addPrendaBtn">
                                <i class="fas fa-plus me-1"></i>Agregar Producto
                            </button>
                        </div>
                        <div class="card-body">
                            <div id="prendasContainer">
                                <!-- Las prendas se agregarán aquí -->
                            </div>
                        </div>
                    </div>

                    <!-- Resumen -->
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Resumen de Compra</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <i class="fas fa-box-open me-2 text-primary"></i>
                                        <strong>Total de productos:</strong> 
                                        <span id="summaryTotalPrendas" class="badge bg-primary ms-2">0</span>
                                    </p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p class="mb-2">
                                        <i class="fas fa-dollar-sign me-2 text-success"></i>
                                        <strong>Monto total:</strong> 
                                        <span class="fs-5 text-success ms-2">$<span id="summaryMontoTotal">0.00</span></span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="montoTotal" name="monto_total" value="0.00">
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success" id="btnGuardar">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                        <span class="btn-text">
                            <i class="fas fa-save me-1"></i>Guardar Compra
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: EDITAR COMPRA -->
<div class="modal fade" id="editPurchaseModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <form id="editPurchaseForm" autocomplete="off">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-edit me-2"></i>Editar Compra
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="editPurchaseErrors" class="alert alert-danger d-none"></div>

                    <input type="hidden" id="editCompraId" name="compra_id">

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información de la Factura</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">N° Factura <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="editFacturaNumero" name="factura_numero" maxlength="8" pattern="\d{8}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Fecha de Compra <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="editFechaCompra" name="fecha_compra" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">N° Tracking</label>
                                    <input type="text" class="form-control" id="editTracking" name="tracking" maxlength="8" pattern="\d{8}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-truck me-2"></i>Proveedor</h6>
                        </div>
                        <div class="card-body">
                            <label class="form-label fw-bold">Buscar Proveedor <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="editSearchSupplier" placeholder="Escriba el nombre, empresa o RIF..." autocomplete="off">
                            <input type="hidden" id="editProveedorId" name="proveedor_rif" required>
                            <div id="editSupplierResults" class="list-group mt-2 position-absolute w-100" style="z-index: 1050; display: none;"></div>
                        </div>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body">
                            <label class="form-label fw-bold">Observaciones</label>
                            <textarea class="form-control" id="editObservaciones" name="observaciones" rows="2" maxlength="500"></textarea>
                        </div>
                    </div>

                    <!-- Productos (Solo lectura) -->
                    <div class="card mb-3">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-box me-2"></i>Productos (No editables)</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Las prendas no pueden modificarse. Para cambios, elimine la compra y cree una nueva.
                            </div>
                            <div id="editPrendasContainer"></div>
                        </div>
                    </div>

                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Resumen</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Total de productos:</strong> <span id="editSummaryTotalPrendas" class="badge bg-warning">0</span></p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p><strong>Monto total:</strong> <span class="fs-5 text-success">$<span id="editSummaryMontoTotal">0.00</span></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="editMontoTotal" name="monto_total" value="0.00">
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning" id="btnGuardarEdit">
                        <span class="spinner-border spinner-border-sm d-none me-2"></span>
                        <span class="btn-text"><i class="fas fa-save me-1"></i>Guardar Cambios</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: VER DETALLE -->
<div class="modal fade" id="viewPurchaseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Detalle de Compra</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewPurchaseContent">
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

<!-- TEMPLATE: PRENDA (SIN PRECIO DE VENTA) -->
<template id="prendaTemplate">
    <div class="prenda-row border rounded p-3 mb-3 bg-light">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0"><i class="fas fa-tshirt me-2"></i>Prenda #<span class="prenda-number">1</span></h6>
            <button type="button" class="btn btn-sm btn-danger remove-prenda">
                <i class="fas fa-trash"></i>
            </button>
        </div>
        
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Código <span class="text-danger">*</span></label>
                <input type="text" class="form-control prenda-codigo" placeholder="PRD001" maxlength="20" required>
            </div>
            <div class="col-md-8">
                <label class="form-label small fw-bold">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control prenda-nombre" placeholder="Pantalón Levi's 501" maxlength="150" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Categoría <span class="text-danger">*</span></label>
                <select class="form-select prenda-categoria" required>
                    <option value="">Seleccionar...</option>
                    <option value="Formal">Formal</option>
                    <option value="Casual">Casual</option>
                    <option value="Deportivo">Deportivo</option>
                    <option value="Invierno">Invierno</option>
                    <option value="Verano">Verano</option>
                    <option value="Fiesta">Fiesta</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Tipo <span class="text-danger">*</span></label>
                <select class="form-select prenda-tipo" required>
                    <option value="">Seleccionar...</option>
                    <option value="Vestido">Vestido</option>
                    <option value="Camisa">Camisa</option>
                    <option value="Pantalon">Pantalón</option>
                    <option value="Chaqueta">Chaqueta</option>
                    <option value="Blusa">Blusa</option>
                    <option value="Short">Short</option>
                    <option value="Falda">Falda</option>
                    <option value="Enterizo">Enterizo</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Precio Costo <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control prenda-costo" min="0.01" step="0.01" placeholder="0.00" required>
                </div>
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-bold">Descripción adicional (opcional)</label>
                <textarea class="form-control prenda-descripcion" rows="2" maxlength="500" placeholder="Talla, color, marca, etc."></textarea>
            </div>
        </div>
    </div>
</template>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/BarkiOS/public/assets/js/purchase-admin.js"></script>
<script src="/BarkiOS/public/assets/js/logout.js"></script>

</body>
</html>