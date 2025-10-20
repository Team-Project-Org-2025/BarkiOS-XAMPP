// ============================================================================
// CREDIT NOTES ADMIN - JAVASCRIPT MVC PATTERN
// ============================================================================

// 1. INITIALIZATION & DEPENDENCIES
$(document).ready(function() {
    // Cache de elementos DOM
    const $notesTableBody = $('#notesTableBody');
    const $addNoteForm = $('#addNoteForm');
    const $editNoteForm = $('#editNoteForm');
    const $addNoteModal = $('#addNoteModal');
    const $editNoteModal = $('#editNoteModal');
    const $searchInput = $('#searchInput');
    
    // Cargar notas al inicio
    loadCreditNotes();
    
    // Event Handlers
    $addNoteForm.on('submit', handleAddNote);
    $editNoteForm.on('submit', handleEditNote);
    $searchInput.on('keyup', handleSearch);
    
    // Reset forms al cerrar modales
    $addNoteModal.on('hidden.bs.modal', function() {
        resetForm($addNoteForm);
    });
    
    $editNoteModal.on('hidden.bs.modal', function() {
        resetForm($editNoteForm);
    });
    
    // Aplicar validación en tiempo real
    aplicarValidacionEnTiempoReal($addNoteForm);
    aplicarValidacionEnTiempoReal($editNoteForm);
    
    // Contador de caracteres para motivo
    $('#add_motivo').on('input', function() {
        updateCharCounter($(this), '#add_motivo_count');
    });
    
    $('#edit_motivo').on('input', function() {
        updateCharCounter($(this), '#edit_motivo_count');
    });
});

// ============================================================================
// 2. UTILITIES (Helper Functions)
// ============================================================================

/**
 * Escapa HTML para prevenir XSS
 */
const escapeHtml = (text) => {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
};

/**
 * Muestra alertas con SweetAlert2
 */
const showAlert = (type, title, text) => {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
    
    Toast.fire({
        icon: type,
        title: title,
        text: text
    });
};

/**
 * Formatea fecha a formato legible
 */
const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('es-ES', options);
};

/**
 * Formatea montos a formato moneda
 */
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('es-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
    }).format(amount);
};

/**
 * Actualiza contador de caracteres
 */
const updateCharCounter = ($textarea, counterId) => {
    const length = $textarea.val().length;
    $(counterId).text(length);
};

/**
 * Reset de formulario
 */
const resetForm = ($form) => {
    $form[0].reset();
    $form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
};

// ============================================================================
// 3. VALIDATION
// ============================================================================

/**
 * Valida datos de nota de crédito
 */
const validarNotaCredito = (data) => {
    const errors = [];
    
    // Validar cédula
    if (!data.cliente_cedula || !/^\d{7,10}$/.test(data.cliente_cedula)) {
        errors.push('La cédula debe tener entre 7 y 10 dígitos');
    }
    
    // Validar monto
    const monto = parseFloat(data.monto_total);
    if (isNaN(monto) || monto <= 0) {
        errors.push('El monto debe ser mayor a cero');
    }
    
    // Validar motivo
    if (!data.motivo || data.motivo.trim().length < 10) {
        errors.push('El motivo debe tener al menos 10 caracteres');
    }
    
    return {
        isValid: errors.length === 0,
        errors: errors
    };
};

/**
 * Aplicar validación en tiempo real a formulario
 */
function aplicarValidacionEnTiempoReal($form) {
    // Validar cédula
    $form.find('input[name="cliente_cedula"]').on('input', function() {
        const $input = $(this);
        const value = $input.val();
        const isValid = /^\d{7,10}$/.test(value);
        
        $input.removeClass('is-valid is-invalid');
        if (value.length > 0) {
            $input.addClass(isValid ? 'is-valid' : 'is-invalid');
        }
    });
    
    // Validar monto
    $form.find('input[name="monto_total"]').on('input', function() {
        const $input = $(this);
        const value = parseFloat($input.val());
        const isValid = !isNaN(value) && value > 0;
        
        $input.removeClass('is-valid is-invalid');
        if ($input.val().length > 0) {
            $input.addClass(isValid ? 'is-valid' : 'is-invalid');
        }
    });
    
    // Validar motivo
    $form.find('textarea[name="motivo"]').on('input', function() {
        const $input = $(this);
        const value = $input.val().trim();
        const isValid = value.length >= 10 && value.length <= 500;
        
        $input.removeClass('is-valid is-invalid');
        if (value.length > 0) {
            $input.addClass(isValid ? 'is-valid' : 'is-invalid');
        }
    });
}

