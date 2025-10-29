<?php $pageTitle = "Proveedores | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<!-- Barra lateral de navegación -->
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?> 

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">Proveedores</h1>
        </div>
        <button class="btn btn-primary rounded-pill px-4 me-3" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
            <i class="fas fa-plus me-1"></i> Agregar proveedor
        </button>
        
        <!-- Mensajes de éxito/error dinámicos -->
        <!-- <div id="alertContainer" class="mt-3"></div> -->

        <!-- Tabla de Proveedores -->
        <div class="card mt-3">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="suppliersTable" class="table table-hover align-middle text-center">
                        <thead>
                            <tr>
                                <th>RIF</th>
                                <th>Tipo RIF</th>
                                <th>Nombre del Contacto</th>
                                <th>Empresa</th>
                                <th>Dirección</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="suppliersTableBody">
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

<!-- Modal para Añadir Proveedor -->
<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
 <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="addSupplierModalLabel">Añadir Nuevo Proveedor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addSupplierForm">
            <div class="modal-body">
                <div id="addSupplierErrors" class="alert alert-danger d-none"></div>
                <div class="mb-3">
                    <label class="form-label">Tipo de RIF</label>
                    <select class="form-select" name="tipo_rif" id="supplierTipoRif" required>
                        <option value="">Seleccione un tipo de rif</option>
                        <option value="J">J</option>
                        <option value="G">G</option>
                        <option value="C">C</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">RIF</label>
                    <input type="text" class="form-control"
                        name="proveedor_rif"
                        id="supplierRif"
                        placeholder="Ingrese el RIF (solo números)"
                        pattern="\d{9}"
                        maxlength="9"
                        required>
                    <div class="invalid-feedback">Por favor ingrese un RIF válido (9 dígitos)</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre del Contacto</label>
                    <input type="text" class="form-control"
                        name="nombre_contacto"
                        id="supplierNombreContacto"
                        placeholder="Ingrese nombre del contacto"
                        required>
                    <div class="invalid-feedback">Por favor ingrese un nombre válido (solo letras y espacios)</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre de la Empresa</label>
                    <input type="text" class="form-control"
                        name="nombre_empresa"
                        id="supplierNombreEmpresa"
                        placeholder="Ingrese nombre de la empresa"
                        required>
                    <div class="invalid-feedback">Por favor ingrese un nombre de empresa válido</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dirección</label>
                    <input type="text" class="form-control"
                        name="direccion"
                        id="supplierDireccion"
                        placeholder="Ingrese la dirección"
                        required>
                    <div class="invalid-feedback">Por favor ingrese una dirección válida</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="addSupplierBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Guardar</span>
                </button>
            </div>
        </form>
    </div>
 </div>
</div>

<!-- Modal para Editar Proveedor -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editSupplierModalLabel">Editar Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSupplierForm">
                <input type="hidden" name="id" id="editSupplierId">
                <input type="hidden" name="proveedor_rif" id="editSupplierRifHidden">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tipo de RIF</label>
                        <select class="form-select" name="tipo_rif" id="editSupplierTipoRif" required>
                            <option value="J">J</option>
                            <option value="G">G</option>
                            <option value="C">C</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">RIF</label>
                        <input type="text" class="form-control"
                            name="proveedor_rif"
                            id="editSupplierRif"
                            disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre del Contacto</label>
                        <input type="text" class="form-control"
                            name="nombre_contacto"
                            id="editSupplierNombreContacto"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre de la Empresa</label>
                        <input type="text" class="form-control"
                            name="nombre_empresa"
                            id="editSupplierNombreEmpresa"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control"
                            name="direccion"
                            id="editSupplierDireccion"
                            required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.3/dist/sweetalert2.all.min.js"></script>
<script src="/BarkiOS/public/assets/js/suppliers-admin.js"></script>
<script src="/BarkiOS/public/assets/js/logout.js"></script>

</body>
</html>