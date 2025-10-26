<?php $pageTitle = "Compras | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<!-- Barra lateral de navegación -->
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?>


<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">Compras</h1>
        </div>
        <button class="btn btn-primary rounded-pill px-4 me-3" data-bs-toggle="modal" data-bs-target="#addPurchaseModal">
            <i class="fas fa-plus me-1"></i> Nueva Compra
        </button>

        <!-- Filtros -->
        <div class="row mt-3 mb-3">
            <div class="col-md-6">
                <input type="text" class="form-control" id="searchInput" placeholder="Buscar por factura, proveedor...">
            </div>
        </div>

        <!-- Tabla de Compras -->
        <div class="card mt-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-hover text-center">
                        <thead>
                            <tr>
                                <th>N° Factura</th>
                                <th>Proveedor</th>
                                <th>Fecha Compra</th>
                                <th>Descargar PDF</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseTableBody">
                            <tr>
                                <td colspan="4" class="text-center">
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

<!-- Modal para Agregar Compra -->
<div class="modal fade" id="addPurchaseModal" tabindex="-1" aria-labelledby="addPurchaseModalLabel" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPurchaseModalLabel">Nueva Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPurchaseForm">
                <div class="modal-body">
                    <div id="addPurchaseErrors" class="alert alert-danger d-none"></div>

                    <!-- Información General -->
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Número de Factura -->
                            <div class="mb-3">
                                <label class="form-label">N° Factura <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="facturaNumero" name="factura_numero"
                                       maxlength="8" pattern="\d{8}" placeholder="12345678" required>
                                <div class="form-text">Debe tener exactamente 8 dígitos</div>
                                <div class="invalid-feedback">Por favor ingrese un número de factura de 8 dígitos</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Fecha de Compra -->
                            <div class="mb-3">
                                <label class="form-label">Fecha de Compra <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fechaCompra" name="fecha_compra"
                                       value="<?= date('Y-m-d') ?>" required>
                                <div class="invalid-feedback">Por favor ingrese la fecha de compra</div>
                            </div>
                        </div>
                    </div>

                    <!-- Búsqueda de Proveedor -->
                    <div class="mb-3 search-loading">
                        <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="searchSupplier"
                               placeholder="Buscar proveedor (nombre o RIF)..." autocomplete="off" data-min-chars="2">
                        <input type="hidden" id="proveedorId" name="proveedor_rif" required>
                        <div id="supplierResults" class="list-group mt-1 position-absolute w-100" style="z-index: 1050; max-height: 200px; overflow-y: auto;"></div>
                        <div class="invalid-feedback">Por favor seleccione un proveedor</div>
                    </div>

                    <!-- Tracking -->
                    <div class="mb-3">
                        <label class="form-label">N° Tracking</label>
                        <input type="text" class="form-control" id="tracking" name="tracking"
                               placeholder="12345678" maxlength="8" pattern="\d{8}">
                    </div>

                    <!-- Monto Total (oculto, solo se muestra en el resumen) -->
                    <input type="hidden" id="montoTotal" name="monto_total" value="0.00">

                    <!-- Resumen -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-2"></i>Resumen de Compra</h6>
                        <div id="purchaseSummary">
                            <p class="mb-1">Total de prendas: <span id="summaryTotalPrendas">0</span></p>
                            <p class="mb-0">Monto total: $<span id="summaryMontoTotal">0.00</span></p>
                        </div>
                    </div>

                    <!-- Prendas Compradas -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Prendas Compradas</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addPrendaBtn">
                                <i class="fas fa-plus me-1"></i>Agregar Prenda
                            </button>
                        </div>

                        <div id="prendasContainer">
                            <!-- Las prendas se agregarán dinámicamente aquí -->
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                        <span class="btn-text">Guardar Compra</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Compra -->
<div class="modal fade" id="editPurchaseModal" tabindex="-1" aria-labelledby="editPurchaseModalLabel" aria-hidden="true" style="z-index: 1055;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPurchaseModalLabel">Editar Compra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPurchaseForm">
                <div class="modal-body">
                    <div id="editPurchaseErrors" class="alert alert-danger d-none"></div>

                    <input type="hidden" id="editCompraId" name="compra_id">

                    <!-- Información General -->
                    <div class="row">
                        <div class="col-md-6">
                            <!-- Número de Factura -->
                            <div class="mb-3">
                                <label class="form-label">N° Factura <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="editFacturaNumero" name="factura_numero"
                                       maxlength="8" pattern="\d{8}" required>
                                <div class="form-text">Debe tener exactamente 8 dígitos</div>
                                <div class="invalid-feedback">Por favor ingrese un número de factura de 8 dígitos</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Fecha de Compra -->
                            <div class="mb-3">
                                <label class="form-label">Fecha de Compra <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="editFechaCompra" name="fecha_compra" required>
                                <div class="invalid-feedback">Por favor ingrese la fecha de compra</div>
                            </div>
                        </div>
                    </div>

                    <!-- Búsqueda de Proveedor -->
                    <div class="mb-3 search-loading">
                        <label class="form-label">Proveedor <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="editSearchSupplier"
                               placeholder="Buscar proveedor (nombre o RIF)..." autocomplete="off" data-min-chars="2">
                        <input type="hidden" id="editProveedorId" name="proveedor_rif" required>
                        <div id="editSupplierResults" class="list-group mt-1 position-absolute w-100" style="z-index: 1050; max-height: 200px; overflow-y: auto;"></div>
                        <div class="invalid-feedback">Por favor seleccione un proveedor</div>
                    </div>

                    <!-- Tracking -->
                    <div class="mb-3">
                        <label class="form-label">N° Tracking</label>
                        <input type="text" class="form-control" id="editTracking" name="tracking"
                               placeholder="12345678" maxlength="8" pattern="\d{8}">
                    </div>

                    <!-- Monto Total (oculto, solo se muestra en el resumen) -->
                    <input type="hidden" id="editMontoTotal" name="monto_total" value="0.00">

                    <!-- Prendas Compradas -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <label class="form-label mb-0">Prendas Compradas</label>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="editAddPrendaBtn">
                                <i class="fas fa-plus me-1"></i>Agregar Prenda
                            </button>
                        </div>

                        <div id="editPrendasContainer">
                            <!-- Las prendas se cargarán dinámicamente aquí -->
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarEdit">
                        <span class="spinner-border spinner-border-sm d-none me-2" role="status"></span>
                        <span class="btn-text">Guardar Cambios</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Template para prenda -->
<template id="prendaTemplate">
    <div class="prenda-row border rounded p-3 mb-2">
        <div class="row">
            <div class="col-md-5">
                <label class="form-label">Nombre/Descripción</label>
                <input type="text" class="form-control" name="prendas[INDEX][producto_nombre]"
                       placeholder="Ej: Pantalón Levi's 501 Talla 32" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <select class="form-control" name="prendas[INDEX][categoria]" required>
                    <option value="">Seleccionar...</option>
                    <option value="Pantalones">Pantalones</option>
                    <option value="Camisas">Camisas</option>
                    <option value="Camisetas">Camisetas</option>
                    <option value="Zapatos">Zapatos</option>
                    <option value="Accesorios">Accesorios</option>
                    <option value="Ropa Interior">Ropa Interior</option>
                    <option value="Otros">Otros</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Precio Costo</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" class="form-control" name="prendas[INDEX][precio_costo]"
                           min="0" step="0.01" placeholder="0.00" required>
                </div>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger btn-sm remove-prenda">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 para alertas bonitas -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="/BarkiOS/public/assets/js/purchase-admin.js"></script>