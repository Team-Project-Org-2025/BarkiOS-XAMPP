<?php $pageTitle = "Empleados | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?> 

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">Empleados</h1>
        </div>
        <button class="btn btn-primary rounded-pill px-4 me-3" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
            <i class="fas fa-plus me-1"></i> Agregar empleado
        </button>
        
        <!-- Tabla de Empleados -->
        <div class="card mt-3">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="employeesTable" class="table table-hover align-middle text-center">
                        <thead>
                            <tr>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Cargo</th>
                                <th>Fecha Ingreso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="employeesTableBody">
                            <tr>
                                <td colspan="6" class="text-center">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Añadir Empleado -->
<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
 <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Añadir Nuevo Empleado</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form id="addEmployeeForm">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Cédula</label>
                    <input type="text" class="form-control" 
                        name="cedula" 
                        placeholder="Ingrese la cédula" 
                        required>
                    <div class="invalid-feedback">Ingrese una cédula válida (7-10 dígitos)</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nombre Completo</label>
                    <input type="text" class="form-control" 
                        name="nombre" 
                        placeholder="Ingrese el nombre completo" 
                        required>
                    <div class="invalid-feedback">Ingrese un nombre válido</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" 
                        name="telefono" 
                        placeholder="04121234567" 
                        required>
                    <div class="invalid-feedback">Ingrese un teléfono válido (11 dígitos)</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Cargo</label>
                    <input type="text" class="form-control" 
                        name="cargo" 
                        placeholder="Ej: Vendedor, Supervisor, etc." 
                        value="Empleado">
                    <div class="invalid-feedback">Ingrese un cargo válido</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
 </div>
</div>

<!-- Modal para Editar Empleado -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editEmployeeForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cédula</label>
                        <input type="text" class="form-control" id="editEmployeeCedula" disabled>
                        <input type="hidden" name="cedula" id="editEmployeeCedulaHidden">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" 
                            name="nombre" 
                            id="editEmployeeNombre" 
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="text" class="form-control" 
                            name="telefono" 
                            id="editEmployeeTelefono" 
                            required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Cargo</label>
                        <input type="text" class="form-control" 
                            name="cargo" 
                            id="editEmployeeCargo" 
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
<script type="module" src="/BarkiOS/public/assets/js/admin/employees-admin.js"></script>
<script src="/BarkiOS/public/assets/js/admin/logout.js"></script>
<script src="/BarkiOS/public/assets/js/utils/skeleton.js"></script>
</body>
</html>