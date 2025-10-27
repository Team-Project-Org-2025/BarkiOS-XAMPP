<?php $pageTitle = "Compras | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?>

<style>
/* Scroll para toda la página */
html, body {
    height: 100%;
    overflow-y: auto;
}

.main-content {
    overflow-y: auto;
    max-height: calc(100vh - 80px);
    padding-bottom: 2rem;
}

/* Scroll para los modales */
.modal-dialog-scrollable .modal-body {
    max-height: calc(100vh - 200px);
    overflow-y: auto;
}

/* Estilos de prendas */
.prenda-row {
    transition: all 0.3s ease;
    border-left: 4px solid transparent;
}

.prenda-row:hover {
    border-left-color: #0d6efd;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}

/* Búsqueda de proveedores */
.search-loading {
    position: relative;
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

/* Filas de tabla */
.purchase-row {
    transition: all 0.2s ease;
}

.purchase-row:hover {
    background-color: #f8f9fa;
    transform: translateX(2px);
}

/* Margen de ganancia */
.margen-display {
    font-weight: 600;
}

/* Tarjetas estadísticas */
.stat-card {
    transition: transform 0.2s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

/* Scrollbar personalizado */
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

/* Container de prendas con scroll */
#prendasContainer, #editPrendasContainer {
    max-height: 500px;
    overflow-y: auto;
    padding-right: 10px;
}
</style>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <div>
                <h3 class="mb-1"><i class="fas fa-shopping-bag me-2 text-primary"></i>Gestión de Compras</h3>
                <p class="text-muted mb-0 small">Registre las compras a proveedores y agregue productos al inventario</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
                <i class="fas fa-plus me-2"></i>Nueva Compra
            </button>
        </div>

        <!-- Filtros y Búsqueda -->
        <div class="row mb-4">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" 
                           class="form-control border-start-0" 
                           id="searchInput" 
                           placeholder="Buscar por factura, proveedor, fecha...">
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
                                <th>Fecha / Monto</th>
                                <th class="text-center" width="200">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseTableBody">
                            <tr>
                                <td colspan="4" class="text-center py-5">
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

<!-- ============================================ -->
<!-- MODAL: AGREGAR COMPRA -->
<!-- ============================================ -->
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
                                    <div class="invalid-feedback">Debe tener exactamente 8 dígitos</div>
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
                                    <div class="invalid-feedback">Ingrese la fecha de compra</div>
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
                            <div class="search-loading">
                                <label class="form-label fw-bold">
                                    Buscar Proveedor <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="searchSupplier"
                                       placeholder="Escriba el nombre, empresa o RIF del proveedor..." 
                                       autocomplete="off">
                                <input type="hidden" id="proveedorId" name="proveedor_rif" required>
                                <div id="supplierResults" 
                                     class="list-group mt-2 position-absolute w-100" 
                                     style="z-index: 1050; display: none;">
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Empiece a escribir para buscar (mínimo 2 caracteres)
                                </div>
                                <div class="invalid-feedback">Debe seleccionar un proveedor</div>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <label class="form-label fw-bold">
                                Observaciones <small class="text-muted">(opcional)</small>
                            </label>
                            <textarea class="form-control" 
                                      name="observaciones" 
                                      rows="2" 
                                      maxlength="500"
                                      placeholder="Notas adicionales sobre esta compra..."></textarea>
                            <div class="form-text">Máximo 500 caracteres</div>
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
                                <!-- Las prendas se agregarán dinámicamente aquí -->
                            </div>
                        </div>
                    </div>

                    <!-- Resumen -->
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Resumen de Compra</h6>
                        </div>
                        <div class="card-body">
                            <div id="purchaseSummary">
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

<!-- ============================================ -->
<!-- MODAL: EDITAR COMPRA -->
<!-- ============================================ -->
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
                                           id="editFacturaNumero" 
                                           name="factura_numero"
                                           maxlength="8" 
                                           pattern="\d{8}" 
                                           required>
                                    <div class="invalid-feedback">Debe tener exactamente 8 dígitos</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">
                                        Fecha de Compra <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="editFechaCompra" 
                                           name="fecha_compra"
                                           required>
                                    <div class="invalid-feedback">Ingrese la fecha de compra</div>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-bold">
                                        N° Tracking <small class="text-muted">(opcional)</small>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="editTracking" 
                                           name="tracking"
                                           maxlength="8" 
                                           pattern="\d{8}">
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
                            <div class="search-loading">
                                <label class="form-label fw-bold">
                                    Buscar Proveedor <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="editSearchSupplier"
                                       placeholder="Escriba el nombre, empresa o RIF del proveedor..." 
                                       autocomplete="off">
                                <input type="hidden" id="editProveedorId" name="proveedor_rif" required>
                                <div id="editSupplierResults" 
                                     class="list-group mt-2 position-absolute w-100" 
                                     style="z-index: 1050; display: none;">
                                </div>
                                <div class="invalid-feedback">Debe seleccionar un proveedor</div>
                            </div>
                        </div>
                    </div>

                    <!-- Observaciones -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <label class="form-label fw-bold">
                                Observaciones <small class="text-muted">(opcional)</small>
                            </label>
                            <textarea class="form-control" 
                                      id="editObservaciones"
                                      name="observaciones" 
                                      rows="2" 
                                      maxlength="500"
                                      placeholder="Notas adicionales sobre esta compra..."></textarea>
                        </div>
                    </div>

                    <!-- Productos -->
                    <div class="card mb-3">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="fas fa-box me-2"></i>Productos Comprados</h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Las prendas de esta compra no pueden modificarse desde este módulo.
                            </div>
                            <div id="editPrendasContainer">
                                <!-- Las prendas se cargarán dinámicamente aquí -->
                            </div>
                        </div>
                    </div>

                    <!-- Resumen -->
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Resumen de Compra</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-2">
                                        <strong>Total de productos:</strong> 
                                        <span id="editSummaryTotalPrendas" class="badge bg-warning ms-2">0</span>
                                    </p>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <p class="mb-2">
                                        <strong>Monto total:</strong> 
                                        <span class="fs-5 text-success ms-2">$<span id="editSummaryMontoTotal">0.00</span></span>
                                    </p>
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
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                        <span class="btn-text">
                            <i class="fas fa-save me-1"></i>Guardar Cambios
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- TEMPLATE: PRENDA -->
<!-- ============================================ -->
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
                <label class="form-label small fw-bold">Código de Prenda <span class="text-danger">*</span></label>
                <input type="text" class="form-control prenda-codigo" 
                       placeholder="Ej: PRD001" maxlength="20" required>
                <div class="invalid-feedback">El código es obligatorio</div>
            </div>
        
            <div class="col-md-8">
                <label class="form-label small fw-bold">Nombre/Descripción <span class="text-danger">*</span></label>
                <input type="text" class="form-control prenda-nombre" 
                       placeholder="Ej: Pantalón Levi's 501 Talla 32" 
                       maxlength="150" required>
                <div class="invalid-feedback">El nombre es obligatorio (3-150 caracteres)</div>
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
                <div class="invalid-feedback">Seleccione una categoría</div>
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
                <div class="invalid-feedback">Seleccione un tipo</div>
            </div>
            
            <div class="col-md-4">
                <label class="form-label small fw-bold">Precio Costo <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control precio-input prenda-costo" 
                           min="0.01" step="0.01" placeholder="0.00" required>
                </div>
                <div class="invalid-feedback">Ingrese un precio válido</div>
            </div>
            
            <div class="col-md-6">
                <label class="form-label small fw-bold">Precio Venta <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control precio-input prenda-venta" 
                           min="0.01" step="0.01" placeholder="0.00" required>
                </div>
                <div class="invalid-feedback">El precio de venta debe ser mayor al costo</div>
            </div>
            
            <div class="col-md-6">
                <label class="form-label small fw-bold">Margen de Ganancia</label>
                <div class="input-group">
                    <input type="text" class="form-control margen-display" readonly 
                           value="$0.00 (0%)" style="background-color: #f8f9fa;">
                    <span class="input-group-text"><i class="fas fa-chart-line"></i></span>
                </div>
            </div>
            
            <div class="col-md-12">
                <label class="form-label small fw-bold">Descripción adicional (opcional)</label>
                <textarea class="form-control prenda-descripcion" 
                          rows="2" maxlength="500" 
                          placeholder="Talla, color, marca, etc."></textarea>
            </div>
        </div>
    </div>
</template>

<!-- ============================================ -->
<!-- SCRIPTS -->
<!-- ============================================ -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/BarkiOS/public/assets/js/purchase-admin.js"></script>

</body>
</html>