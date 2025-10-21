$(document).ready(function() {
    const $productsTableBody = $('#productsTableBody');
    const $addProductForm = $('#addProductForm');
    const $editProductForm = $('#editProductForm');

    // --- Tipos de prenda por categoría ---
    const tiposPorCategoria = {
        Formal:    ["Vestido", "Camisa", "Pantalon", "Chaqueta"],
        Casual:    ["Blusa", "Pantalon", "Short", "Falda"],
        Deportivo: ["Short", "Falda", "Chaqueta"],
        Invierno:  ["Chaqueta", "Pantalon"],
        Verano:    ["Vestido", "Short", "Blusa"],
        Fiesta:    ["Vestido", "Falda", "Blusa", "Enterizo"]
    };

    function actualizarTipos($selectCategoria, $selectTipo) {
        const categoria = $selectCategoria.val();
        $selectTipo.html('<option value="">Seleccione un tipo</option>');
        if (tiposPorCategoria[categoria]) {
            $.each(tiposPorCategoria[categoria], function(_, tipo) {
                $selectTipo.append(`<option value="${tipo}">${tipo}</option>`);
            });
        }
    }

    // --- Dependencias tipo-categoría ---
    const $catAdd = $('#productCategory');
    const $tipoAdd = $('#productType');
    const $catEdit = $('#editProductCategory');
    const $tipoEdit = $('#editProductType');

    if ($catAdd.length && $tipoAdd.length) {
        $catAdd.on('change', () => actualizarTipos($catAdd, $tipoAdd));
    }
    if ($catEdit.length && $tipoEdit.length) {
        $catEdit.on('change', () => actualizarTipos($catEdit, $tipoEdit));
    }

    // --- Utilidades ---
    const escapeHtml = str => String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');

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

    // --- VALIDACIONES CON .is-invalid ---
    function validarProducto($form, validarCodigo = true) {
        const $codigo = $form.find('[name="prenda_id"]');
        const $nombre = $form.find('[name="nombre"]');
        const $precio = $form.find('[name="precio"]');
        const $categoria = $form.find('[name="categoria"]');
        const $tipo = $form.find('[name="tipo"]');

        const regexCodigo = /^\d{9}$/;
        const regexNombre = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,40}$/;
        const regexPrecio = /^\d+(\.\d{1,2})?$/;

        let valido = true;

        $form.find('.is-invalid').removeClass('is-invalid');

        if (validarCodigo && !regexCodigo.test($codigo.val().trim())) {
            $codigo.addClass('is-invalid');
            valido = false;
        }

        if ($categoria.val().trim() === '') {
            $categoria.addClass('is-invalid');
            valido = false;
        }

        if ($tipo.val().trim() === '') {
            $tipo.addClass('is-invalid');
            valido = false;
        }

        if (!regexNombre.test($nombre.val().trim())) {
            $nombre.addClass('is-invalid');
            valido = false;
        }

        const precio = $precio.val().trim();
        if (!regexPrecio.test(precio) || parseFloat(precio) <= 0) {
            $precio.addClass('is-invalid');
            valido = false;
        }

        if (!valido) {
            showAlert('Por favor corrija los campos resaltados antes de continuar.', 'warning');
        }

        return valido;
    }

    // --- CRUD AJAX (tu código intacto) ---
    function AjaxProducts() {
        $productsTableBody.html(`
            <tr><td colspan="6" class="text-center" style="padding: 1.25rem 0;">
                <div class="spinner-border text-primary"></div> Cargando...
            </td></tr>
        `);

        $.ajax({
            url: window.location.pathname + '?action=get_products',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            dataType: 'json'
        }).done(function(data) {
            if (!data.products?.length) {
                $productsTableBody.html(`
                    <td colspan="6" class="text-center" style="padding: 1.5rem 0;">
                        <i class="fa-solid fa-circle-info me-2 text-primary"></i>
                        No hay productos disponibles
                    </td>
                `);
                return;
            }

            const rows = data.products.map(p => `
                <tr id="producto-${escapeHtml(p.prenda_id)}">
                    <td>${escapeHtml(p.prenda_id)}</td>
                    <td>${escapeHtml(p.nombre)}</td>
                    <td>${escapeHtml(p.tipo)}</td>
                    <td>${escapeHtml(p.categoria)}</td>
                    <td>$${parseFloat(p.precio).toFixed(2)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-edit"
                            data-id="${escapeHtml(p.prenda_id)}"
                            data-nombre="${escapeHtml(p.nombre)}"
                            data-tipo="${escapeHtml(p.tipo)}"
                            data-categoria="${escapeHtml(p.categoria)}"
                            data-precio="${p.precio}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-delete"
                            data-product-id="${escapeHtml(p.prenda_id)}"
                            data-product-name="${escapeHtml(p.nombre)}">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>
            `).join('');
            $productsTableBody.html(rows);

            $('.btn-delete').on('click', handleDelete);
            $('.btn-edit').on('click', function() {
                loadProductForEdit($(this));
            });
        }).fail(() => showAlert('Error al cargar productos', 'danger'));
    }

    function handleAdd(e) {
        e.preventDefault();
        if (!validarProducto($addProductForm)) return;

        const fd = $addProductForm.serialize();
        $.ajax({
            url: 'index.php?controller=products&action=add_ajax',
            method: 'POST',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            data: fd,
            dataType: 'json'
        }).done(function(data) {
            if (data.success) {
                showAlert('Producto agregado correctamente', 'success');
                $addProductForm.trigger('reset');
                $('#addProductModal').modal('hide');
                AjaxProducts();
            } else showAlert(data.message, 'danger');
        }).fail(() => showAlert('Error al agregar', 'danger'));
    }

    function loadProductForEdit($btn) {
        $('#editProductId').val($btn.data('id'));
        $('#editProductIdHidden').val($btn.data('id'));
        $('#editProductName').val($btn.data('nombre'));
        $('#editProductCategory').val($btn.data('categoria'));
        actualizarTipos($catEdit, $tipoEdit);
        $('#editProductType').val($btn.data('tipo'));
        $('#editProductPrice').val($btn.data('precio'));
        $('#editProductModal').modal('show');
    }

    function handleEdit(e) {
        e.preventDefault();
        if (!validarProducto($editProductForm, false)) return;
        $.ajax({
            url: 'index.php?controller=products&action=edit_ajax',
            method: 'POST',
            data: $editProductForm.serialize(),
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    showAlert('Producto actualizado', 'success');
                    $('#editProductModal').modal('hide');
                    AjaxProducts();
                } else showAlert(data.message, 'danger');
            },
            error: function () { showAlert('Error al actualizar', 'danger'); }
        });
    }

    function handleDelete() {
        const prenda_id = $(this).data('product-id');
        const name = $(this).data('product-name');
        Swal.fire({
            title: '¿Eliminar producto?',
            html: `¿Deseas eliminar <strong>${escapeHtml(name)}</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(res => {
            if (res.isConfirmed) {
                $.ajax({
                    url: 'index.php?controller=products&action=delete_ajax',
                    method: 'POST',
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    data: { prenda_id },
                    dataType: 'json'
                }).done(function(data) {
                    if (data.success) {
                        showAlert('Producto eliminado correctamente', 'success');
                        AjaxProducts();
                    } else showAlert(data.message, 'danger');
                }).fail(() => showAlert('Error al eliminar', 'danger'));
            }
        });
    }

    // --- Validación en tiempo real (incluye selects) ---
    function aplicarValidacionEnTiempoReal($form) {
        const $codigo = $form.find('[name="prenda_id"]');
        const $nombre = $form.find('[name="nombre"]');
        const $precio = $form.find('[name="precio"]');
        const $categoria = $form.find('[name="categoria"]');
        const $tipo = $form.find('[name="tipo"]');

        const regexCodigo = /^\d{9}$/;
        const regexNombre = /^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,40}$/;
        const regexPrecio = /^(100(\.00?)?|[1-9]?\d(\.\d{1,2})?)$/;

        const validarCampo = ($input, regex, extraCheck = null) => {
            const val = $input.val().trim();
            if (val === '') {
                $input.removeClass('is-valid').addClass('is-invalid');
                return;
            }
            let valido = regex.test(val);
            if (extraCheck) valido = valido && extraCheck(val);

            $input.toggleClass('is-invalid', !valido);
            $input.toggleClass('is-valid', valido);
        };

        const validarSelect = ($select) => {
            if ($select.val().trim() === '') {
                $select.removeClass('is-valid').addClass('is-invalid');
            } else {
                $select.removeClass('is-invalid').addClass('is-valid');
            }
        };

        $codigo.on('input blur', () => validarCampo($codigo, regexCodigo));
        $nombre.on('input blur', () => validarCampo($nombre, regexNombre));
        $precio.on('input blur', () => validarCampo($precio, regexPrecio, v => parseFloat(v) > 0));
        $categoria.on('change blur', () => validarSelect($categoria));
        $tipo.on('change blur', () => validarSelect($tipo));
    }

    // Aplicar validación
    aplicarValidacionEnTiempoReal($addProductForm);
    aplicarValidacionEnTiempoReal($editProductForm);

    // Inicialización
    if ($addProductForm.length) $addProductForm.on('submit', handleAdd);
    if ($editProductForm.length) $editProductForm.on('submit', handleEdit);
    AjaxProducts();

    $('#addProductModal').on('hidden.bs.modal', function () {
    const $form = $('#addProductForm');
    $form.trigger('reset');
    $form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
});

$('#editProductModal').on('hidden.bs.modal', function () {
    const $form = $('#editProductForm');
    $form.trigger('reset');
    $form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
});

$('#addProductModal').on('show.bs.modal', function () {
    const $form = $('#addProductForm');
    $form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
});

});

