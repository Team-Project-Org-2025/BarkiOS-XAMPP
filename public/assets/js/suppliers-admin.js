$(document).ready(function () {
    const $suppliersTableBody = $('#suppliersTableBody');
    const $addSupplierForm = $('#addSupplierForm');
    const $editSupplierForm = $('#editSupplierForm');
    const baseUrl = '/BarkiOS/admin/supplier'
    // --- UTILIDADES ---
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

    // --- VALIDACIÓN ---
    function validarProveedor($form, validarRif = true) {
        const $tipoRif = $form.find('[name="tipo_rif"]'); // select
        const $rif = $form.find('[name="proveedor_rif"]');
        const $nombreContacto = $form.find('[name="nombre_contacto"]');
        const $nombreEmpresa = $form.find('[name="nombre_empresa"]');
        const $direccion = $form.find('[name="direccion"]');

        // Expresiones regulares
        const regexRif = /^\d{9}$/; // ejemplo: J-12345678 o V123456789
        const regexNombre = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-\_\.\,\&\"\']{2,60}$/;
        const regexDireccion = /^.{5,150}$/; // mínimo 5 caracteres, máx 150

        let valido = true;
        $form.find('.is-invalid').removeClass('is-invalid');

        if ($tipoRif.val().trim() === '') {
            $tipoRif.addClass('is-invalid');
            valido = false;
        }

        if (validarRif && !regexRif.test($rif.val().trim())) {
            $rif.addClass('is-invalid');
            valido = false;
        }

        if (!regexNombre.test($nombreContacto.val().trim())) {
            $nombreContacto.addClass('is-invalid');
            valido = false;
        }

        if (!regexNombre.test($nombreEmpresa.val().trim())) {
            $nombreEmpresa.addClass('is-invalid');
            valido = false;
        }

        if (!regexDireccion.test($direccion.val().trim())) {
            $direccion.addClass('is-invalid');
            valido = false;
        }

        if (!valido) {
            showAlert('Por favor corrija los campos resaltados antes de continuar.', 'warning');
        }

        return valido;
    }

    // --- CARGAR PROVEEDORES ---
    function AjaxSuppliers() {
        $suppliersTableBody.html(`
            <tr><td colspan="6" class="text-center py-3">
                <div class="spinner-border text-primary"></div> Cargando...
            </td></tr>
        `);

        $.ajax({
            url: window.location.pathname + '?action=get_suppliers',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            dataType: 'json'
        }).done(function (data) {
            if (!data.suppliers?.length) {
                $suppliersTableBody.html(`
                    <td colspan="6" class="text-center" style="padding: 1.5rem 0;">
                        <i class="fa-solid fa-circle-info me-2 text-primary"></i>
                        No hay proveedores disponibles
                    </td>
                `);
                return;
            }

            const rows = data.suppliers.map(s => `
                <tr id="proveedor-${escapeHtml(s.proveedor_rif)}">
                    <td>${escapeHtml(s.tipo_rif)}-${escapeHtml(s.proveedor_rif)}</td>
                    <td>${escapeHtml(s.tipo_rif)}</td>
                    <td>${escapeHtml(s.nombre_contacto)}</td>
                    <td>${escapeHtml(s.nombre_empresa)}</td>
                    <td>${escapeHtml(s.direccion)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary btn-editar"
                            data-proveedor_rif="${escapeHtml(s.proveedor_rif)}"
                            data-tipo_rif="${escapeHtml(s.tipo_rif)}"
                            data-nombre_contacto="${escapeHtml(s.nombre_contacto)}"
                            data-nombre_empresa="${escapeHtml(s.nombre_empresa)}"
                            data-direccion="${escapeHtml(s.direccion)}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-proveedor_rif="${escapeHtml(s.proveedor_rif)}"
                            data-nombre="${escapeHtml(s.nombre_contacto)}">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>
            `).join('');

            $suppliersTableBody.html(rows);
            $('.btn-eliminar').on('click', handleDelete);
            $('.btn-editar').on('click', function () {
                loadSupplierForEdit($(this));
            });
        }).fail(() => showAlert('Error al cargar proveedores', 'danger'));
    }

    // --- AGREGAR ---
    function handleAdd(e) {
        e.preventDefault();
        if (!validarProveedor($addSupplierForm)) return;

        $.ajax({
            url: `${baseUrl}?action=add_ajax`,
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: $addSupplierForm.serialize(),
            dataType: 'json'
        }).done(function (data) {
            if (data.success) {
                showAlert('Proveedor agregado correctamente', 'success');
                $addSupplierForm.trigger('reset');
                $('#addSupplierModal').modal('hide');
                AjaxSuppliers();
            } else showAlert(data.message, 'danger');
        }).fail(() => showAlert('Error al agregar', 'danger'));
    }

    // --- CARGAR DATOS EN MODAL EDITAR ---
    function loadSupplierForEdit($btn) {
        $('#editSupplierRif').val($btn.data('proveedor_rif'));
        $('#editSupplierRifHidden').val($btn.data('proveedor_rif'));
        $('#editSupplierTipoRif').val($btn.data('tipo_rif'));
        $('#editSupplierNombreContacto').val($btn.data('nombre_contacto'));
        $('#editSupplierNombreEmpresa').val($btn.data('nombre_empresa'));
        $('#editSupplierDireccion').val($btn.data('direccion'));
        $('#editSupplierModal').modal('show');
    }

    // --- EDITAR ---
    function handleEdit(e) {
        e.preventDefault();
        if (!validarProveedor($editSupplierForm, false)) return;

        $.ajax({
            url: `${baseUrl}?action=edit_ajax`,
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            data: $editSupplierForm.serialize(),
            dataType: 'json'
        }).done(function (data) {
            if (data.success) {
                showAlert('Proveedor actualizado correctamente', 'success');
                $('#editSupplierModal').modal('hide');
                AjaxSuppliers();
            } else showAlert(data.message, 'danger');
        }).fail(() => showAlert('Error al actualizar', 'danger'));
    }

    // --- ELIMINAR ---
    function handleDelete() {
        const proveedor_rif = $(this).data('proveedor_rif');
        const nombre = $(this).data('nombre');
        Swal.fire({
            title: '¿Eliminar proveedor?',
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
                    data: { proveedor_rif },
                    dataType: 'json'
                }).done(function (data) {
                    if (data.success) {
                        showAlert('Proveedor eliminado correctamente', 'success');
                        AjaxSuppliers();
                    } else showAlert(data.message, 'danger');
                }).fail(() => showAlert('Error al eliminar', 'danger'));
            }
        });
    }

    // --- VALIDACIÓN EN TIEMPO REAL ---
    function aplicarValidacionTiempoReal($form) {
        const $tipoRif = $form.find('[name="tipo_rif"]');
        const $rif = $form.find('[name="proveedor_rif"]');
        const $nombreContacto = $form.find('[name="nombre_contacto"]');
        const $nombreEmpresa = $form.find('[name="nombre_empresa"]');
        const $direccion = $form.find('[name="direccion"]');

        const regexRif = /^\d{9}$/;
        const regexNombre = /^[A-Za-zÁÉÍÓÚáéíóúÑñ0-9\s\-\_\.\,\&\"\']{2,60}$/;
        const regexDireccion = /^.{5,150}$/;

        const validarCampo = ($input, regex) => {
            const val = $input.val().trim();
            $input.toggleClass('is-invalid', !regex.test(val));
            $input.toggleClass('is-valid', regex.test(val));
        };

        const validarSelect = ($select) => {
            if ($select.val().trim() === '') {
                $select.addClass('is-invalid').removeClass('is-valid');
            } else {
                $select.addClass('is-valid').removeClass('is-invalid');
            }
        };

        $tipoRif.on('change blur', () => validarSelect($tipoRif));
        $rif.on('input blur', () => validarCampo($rif, regexRif));
        $nombreContacto.on('input blur', () => validarCampo($nombreContacto, regexNombre));
        $nombreEmpresa.on('input blur', () => validarCampo($nombreEmpresa, regexNombre));
        $direccion.on('input blur', () => validarCampo($direccion, regexDireccion));
    }

    aplicarValidacionTiempoReal($addSupplierForm);
    aplicarValidacionTiempoReal($editSupplierForm);

    // --- RESET VISUAL AL CERRAR MODALES ---
    $('#addSupplierModal, #editSupplierModal').on('hidden.bs.modal', function () {
        const $form = $(this).find('form');
        $form.trigger('reset');
        $form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
    });

    // --- INICIALIZACIÓN ---
    if ($addSupplierForm.length) $addSupplierForm.on('submit', handleAdd);
    if ($editSupplierForm.length) $editSupplierForm.on('submit', handleEdit);
    AjaxSuppliers();
});
