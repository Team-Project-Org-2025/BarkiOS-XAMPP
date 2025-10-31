import * as Validations from '/BarkiOS/public/assets/js/utils/validation.js';
import * as Helpers from '/BarkiOS/public/assets/js/utils/helpers.js';
import * as Ajax from '/BarkiOS/public/assets/js/utils/ajax-handler.js';

$(document).ready(function() {
    const baseUrl = '/BarkiOS/admin/sale';
    let salesTable = null;
    let clients = [], employees = [], products = [], cart = [], pid = 0;
    const IVA_DEFAULT = 16.00;

    const initDataTable = () => {
        salesTable = $('#salesTable').DataTable({
            ajax: {
                url: `${baseUrl}?action=get_sales`,
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataSrc: (json) => {
                    if (!json.success) {
                        Helpers.toast('warning', json.message || 'No se pudieron cargar las ventas');
                        return [];
                    }
                    updateStats(json.sales || []);
                    return json.sales || [];
                }
            },
            columns: [
                { data: 'venta_id', className: 'text-center d-none d-md-table-cell' },
                { data: 'referencia', render: d => `<code>${Helpers.escapeHtml(d ?? '')}</code>` },
                { data: 'nombre_cliente', className: 'd-none d-lg-table-cell' },
                { data: 'nombre_empleado', className: 'd-none d-xl-table-cell' },
                { data: 'fecha', className: 'd-none d-md-table-cell', render: d => Helpers.formatDate(d, true) },
                { data: 'monto_total', className: 'text-end', render: d => Helpers.formatCurrency(parseFloat(d) || 0) },
                {
                    data: 'estado_venta',
                    className: 'text-center d-none d-sm-table-cell',
                    render: estado => {
                        const color = estado === 'Completada' ? 'success' : estado === 'Pendiente' ? 'warning' : 'secondary';
                        return `<span class="badge bg-${color}">${Helpers.escapeHtml(estado)}</span>`;
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: (data, type, row) => `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-info btn-view" data-id="${row.venta_id}"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-outline-success btn-pdf" data-id="${row.venta_id}"><i class="fas fa-file-pdf"></i></button>
                            ${(row.estado_venta || '').toLowerCase() !== 'cancelada' ? `
                                <button class="btn btn-outline-danger btn-cancel" data-id="${row.venta_id}"><i class="fas fa-ban"></i></button>
                            ` : ''}
                        </div>
                    `
                }
            ],
            pageLength: 10,
            responsive: true,
            autoWidth: false,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
            dom: '<"d-flex justify-content-between align-items-center mb-2"lfB>tip',
            buttons: [{
                text: '<i class="fas fa-sync-alt"></i> Actualizar',
                className: 'btn btn-outline-secondary btn-sm',
                action: () => salesTable.ajax.reload(null, false)
            }]
        });
    };

    const updateStats = (sales) => {
        const totalSales = sales.length;
        const revenue = sales.reduce((acc, s) => acc + (parseFloat(s.monto_total ?? 0) || 0), 0);
        const pending = sales.reduce((acc, s) => acc + (parseFloat(s.saldo_pendiente || 0) || 0), 0);
        const completed = sales.filter(s => (s.estado_venta || '').toLowerCase() === 'completada').length;

        $('#totalSales').text(totalSales);
        $('#totalRevenue').text(Helpers.formatCurrency(revenue));
        $('#totalPending').text(Helpers.formatCurrency(pending));
        $('#completedSales').text(completed);
    };

    //Cargar datos iniciales
    const loadClients = () => {
        Ajax.get(`${baseUrl}?action=get_clients`)
            .then(r => {
                if (r?.success) {
                    clients = r.clients || [];
                    const opts = clients.map(c => 
                        `<option value="${Helpers.escapeHtml(c.cliente_ced)}" data-tipo="${Helpers.escapeHtml(c.tipo)}">
                            ${Helpers.escapeHtml(c.nombre_cliente)} (${Helpers.escapeHtml(c.cliente_ced)}) ${c.tipo === 'vip' ? '⭐' : ''}
                        </option>`
                    ).join('');
                    $('#add_cliente').html('<option value="">Seleccione...</option>' + opts);
                }
            });
    };

    const loadEmployees = () => {
        Ajax.get(`${baseUrl}?action=get_employees`)
            .then(r => {
                if (r?.success) {
                    employees = r.employees || [];
                    const opts = employees.map(e => 
                        `<option value="${Helpers.escapeHtml(e.empleado_ced)}">
                            ${Helpers.escapeHtml(e.nombre)} - ${Helpers.escapeHtml(e.cargo || '')}
                        </option>`
                    ).join('');
                    $('#add_empleado').html('<option value="">Seleccione...</option>' + opts);
                }
            });
    };

    const loadProducts = () => {
        Ajax.get(`${baseUrl}?action=get_products`)
            .then(r => {
                if (r?.success) {
                    products = r.products || [];
                    $('#productsCount').text(`${products.length} disponibles`).toggleClass('text-danger', products.length === 0);
                }
            });
    };


    const addProductRow = () => {
        if (!products || products.length === 0) {
            Helpers.toast('info', 'No hay productos disponibles');
            return;
        }

        $('#noProductsAlert').hide();
        pid++;
        const rowId = `prod_${pid}`;

        const productSelect = products.map(p => {
            const price = parseFloat(p.precio ?? 0);
            const codigo = p.codigo_prenda ?? '';
            const name = p.nombre ?? `ID:${p.prenda_id || ''}`;
            return `<option value="${Helpers.escapeHtml(codigo)}" data-price="${price}" data-name="${Helpers.escapeHtml(name)}">
                ${Helpers.escapeHtml(codigo)} - ${Helpers.escapeHtml(name)} - ${Helpers.formatCurrency(price)}
            </option>`;
        }).join('');

        const html = `
            <div class="product-row mb-3 border rounded p-3" id="${rowId}">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-6">
                        <label class="form-label small">Producto</label>
                        <select class="form-select form-select-sm product-select" data-row="${rowId}">
                            <option value="">Seleccione...</option>
                            ${productSelect}
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small">Precio</label>
                        <input type="number" step="0.01" class="form-control form-control-sm product-price" data-row="${rowId}" readonly>
                    </div>
                    <div class="col-4 col-md-2">
                        <label class="form-label small">Subtotal</label>
                        <div class="fw-bold text-primary product-subtotal" data-row="${rowId}">${Helpers.formatCurrency(0)}</div>
                    </div>
                    <div class="col-2 col-md-1 text-end">
                        <label class="form-label small d-block">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-danger remove-product" data-row="${rowId}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" class="product-code" data-row="${rowId}">
            </div>
        `;

        $('#productsContainer').append(html);

        $(`#${rowId} .product-select`).on('change', function() {
            const r = $(this).data('row');
            const codigo = $(this).val();
            const $opt = $(this).find('option:selected');
            const price = parseFloat($opt.data('price') || 0);
            const name = $opt.data('name') || '';
            
            if (codigo && cart.some(c => c.codigo_prenda === codigo && c.id !== r)) {
                Helpers.toast('warning', `El producto ${codigo} ya está agregado`);
                $(this).val('');
                updateProductRow(r, '', '', 0);
                return;
            }

            updateProductRow(r, codigo, name, price);
        });

        $(`#${rowId} .remove-product`).on('click', function() {
            removeProductRow($(this).data('row'));
        });

        calcTotals();
    };

    const updateProductRow = (rowId, codigo, name, price) => {
        const item = { id: rowId, codigo_prenda: codigo, name: name, price: price, subtotal: price };
        const existingIndex = cart.findIndex(c => c.id === rowId);
        
        if (existingIndex >= 0) {
            cart[existingIndex] = item;
        } else {
            cart.push(item);
        }

        $(`#${rowId} .product-code`).val(codigo);
        $(`#${rowId} .product-price`).val(price ? price.toFixed(2) : '');
        $(`#${rowId} .product-subtotal`).text(Helpers.formatCurrency(price));
        calcTotals();
    };

    const removeProductRow = (rowId) => {
        cart = cart.filter(c => c.id !== rowId);
        $(`#${rowId}`).fadeOut(200, function() { $(this).remove(); });
        if (cart.length === 0) $('#noProductsAlert').show();
        calcTotals();
    };

    const calcTotals = () => {
        const subtotal = cart.reduce((acc, i) => acc + (parseFloat(i.subtotal || 0) || 0), 0);
        const ivaPct = parseFloat($('#add_iva').val() || IVA_DEFAULT);
        const ivaAmount = subtotal * (ivaPct / 100);
        const total = subtotal + ivaAmount;

        $('#summary_subtotal').text(Helpers.formatCurrency(subtotal));
        $('#summary_iva').text(Helpers.formatCurrency(ivaAmount));
        $('#summary_total').text(Helpers.formatCurrency(total));
        
        if (typeof DOLAR_BCV_RATE !== 'undefined') {
            const totalBs = total * DOLAR_BCV_RATE;
            $('#summary_total_bs').text(Helpers.formatCurrencyBs(totalBs));
        }
        
        $('#iva_percentage').text(ivaPct.toFixed(2));
    };

    //Control de fecha de vencimiento
    const $tipoVentaSelect = $('[name="tipo_venta"]');
    const $clienteSelect = $('#add_cliente');
    const $fechaVencimientoGroup = $('#fechaVencimientoGroup');
    const $fechaVencimientoInput = $('#add_fecha_vencimiento');

    const setMinDate = () => {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        $fechaVencimientoInput.attr('min', tomorrow.toISOString().split('T')[0]);
    };

    $tipoVentaSelect.on('change', function() {
        const tipo = $(this).val();
        const clienteCed = $clienteSelect.val();
        
        if (tipo === 'credito') {
            if (!clienteCed) {
                Helpers.toast('warning', 'Seleccione primero un cliente');
                $(this).val('contado');
                return;
            }

            const $selectedOption = $clienteSelect.find('option:selected');
            const tipoCliente = $selectedOption.data('tipo');
            
            if (tipoCliente === 'vip') {
                $fechaVencimientoGroup.show();
                $fechaVencimientoInput.prop('required', true);
                setMinDate();
                Helpers.toast('info', 'Debe seleccionar una fecha de vencimiento');
            } else {
                Helpers.toast('warning', 'Solo clientes VIP pueden comprar a crédito');
                $(this).val('contado');
                $fechaVencimientoGroup.hide();
                $fechaVencimientoInput.prop('required', false).val('');
            }
        } else {
            $fechaVencimientoGroup.hide();
            $fechaVencimientoInput.prop('required', false).val('');
        }
    });

    $clienteSelect.on('change', function() {
        const tipo = $tipoVentaSelect.val();
        if (tipo === 'credito') {
            $tipoVentaSelect.trigger('change');
        }
    });

    $fechaVencimientoInput.on('change', function() {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate <= today) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            Helpers.toast('error', 'La fecha debe ser posterior a hoy');
        } else {
            $(this).addClass('is-valid').removeClass('is-invalid');
        }
    });

    //Guardar
    $('#addSaleForm').on('submit', function(e) {
        e.preventDefault();

        const cliente = $clienteSelect.val();
        const empleado = $('#add_empleado').val();
        const tipo = $tipoVentaSelect.val();
        const referencia = $('#add_referencia').val().trim();

        if (!cliente || !empleado) {
            Helpers.toast('error', 'Seleccione cliente y vendedor');
            return;
        }

        //Validar
        if (referencia && !Validations.REGEX.referenciaVenta.test(referencia)) {
            Helpers.toast('error', 'Referencia inválida (máx 15 caracteres, solo letras, números y guión)');
            $('#add_referencia').addClass('is-invalid').focus();
            return;
        }

        if (cart.length === 0) {
            Helpers.toast('error', 'Agregue al menos un producto');
            return;
        }

        if (tipo === 'credito') {
            const $opt = $clienteSelect.find('option:selected');
            const tipoCliente = $opt.data('tipo');
            
            if (tipoCliente !== 'vip') {
                Helpers.toast('error', 'Solo clientes VIP pueden comprar a crédito');
                return;
            }

            const fechaVencimiento = $fechaVencimientoInput.val();
            if (!fechaVencimiento) {
                Helpers.toast('error', 'Debe seleccionar una fecha de vencimiento');
                $fechaVencimientoInput.focus();
                return;
            }

            const selectedDate = new Date(fechaVencimiento);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate <= today) {
                Helpers.toast('error', 'La fecha debe ser posterior a hoy');
                $fechaVencimientoInput.focus();
                return;
            }
        }

        const productosPayload = cart.map(i => ({
            codigo_prenda: i.codigo_prenda,
            precio_unitario: i.price
        }));

        const data = {
            cliente_ced: cliente,
            empleado_ced: empleado,
            tipo_venta: tipo,
            referencia: referencia,
            iva_porcentaje: $('#add_iva').val() || IVA_DEFAULT,
            observaciones: $('[name="observaciones"]').val() || '',
            productos: JSON.stringify(productosPayload)
        };

        if (tipo === 'credito') {
            data.fecha_vencimiento = $fechaVencimientoInput.val();
        }

        const $btn = $(this).find('button[type="submit"]');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

        Ajax.post(`${baseUrl}?action=add_sale`, data)
            .then(r => {
                if (r?.success) {
                    Helpers.toast('success', r.message || 'Venta registrada');
                    resetSaleForm();
                    $('#addSaleModal').modal('hide');
                    salesTable.ajax.reload(null, false);
                    loadProducts();
                } else {
                    Helpers.toast('error', r?.message || 'Error al guardar venta');
                }
            })
            .catch(msg => Helpers.toast('error', msg))
            .finally(() => $btn.prop('disabled', false).html(btnText));
    });

    const resetSaleForm = () => {
        $('#addSaleForm')[0].reset();
        $('#productsContainer').empty();
        cart = [];
        pid = 0;
        $('#noProductsAlert').show();
        $fechaVencimientoGroup.hide();
        $fechaVencimientoInput.prop('required', false).val('');
        calcTotals();
    };

    //Ver detalles
    $(document).on('click', '.btn-view', function() {
        const id = $(this).data('id');
        if (!id) return;
        
        $('#saleDetailsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando...</p></div>');
        $('#viewSaleModal').modal('show');

        Ajax.get(`${baseUrl}?action=get_by_id&id=${encodeURIComponent(id)}`)
            .then(r => {
                if (r?.success && r.venta) renderSaleDetails(r.venta);
                else $('#saleDetailsContent').html('<p class="text-center text-muted">No se encontraron detalles</p>');
            })
            .catch(msg => $('#saleDetailsContent').html(`<p class="text-center text-muted">${Helpers.escapeHtml(msg)}</p>`));
    });

    const renderSaleDetails = (s) => {
        const subtotal = parseFloat(s.monto_subtotal ?? s.subtotal ?? 0);
        const iva = parseFloat(s.monto_iva ?? s.iva ?? 0);
        const total = parseFloat(s.monto_total ?? s.total ?? 0);
        const pagado = parseFloat(s.total_pagado ?? s.pagado ?? 0);
        const saldo = parseFloat(s.saldo_pendiente ?? s.saldo ?? 0);

        let html = `
            <div class="text-end mb-3">
                <button class="btn btn-sm btn-success btn-pdf" data-id="${s.venta_id ?? s.id}">
                    <i class="fas fa-file-pdf me-1"></i> Descargar PDF
                </button>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Venta #${Helpers.escapeHtml(s.venta_id ?? '')}</h5>
                    <p class="mb-1"><strong>Referencia:</strong> <code>${Helpers.escapeHtml(s.referencia ?? 'N/A')}</code></p>
                    <p class="mb-1"><strong>Estado:</strong> ${Helpers.getBadge(s.estado_venta ?? '')}</p>
                    <p class="mb-1"><strong>Tipo:</strong> <span class="badge bg-info">${Helpers.escapeHtml(s.tipo_venta ?? '')}</span></p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1"><strong>Fecha:</strong> ${Helpers.formatDate(s.fecha, true)}</p>
                    <p class="mb-1"><strong>Cliente:</strong> ${Helpers.escapeHtml(s.nombre_cliente ?? '')}</p>
                    <p class="mb-1"><strong>Vendedor:</strong> ${Helpers.escapeHtml(s.nombre_empleado ?? '')}</p>
                </div>
            </div>
            <h6 class="border-bottom pb-2">Productos</h6>
            <table class="table table-sm">
                <thead>
                    <tr><th>Código</th><th>Producto</th><th>Tipo</th><th>Categoría</th><th class="text-end">Precio</th></tr>
                </thead>
                <tbody>
                    ${(s.prendas ?? s.items ?? []).map(it => {
                        const codigo = it.codigo_prenda ?? it.codigo ?? 'N/A';
                        const name = it.nombre_prenda ?? it.nombre ?? '';
                        const tipo = it.tipo ?? it.tipo_prenda ?? 'N/A';
                        const categoria = it.categoria ?? it.categoria_prenda ?? '';
                        const precio = parseFloat(it.precio_unitario ?? it.subtotal ?? it.precio ?? 0);

                        return `
                            <tr>
                                <td><code>${Helpers.escapeHtml(codigo)}</code></td>
                                <td>${Helpers.escapeHtml(name)}</td>
                                <td>${Helpers.escapeHtml(tipo)}</td>
                                <td><small class="text-muted">${Helpers.escapeHtml(categoria)}</small></td>
                                <td class="text-end">${Helpers.formatCurrency(precio)}</td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><td>Subtotal:</td><td class="text-end">${Helpers.formatCurrency(subtotal)}</td></tr>
                        <tr><td>IVA (${s.iva_porcentaje ?? 16}%):</td><td class="text-end">${Helpers.formatCurrency(iva)}</td></tr>
                        <tr class="fw-bold"><td>Total:</td><td class="text-end">${Helpers.formatCurrency(total)}</td></tr>
                        ${pagado > 0 ? `<tr class="text-success"><td>Pagado:</td><td class="text-end">${Helpers.formatCurrency(pagado)}</td></tr>` : ''}
                        ${saldo > 0 ? `<tr class="text-danger"><td>Saldo:</td><td class="text-end">${Helpers.formatCurrency(saldo)}</td></tr>` : ''}
                    </table>
                </div>
            </div>
        `;

        $('#saleDetailsContent').html(html);
    };

    //Anular venta
    $(document).on('click', '.btn-cancel', function() {
        const ventaId = $(this).data('id');
        if (!ventaId) return;
        
        Helpers.confirmDialog(
            '¿Anular venta?',
            'Esto liberará las prendas y marcará la venta como anulada.',
            () => {
                Ajax.post(`${baseUrl}?action=cancel_sale`, { venta_id: ventaId })
                    .then(r => {
                        if (r?.success) {
                            Helpers.toast('success', r.message || 'Venta anulada');
                            salesTable.ajax.reload(null, false);
                            loadProducts();
                        } else {
                            Helpers.toast('error', r?.message || 'Error al anular venta');
                        }
                    })
                    .catch(msg => Helpers.toast('error', msg));
            },
            'Sí, anular'
        );
    });

    //Generar pdf
    $(document).on('click', '.btn-pdf', function() {
        const ventaId = $(this).data('id');
        if (!ventaId) return;
        window.open(`${baseUrl}?action=generate_pdf&venta_id=${ventaId}`, '_blank');
    });

    //Busqueda
    $('#searchInput').on('keyup', Helpers.debounce(function() {
        salesTable.search($(this).val()).draw();
    }, 300));

    //Eventos
    $('#btnAddProduct').on('click', addProductRow);
    
    //Validacion
    $('#add_referencia').on('input blur', function() {
        const val = $(this).val().trim();
        if (val === '') {
            $(this).removeClass('is-valid is-invalid');
            return;
        }
        
        if (Validations.REGEX.referenciaVenta.test(val)) {
            $(this).addClass('is-valid').removeClass('is-invalid');
        } else {
            $(this).addClass('is-invalid').removeClass('is-valid');
        }
    });
    
    $('#add_iva').on('input', function() {
        const val = parseFloat($(this).val());
        $(this).toggleClass('is-invalid', isNaN(val) || val < 0 || val > 100);
        $(this).toggleClass('is-valid', !isNaN(val) && val >= 0 && val <= 100);
        calcTotals();
    });

    $('#addSaleModal, #viewSaleModal').on('hidden.bs.modal', function() {
        if ($(this).attr('id') === 'addSaleModal') {
            resetSaleForm();
        }
    });

    initDataTable();
    loadClients();
    loadEmployees();
    loadProducts();
    setMinDate();
    $fechaVencimientoGroup.hide();
    $fechaVencimientoInput.prop('required', false);
});