// ============================================================================
// 4. VIEW MANAGEMENT (Rendering Functions)
// ============================================================================

/**
 * Carga las notas de crédito desde el servidor
 */
function loadCreditNotes() {
    $.ajax({
        url: window.location.pathname + '?action=get_notes',
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                renderNotesTable(response.notes);
                updateStatistics(response.notes);
            } else {
                showAlert('error', 'Error', response.message || 'Error al cargar las notas');
                renderEmptyState('Error al cargar las notas de crédito');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error AJAX:', error);
            showAlert('error', 'Error', 'No se pudo conectar con el servidor');
            renderEmptyState('Error de conexión con el servidor');
        }
    });
}

/**
 * Renderiza la tabla de notas
 */
function renderNotesTable(notes) {
    const $tbody = $('#notesTableBody');
    
    if (!notes || notes.length === 0) {
        renderEmptyState('No hay notas de crédito registradas');
        return;
    }
    
    let html = '';
    notes.forEach(note => {
        html += createNoteRow(note);
    });
    
    $tbody.html(html);
    
    // Agregar event listeners a botones de acción
    attachActionButtons();
}

/**
 * Crea una fila de la tabla para una nota
 */
function createNoteRow(note) {
    const estadoBadge = getEstadoBadge(note.estado);
    const actions = note.estado === 'ACTIVA' ? getActionButtons(note) : getDisabledActions();
    
    return `
        <tr data-note-id="${escapeHtml(note.nota_id)}">
            <td class="text-center fw-bold">#${escapeHtml(note.nota_id)}</td>
            <td>${escapeHtml(note.cliente_cedula)}</td>
            <td class="text-end fw-bold text-success">${formatCurrency(note.monto_total)}</td>
            <td>
                <span class="d-inline-block text-truncate" style="max-width: 250px;" 
                      title="${escapeHtml(note.motivo)}">
                    ${escapeHtml(note.motivo)}
                </span>
            </td>
            <td class="text-center">${formatDate(note.fecha)}</td>
            <td class="text-center">${estadoBadge}</td>
            <td class="text-center">${actions}</td>
        </tr>
    `;
}

/**
 * Obtiene el badge de estado
 */
function getEstadoBadge(estado) {
    const badges = {
        'ACTIVA': '<span class="badge bg-success">Activa</span>',
        'USADA': '<span class="badge bg-info">Usada</span>',
        'CANCELADA': '<span class="badge bg-secondary">Cancelada</span>'
    };
    return badges[estado] || '<span class="badge bg-secondary">Desconocido</span>';
}

/**
 * Obtiene los botones de acción
 */
function getActionButtons(note) {
    return `
        <div class="btn-group btn-group-sm" role="group">
            <button class="btn btn-outline-primary btn-edit" 
                    data-note-id="${escapeHtml(note.nota_id)}"
                    title="Editar">
                <i class="fas fa-edit"></i>
            </button>
            <button class="btn btn-outline-danger btn-delete" 
                    data-note-id="${escapeHtml(note.nota_id)}"
                    data-cedula="${escapeHtml(note.cliente_cedula)}"
                    title="Cancelar">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `;
}

/**
 * Obtiene botones deshabilitados
 */
function getDisabledActions() {
    return '<span class="text-muted">Sin acciones</span>';
}

/**
 * Renderiza estado vacío
 */
function renderEmptyState(message) {
    $('#notesTableBody').html(`
        <tr>
            <td colspan="7" class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted">${escapeHtml(message)}</p>
            </td>
        </tr>
    `);
}

/**
 * Actualiza las estadísticas
 */
function updateStatistics(notes) {
    const total = notes.length;
    const activas = notes.filter(n => n.estado === 'ACTIVA').length;
    const montoTotal = notes
        .filter(n => n.estado === 'ACTIVA')
        .reduce((sum, n) => sum + parseFloat(n.monto_total), 0);
    
    $('#totalNotes').text(total);
    $('#activeNotes').text(activas);
    $('#totalAmount').text(formatCurrency(montoTotal));
}

/**
 * Adjunta event listeners a botones de acción
 */
function attachActionButtons() {
    $('.btn-edit').off('click').on('click', function() {
        const noteId = $(this).data('note-id');
        loadNoteForEdit(noteId);
    });
    
    $('.btn-delete').off('click').on('click', function() {
        const noteId = $(this).data('note-id');
        const cedula = $(this).data('cedula');
        confirmDelete(noteId, cedula);
    });
}

// ============================================================================
// 5. CONTROLLER FUNCTIONS (Action Handlers)
// ============================================================================

