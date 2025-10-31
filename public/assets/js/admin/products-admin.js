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
                    orderable: false,
                    className: 'text-center',
                    render: (data, type, row) => {
                        if (data) {
                            return `
                                <img src="/BarkiOS/${Helpers.escapeHtml(data)}" 
                                    style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" 
                                    class="img-thumbnail view-image"
                                    data-imagen="/BarkiOS/${Helpers.escapeHtml(data)}"
                                    data-nombre="${Helpers.escapeHtml(row.nombre)}"
                                    title="Click para ampliar"
                                    alt="${Helpers.escapeHtml(row.nombre)}">
                            `;
                        }
                        return `<i class="fas fa-image fa-2x text-muted"></i>`;
                    }
                },
                { 
                    data: 'codigo_prenda',
                    className: 'text-center',
                    render: (data) => Helpers.escapeHtml(data)
                },
                { 
                    data: 'nombre',
                    render: (data) => `<strong>${Helpers.escapeHtml(data)}</strong>`
                },
                { 
                    data: 'tipo',
                    className: 'text-center'
                },
                { 
                    data: 'categoria',
                    className: 'text-center',
                    render: (data) => Helpers.escapeHtml(data)
                },
                {
                    data: 'precio_compra',
                    className: 'text-end',
                    render: (data) => {
                        if (data && parseFloat(data) > 0) {
                            return `<span class="text-primary fw-bold">$${parseFloat(data).toFixed(2)}</span>`;
                        }
                        return `<span class="text-muted fst-italic">N/A</span>`;
                    }
                },
                {
                    data: 'precio',
                    className: 'text-end',
                    render: (data) => `<span class="text-success fw-bold">${parseFloat(data).toFixed(2)}</span>`
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: (data, type, row) => {
                        const hasImage = row.imagen ? true : false;
                        
                        return `
                            <div class="btn-group" role="group">
                                ${hasImage ? `
                                    <button class="btn btn-sm btn-outline-info view-image" 
                                            data-imagen="/BarkiOS/${Helpers.escapeHtml(row.imagen)}"
                                            data-nombre="${Helpers.escapeHtml(row.nombre)}"
                                            title="Ver imagen ampliada">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                ` : ''}
                                <button class="btn btn-sm btn-outline-primary btn-edit" 
                                        data-id="${Helpers.escapeHtml(row.prenda_id)}"
                                        data-codigo="${Helpers.escapeHtml(row.codigo_prenda)}"
                                        data-nombre="${Helpers.escapeHtml(row.nombre)}"
                                        data-tipo="${Helpers.escapeHtml(row.tipo)}"
                                        data-categoria="${Helpers.escapeHtml(row.categoria)}"
                                        data-precio="${row.precio}"
                                        data-precio-compra="${row.precio_compra || ''}"
                                        data-imagen="${Helpers.escapeHtml(row.imagen || '')}"
                                        title="Editar producto">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            pageLength: 10,
            responsive: true,
            autoWidth: false,
            order: [[1, 'asc']], // Ordenar por código
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
            }
        });
    };

    // ============================================
    // VER IMAGEN AMPLIADA
    // ============================================
    $(document).on('click', '.view-image', function(e) {
        e.stopPropagation();
        const imagenUrl = $(this).data('imagen');
        const nombre = $(this).data('nombre');
        
        Swal.fire({
            title: nombre,
            imageUrl: imagenUrl,
            imageAlt: nombre,
            showCloseButton: true,
            showConfirmButton: false,
            width: '80%',
            padding: '2em',
            background: '#fff',
            customClass: {
                image: 'img-fluid rounded shadow-lg',
                popup: 'rounded-3'
            },
            showClass: {
                popup: 'animate__animated animate__zoomIn animate__faster'
            }
        });
    });

    // ============================================
    // ACTUALIZAR TIPOS SEGÚN CATEGORÍA
    // ============================================
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
        
        $('#editProductId').val($btn.data('codigo'));
        $('#editProductIdHidden').val($btn.data('id'));
        $('#editProductName').val($btn.data('nombre'));
        $('#editProductCategory').val($btn.data('categoria'));
        
        actualizarTipos($('#editProductCategory'), $('#editProductType'));
        $('#editProductType').val($btn.data('tipo'));
        $('#editProductPrice').val($btn.data('precio'));
        
        // Manejo del precio de compra
        const precioCompra = $btn.data('precio-compra');
        $('#editProductPriceCompra').val(precioCompra || '');
        
        // Calcular y mostrar ganancia
        calcularGanancia('edit');
        
        // Mostrar imagen actual si existe
        const imagen = $btn.data('imagen');
        if (imagen) {
            $('#currentImagePreview').html(`
                <div class="text-center mt-3">
                    <p class="text-muted mb-2"><small>Imagen actual:</small></p>
                    <img src="/BarkiOS/${Helpers.escapeHtml(imagen)}" 
                         class="img-thumbnail shadow-sm view-image" 
                         style="max-width: 250px; max-height: 250px; cursor: pointer; border-radius: 8px;"
                         data-imagen="/BarkiOS/${Helpers.escapeHtml(imagen)}"
                         data-nombre="${$btn.data('nombre')}"
                         title="Click para ver en tamaño completo">
                    <p class="text-muted small mt-2"><i class="fas fa-search-plus"></i> Click para ampliar</p>
                </div>
            `);
        } else {
            $('#currentImagePreview').html('<p class="text-muted text-center"><i class="fas fa-image"></i> Sin imagen</p>');
        }
        
        Validations.clearValidation($('#editProductForm'));
        $('#editProductModal').modal('show');
    });

    // ============================================
    // CALCULAR GANANCIA
    // ============================================
    function calcularGanancia(mode) {
        const prefix = mode === 'edit' ? 'edit' : 'add';
        const precioVenta = parseFloat($(`#${prefix}ProductPrice`).val()) || 0;
        const precioCompra = parseFloat($(`#${prefix}ProductPriceCompra`).val()) || 0;
        
        if (precioCompra > 0 && precioVenta > 0) {
            const ganancia = precioVenta - precioCompra;
            const margen = ((ganancia / precioCompra) * 100).toFixed(2);
            
            let mensaje = '';
            let colorClass = '';
            
            if (ganancia > 0) {
                mensaje = `Ganancia: $${ganancia.toFixed(2)} (${margen}%)`;
                colorClass = 'text-success';
            } else if (ganancia < 0) {
                mensaje = `Pérdida: $${Math.abs(ganancia).toFixed(2)} (${margen}%)`;
                colorClass = 'text-danger';
            } else {
                mensaje = 'Sin ganancia';
                colorClass = 'text-warning';
            }
            
            $(`#${prefix}GananciaInfo`).html(`<span class="${colorClass} fw-bold">${mensaje}</span>`).show();
        } else {
            $(`#${prefix}GananciaInfo`).html('').hide();
        }
    }

    // Calcular ganancia al cambiar precios
    $('#editProductPrice, #editProductPriceCompra').on('input', function() {
        calcularGanancia('edit');
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

        Swal.fire({
            title: '¿Eliminar producto?',
            html: `¿Estás seguro de eliminar <strong>${Helpers.escapeHtml(nombre)}</strong>?<br><small class="text-muted">Esta acción no se puede deshacer</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
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
            }
        });
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
        $('#currentImagePreview').html('');
        $('#editGananciaInfo').html('').hide();
    });

    initDataTable();
});