$(document).ready(function () {
    const $usersTableBody = $('#usersTableBody');
    const $addUserForm = $('#addUserForm');
    const $editUserForm = $('#editUserForm');
    const baseUrl = '/BarkiOS/users'; // ✅ Controlador base

    // --- Utilidades ---
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
            icon,
            timer: 3000,
            showConfirmButton: false,
            timerProgressBar: true,
            position: 'top',
            toast: true
        });
    };

    // --- VALIDACIÓN EN TIEMPO REAL ---
    function validarCampo($input, regex, minLength = 1) {
        const valor = $input.val().trim();
        let valido = true;

        if (valor.length < minLength) valido = false;
        if (valido && regex && !regex.test(valor)) valido = false;
        
        $input.toggleClass('is-valid', valido && valor.length > 0);
        $input.toggleClass('is-invalid', !valido && valor.length > 0);
        return valido;
    }

    function configurarValidacion($form, isEdit = false) {
        const reglas = {
            'nombre': /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{3,60}$/,
            'email': /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            'password': isEdit ? /^.{0,30}$/ : /^.{6,30}$/
        };

        $.each(reglas, function (campo, regex) {
            const $input = $form.find(`[name="${campo}"]`);
            if ($input.length) {
                const minLen = campo === 'password' && !isEdit ? 6 : 1;
                if (isEdit && campo === 'password') {
                    $input.on('input', () => {
                        if ($input.val() === '') {
                            $input.removeClass('is-valid is-invalid');
                            return true;
                        }
                        return validarCampo($input, regex, minLen);
                    });
                } else {
                    $input.on('input', () => validarCampo($input, regex, minLen));
                }
            }
        });

        $form.on('submit', function (e) {
            let valido = true;
            $.each(reglas, function (campo, regex) {
                const $input = $form.find(`[name="${campo}"]`);
                if (isEdit && campo === 'password' && $input.val() === '') return true;
                if ($input.length && !validarCampo($input, regex)) valido = false;
            });

            if (!valido) {
                e.preventDefault();
                showAlert('Por favor corrige los campos resaltados.', 'warning');
            }
        });
    }

    // --- CARGAR USUARIOS ---
    function AjaxUsers() {
        $usersTableBody.html(`<tr><td colspan="4" class="text-center py-3">
            <div class="spinner-border text-primary"></div> Cargando...
        </td></tr>`);

        $.ajax({
            url: `${baseUrl}/get_users`, // ✅ Ruta correcta según FrontController
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            dataType: 'json'
        }).done(data => {
            if (!data.users?.length) {
                $usersTableBody.html(`<td colspan="4" class="text-center py-3">No hay usuarios disponibles</td>`);
                return;
            }

            const rows = data.users.map(u => `
                <tr id="user-${escapeHtml(u.id)}">
                    <td class="text-center">${escapeHtml(u.id)}</td>
                    <td>${escapeHtml(u.nombre)}</td>
                    <td>${escapeHtml(u.email)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary btn-editar"
                            data-id="${escapeHtml(u.id)}"
                            data-nombre="${escapeHtml(u.nombre)}"
                            data-email="${escapeHtml(u.email)}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-user-id='${escapeHtml(u.id)}'
                            data-user-name='${escapeHtml(u.nombre)}'>
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>`).join('');

            $usersTableBody.html(rows);
            $('.btn-eliminar').on('click', handleDelete);
            $('.btn-editar').on('click', e => loadUserForEdit($(e.currentTarget)));
        }).fail(xhr => {
            console.error(xhr.responseText);
            showAlert('Error al cargar usuarios', 'danger');
        });
    }

    // --- AGREGAR USUARIO ---
    function handleAdd(e) {
        e.preventDefault();
        const fd = $addUserForm.serialize();

        $.ajax({
            url: `${baseUrl}/add_ajax`, // ✅ acción limpia
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: fd,
            dataType: 'json'
        }).done(data => {
            if (data.success) {
                showAlert('Usuario agregado correctamente', 'success');
                $('#addUserModal').modal('hide');
                AjaxUsers();
            } else showAlert(data.message, 'danger');
        }).fail(() => showAlert('Error al agregar usuario', 'danger'));
    }

    // --- CARGAR DATOS EN EL MODAL DE EDICIÓN ---
    function loadUserForEdit($btn) {
        $('#editUserId').val($btn.data('id'));
        $('#editUserIdHidden').val($btn.data('id'));
        $('#editUserName').val($btn.data('nombre'));
        $('#editUserEmail').val($btn.data('email'));
        $('#editUserPassword').val('');
        $('#editUserForm').find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
        $('#editUserModal').modal('show');
    }

    // --- EDITAR USUARIO ---
    function handleEdit(e) {
        e.preventDefault();
        let data = $editUserForm.serializeArray();
        data = data.filter(item => item.name !== 'password' || item.value !== '');

        $.ajax({
            url: `${baseUrl}/edit_ajax`, // ✅ acción limpia
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: $.param(data),
            dataType: 'json'
        }).done(data => {
            if (data.success) {
                showAlert('Usuario actualizado correctamente', 'success');
                $('#editUserModal').modal('hide');
                AjaxUsers();
            } else showAlert(data.message, 'danger');
        }).fail(() => showAlert('Error al actualizar usuario', 'danger'));
    }

    // --- ELIMINAR USUARIO ---
    function handleDelete() {
        const id = $(this).data('user-id');
        const name = $(this).data('user-name');

        Swal.fire({
            title: '¿Eliminar usuario?',
            html: `¿Deseas eliminar a <strong>${escapeHtml(name)}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(res => {
            if (res.isConfirmed) {
                $.ajax({
                    url: `${baseUrl}/delete_ajax`, // ✅ acción limpia
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    data: { id },
                    dataType: 'json'
                }).done(data => {
                    if (data.success) {
                        showAlert('Usuario eliminado correctamente', 'success');
                        AjaxUsers();
                    } else showAlert(data.message, 'danger');
                }).fail(() => showAlert('Error al eliminar usuario', 'danger'));
            }
        });
    }

    // --- RESET Y ENLACES ---
    $('#addUserModal, #editUserModal').on('hidden.bs.modal', function () {
        const $form = $(this).find('form');
        $form.trigger('reset').find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
    });

    // Inicialización
    configurarValidacion($addUserForm, false);
    configurarValidacion($editUserForm, true);
    if ($addUserForm.length) $addUserForm.on('submit', handleAdd);
    if ($editUserForm.length) $editUserForm.on('submit', handleEdit);
    
    AjaxUsers(); // 🔥 Carga inicial
});