/**
 * Maneja la adición de nueva nota
 */
function handleAddNote(e) {
    e.preventDefault();
    
    const $form = $(this);
    const formData = $form.serialize();
    const data = Object.fromEntries(new URLSearchParams(formData));
    
    // Validar datos
    const validation = validarNotaCredito(data);
    if (!validation.isValid) {
        showAlert('error', 'Validación', validation.errors.join('<br>'));
        return;
    }
    
    // Mostrar loading
    const $submitBtn = $form.find('button[type="submit"]');
    const originalText = $submitBtn.html();
    $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');
    
    $.ajax({
        url: '/BarkiOS/creditnote?action=add_ajax',
        method: 'POST',
        data: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', 'Éxito', response.message);
                $('#addNoteModal').modal('hide');
                loadCreditNotes(); // Recargar tabla
            } else {
                showAlert('error', 'Error', response.message);
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const message = response && response.message ? response.message : 'Error al guardar la nota';
            showAlert('error', 'Error', message);
        },
        complete: function() {
            $submitBtn.prop('disabled', false).html(originalText);
        }
    });
}

/**
 * Carga una nota para editar
 */
function loadNoteForEdit(noteId) {
    $.ajax({
        url: '/BarkiOS/creditnote?action=get_by_id&id=' + noteId,
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.note) {
                populateEditForm(response.note);
                $('#editNoteModal').modal('show');
            } else {
                showAlert('error', 'Error', 'No se pudo cargar la nota');
            }
        },
        error: function() {
            showAlert('error', 'Error', 'Error al cargar la nota para editar');
        }
    });
}

/**
 * Popula el formulario de edición
 */
function populateEditForm(note) {
    $('#edit_nota_id').val(note.nota_id);
    $('#edit_cedula').val(note.cliente_cedula);
    $('#edit_monto').val(note.monto_total);
    $('#edit_motivo').val(note.motivo);
    
    // Actualizar contador
    updateCharCounter($('#edit_motivo'), '#edit_motivo_count');
    
    // Limpiar validaciones previas
    $('#editNoteForm').find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
}

/**
 * Maneja la edición de nota
 */
function handleEditNote(e) {
    e.preventDefault();
    
    const $form = $(this);
    const formData = $form.serialize();
    const data = Object.fromEntries(new URLSearchParams(formData));
    
    // Validar datos
    const validation = validarNotaCredito(data);
    if (!validation.isValid) {
        showAlert('error', 'Validación', validation.errors.join('<br>'));
        return;
    }
    
    // Mostrar loading
    const $submitBtn = $form.find('button[type="submit"]');
    const originalText = $submitBtn.html();
    $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...');
    
    $.ajax({
        url: '/BarkiOS/creditnote?action=edit_ajax',
        method: 'POST',
        data: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', 'Éxito', response.message);
                $('#editNoteModal').modal('hide');
                loadCreditNotes(); // Recargar tabla
            } else {
                showAlert('error', 'Error', response.message);
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const message = response && response.message ? response.message : 'Error al actualizar la nota';
            showAlert('error', 'Error', message);
        },
        complete: function() {
            $submitBtn.prop('disabled', false).html(originalText);
        }
    });
}

/**
 * Confirma la eliminación de una nota
 */
function confirmDelete(noteId, cedula) {
    Swal.fire({
        title: '¿Cancelar Nota de Crédito?',
        html: `¿Estás seguro de cancelar la nota <strong>#${noteId}</strong> del cliente <strong>${cedula}</strong>?<br><br>Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No, mantener'
    }).then((result) => {
        if (result.isConfirmed) {
            deleteNote(noteId);
        }
    });
}

/**
 * Elimina (cancela) una nota
 */
function deleteNote(noteId) {
    $.ajax({
        url: '/BarkiOS/creditnote?action=delete_ajax',
        method: 'POST',
        data: { nota_id: noteId },
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showAlert('success', 'Éxito', response.message);
                loadCreditNotes(); // Recargar tabla
            } else {
                showAlert('error', 'Error', response.message);
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            const message = response && response.message ? response.message : 'Error al cancelar la nota';
            showAlert('error', 'Error', message);
        }
    });
}

/**
 * Maneja la búsqueda en la tabla
 */
function handleSearch() {
    const searchTerm = $(this).val().toLowerCase();
    
    $('#notesTableBody tr').each(function() {
        const $row = $(this);
        const text = $row.text().toLowerCase();
        $row.toggle(text.indexOf(searchTerm) > -1);
    });
}
