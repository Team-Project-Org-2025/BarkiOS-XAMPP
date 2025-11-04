import * as Validations from '/BarkiOS/public/assets/js/utils/validation.js';
import * as Helpers from '/BarkiOS/public/assets/js/utils/helpers.js';
import * as Ajax from '/BarkiOS/public/assets/js/utils/ajax-handler.js';

$(document).ready(function() {
    const baseUrl = '/BarkiOS/admin/sale';
    let salesTable = null;
    let cart = [], pid = 0;
    const IVA_DEFAULT = 16.00;

    // ==================== INICIALIZACIÓN ====================
    
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

    // ==================== BUSCADORES ====================
    
    const setupClientSearch = () => {
        let timeout;
        $('#searchClient').on('input', function() {
            clearTimeout(timeout);
            const query = $(this).val().trim();
            
            if (query.length < 2) {
                $('#clientResults').hide();
                $('#add_cliente').val('');
                $('#clientTypeIndicator').text('');
                return;
            }
            
            timeout = setTimeout(() => {
                $('#clientResults').html('<div class="list-group-item"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>').show();
                
                Ajax.get(`${baseUrl}?action=search_clients`, { search: query })
                    .then(data => {
                        if (data.success && data.results?.length) {
                            const html = data.results.map(c => `
                                <button type="button" class="list-group-item list-group-item-action client-item"
                                        data-id="${c.cliente_ced}" 
                                        data-nombre="${Helpers.escapeHtml(c.nombre_cliente)}"
                                        data-tipo="${c.tipo}">
                                    <strong>${Helpers.escapeHtml(c.nombre_cliente)}</strong> ${c.tipo === 'vip' ? '<i class="fas fa-star text-warning"></i>' : ''}
                                    <br>
                                    <small class="text-muted">Cédula: ${c.cliente_ced} ${c.telefono ? '| ' + c.telefono : ''}</small>
                                </button>
                            `).join('');
                            
                            $('#clientResults').html(html);
                            
                            $('.client-item').on('click', function() {
                                const nombre = $(this).data('nombre');
                                const id = $(this).data('id');
                                const tipo = $(this).data('tipo');
                                
                                $('#searchClient').val(nombre).addClass('is-valid');
                                $('#add_cliente').val(id).attr('data-tipo', tipo);
                                $('#clientResults').hide();
                                
                                if (tipo === 'vip') {
                                    $('#clientTypeIndicator').html('<i class="fas fa-star text-warning"></i> Cliente VIP');
                                } else {
                                    $('#clientTypeIndicator').text('Cliente Regular');
                                }
                            });
                        } else {
                            $('#clientResults').html('<div class="list-group-item text-muted">Sin resultados</div>');
                        }
                    })
                    .catch(() => {
                        $('#clientResults').html('<div class="list-group-item text-danger">Error al buscar</div>');
                    });
            }, 300);
        });
    };

    const setupEmployeeSearch = () => {
        let timeout;
        $('#searchEmployee').on('input', function() {
            clearTimeout(timeout);
            const query = $(this).val().trim();
            
            if (query.length < 2) {
                $('#employeeResults').hide();
                $('#add_empleado').val('');
                return;
            }
            
            timeout = setTimeout(() => {
                $('#employeeResults').html('<div class="list-group-item"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>').show();
                
                Ajax.get(`${baseUrl}?action=search_employees`, { search: query })
                    .then(data => {
                        if (data.success && data.results?.length) {
                            const html = data.results.map(e => `
                                <button type="button" class="list-group-item list-group-item-action employee-item"
                                        data-id="${e.empleado_ced}" 
                                        data-nombre="${Helpers.escapeHtml(e.nombre)}">
                                    <strong>${Helpers.escapeHtml(e.nombre)}</strong>
                                    <br>
                                    <small class="text-muted">Cédula: ${e.empleado_ced} | ${e.cargo || 'Vendedor'}</small>
                                </button>
                            `).join('');
                            
                            $('#employeeResults').html(html);
                            
                            $('.employee-item').on('click', function() {
                                const nombre = $(this).data('nombre');
                                const id = $(this).data('id');
                                
                                $('#searchEmployee').val(nombre).addClass('is-valid');
                                $('#add_empleado').val(id);
                                $('#employeeResults').hide();
                            });
                        } else {
                            $('#employeeResults').html('<div class="list-group-item text-muted">Sin resultados</div>');
                        }
                    })
                    .catch(() => {
                        $('#employeeResults').html('<div class="list-group-item text-danger">Error al buscar</div>');
                    });
            }, 300);
        });
    };

    const setupProductSearch = () => {
        let timeout;
        $('#searchProduct').on('input', function() {
            clearTimeout(timeout);
            const query = $(this).val().trim();
            
            if (query.length < 2) {
                $('#productResults').hide();
                return;
            }
            
            timeout = setTimeout(() => {
                $('#productResults').html('<div class="list-group-item"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>').show();
                
                Ajax.get(`${baseUrl}?action=search_products`, { search: query })
                    .then(data => {
                        if (data.success && data.results?.length) {
                            const html = data.results.map(p => {
                                const priceWithIva = parseFloat(p.precio ?? 0);
                                const priceBase = priceWithIva / 1.16;
                                
                                return `
                                    <button type="button" class="list-group-item list-group-item-action product-search-item"
                                            data-codigo="${Helpers.escapeHtml(p.codigo_prenda)}" 
                                            data-nombre="${Helpers.escapeHtml(p.nombre)}"
                                            data-categoria="${Helpers.escapeHtml(p.categoria)}"
                                            data-tipo="${Helpers.escapeHtml(p.tipo)}"
                                            data-price="${priceWithIva}">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>${Helpers.escapeHtml(p.codigo_prenda)}</strong> - ${Helpers.escapeHtml(p.nombre)}
                                                <br>
                                                <small class="text-muted">
                                                    <span class="badge bg-secondary">${Helpers.escapeHtml(p.categoria)}</span>
                                                    <span class="badge bg-info">${Helpers.escapeHtml(p.tipo)}</span>
                                                </small>
                                            </div>
                                            <div class="text-end">
                                                <strong class="text-success">${Helpers.formatCurrency(priceWithIva)}</strong>
                                                <br>
                                                <small class="text-muted">${Helpers.formatCurrency(priceBase)} + IVA</small>
                                            </div>
                                        </div>
                                    </button>
                                `;
                            }).join('');
                            
                            $('#productResults').html(html);
                            $('#productsCount').text(`${data.results.length} encontrados`);
                            
                            $('.product-search-item').on('click', function() {
                                const codigo = $(this).data('codigo');
                                const nombre = $(this).data('nombre');
                                const categoria = $(this).data('categoria');
                                const tipo = $(this).data('tipo');
                                const price = parseFloat($(this).data('price'));
                                
                                // Verificar si ya está en el carrito
                                if (cart.some(c => c.codigo_prenda === codigo)) {
                                    Helpers.toast('warning', `El producto ${codigo} ya está agregado`);
                                    return;
                                }
                                
                                addProductToCart(codigo, nombre, categoria, tipo, price);
                                $('#searchProduct').val('');
                                $('#productResults').hide();
                            });
                        } else {
                            $('#productResults').html('<div class="list-group-item text-muted">Sin resultados</div>');
                            $('#productsCount').text('0 encontrados');
                        }
                    })
                    .catch(() => {
                        $('#productResults').html('<div class="list-group-item text-danger">Error al buscar</div>');
                    });
            }, 300);
        });
    };

    // Cerrar resultados al hacer clic fuera
    $(document).on('click', (e) => {
        if (!$(e.target).closest('#searchClient, #clientResults').length) {
            $('#clientResults').hide();
        }
        if (!$(e.target).closest('#searchEmployee, #employeeResults').length) {
            $('#employeeResults').hide();
        }
        if (!$(e.target).closest('#searchProduct, #productResults').length) {
            $('#productResults').hide();
        }
    });

    // ==================== GESTIÓN DEL CARRITO ====================
    
    const addProductToCart = (codigo, nombre, categoria, tipo, priceWithIva) => {
        $('#noProductsAlert').hide();
        pid++;
        const rowId = `prod_${pid}`;

        const priceBase = priceWithIva / 1.16;
        const ivaAmount = priceWithIva - priceBase;
        
        const item = { 
            id: rowId, 
            codigo_prenda: codigo, 
            name: nombre,
            categoria: categoria,
            tipo: tipo,
            price: priceWithIva,
            priceBase: priceBase,
            iva: ivaAmount,
            subtotal: priceWithIva
        };
        
        cart.push(item);

        const html = `
            <div class="product-row mb-3 border rounded p-3 bg-light" id="${rowId}">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-2">
                        <label class="form-label small fw-bold">Código</label>
                        <div class="fw-bold"><code>${Helpers.escapeHtml(codigo)}</code></div>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold">Producto</label>
                        <div>${Helpers.escapeHtml(nombre)}</div>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">Categoría</label>
                        <div><span class="badge bg-secondary">${Helpers.escapeHtml(categoria)}</span></div>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label small fw-bold">Tipo</label>
                        <div><span class="badge bg-info">${Helpers.escapeHtml(tipo)}</span></div>
                    </div>
                    <div class="col-6 col-md-2 text-end">
                        <label class="form-label small fw-bold">Precio</label>
                        <div class="fw-bold text-primary">${Helpers.formatCurrency(priceWithIva)}</div>
                    </div>
                    <div class="col-6 col-md-1 text-end">
                        <label class="form-label small d-block">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-danger remove-product" data-row="${rowId}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <input type="hidden" class="product-code" data-row="${rowId}" value="${codigo}">
                <input type="hidden" class="product-price-total" data-row="${rowId}" value="${priceWithIva}">
            </div>
        `;

        $('#productsContainer').append(html);

        $(`#${rowId} .remove-product`).on('click', function() {
            removeProductRow($(this).data('row'));
        });

        calcTotals();
    };

    const removeProductRow = (rowId) => {
        cart = cart.filter(c => c.id !== rowId);
        $(`#${rowId}`).fadeOut(200, function() { $(this).remove(); });
        if (cart.length === 0) $('#noProductsAlert').show();
        calcTotals();
    };

    const calcTotals = () => {
        const totalConIva = cart.reduce((acc, i) => acc + (parseFloat(i.price || 0) || 0), 0);
        const subtotal = totalConIva / 1.16;
        const ivaAmount = totalConIva - subtotal;

        $('#summary_subtotal').text(Helpers.formatCurrency(subtotal));
        $('#summary_iva').text(Helpers.formatCurrency(ivaAmount));
        $('#summary_total').text(Helpers.formatCurrency(totalConIva));
        
        if (typeof DOLAR_BCV_RATE !== 'undefined') {
            const totalBs = totalConIva * DOLAR_BCV_RATE;
            $('#summary_total_bs').text(Helpers.formatCurrencyBs(totalBs));
        }
    };

    // ==================== VALIDACIONES ====================
    
    const $tipoVentaSelect = $('[name="tipo_venta"]');
    const $fechaVencimientoGroup = $('#fechaVencimientoGroup');
    const $fechaVencimientoInput = $('#add_fecha_vencimiento');

    const setMinDate = () => {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        $fechaVencimientoInput.attr('min', tomorrow.toISOString().split('T')[0]);
    };

    $tipoVentaSelect.on('change', function() {
        const tipo = $(this).val();
        const clienteCed = $('#add_cliente').val();
        
        if (tipo === 'credito') {
            if (!clienteCed) {
                Helpers.toast('warning', 'Seleccione primero un cliente');
                $(this).val('contado');
                return;
            }

            const tipoCliente = $('#add_cliente').attr('data-tipo');
            
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

    // ==================== SUBMIT VENTA ====================
    
    $('#addSaleForm').on('submit', function(e) {
        e.preventDefault();

        const cliente = $('#add_cliente').val();
        const empleado = $('#add_empleado').val();
        const tipo = $tipoVentaSelect.val();
        const referencia = $('#add_referencia').val().trim();

        if (!cliente || !empleado) {
            Helpers.toast('error', 'Seleccione cliente y vendedor');
            return;
        }

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
            const tipoCliente = $('#add_cliente').attr('data-tipo');
            
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
            iva_porcentaje: 16.00,
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
        $('#searchClient').val('').removeClass('is-valid');
        $('#searchEmployee').val('').removeClass('is-valid');
        $('#searchProduct').val('');
        $('#add_cliente').val('').removeAttr('data-tipo');
        $('#add_empleado').val('');
        $('#clientTypeIndicator').text('');
        cart = [];
        pid = 0;
        $('#noProductsAlert').show();
        $fechaVencimientoGroup.hide();
        $fechaVencimientoInput.prop('required', false).val('');
        calcTotals();
    };

    // ==================== VER DETALLE ====================
    
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
            <div class="table-responsive">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Tipo</th>
                            <th class="text-end">Precio</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${(s.prendas ?? s.items ?? []).map(it => {
                            const codigo = it.codigo_prenda ?? it.codigo ?? 'N/A';
                            const name = it.nombre_prenda ?? it.nombre ?? '';
                            const categoria = it.categoria ?? it.categoria_prenda ?? '';
                            const tipo = it.tipo ?? it.tipo_prenda ?? 'N/A';
                            const precioConIva = parseFloat(it.precio_con_iva ?? it.precio_unitario ?? 0);
                            
                            return `
                                <tr>
                                    <td><code>${Helpers.escapeHtml(codigo)}</code></td>
                                    <td>${Helpers.escapeHtml(name)}</td>
                                    <td><span class="badge bg-secondary">${Helpers.escapeHtml(categoria)}</span></td>
                                    <td><span class="badge bg-info">${Helpers.escapeHtml(tipo)}</span></td>
                                    <td class="text-end"><strong>${Helpers.formatCurrency(precioConIva)}</strong></td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            </div>
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

    // ==================== CANCELAR VENTA ====================
    
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
                        } else {
                            Helpers.toast('error', r?.message || 'Error al anular venta');
                        }
                    })
                    .catch(msg => Helpers.toast('error', msg));
            },
            'Sí, anular'
        );
    });

    // ==================== GENERAR PDF ====================
    
    $(document).on('click', '.btn-pdf', function() {
        const ventaId = $(this).data('id');
        if (!ventaId) return;
        window.open(`${baseUrl}?action=generate_pdf&venta_id=${ventaId}`, '_blank');
    });

    // ==================== BÚSQUEDA EN TABLA ====================
    
    $('#searchInput').on('keyup', Helpers.debounce(function() {
        salesTable.search($(this).val()).draw();
    }, 300));

    // ==================== VALIDACIÓN DE REFERENCIA ====================
    
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

    // ==================== RESET AL CERRAR MODAL ====================
    
    $('#addSaleModal').on('hidden.bs.modal', function() {
        resetSaleForm();
    });

    $('#viewSaleModal').on('hidden.bs.modal', function() {
        $('#saleDetailsContent').html('');
    });

    // ==================== INICIALIZACIÓN FINAL ====================
    
    initDataTable();
    setupClientSearch();
    setupEmployeeSearch();
    setupProductSearch();
    setMinDate();
    $fechaVencimientoGroup.hide();
    $fechaVencimientoInput.prop('required', false);
});