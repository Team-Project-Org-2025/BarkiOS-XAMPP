<!-- /app/views/admin/credit-notes-admin.php -->
<?php $pageTitle = "Notas de Crédito | Garage Barki"; ?>
<?php require_once ROOT_PATH . 'app/views/admin/partials/header-admin.php'; ?>

<!-- Barra lateral de navegación -->
<?= require_once ROOT_PATH . 'app/views/admin/partials/navbar-admin.php'; ?> 
<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">Notas de Crédito</h1>
        </div>
        <button class="btn btn-success rounded-pill px-4 me-3" data-bs-toggle="modal" data-bs-target="#addNoteModal">
            <i class="fas fa-plus me-1"></i> Generar Nota
        </button>
        
        <!-- Tabla de Notas de Crédito -->
        <div class="card mt-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-hover text-center">
                        <thead>
                            <tr>
                                <th># Nota</th>
                                <th>Cédula Cliente</th>
                                <th>Monto</th>
                                <th>Motivo</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="notesTableBody">
                            <tr><td colspan="7" class="text-center">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Añadir Nota de Crédito (Simplificado) -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNoteModalLabel">Generar Nueva Nota de Crédito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addNoteForm" action="/BarkiOS/creditnote?action=add" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cédula Cliente</label>
                        <input type="text" class="form-control" name="cliente_cedula" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Monto Total ($)</label>
                        <input type="number" step="0.01" class="form-control" name="monto_total" required min="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motivo</label>
                        <textarea class="form-control" name="motivo" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar Nota</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// El JS para cargar la tabla y manejar el AJAX se implementaría aquí.
// Usando el patrón de tu módulo de productos, llamarías a:
// url: '/BarkiOS/creditnote?action=get_notes'
</script>

<?php require_once ROOT_PATH . 'app/views/admin/partials/footer-admin.php'; ?>