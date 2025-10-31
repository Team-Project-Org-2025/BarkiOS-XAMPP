<?php require_once __DIR__ . '/../../core/AdminContext.php'; ?>
<?php $pageTitle = "Usuarios | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?> 

<div class="main-content">
  
  <div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="display-6 fw-bold text-dark mb-0">
        <i class="fas fa-users text-primary me-2"></i> Gestión de Usuarios
      </h1>
      <button class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="fas fa-plus me-2"></i> Nuevo Usuario
      </button>
    </div>
  <div class="d-flex align-items-center">
  </div>
</div>

    <!-- Tarjeta de tabla -->
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-light py-3">
        <h5 class="mb-0 fw-semibold text-secondary">
          <i class="fas fa-list me-2 text-primary"></i> Lista de Usuarios
        </h5>
      </div>
      <div class="card-body p-3">
        <div class="table-responsive">
        <table id="usersTable" class="table table-hover align-middle text-center mb-0">
          <thead class="table-primary">
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Email</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Agregar Usuario -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-semibold" id="addUserModalLabel">
          <i class="fas fa-user-plus me-2"></i> Registrar Nuevo Usuario
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="addUserForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nombre</label>
            <input type="text" class="form-control" name="nombre" placeholder="Nombre completo" required>
            <div class="invalid-feedback">Por favor ingrese un nombre válido.</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" class="form-control" name="email" placeholder="correo@ejemplo.com" required>
            <div class="invalid-feedback">El correo electrónico no tiene un formato válido.</div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Contraseña</label>
            <input type="password" class="form-control" name="password" placeholder="Mínimo 8 caracteres" required>
            <div class="invalid-feedback">La contraseña debe tener 8 caracteres, mayúsculas, minúsculas, números y símbolos.</div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary px-4">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Editar Usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title fw-semibold" id="editUserModalLabel">
          <i class="fas fa-user-edit me-2"></i> Editar Usuario
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editUserForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold">ID</label>
            <input type="text" class="form-control" id="editUserId" disabled>
            <input type="hidden" name="id" id="editUserIdHidden">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nombre</label>
            <input type="text" class="form-control" name="nombre" id="editUserName" placeholder="Nombre completo" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" class="form-control" name="email" id="editUserEmail" placeholder="correo@ejemplo.com" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Contraseña (opcional)</label>
            <input type="password" class="form-control" name="password" id="editUserPassword" placeholder="Solo si desea cambiarla">
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-warning text-dark fw-semibold px-4">Guardar Cambios</button>
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
<script type="module" src="/BarkiOS/public/assets/js/admin/users-admin.js"></script>
<script src="/BarkiOS/public/assets/js/admin/logout.js"></script>

</body>
</html>
