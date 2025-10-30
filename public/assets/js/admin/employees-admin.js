$(document).ready(function () {
    const $employeesTableBody = $('#employeesTableBody');
    const $addEmployeeForm = $('#addEmployeeForm');
    const $editEmployeeForm = $('#editEmployeeForm');
    const baseUrl = '/BarkiOS/admin/employees';

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
    
    const formatearFecha = f => {
        if (!f) return '';
        const fecha = new Date(f);
        return fecha.toLocaleDateString('es-VE', { year: 'numeric', month: '2-digit', day: '2-digit' });
    };

    // VALIDACIÓN
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

    function configurarValidacion($form) {
        const reglas = {
            'cedula': /^\d{7,10}$/,
            'nombre': /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,100}$/,
            'telefono': /^\d{11}$/,
            'cargo': /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{2,50}$/
        };

        $.each(reglas, function (campo, regex) {
            const $input = $form.find(`[name="${campo}"]`);
            if ($input.length) $input.on('input', () => validarCampo($input, regex));
        });

        $form.on('submit', function (e) {
            let valido = true;
            $.each(reglas, function (campo, regex) {
                const $input = $form.find(`[name="${campo}"]`);
                // Solo validar si el campo existe y no es opcional
                if ($input.length && campo !== 'cargo' && !validarCampo($input, regex)) {
                    valido = false;
                }
            });
            if (!valido) {
                e.preventDefault();
                showAlert('Corrige los campos en rojo', 'warning');
            }
        });
    }

    // CARGAR EMPLEADOS
    function loadEmployees() {
        $employeesTableBody.html(`<tr><td colspan="6" class="text-center py-3">
            <div class="spinner-border text-primary"></div> Cargando...
        </td></tr>`);

        $.ajax({
            url: `${baseUrl}?action=get_employees`,
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            dataType: 'json'
        }).done(data => {
            if (!data.employees?.length) {
                $employeesTableBody.html(`
                    <tr><td colspan="6" class="text-center py-4">
                        <i class="fa-solid fa-circle-info me-2 text-primary"></i>
                        No hay empleados disponibles
                    </td></tr>`);
                return;
            }

            const rows = data.employees.map(e => `
                <tr>
                    <td>${escapeHtml(e.empleado_ced)}</td>
                    <td>${escapeHtml(e.nombre)}</td>
                    <td>${formatearTelefono(e.telefono)}</td>
                    <td>${escapeHtml(e.cargo || 'Empleado')}</td>
                    <td>${formatearFecha(e.fecha_ingreso)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-editar"
                            data-cedula="${escapeHtml(e.empleado_ced)}"
                            data-nombre="${escapeHtml(e.nombre)}"
                            data-telefono="${escapeHtml(e.telefono)}"
                            data-cargo="${escapeHtml(e.cargo || 'Empleado')}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-cedula="${escapeHtml(e.empleado_ced)}"
                            data-nombre="${escapeHtml(e.nombre)}">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>`).join('');

            $employeesTableBody.html(rows);
            $('.btn-eliminar').on('click', handleDelete);
            $('.btn-editar').on('click', e => loadEmployeeForEdit($(e.currentTarget)));
        }).fail(() => showAlert('Error al cargar empleados', 'danger'));
    }

    // AGREGAR
    function handleAdd(e) {
        e.preventDefault();
        $.ajax({
            url: `${baseUrl}?action=add_ajax`,
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: $addEmployeeForm.serialize(),
            dataType: 'json'
        }).done(data => {
            if (data.success) {
                showAlert('Empleado agregado correctamente', 'success');
                $addEmployeeForm.trigger('reset');
                $('#addEmployeeModal').modal('hide');
                loadEmployees();
            } else {
                showAlert(data.message, 'danger');
            }
        }).fail(() => showAlert('Error al agregar empleado', 'danger'));
    }

    // EDITAR
    function loadEmployeeForEdit($btn) {
        $('#editEmployeeCedula').val($btn.data('cedula'));
        $('#editEmployeeCedulaHidden').val($btn.data('cedula'));
        $('#editEmployeeNombre').val($btn.data('nombre'));
        $('#editEmployeeTelefono').val($btn.data('telefono'));
        $('#editEmployeeCargo').val($btn.data('cargo'));
        $('#editEmployeeModal').modal('show');
    }

    function handleEdit(e) {
        e.preventDefault();
        $.ajax({
            url: `${baseUrl}?action=edit_ajax`,
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: $editEmployeeForm.serialize(),
            dataType: 'json'
        }).done(data => {
            if (data.success) {
                showAlert('Empleado actualizado correctamente', 'success');
                $('#editEmployeeModal').modal('hide');
                loadEmployees();
            } else {
                showAlert(data.message, 'danger');
            }
        }).fail(() => showAlert('Error al actualizar empleado', 'danger'));
    }

    // ELIMINAR
    function handleDelete() {
        const cedula = $(this).data('cedula');
        const nombre = $(this).data('nombre');

        Swal.fire({
            title: '¿Eliminar empleado?',
            html: `¿Deseas eliminar a <strong>${escapeHtml(nombre)}</strong>?`,
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
                        showAlert('Empleado eliminado correctamente', 'success');
                        loadEmployees();
                    } else {
                        showAlert(data.message, 'danger');
                    }
                }).fail(() => showAlert('Error al eliminar empleado', 'danger'));
            }
        });
    }

    // RESET MODALES
    $('#addEmployeeModal, #editEmployeeModal').on('hidden.bs.modal', function () {
        $(this).find('form').trigger('reset').find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
    });

    // INICIALIZACIÓN
    if ($addEmployeeForm.length) {
        $addEmployeeForm.on('submit', handleAdd);
        configurarValidacion($addEmployeeForm);
    }
    if ($editEmployeeForm.length) {
        $editEmployeeForm.on('submit', handleEdit);
        configurarValidacion($editEmployeeForm);
    }

    loadEmployees();
});