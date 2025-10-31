import * as Validations from '/BarkiOS/public/assets/js/utils/validation.js';
import * as Helpers from '/BarkiOS/public/assets/js/utils/helpers.js';
import * as Ajax from '/BarkiOS/public/assets/js/utils/ajax-handler.js';

$(document).ready(function() {
    const baseUrl = window.location.pathname;
    let prendaIndex = 0;
    let allPurchases = [];

    // Tipos por categoría
    const TIPOS_POR_CATEGORIA = {
        Formal: ["Vestido", "Camisa", "Pantalon", "Chaqueta"],
        Casual: ["Blusa", "Pantalon", "Short", "Falda"],
        Deportivo: ["Short", "Falda", "Chaqueta"],
        Invierno: ["Chaqueta", "Pantalon"],
        Verano: ["Vestido", "Short", "Blusa"],
        Fiesta: ["Vestido", "Falda", "Blusa", "Enterizo"]
    };

    // ============================================
    // CARGAR COMPRAS
    // ============================================
// ============================================
// INICIALIZAR TABLA DE COMPRAS (DataTable)
// ============================================
let purchasesTable = null;

const initPurchaseTable = () => {
    purchasesTable = $('#purchaseTable').DataTable({
        ajax: {
            url: `${baseUrl}?action=get_purchases`,
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            dataSrc: data => data.success ? data.data : []
        },
        columns: [
            { 
                data: 'factura_numero',
                render: (data, type, row) => `
                    <strong>#${data}</strong>
                    ${row.pdf_generado == 1 
                        ? '<i class="fas fa-check-circle text-success ms-2" title="PDF generado"></i>' 
                        : ''}`
            },
            { 
                data: 'nombre_proveedor',
                render: (data, type, row) => `
                    <div><strong>${data}</strong></div>
                    <small class="text-muted">${row.total_prendas} prenda(s)</small>`
            },
            { 
                data: 'fecha_compra',
                render: data => Helpers.formatDate(data)
            },
            { 
                data: 'monto_total',
                className: 'text-end',
                render: data => `<strong class="text-success">${Helpers.formatCurrency(data)}</strong>`
            },
            { 
                data: null,
                className: 'text-center',
                render: row => `
                    <span class="badge bg-success">${row.prendas_disponibles || 0}</span>
                    <span class="badge bg-secondary">${row.prendas_vendidas || 0}</span>`
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                render: row => `
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary btn-view" data-id="${row.compra_id}" title="Ver"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-outline-success btn-pdf" data-id="${row.compra_id}" title="PDF"><i class="fas fa-file-pdf"></i></button>
                        <button class="btn btn-outline-warning btn-edit" data-id="${row.compra_id}" title="Editar"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-outline-danger btn-delete" data-id="${row.compra_id}" title="Eliminar"><i class="fas fa-trash"></i></button>
                    </div>`
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

// ✅ Para recargar la tabla sin perder paginación
const reloadPurchases = () => {
    if (purchasesTable) purchasesTable.ajax.reload(null, false);
};


    // ============================================
    // ACTUALIZAR ESTADÍSTICAS
    // ============================================
    const updateStats = () => {
        let totalPagado = 0, totalPendiente = 0, montoTotal = 0;

        allPurchases.forEach(p => {
            totalPagado += parseFloat(p.total_pagado || 0);
            totalPendiente += parseFloat(p.saldo_pendiente || 0);
            montoTotal += parseFloat(p.monto_total || 0);
        });

        $('#statMontoPagado').text(`Pagado: ${Helpers.formatCurrency(totalPagado)}`);
        $('#statSaldoPendiente').text(Helpers.formatCurrency(totalPendiente));
        $('#statMontoTotal').text(Helpers.formatCurrency(montoTotal));

        const porcentaje = montoTotal > 0 ? (totalPagado / montoTotal) * 100 : 0;
        const offset = 220 - (220 * porcentaje / 100);

        $('#progressBar').css('stroke-dashoffset', offset);
        $('#progressPercent').text(Math.round(porcentaje) + '%');
    };

    // ============================================
    // GESTIÓN DE PRENDAS
    // ============================================
    const actualizarTipos = ($cat, $tipo) => {
        const categoria = $cat.val();
        $tipo.html('<option value="">Seleccione un tipo</option>');
        
        (TIPOS_POR_CATEGORIA[categoria] || []).forEach(tipo => {
            $tipo.append(`<option value="${tipo}">${tipo}</option>`);
        });
    };

    const addPrenda = (containerId, data = null, editable = true) => {
        const $container = $(`#${containerId}`);
        const $template = $(document.getElementById('prendaTemplate').innerHTML);
        
        // Configurar atributos
        ['codigo_prenda', 'nombre', 'categoria', 'tipo', 'precio_costo', 'descripcion'].forEach(attr => {
            $template.find(`.prenda-${attr.replace('_', '-')}`).attr('name', `prendas[${prendaIndex}][${attr}]`);
        });
        
        $template.find('.prenda-number').text(prendaIndex + 1);

        // Llenar datos si existen
        if (data) {
            $template.find('.prenda-codigo').val(data.codigo_prenda || '');
            $template.find('.prenda-nombre').val(data.nombre || '');
            $template.find('.prenda-categoria').val(data.categoria || '');
            $template.find('.prenda-costo').val(data.precio_costo || '');
            $template.find('.prenda-descripcion').val(data.descripcion || '');
            
            if (data.categoria) {
                actualizarTipos($template.find('.prenda-categoria'), $template.find('.prenda-tipo'));
                if (data.tipo) $template.find('.prenda-tipo').val(data.tipo);
            }

            if (!editable) {
                $template.addClass('prenda-existente')
                    .find('input, select, textarea').prop('disabled', true).end()
                    .find('.remove-prenda').remove();
                $template.css('background-color', '#f8f9fa');
            }
        }

        // Eventos
        const $cat = $template.find('.prenda-categoria');
        const $tipo = $template.find('.prenda-tipo');

        $cat.on('change', function() {
            actualizarTipos($cat, $tipo);
        });

        $container.append($template);
        prendaIndex++;

        if (editable) {
            $template.find('.remove-prenda').on('click', function() {
                $template.fadeOut(300, function() {
                    $(this).remove();
                    updateSummary();
                    $(`#${containerId} .prenda-row`).each((i, el) => {
                        $(el).find('.prenda-number').text(i + 1);
                    });
                });
            });

            $template.find('input, select').on('input change', updateSummary);
            aplicarValidacion($template);
        }

        updateSummary();
    };

    const aplicarValidacion = ($row) => {
        const campos = [
            { sel: '.prenda-codigo', tipo: 'codigo' },
            { sel: '.prenda-nombre', tipo: 'nombrePrenda' },
            { sel: '.prenda-costo', tipo: 'precio' }
        ];

        campos.forEach(({ sel, tipo }) => {
            const $campo = $row.find(sel);
            $campo.on('input blur', () => {
                Validations.validateField($campo, Validations.REGEX[tipo], Validations.MESSAGES[tipo]);
                if (sel === '.prenda-costo') updateSummary();
            });
        });
    };

    const updateSummary = () => {
        let total = 0;
        $('.prenda-row').each(function() {
            total += parseFloat($(this).find('.prenda-costo').val()) || 0;
        });
        
        const count = $('.prenda-row').length;
        $('#summaryTotalPrendas, #editSummaryTotalPrendas').text(count);
        $('#summaryMontoTotal, #editSummaryMontoTotal').text(total.toFixed(2));
        $('#montoTotal, #editMontoTotal').val(total.toFixed(2));
    };

    const recopilarPrendas = (selector) => {
        const prendas = [];
        let error = null;

        $(selector).each(function() {
            const $row = $(this);
            const prenda = {
                codigo_prenda: $row.find('.prenda-codigo').val().trim(),
                nombre: $row.find('.prenda-nombre').val().trim(),
                categoria: $row.find('.prenda-categoria').val(),
                tipo: $row.find('.prenda-tipo').val(),
                precio_costo: $row.find('.prenda-costo').val(),
                descripcion: $row.find('.prenda-descripcion').val().trim()
            };

            if (!Validations.REGEX.codigo.test(prenda.codigo_prenda)) {
                error = 'Código inválido (9 dígitos)';
                return false;
            }
            if (!Validations.REGEX.nombrePrenda.test(prenda.nombre)) {
                error = 'Nombre inválido (3-150 caracteres)';
                return false;
            }
            if (!prenda.categoria || !prenda.tipo) {
                error = 'Seleccione categoría y tipo';
                return false;
            }
            if (!Validations.REGEX.precio.test(prenda.precio_costo) || parseFloat(prenda.precio_costo) <= 0) {
                error = 'Precio inválido';
                return false;
            }
            prendas.push(prenda);
        });

        return { prendas, error };
    };

    // ============================================
    // BÚSQUEDA DE PROVEEDORES
    // ============================================
    const setupSupplierSearch = (inputId, resultsId, hiddenId) => {
        let timeout;
        const $input = $(`#${inputId}`);
        const $results = $(`#${resultsId}`);
        const $hidden = $(`#${hiddenId}`);

       $input.on('input', Helpers.debounce(() => {
           const query = $input.val()?.trim() || ''; // evita undefined

           if (query.length < 2) {
               $results.hide();
               $hidden.val('');
               return;
           }
            
            $results.html('<div class="list-group-item"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>').show();
            
           Ajax.get(`${baseUrl}?action=search_supplier`, { search: query })
                .then(data => {
                    if (data.success && data.results?.length) {
                        const html = data.results.map(s => `
                            <button type="button" class="list-group-item list-group-item-action supplier-item"
                                    data-id="${s.proveedor_rif || s.rif}" data-nombre="${s.nombre_empresa}">
                                <strong>${s.nombre_empresa}</strong><br>
                                <small class="text-muted">RIF: ${s.proveedor_rif || s.rif} | ${s.nombre_contacto}</small>
                            </button>
                        `).join('');
                        
                        $results.html(html).find('.supplier-item').on('click', function() {
                            $input.val($(this).data('nombre')).addClass('is-valid');
                            $hidden.val($(this).data('id'));
                            $results.hide();
                        });
                    } else {
                        $results.html('<div class="list-group-item text-muted">Sin resultados</div>');
                    }
                })
                .catch(() => {
                    $results.html('<div class="list-group-item text-danger">Error</div>');
                });
        }, 300));

        $(document).on('click', (e) => {
            if (!$(e.target).closest(`#${inputId}, #${resultsId}`).length) {
                $results.hide();
            }
        });
    };

    // ============================================
    // AGREGAR COMPRA
    // ============================================
    $('#addPurchaseForm').on('submit', function(e) {
        e.preventDefault();
        
        const factura = $('#facturaNumero').val();
        const tracking = $('#tracking').val();
        const proveedor = $('#proveedorId').val();
        
        if (!Validations.REGEX.factura.test(factura)) {
            Helpers.toast('error', 'Factura inválida (8 dígitos)');
            return;
        }
        if (tracking && !Validations.REGEX.factura.test(tracking)) {
            Helpers.toast('error', 'Tracking inválido (8 dígitos)');
            return;
        }
        if (!proveedor) {
            Helpers.toast('error', 'Seleccione un proveedor');
            return;
        }
        
        const { prendas, error } = recopilarPrendas('.prenda-row');
        if (error) {
            Helpers.toast('error', error);
            return;
        }
        if (!prendas.length) {
            Helpers.toast('error', 'Agregue al menos un producto');
            return;
        }
        
        const formData = new FormData(this);
        formData.delete('prendas');
        prendas.forEach((p, i) => {
            Object.keys(p).forEach(k => formData.append(`prendas[${i}][${k}]`, p[k]));
        });
        
        const $btn = $('#btnGuardar');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

        Ajax.post(`${baseUrl}?action=add_ajax`, formData)
            .then(res => {
                if (res.success) {
                    Helpers.toast('success', res.message);
                    $('#addPurchaseModal').modal('hide');
                    this.reset();
                    $('#prendasContainer').empty();
                    reloadPurchases();
                } else {
                    Helpers.toast('error', res.message);
                }
            })
            .catch(err => Helpers.toast('error', err))
            .finally(() => $btn.prop('disabled', false).html(btnText));
    });

    // ============================================
    // VER / EDITAR / ELIMINAR
    // ============================================
    $(document).on('click', '.btn-view', function() {
        const id = $(this).data('id');
        Helpers.showLoading('Cargando detalles...');

        Ajax.get(`${baseUrl}?action=get_purchase_detail`, { compra_id: id })
            .then(data => {
                Helpers.closeLoading(); // ✅ cerrar solo cuando ya tenemos los datos
                if (data.success) {
                    renderPurchaseDetails(data.data);
                } else {
                    Helpers.toast('error', 'No se pudieron cargar los detalles');
                }
            })
            .catch(err => {
                Helpers.closeLoading();
                Helpers.toast('error', err);
            });
    });


    $(document).on('click', '.btn-pdf', function() {
        const id = $(this).data('id');
        window.location.href = `${baseUrl}?action=generate_pdf&compra_id=${id}`;
    });

    // ============================================
// EDITAR COMPRA
// ============================================
$(document).on('click', '.btn-edit', function(e) {
    e.preventDefault();

    const id = $(this).data('id');
    Helpers.showLoading('Cargando compra para editar...');

    Ajax.get(`${baseUrl}?action=get_purchase_detail`, { compra_id: id })
        .then(data => {
            Helpers.closeLoading();

            if (!data.success) {
                Helpers.toast('error', data.message || 'No se pudieron cargar los datos');
                return;
            }

            const compra = data.data.compra || {};
            const prendas = Array.isArray(data.data.prendas) ? data.data.prendas : [];

            // Cargar datos en el formulario de edición
            $('#editCompraId').val(compra.compra_id ?? '');
            $('#editFacturaNumero').val(compra.factura_numero ?? '');
            // CARGAR LA FECHA (input type="date" espera YYYY-MM-DD)
            if (compra.fecha_compra) {
                // Si el backend devuelve fecha en formato 'YYYY-MM-DD' esto sirve directamente.
                $('#editFechaCompra').val(compra.fecha_compra ? compra.fecha_compra.split(' ')[0] : '');
            } else {
                $('#editFechaCompra').val('');
            }
            $('#editTracking').val(compra.tracking || '');
            // Campo visible de búsqueda dentro del modal
            $('#editSearchSupplier').val(compra.nombre_proveedor || '');
            // Hidden que guarda el RIF/id real
            $('#editProveedorId').val(compra.proveedor_rif || '');

            // Limpiar prendas anteriores
            $('#editPrendasContainer').empty();
            prendaIndex = 0;

            // Agregar prendas existentes (NO editables) -> pasa false para que queden bloqueadas
            prendas.forEach(pr => addPrenda('editPrendasContainer', pr, false));

            // Garantizar prendaIndex correcto (por si addPrenda no actualizó como esperas)
            prendaIndex = $('#editPrendasContainer .prenda-row').length;

            // Actualizar resumen visual
            updateSummary();

            // Mostrar modal
            $('#editPurchaseModal').modal('show');

            // Enfocar el input de proveedor para que la UX sea clara (opcional)
            // $('#editSearchSupplier').focus();
        })
        .catch(err => {
            Helpers.closeLoading();
            console.error('Error al cargar compra:', err);
            Helpers.toast('error', 'Error al cargar datos de la compra');
        });
});


$(document).on('click', '.btn-delete', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    
    Helpers.confirmDialog(
        '¿Eliminar compra?',
        'Las prendas también se eliminarán.',
        () => {
            Ajax.post(`${baseUrl}?action=delete_ajax`, { compra_id: id })
                .then(res => {
                    if (res.success) {
                        Helpers.toast('success', 'Eliminado correctamente');
                        reloadPurchases();
                    } else {
                        Helpers.toast('error', res.message);
                    }
                })
                .catch(err => Helpers.toast('error', err));
        },
        'Sí, eliminar'
    );
});

$('#editPurchaseForm').on('submit', function(e) {
    e.preventDefault();

    const { prendas, error } = recopilarPrendas('#editPrendasContainer .prenda-row:not(.prenda-existente)');
    if (error) {
        $('#editPurchaseErrors').removeClass('d-none').text(error);
        return;
    }

    const formData = new FormData(this);
    formData.delete('prendas');

    // ✅ Añadir nuevas prendas
    prendas.forEach((p, i) => {
        Object.keys(p).forEach(k => formData.append(`nuevas_prendas[${i}][${k}]`, p[k]));
    });

    // ✅ Asegurar que se envía la fecha de compra
    const fechaCompra = $('#editFechaCompra').val();
    if (!fechaCompra) {
        $('#editPurchaseErrors').removeClass('d-none').text('Debe ingresar la fecha de compra.');
        return;
    }
    formData.set('fecha_compra', fechaCompra);

    // --- Feedback visual (spinner + bloqueo) ---
    const $btn = $('#btnGuardarEdit');
    $btn.prop('disabled', true);
    $btn.find('.spinner-border').removeClass('d-none');
    $btn.find('.btn-text').text('Guardando...');

Ajax.post(`${baseUrl}?action=edit_ajax`, formData)
    .then(response => {
        $btn.prop('disabled', false);
        $btn.find('.spinner-border').addClass('d-none');
        $btn.find('.btn-text').text('Guardar Cambios');

        if (response.success) {
            Helpers.toast('success', 'Compra actualizada correctamente');
            $('#editPurchaseModal').modal('hide');
            reloadPurchases();
        } else {
            $('#editPurchaseErrors')
                .removeClass('d-none')
                .text(response.message || 'Error al actualizar la compra.');
        }
    })
    .catch(error => {
        $btn.prop('disabled', false);
        $btn.find('.spinner-border').addClass('d-none');
        $btn.find('.btn-text').text('Guardar Cambios');

        let msg = 'Error inesperado al guardar la compra.';

        // ✅ Captura mensaje del servidor si viene en formato JSON
        if (error && error.responseText) {
            try {
                const json = JSON.parse(error.responseText);
                msg = json.message || msg;
            } catch (e) {
                console.error('Respuesta no JSON:', error.responseText);
            }
        }

        // Mostrar mensaje en el contenedor de errores
        $('#editPurchaseErrors')
            .removeClass('d-none')
            .text(msg);

        console.error('Error al guardar compra:', msg);
    });
});



    const renderPurchaseDetails = (data) => {
        const c = data.compra;
        const p = data.prendas;
        
        Swal.fire({
            title: `Compra #${c.factura_numero}`,
            html: `
                <div class="text-start">
                    <div class="row mb-3">
                        <div class="col-6">
                            <p><strong>Proveedor:</strong><br>${c.nombre_proveedor}</p>
                            <p><strong>RIF:</strong> ${c.tipo_rif}-${c.proveedor_rif}</p>
                        </div>
                        <div class="col-6">
                            <p><strong>Fecha:</strong><br>${Helpers.formatDate(c.fecha_compra)}</p>
                            <p><strong>Tracking:</strong> ${c.tracking || 'N/A'}</p>
                        </div>
                    </div>
                    <div class="alert alert-success">
                        <strong>Monto Total:</strong> ${Helpers.formatCurrency(c.monto_total)}
                    </div>
                    <hr>
                    <h6>Prendas (${p.length})</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Código</th><th>Nombre</th><th>Categoría</th><th>Tipo</th><th>P.Costo</th><th>Estado</th></tr>
                            </thead>
                            <tbody>
                                ${p.map(pr => `
                                    <tr>
                                        <td><code>${pr.codigo_prenda}</code></td>
                                        <td>${pr.nombre}</td>
                                        <td>${pr.categoria}</td>
                                        <td>${pr.tipo}</td>
                                        <td>${Helpers.formatCurrency(pr.precio_costo)}</td>
                                        <td>${Helpers.getBadge(pr.estado)}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `,
            width: '900px',
            showConfirmButton: false,
            showCloseButton: true
        });
    };

    // ============================================
    // EVENTOS
    // ============================================
    $('#addPrendaBtn').on('click', () => addPrenda('prendasContainer'));
    $('#addEditPrendaBtn').off('click').on('click', function() {
    addPrenda('editPrendasContainer', null, true);
});



    $('#searchInput').on('input', Helpers.debounce(function() {
        const term = $(this).val().toLowerCase();
        const filtered = allPurchases.filter(p => 
            String(p.factura_numero).includes(term) || 
            p.nombre_proveedor.toLowerCase().includes(term)
        );
        renderPurchases(filtered);
    }, 300));

    $('#addPurchaseModal').on('show.bs.modal', function() {
        $('#addPurchaseForm')[0].reset();
        $('#prendasContainer').empty();
        prendaIndex = 0;
        setTimeout(() => addPrenda('prendasContainer'), 100);
    });


    // ============================================
    // INICIALIZAR
    // ============================================
    initPurchaseTable(); // 🚀 crea y carga la tabla con AJAX
    setupSupplierSearch('searchSupplier', 'supplierResults', 'proveedorId');
});