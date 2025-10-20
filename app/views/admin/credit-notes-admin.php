<!-- /app/views/admin/credit-notes-admin.php -->
<?php $pageTitle = "Notas de Crédito | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?> 

<div class="main-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">
                <i class="fas fa-file-invoice-dollar me-2"></i>Notas de Crédito
            </h1>
            <button class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                <i class="fas fa-plus me-1"></i> Generar Nota
            </button>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-file-alt fa-2x text-primary"></i>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Total Notas</h6>
                            <h3 class="mb-0" id="totalNotes">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-check-circle fa-2x text-success"></i>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Notas Activas</h6>
                            <h3 class="mb-0" id="activeNotes">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center">
                        <i class="fas fa-dollar-sign fa-2x text-info"></i>
                        <div class="ms-3">
                            <h6 class="text-muted mb-1">Monto Total</h6>
                            <h3 class="mb-0" id="totalAmount">$0.00</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabla de Notas de Crédito -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">Listado de Notas</h5>
                    </div>
                    <div class="col-auto">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" id="searchInput" class="form-control border-0 bg-light" placeholder="Buscar por cédula o motivo...">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center"># Nota</th>
                                <th>Cédula Cliente</th>
                                <th class="text-end">Monto</th>
                                <th>Motivo</th>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="notesTableBody">
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                    <p class="text-muted mt-2">Cargando notas de crédito...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Añadir Nota -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="addNoteForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNoteModalLabel">
                        <i class="fas fa-plus-circle me-2"></i>Generar Nueva Nota de Crédito
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cédula Cliente <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="cliente_cedula" id="add_cedula"
                               required maxlength="10" pattern="\d{7,10}" placeholder="Ej: 1234567890">
                        <div class="invalid-feedback">Ingrese una cédula válida (7-10 dígitos)</div>
                        <small class="text-muted">El cliente debe estar registrado en el sistema</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Monto Total ($) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" name="monto_total" id="add_monto"
                                   required min="0.01" placeholder="0.00">
                            <div class="invalid-feedback">Ingrese un monto válido mayor a 0</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="motivo" id="add_motivo" rows="3"
                                  required minlength="10" maxlength="500"
                                  placeholder="Describa el motivo de la nota de crédito"></textarea>
                        <div class="invalid-feedback">El motivo debe tener entre 10 y 500 caracteres</div>
                        <small class="text-muted"><span id="add_motivo_count">0</span>/500 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-1"></i>Guardar Nota
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Nota -->
<div class="modal fade" id="editNoteModal" tabindex="-1" aria-labelledby="editNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="editNoteForm">
                <input type="hidden" name="nota_id" id="edit_nota_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editNoteModalLabel">
                        <i class="fas fa-edit me-2"></i>Editar Nota de Crédito
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cédula Cliente</label>
                        <input type="text" class="form-control" name="cliente_cedula" id="edit_cedula"
                               required maxlength="10" pattern="\d{7,10}" placeholder="Ej: 1234567890">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Monto Total ($)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" name="monto_total" id="edit_monto"
                                   required min="0.01" placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Motivo</label>
                        <textarea class="form-control" name="motivo" id="edit_motivo" rows="3"
                                  required minlength="10" maxlength="500"></textarea>
                        <small class="text-muted"><span id="edit_motivo_count">0</span>/500 caracteres</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Actualizar Nota
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.3/dist/sweetalert2.all.min.js"></script>
<script src="/BarkiOS/public/assets/js/credit_note-admin.js"></script>


</body>
</html>
