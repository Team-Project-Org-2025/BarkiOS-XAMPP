import * as Validations from '/BarkiOS/public/assets/js/utils/validation.js';
import * as Helpers from '/BarkiOS/public/assets/js/utils/helpers.js';
import * as Ajax from '/BarkiOS/public/assets/js/utils/ajax-handler.js';

$(document).ready(function() {
    const baseUrl = window.location.pathname;
    let prendaIndex = 0;
    let allPurchases = [];
    let purchasesTable = null;

     const addPurchaseRules = {
        factura_numero: 'factura',      // Coincide con name="factura_numero"
        fecha_compra: 'required',        // Coincide con name="fecha_compra"
        proveedor_rif: 'select'          // Coincide con name="proveedor_rif"
    };
    
    const editPurchaseRules = {
        factura_numero: 'factura',      // Coincide con name="factura_numero" del edit
        fecha_compra: 'required',        // Coincide con name="fecha_compra" del edit
        proveedor_rif: 'select'          // Coincide con name="proveedor_rif" del edit
    };

    // Tipos por categoría
    const TIPOS_POR_CATEGORIA = {
        Formal: ["Vestido", "Camisa", "Pantalon", "Chaqueta"],
        Casual: ["Blusa", "Pantalon", "Short", "Falda"],
        Deportivo: ["Short", "Falda", "Chaqueta"],
        Invierno: ["Chaqueta", "Pantalon"],
        Verano: ["Vestido", "Short", "Blusa"],
        Fiesta: ["Vestido", "Falda", "Blusa", "Enterizo"]
    };

    const setupPurchaseValidation = () => {
        // Validación en tiempo real
        Validations.setupRealTimeValidation($('#addPurchaseForm'), addPurchaseRules);
        Validations.setupRealTimeValidation($('#editPurchaseForm'), editPurchaseRules);
        
        // Validación para tracking (opcional)
        $('#tracking, #editTracking').on('input blur', function() {
            const val = $(this).val().trim();
            if (val === '') {
                $(this).removeClass('is-valid is-invalid');
                $(this).siblings('.invalid-feedback').remove();
                return;
            }
            Validations.validateField(
                $(this), 
                Validations.REGEX.factura, 
                Validations.MESSAGES.tracking
            );
        });
        
        // Validación para fecha (no puede ser futura)
        $('#fecha_compra, #editFechaCompra').on('change blur', function() {
            const fecha = new Date($(this).val());
            const hoy = new Date();
            hoy.setHours(0, 0, 0, 0);
            
            if (!$(this).val()) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                $(this).siblings('.invalid-feedback').remove();
                $(this).after('<div class="invalid-feedback">La fecha es requerida</div>');
                return;
            }
            
            if (fecha > hoy) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                $(this).siblings('.invalid-feedback').remove();
                $(this).after('<div class="invalid-feedback">La fecha no puede ser futura</div>');
                return;
            }
            
            $(this).addClass('is-valid').removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        });
    };

    // ==================== SUBMIT AGREGAR ====================
    $('#addPurchaseForm').on('submit', function(e) {
        e.preventDefault();
        
        console.log('🔍 Validando formulario...'); // Debug
        
        // Validar formulario
        if (!Validations.validateForm($(this), addPurchaseRules)) {
            Helpers.toast('warning', 'Por favor corrija los campos resaltados');
            return;
        }
        
        console.log('✅ Validación principal pasada'); // Debug
        
        // Validaciones adicionales
        const tracking = $('#tracking').val().trim();
        if (tracking && !Validations.REGEX.factura.test(tracking)) {
            Helpers.toast('error', 'Tracking inválido (8 dígitos)');
            $('#tracking').addClass('is-invalid').focus();
            return;
        }
        
        const fecha = new Date($('#fecha_compra').val());
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        if (fecha > hoy) {
            Helpers.toast('error', 'La fecha no puede ser futura');
            $('#fecha_compra').focus();
            return;
        }
        
        // Aquí irían tus validaciones de prendas
        // const { prendas, error } = recopilarPrendas('.prenda-row');
        // if (error) { return; }
        
        console.log('✅ Todas las validaciones pasadas, enviando...'); // Debug
        
        // Enviar
        const formData = new FormData(this);
        const $btn = $('#btnGuardar');
        const btnText = $btn.find('.btn-text').html();
        
        $btn.prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');
        $btn.find('.btn-text').html('Guardando...');
        
        Ajax.post(`${baseUrl}?action=add_ajax`, formData)
            .then(res => {
                if (res.success) {
                    Helpers.toast('success', res.message || 'Compra guardada correctamente');
                    $('#addPurchaseModal').modal('hide');
                    // Recargar tabla
                } else {
                    Helpers.toast('error', res.message || 'Error al guardar');
                }
            })
            .catch(err => {
                console.error('Error:', err);
                Helpers.toast('error', err || 'Error inesperado');
            })
            .finally(() => {
                $btn.prop('disabled', false);
                $btn.find('.spinner-border').addClass('d-none');
                $btn.find('.btn-text').html(btnText);
            });
    });

    // ==================== SUBMIT EDITAR ====================
    $('#editPurchaseForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validar formulario
        if (!Validations.validateForm($(this), editPurchaseRules)) {
            Helpers.toast('warning', 'Por favor corrija los campos resaltados');
            return;
        }
        
        // Validaciones adicionales
        const tracking = $('#editTracking').val().trim();
        if (tracking && !Validations.REGEX.factura.test(tracking)) {
            Helpers.toast('error', 'Tracking inválido (8 dígitos)');
            return;
        }
        
        const fecha = new Date($('#editFechaCompra').val());
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        if (fecha > hoy) {
            Helpers.toast('error', 'La fecha no puede ser futura');
            return;
        }
        
        // Enviar
        const formData = new FormData(this);
        const $btn = $('#btnGuardarEdit');
        
        $btn.prop('disabled', true);
        $btn.find('.spinner-border').removeClass('d-none');
        
        Ajax.post(`${baseUrl}?action=edit_ajax`, formData)
            .then(response => {
                if (response.success) {
                    Helpers.toast('success', 'Compra actualizada correctamente');
                    $('#editPurchaseModal').modal('hide');
                } else {
                    Helpers.toast('error', response.message);
                }
            })
            .catch(error => {
                Helpers.toast('error', 'Error al actualizar');
            })
            .finally(() => {
                $btn.prop('disabled', false);
                $btn.find('.spinner-border').addClass('d-none');
            });
    });

    // ==================== LIMPIAR AL CERRAR ====================
    $('#addPurchaseModal, #editPurchaseModal').on('hidden.bs.modal', function() {
        const $form = $(this).find('form');
        $form[0].reset();
        Validations.clearValidation($form);
        $('#searchSupplier, #editSearchSupplier').val('').removeClass('is-valid is-invalid');
    });

    $('#addPurchaseModal').on('show.bs.modal', function() {
        const $form = $('#addPurchaseForm');
        $form[0].reset();
        Validations.clearValidation($form);
        $('#searchSupplier').val('').removeClass('is-valid is-invalid');
        console.log('🟢 Modal abierto y limpiado'); // Debug
    });

    // ==================== INVOCAR ====================
    console.log('🚀 Inicializando validaciones...'); // Debug
    setupPurchaseValidation();
    console.log('✅ Validaciones configuradas'); // Debug

    const reloadPurchases = () => {
        if (purchasesTable) {

            SkeletonHelper.showTableSkeleton('purchaseTable', 5, 6);
            purchasesTable.ajax.reload(() => updateStats(), false);
        }
    };
    
    const initPurchaseTable = () => {
        SkeletonHelper.showTableSkeleton('purchaseTable', 5, 6);
        purchasesTable = $('#purchaseTable').DataTable({
            ajax: {
                url: `${baseUrl}?action=get_purchases`,
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataSrc: data => {
                    if (data.success) {
                        allPurchases = data.data;
                        updateStats();
                        return data.data;
                    } else {
                        allPurchases = [];
                        updateStats();
                        return [];
                    }
                }
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
            order: [[2, 'desc']],
            pageLength: 10,
            responsive: true,
            autoWidth: false,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"},
            dom: '<"d-flex justify-content-between align-items-center mb-2"lfB>tip',
            buttons: [{
            text: '<i class="fas fa-sync-alt"></i> Actualizar',
            className: 'btn btn-outline-secondary btn-sm',
            action: () => {
                SkeletonHelper.showTableSkeleton('purchaseTable', 5, 6);
                purchasesTable.ajax.reload(() => updateStats(), false);
            }
        }]
        });
    };

    //Actualizar estadisticas
    const updateStats = () => {
        let totalPagado = 0, totalPendiente = 0, montoTotal = 0;
        let totalCompras = allPurchases.length;
        let valorInventario = 0;

        allPurchases.forEach(p => {
            totalPagado += parseFloat(p.total_pagado || 0);
            totalPendiente += parseFloat(p.saldo_pendiente || 0);
            montoTotal += parseFloat(p.monto_total || 0);

            if (p.valor_inventario) {
                valorInventario += parseFloat(p.valor_inventario);
            } else if (p.monto_total && p.prendas_disponibles) {
                valorInventario += (parseFloat(p.monto_total) / (p.total_prendas || 1)) * (p.prendas_disponibles || 0);
            }
        });

        // Actualizar las estadísticas visuales
        $('#statTotalCompras').text(totalCompras);
        $('#statValorInventario').text(Helpers.formatCurrency(valorInventario));

        $('#statMontoPagado').text(`Pagado: ${Helpers.formatCurrency(totalPagado)}`);
        $('#statSaldoPendiente').text(Helpers.formatCurrency(totalPendiente));
        $('#statMontoTotal').text(Helpers.formatCurrency(montoTotal));

        const porcentaje = montoTotal > 0 ? (totalPagado / montoTotal) * 100 : 0;
        const offset = 220 - (220 * porcentaje / 100);
        $('#progressBar').css('stroke-dashoffset', offset);
        $('#progressPercent').text(Math.round(porcentaje) + '%');
    };

    //Gestion de prendas
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

    //Busqueda de proveedor
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

    //Agregar compra
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

    //Ver, editar, eliminar
    $(document).on('click', '.btn-view', function() {
        const id = $(this).data('id');
        
        if (!$('#viewPurchaseModal').length) {
            $('body').append(`
                <div class="modal fade" id="viewPurchaseModal" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Detalles de Compra</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body" id="purchaseDetailsContent"></div>
                        </div>
                    </div>
                </div>
            `);
        }
        
        $('#viewPurchaseModal').modal('show');
        
        // ✅ MOSTRAR SKELETON
        SkeletonHelper.showModalSkeleton('purchaseDetailsContent');

        Ajax.get(`${baseUrl}?action=get_purchase_detail`, { compra_id: id })
            .then(data => {
                if (data.success) {
                    const html = renderPurchaseDetails(data.data);
                    // ✅ OCULTAR SKELETON CON ANIMACIÓN
                    SkeletonHelper.hideModalSkeleton('purchaseDetailsContent', html);
                } else {
                    $('#purchaseDetailsContent').html('<div class="alert alert-danger">No se pudieron cargar los detalles</div>');
                }
            })
            .catch(err => {
                $('#purchaseDetailsContent').html(`<div class="alert alert-danger">${Helpers.escapeHtml(err)}</div>`);
            });
    });

    $(document).on('click', '.btn-pdf', function() {
        const id = $(this).data('id');
        window.location.href = `${baseUrl}?action=generate_pdf&compra_id=${id}`;
    });

    $(document).on('click', '.btn-edit', function(e) {
        e.preventDefault();

        const id = $(this).data('id');
        $('#editPurchaseModal').modal('show');
        $('#editPrendasContainer').html(SkeletonHelper.createFormSkeleton(4));

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

                if (compra.fecha_compra) {
  
                    $('#editFechaCompra').val(compra.fecha_compra ? compra.fecha_compra.split(' ')[0] : '');
                } else {
                    $('#editFechaCompra').val('');
                }
                $('#editTracking').val(compra.tracking || '');

                $('#editSearchSupplier').val(compra.nombre_proveedor || '');

                $('#editProveedorId').val(compra.proveedor_rif || '');

                $('#editPrendasContainer').empty();
                prendaIndex = 0;

                prendas.forEach(pr => addPrenda('editPrendasContainer', pr, false));

                prendaIndex = $('#editPrendasContainer .prenda-row').length;

                updateSummary();

                $('#editPurchaseModal').modal('show');

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

        // Añadir nuevas prendas
        prendas.forEach((p, i) => {
            Object.keys(p).forEach(k => formData.append(`nuevas_prendas[${i}][${k}]`, p[k]));
        });


        const fechaCompra = $('#editFechaCompra').val();
        if (!fechaCompra) {
            $('#editPurchaseErrors').removeClass('d-none').text('Debe ingresar la fecha de compra.');
            return;
        }
        formData.set('fecha_compra', fechaCompra);

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

                if (error && error.responseText) {
                    try {
                        const json = JSON.parse(error.responseText);
                        msg = json.message || msg;
                    } catch (e) {
                        console.error('Respuesta no JSON:', error.responseText);
                    }
                }

                $('#editPurchaseErrors')
                    .removeClass('d-none')
                    .text(msg);

                console.error('Error al guardar compra:', msg);
            });
        });



    const renderPurchaseDetails = (data) => {
        const c = data.compra;
        const p = data.prendas;
        
        return `
            <div class="text-start">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h5>Compra #${c.factura_numero}</h5>
                        <p class="mb-1"><strong>Proveedor:</strong> ${c.nombre_proveedor}</p>
                        <p class="mb-1"><strong>RIF:</strong> ${c.tipo_rif}-${c.proveedor_rif}</p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p class="mb-1"><strong>Fecha:</strong> ${Helpers.formatDate(c.fecha_compra)}</p>
                        <p class="mb-1"><strong>Tracking:</strong> ${c.tracking || 'N/A'}</p>
                    </div>
                </div>
                <div class="alert alert-success">
                    <strong>Monto Total:</strong> ${Helpers.formatCurrency(c.monto_total)}
                </div>
                <hr>
                <h6 class="mb-3"><i class="fas fa-box me-2"></i>Prendas (${p.length})</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Tipo</th>
                                <th class="text-end">P.Costo</th>
                                <th class="text-center">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${p.map(pr => `
                                <tr>
                                    <td><code>${pr.codigo_prenda}</code></td>
                                    <td>${pr.nombre}</td>
                                    <td><span class="badge bg-secondary">${pr.categoria}</span></td>
                                    <td><span class="badge bg-info">${pr.tipo}</span></td>
                                    <td class="text-end"><strong>${Helpers.formatCurrency(pr.precio_costo)}</strong></td>
                                    <td class="text-center">${Helpers.getBadge(pr.estado)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    };

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

    SkeletonHelper.showTableSkeleton('purchaseTable', 5, 6);

    initPurchaseTable();
    setupSupplierSearch('searchSupplier', 'supplierResults', 'proveedorId');
});

