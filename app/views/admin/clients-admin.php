<?php $pageTitle = "Clientes | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<!-- Barra lateral de navegación -->
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?> 


<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">Clientes</h1>
        </div>
        <button class="btn btn-primary rounded-pill px-4 me-3" data-bs-toggle="modal" data-bs-target="#addClientModal">
            <i class="fas fa-plus me-1"></i> Agregar cliente
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
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Dirección</th>
                                <th>Teléfono</th>
                                <th>Membresía</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="clientesTableBody">
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

<!-- Modal para Añadir Cliente -->
<div class="modal fade" id="addClientModal" tabindex="-1" aria-labelledby="addClientModalLabel" aria-hidden="true">
 <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="addClientModalLabel">Añadir Nuevo Cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addClientForm">
            <div class="modal-body">
                <div id="addClientErrors" class="alert alert-danger d-none"></div>
                <div class="mb-3">
                    <label class="form-label">Cédula</label>
                    <input type="text" class="form-control" 
                        id="clientCedula"
                        name="cedula" 
                        placeholder="Ingrese la cédula del cliente" 
                        inputmode="numeric"
                        maxlength="9"
                        minlength="7"
                        pattern="^\d{7,9}$"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,9);"
                        required>
                    <div class="invalid-feedback">Por favor ingrese una cédula válida (7 a 9 dígitos)</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" 
                        id="clientNombre"
                        name="nombre" 
                        placeholder="Ingrese nombre del cliente" 
                        maxlength="40"
                        pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,40}$"
                        oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').slice(0,40);"
                        required>
                    <div class="invalid-feedback">Por favor ingrese un nombre válido (solo letras y espacios)</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dirección</label>
                    <input type="text" class="form-control"
                        id="clientDireccion"
                        name="direccion"
                        placeholder="Ingrese la dirección"
                        maxlength="100"
                        required>
                    <div class="invalid-feedback">Por favor ingrese una dirección válida</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control"
                        id="clientTelefono"
                        name="telefono"
                        placeholder="Ingrese el teléfono"
                        maxlength="11"
                        pattern="^\d{11}$"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11);"
                        required>
                    <div class="invalid-feedback">Por favor ingrese un número de teléfono válido</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Membresía</label>
                    <select class="form-select" id="clientMembresia" name="membresia" required>
                        <option value="">Seleccione una membresía</option>
                        <option value="Regular">Regular</option>
                        <option value="VIP">VIP</option>
                    </select>
                    <div class="invalid-feedback">Por favor seleccione una membresía</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="addClientBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                    <span class="btn-text">Guardar</span>
                </button>
            </div>
        </form>
    </div>
 </div>
</div>

<!-- Modal para Editar Cliente -->
<div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editClientModalLabel">Editar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editClientForm">
                <div class="modal-body">
                    <div id="editClientErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                </div>
                    <div class="mb-3">
                    <label class="form-label">Cédula</label>
                    <input type="text" class="form-control" 
                        id="editClientCedula"
                        name="cedula" 
                        placeholder="Ingrese la cédula del cliente" 
                        inputmode="numeric"
                        maxlength="9"
                        minlength="7"
                        pattern="^\d{7,9}$"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,9);"
                        readonly
                        disabled>
                    <input type="hidden" id="editClientCedulaHidden" name="cedula" value="">
                    <div class="invalid-feedback">Por favor ingrese una cédula válida (7 a 9 dígitos)</div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" 
                            name="nombre" 
                            id="editClientNombre"
                            placeholder="Ingrese nombre del cliente" 
                            maxlength="40"
                            pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,40}$"
                            oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').slice(0,40);"
                            required>
                        <div class="invalid-feedback">Por favor ingrese un nombre válido (solo letras y espacios)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control"
                            id="editClientDireccion"
                            name="direccion"
                            placeholder="Ingrese la dirección"
                            maxlength="100"
                            required>
                        <div class="invalid-feedback">Por favor ingrese una dirección válida</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control"
                            id="editClientTelefono"
                            name="telefono"
                            placeholder="Ingrese el teléfono"
                            maxlength="11"
                            pattern="^\d{11}$"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,11);"
                            required>
                        <div class="invalid-feedback">Por favor ingrese un número de teléfono válido</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Membresía</label>
                        <select class="form-select" id="editClientMembresia" name="membresia" required>
                            <option value="">Seleccione una membresía</option>
                            <option value="Regular">Regular</option>
                            <option value="VIP">VIP</option>
                        </select>
                        <div class="invalid-feedback">Por favor seleccione una membresía</div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 para alertas bonitas -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="/BarkiOS/public/assets/js/clients-admin.js"></script>
</body>
</html>