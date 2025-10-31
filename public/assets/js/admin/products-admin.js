import * as Validations from '/BarkiOS/public/assets/js/utils/validation.js';
import * as Helpers from '/BarkiOS/public/assets/js/utils/helpers.js';
import * as Ajax from '/BarkiOS/public/assets/js/utils/ajax-handler.js';

$(document).ready(function() {
    const baseUrl = '/BarkiOS/admin/products';
    let productsTable = null;

    // Tipos de prenda por categoría
    const TIPOS_POR_CATEGORIA = {
        Formal: ["Vestido", "Camisa", "Pantalon", "Chaqueta"],
        Casual: ["Blusa", "Pantalon", "Short", "Falda"],
        Deportivo: ["Short", "Falda", "Chaqueta"],
        Invierno: ["Chaqueta", "Pantalon"],
        Verano: ["Vestido", "Short", "Blusa"],
        Fiesta: ["Vestido", "Falda", "Blusa", "Enterizo"]
    };

    const initDataTable = () => {
        productsTable = $('#productsTable').DataTable({
            ajax: {
                url: `${baseUrl}?action=get_products`,
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataSrc: 'products'
            },
            columns: [
                {
                    data: 'imagen',
                    render: (data, type, row) => {
                        if (data) {
                            return `<img src="/BarkiOS/${Helpers.escapeHtml(data)}" 
                                        style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;" 
                                        alt="${Helpers.escapeHtml(row.nombre)}">`;
                        }
                        return `<i class="fas fa-image fa-2x text-muted"></i>`;
                    }
                },
                { data: 'prenda_id' },
                { data: 'nombre' },
                { data: 'tipo' },
                { data: 'categoria' },
                {
                    data: 'precio',
                    render: (data) => `$${parseFloat(data).toFixed(2)}`
                },
                {
                    data: null,
                    orderable: false,
                    render: (data, type, row) => {
                        return `
                            <button class="btn btn-sm btn-outline-primary btn-edit" 
                                    data-id="${Helpers.escapeHtml(row.prenda_id)}"
                                    data-nombre="${Helpers.escapeHtml(row.nombre)}"
                                    data-tipo="${Helpers.escapeHtml(row.tipo)}"
                                    data-categoria="${Helpers.escapeHtml(row.categoria)}"
                                    data-precio="${row.precio}"
                                    data-imagen="${Helpers.escapeHtml(row.imagen || '')}">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        `;
                    }
                }
            ],
            pageLength: 10,
            responsive: true,
            autoWidth: false,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
            }
        });
    };

    const actualizarTipos = ($catSelect, $tipoSelect) => {
        const categoria = $catSelect.val();
        $tipoSelect.html('<option value="">Seleccione un tipo</option>');
        
        if (TIPOS_POR_CATEGORIA[categoria]) {
            TIPOS_POR_CATEGORIA[categoria].forEach(tipo => {
                $tipoSelect.append(`<option value="${tipo}">${tipo}</option>`);
            });
        }
    };

    //Editar producto
    $(document).on('click', '.btn-edit', function() {
        const $btn = $(this);
        
        $('#editProductId').val($btn.data('id'));
        $('#editProductIdHidden').val($btn.data('id'));
        $('#editProductName').val($btn.data('nombre'));
        $('#editProductCategory').val($btn.data('categoria'));
        
        actualizarTipos($('#editProductCategory'), $('#editProductType'));
        $('#editProductType').val($btn.data('tipo'));
        $('#editProductPrice').val($btn.data('precio'));
        
        Validations.clearValidation($('#editProductForm'));
        $('#editProductModal').modal('show');
    });

    // Cambio de categoría en modal editar
    $('#editProductCategory').on('change', function() {
        actualizarTipos($(this), $('#editProductType'));
    });

    // Submit editar
    $('#editProductForm').on('submit', function(e) {
        e.preventDefault();

        const rules = {
            nombre: 'nombreProducto',
            categoria: 'select',
            tipo: 'select',
            precio: 'precio'
        };

        if (!Validations.validateForm($(this), rules)) {
            Helpers.toast('warning', 'Corrija los campos resaltados');
            return;
        }

        const formData = new FormData(this);

        Ajax.post(`${baseUrl}?action=edit_ajax`, formData)
            .then(response => {
                if (response.success) {
                    Helpers.toast('success', 'Producto actualizado correctamente');
                    $('#editProductModal').modal('hide');
                    productsTable.ajax.reload(null, false);
                } else {
                    Helpers.toast('error', response.message);
                }
            })
            .catch(err => {
                Helpers.toast('error', err);
            });
    });

    //Eliminar producto
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');

        Helpers.confirmDialog(
            '¿Eliminar producto?',
            `¿Deseas eliminar <strong>${Helpers.escapeHtml(nombre)}</strong>?`,
            () => {
                Ajax.post(`${baseUrl}?action=delete_ajax`, { prenda_id: id })
                    .then(response => {
                        if (response.success) {
                            Helpers.toast('success', 'Producto eliminado correctamente');
                            productsTable.ajax.reload(null, false);
                        } else {
                            Helpers.toast('error', response.message);
                        }
                    })
                    .catch(err => {
                        Helpers.toast('error', err);
                    });
            },
            'Sí, eliminar'
        );
    });

    //Validacion
    const editRules = {
        nombre: 'nombreProducto',
        categoria: 'select',
        tipo: 'select',
        precio: 'precio'
    };
    Validations.setupRealTimeValidation($('#editProductForm'), editRules);

    $('#editProductModal').on('hidden.bs.modal', function() {
        Helpers.resetForm($('#editProductForm'));
    });

    initDataTable();
});