$(document).ready(function () {
    const $clientsTableBody = $('#clientesTableBody');
    const $addClientForm = $('#addClientForm');
    const $editClientForm = $('#editClientForm');
        // delegación = no se re-bindea en cada recarga
    $(document).on('click', '.btn-eliminar', handleDelete);
    $(document).on('click', '.btn-editar', e => loadClientForEdit($(e.currentTarget)));

    let dt = null; // instancia global datatable
    
        const renderSpinner = () => `
    <tr><td colspan="6" class="text-center py-3">
        <div class="spinner-border text-primary"></div> Cargando...
    </td></tr>`;

    const renderEmpty = () => `
    <tr><td colspan="6" class="text-center py-3">
        No hay clientes disponibles
    </td></tr>`;

    const buildRow = c => `
    <tr id="cliente-${escapeHtml(c.cliente_ced)}">
        <td class="text-center">${escapeHtml(c.cliente_ced)}</td>
        <td>${escapeHtml(c.nombre_cliente)}</td>
        <td>${escapeHtml(c.direccion)}</td>
        <td class="text-end">${formatearTelefono(c.telefono)}</td>
        <td class="text-center">${escapeHtml(c.tipo)}</td>
        <td class="text-center">
            <button class="btn btn-sm btn-outline-primary btn-editar"
                data-cedula="${escapeHtml(c.cliente_ced)}"
                data-nombre="${escapeHtml(c.nombre_cliente)}"
                data-direccion="${escapeHtml(c.direccion)}"
                data-telefono="${escapeHtml(c.telefono)}"
                data-membresia="${escapeHtml(c.tipo)}">
                <i class="fas fa-edit"></i> Editar
            </button>
            <button class="btn btn-sm btn-outline-danger btn-eliminar"
                data-cedula='${escapeHtml(c.cliente_ced)}'
                data-nombre='${escapeHtml(c.nombre_cliente)}'>
                <i class="fas fa-trash"></i> Eliminar
            </button>
        </td>
    </tr>`;

    
    // ✅ CORREGIDO: Base URL correcta
    const baseUrl = '/BarkiOS/admin/clients';

    const escapeHtml = str => String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const showAlert = (msg, type = 'info') => {
        let icon = 'info';
        if (type === 'success') icon = 'success';
        else if (type === 'danger' || type === 'error') icon = 'error';
        else if (type === 'warning') icon = 'warning';
        Swal.fire({
            text: msg,
            icon: icon,
            timer: 3000,
            showConfirmButton: false,
            timerProgressBar: true,
            position: 'top',
            toast: true
        });
    };

    const formatearTelefono = t => t && String(t).length === 11 ? String(t).replace(/(\d{4})(\d{7})/, '$1-$2') : t;

    // --- VALIDACIÓN EN TIEMPO REAL ---
    function validarCampo($input, regex) {
        const valor = $input.val().trim();
        if (regex.test(valor)) {
            $input.removeClass('is-invalid').addClass('is-valid');
            return true;
        } else {
            $input.removeClass('is-valid').addClass('is-invalid');
            return false;
        }
    }

    function validarSelect($select) {
        if (!$select.val()) {
            $select.removeClass('is-valid').addClass('is-invalid');
            return false;
        } else {
            $select.removeClass('is-invalid').addClass('is-valid');
            return true;
        }
    }

    function configurarValidacion($form) {
        const reglas = {
            'cedula': /^\d{7,8}$/,
            'nombre': /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,60}$/,
            'direccion': /^.{5,150}$/,
            'telefono': /^\d{11}$/
        };

        $.each(reglas, function (campo, regex) {
            const $input = $form.find(`[name="${campo}"]`);
            if ($input.length) {
                $input.on('input', () => validarCampo($input, regex));
            }
        });

        const $selectMembresia = $form.find('[name="membresia"]');
        if ($selectMembresia.length) $selectMembresia.on('change', () => validarSelect($selectMembresia));

        $form.on('submit', function (e) {
            let valido = true;
            $.each(reglas, function (campo, regex) {
                const $input = $form.find(`[name="${campo}"]`);
                if ($input.length && !validarCampo($input, regex)) valido = false;
            });
            if ($selectMembresia.length && !validarSelect($selectMembresia)) valido = false;

            if (!valido) {
                e.preventDefault();
                showAlert('Por favor corrige los campos en rojo.', 'warning');
            }
        });
    }

    // --- CARGAR CLIENTES ---
    function AjaxClients() {

    // si datatable ya existe, destruir antes de pintar
    if (dt) {
        dt.destroy();
        dt = null;
    }

    $clientsTableBody.html(renderSpinner());

    $.ajax({
        url: `${baseUrl}?action=get_clients`,
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        dataType: 'json'
    }).done(data => {

        if (!data.clients?.length) {
            $clientsTableBody.html(renderEmpty());
        } else {
            $clientsTableBody.html(data.clients.map(buildRow).join(''));
        }

        // inicializar datatable después de pintar
        dt = $('#clientsTable').DataTable({
            responsive: true,
            language: { url: '/ruta/a/Spanish.json' }
        });

    }).fail(() => showAlert('Error al cargar clientes','danger'));
}


    // --- AGREGAR CLIENTE ---
    function handleAdd(e) {
        e.preventDefault();
        
        $.ajax({
            url: `${baseUrl}?action=add_ajax`,
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: $addClientForm.serialize(),
            dataType: 'json'
        }).done(data => {
            if (data.success) {
                showAlert('Cliente agregado correctamente', 'success');
                $addClientForm.trigger('reset');
                $('#addClientModal').modal('hide');
                AjaxClients();
            } else {
                showAlert(data.message, 'danger');
            }
        }).fail((xhr, status, error) => {
            console.error('Error al agregar:', error);
            showAlert('Error al agregar cliente', 'danger');
        });
    }

    // --- CARGAR CLIENTE EN MODAL EDITAR ---
    function loadClientForEdit($btn) {
        $('#editClientCedula').val($btn.data('cedula'));
        $('#editClientCedulaHidden').val($btn.data('cedula'));
        $('#editClientNombre').val($btn.data('nombre'));
        $('#editClientDireccion').val($btn.data('direccion'));
        $('#editClientTelefono').val($btn.data('telefono'));
        $('#editClientMembresia').val($btn.data('membresia') || '');
        $('#editClientModal').modal('show');
    }

    // --- EDITAR CLIENTE ---
    function handleEdit(e) {
        e.preventDefault();
        
        $.ajax({
            url: `${baseUrl}?action=edit_ajax`,
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: $editClientForm.serialize(),
            dataType: 'json'
        }).done(data => {
            if (data.success) {
                showAlert('Cliente actualizado correctamente', 'success');
                $('#editClientModal').modal('hide');
                AjaxClients();
            } else {
                showAlert(data.message, 'danger');
            }
        }).fail((xhr, status, error) => {
            console.error('Error al actualizar:', error);
            showAlert('Error al actualizar cliente', 'danger');
        });
    }

    // --- ELIMINAR CLIENTE ---
    function handleDelete() {
        const cedula = $(this).data('cedula');
        const nombre = $(this).data('nombre');

        Swal.fire({
            title: '¿Eliminar cliente?',
            html: `¿Deseas eliminar <strong>${escapeHtml(nombre)}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(res => {
            if (res.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}?action=delete_ajax`,
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    data: { cedula },
                    dataType: 'json'
                }).done(data => {
                    if (data.success) {
                        showAlert('Cliente eliminado correctamente', 'success');
                        AjaxClients();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                }).fail((xhr, status, error) => {
                    console.error('Error al eliminar:', error);
                    showAlert('Error al eliminar cliente', 'danger');
                });
            }
        });
    }

    // --- RESET DE FORMULARIOS ---
    $('#addClientModal, #editClientModal').on('hidden.bs.modal', function () {
        const $form = $(this).find('form');
        $form.trigger('reset').find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
    });

    // --- INICIALIZACIÓN ---
    if ($addClientForm.length) {
        $addClientForm.on('submit', handleAdd);
        configurarValidacion($addClientForm);
    }
    if ($editClientForm.length) {
        $editClientForm.on('submit', handleEdit);
        configurarValidacion($editClientForm);
    }

    AjaxClients();
});