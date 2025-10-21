<?php $pageTitle = "Usuarios | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<!-- Barra lateral de navegación -->
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?> 

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">Gestión de Usuarios</h1>
        </div>
        <button class="btn btn-primary rounded-pill px-4 me-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fas fa-plus me-1"></i> Agregar Usuario
        </button>
        
        <!-- Tabla de Usuarios -->
        <div class="card mt-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-hover text-center">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <tr>
                                <td colspan="4" class="text-center py-3">
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

<!-- Modal para Añadir Usuario -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
 <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="addUserModalLabel">Registrar Nuevo Usuario</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addUserForm">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nombre</label>
                    <input type="text" class="form-control" name="nombre" placeholder="Nombre completo" required>
                    <div class="invalid-feedback">Por favor ingrese un nombre válido.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" placeholder="correo@ejemplo.com" required>
                    <div class="invalid-feedback">Por favor ingrese un email válido.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" class="form-control" name="password" placeholder="Mínimo 6 caracteres" required>
                    <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Usuario</button>
            </div>
        </form>
    </div>
 </div>
</div>

<!-- Modal para Editar Usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Editar Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <!-- ID OCULTO -->
                    <div class="mb-3">
                        <label class="form-label">ID</label>
                        <input type="text" class="form-control" id="editUserId" disabled>
                        <input type="hidden" name="id" id="editUserIdHidden">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" name="nombre" id="editUserName" placeholder="Nombre completo" required>
                        <div class="invalid-feedback">Por favor ingrese un nombre válido.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="editUserEmail" placeholder="correo@ejemplo.com" required>
                        <div class="invalid-feedback">Por favor ingrese un email válido.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contraseña (Dejar vacío para no cambiar)</label>
                        <input type="password" class="form-control" name="password" id="editUserPassword" placeholder="Solo ingrese si desea cambiarla">
                        <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.3/dist/sweetalert2.all.min.js"></script>

<!-- Carga el JS del controlador de Usuarios -->
<script src="/BarkiOS/public/assets/js/users-admin.js"></script>
