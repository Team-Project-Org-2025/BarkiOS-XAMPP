<?php $pageTitle = "Cuentas por Cobrar | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<!-- Barra lateral de navegación -->
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?> 


<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">Cuentas por Cobrar</h1>
        </div>
        <button class="btn btn-primary rounded-pill px-4 me-3" data-bs-toggle="modal" data-bs-target="#addAccountModal">
            <i class="fas fa-plus me-1"></i> Agregar cuenta
        </button>
        
        <!-- Mensajes de éxito/error dinámicos (ya no se usa, todo es pop-up con SweetAlert2) -->
        <!-- <div id="alertContainer" class="mt-3"></div> -->

        <!-- Tabla de Clientes -->
        <div class="card mt-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-hover text-center">
                        <thead>
                            <tr>
                                <th>N° Factura</th>
                                <th>Cliente</th>
                                <th>Emisión</th>
                                <th>Monto</th>
                                <th>Vencimiento</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="accountTableBody">
                            <tr>
                                <td colspan="6" class="text-center">
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

<style>
    /* Estilos para búsqueda de clientes VIP */
    #clientResults {
        display: none;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
    
    #clientResults .list-group-item {
        cursor: pointer;
        border: none;
        border-bottom: 1px solid #f0f0f0;
        padding: 0.75rem 1rem;
        transition: background-color 0.2s ease;
    }
    
    #clientResults .list-group-item:hover {
        background-color: #f8f9fa;
    }
    
    #clientResults .list-group-item:last-child {
        border-bottom: none;
    }
    
    #clientResults .list-group-item-action:active {
        background-color: #e9ecef;
    }
    
    /* Estilo para el input cuando está buscando */
    #searchClient.searching {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2'%3E%3Ccircle cx='12' cy='12' r='10'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 1rem;
    }
</style>

<!-- Modal para Agregar Cuenta por Cobrar -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAccountModalLabel">Nueva Cuenta por Cobrar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addAccountForm">
                <div class="modal-body">
                    <div id="addAccountErrors" class="alert alert-danger d-none"></div>
                    
                    <!-- Número de Factura -->
                    <div class="mb-3">
                        <label class="form-label">N° Factura <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" 
                               id="facturaNumero" name="factura_numero" 
                               pattern="[0-9]{8}"
                               maxlength="8"
                               minlength="8"
                               required>
                        <div class="invalid-feedback">El número de factura debe tener exactamente 8 dígitos</div>
                        <small class="form-text text-muted">Ingrese exactamente 8 dígitos numéricos</small>
                    </div>

                    <!-- Búsqueda de Cliente VIP -->
                    <div class="mb-3 position-relative">
                        <label class="form-label">Cliente VIP <span class="text-danger">*</span></label>
                        <input  type="text" 
                                class="form-control" 
                                id="searchClient" 
                                placeholder="Buscar cliente VIP por nombre..." 
                                autocomplete="off">
                        <input type="hidden" id="clienteId" name="cliente_ced" required>
                        <div id="clientResults" class="list-group mt-1 position-absolute w-100 shadow-sm" style="z-index: 1050; max-height: 300px; overflow-y: auto;"></div>
                        <div class="invalid-feedback">Por favor seleccione un cliente VIP de la lista</div>
                        <small class="form-text text-muted">Escriba el nombre del cliente VIP (ej: "Fabrizio")</small>
                    </div>

                    <!-- Fecha de Emisión -->
                    <div class="mb-3">
                        <label class="form-label">Fecha de Emisión <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" 
                               id="fechaEmision" name="fecha_emision" 
                               value="<?= date('Y-m-d') ?>" 
                               required>
                        <div class="invalid-feedback">Por ingrese la fecha de emisión</div>
                    </div>

                    <!-- Fecha de Vencimiento -->
                    <div class="mb-3">
                        <label class="form-label">Fecha de Vencimiento <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" 
                               id="fechaVencimiento" name="fecha_vencimiento" 
                               min="<?= date('Y-m-d') ?>" 
                               required>
                        <div class="invalid-feedback">Por favor ingrese la fecha de vencimiento</div>
                    </div>

                    <!-- Monto Total -->
                    <div class="mb-3">
                        <label class="form-label">Monto Total <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" 
                                    id="montoTotal" name="monto_total" 
                                    step="0.01" min="0" 
                                    required>
                        </div>
                        <div class="invalid-feedback">Por favor ingrese un monto válido</div>
                    </div>

                    <!-- Estado (oculto) -->
                    <input type="hidden" name="estado" value="Pendiente">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="btnGuardar">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <span class="btn-text">Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 para alertas bonitas -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="/BarkiOS/public/assets/js/accountsReceivable.js"></script>
</body>
</html>