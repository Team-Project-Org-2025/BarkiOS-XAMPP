// ============================================================
// MÓDULO DE VENTAS - GARAGE BARKI
// Versión optimizada con estilos originales + fecha vencimiento
// ============================================================

$(document).ready(function () {
    const baseUrl = '/BarkiOS/admin/sale';
    // --- Estado Global ---
    const $salesBody = $('#salesTableBody');
    const $addSaleForm = $('#addSaleForm');
    const $productsContainer = $('#productsContainer');
    const $noProductsAlert = $('#noProductsAlert');

    const REGEX = {
        cedula: /^\d{7,10}$/,
        money: /^\d+(\.\d{1,2})?$/
    };

    let clients = [], employees = [], products = [], cart = [], pid = 0;
    const IVA_DEFAULT = 16.00;

    // --- UTILIDADES ---
    const esc = (t) => {
        const div = document.createElement('div');
        div.textContent = String(t ?? '');
        return div.innerHTML;
    };

    const toast = (type, msg) => {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: type,
                title: msg,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else {
            console[type === 'error' ? 'error' : 'log'](msg);
        }
    };

    const fmt = (n) => {
        const num = Number(n) || 0;
        return new Intl.NumberFormat('es-VE', { 
            style: 'currency', 
            currency: 'USD',
            minimumFractionDigits: 2
        }).format(num);
    };

    const fmtBs = (n) => {
      const num = Number(n) || 0;
      return new Intl.NumberFormat("es-VE", {
        style: "currency",
        currency: "VES",
        minimumFractionDigits: 2
      }).format(num);
    };


    const fmtDate = (d) => {
        if (!d) return '';
        const dt = new Date(d);
        if (isNaN(dt)) return String(d);
        return dt.toLocaleString('es-ES', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    };

    // AJAX helper
    function ajax(method, url, data, success, error) {
        const isFormData = (data instanceof FormData);
        $.ajax({
            url: url,
            method: method,
            data: data,
            dataType: 'json',
            processData: !isFormData,
            contentType: isFormData ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            success: success,
            error: function (xhr) {
                let msg = 'Error en la petición';
                try {
                    const json = xhr.responseJSON || JSON.parse(xhr.responseText);
                    if (json && json.message) msg = json.message;
                } catch (e) {
                    msg = xhr.statusText || msg;
                }
                if (error) error(msg);
                else toast('error', msg);
            }
        });
    }

    // =============================================================
    // ✅ CONTROL DE FECHA DE VENCIMIENTO (NUEVO)
    // =============================================================
    
    const $tipoVentaSelect = $('[name="tipo_venta"]');
    const $clienteSelect = $('#add_cliente');
    const $fechaVencimientoGroup = $('#fechaVencimientoGroup');
    const $fechaVencimientoInput = $('#add_fecha_vencimiento');

    // Establecer fecha mínima (mañana)
    function setMinDate() {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        $fechaVencimientoInput.attr('min', tomorrow.toISOString().split('T')[0]);
    }

    // Manejar cambio de tipo de venta
    $tipoVentaSelect.on('change', function () {
        const tipo = $(this).val();
        const clienteCed = $clienteSelect.val();
        
        if (tipo === 'credito') {
            if (!clienteCed) {
                toast('warning', 'Seleccione primero un cliente');
                $(this).val('contado');
                return;
            }

            const $selectedOption = $clienteSelect.find('option:selected');
            const tipoCliente = $selectedOption.data('tipo');
            
            if (tipoCliente === 'vip') {
                $fechaVencimientoGroup.show();
                $fechaVencimientoInput.prop('required', true);
                setMinDate();
                toast('info', 'Debe seleccionar una fecha de vencimiento');
            } else {
                toast('warning', 'Solo clientes VIP pueden comprar a crédito');
                $(this).val('contado');
                $fechaVencimientoGroup.hide();
                $fechaVencimientoInput.prop('required', false).val('');
            }
        } else {
            $fechaVencimientoGroup.hide();
            $fechaVencimientoInput.prop('required', false).val('');
        }
    });

    // Validar cuando cambia el cliente
    $clienteSelect.on('change', function () {
        const tipo = $tipoVentaSelect.val();
        if (tipo === 'credito') {
            $tipoVentaSelect.trigger('change');
        }
    });

    // Validación de fecha en tiempo real
    $fechaVencimientoInput.on('change', function () {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate <= today) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            toast('error', 'La fecha de vencimiento debe ser posterior a hoy');
        } else {
            $(this).addClass('is-valid').removeClass('is-invalid');
        }
    });

    // =============================================================
    // CARGA INICIAL
    // =============================================================
    
    function loadAll() {
        loadSales();
        loadClients();
        loadEmployees();
        loadProducts();
    }

    function loadSales() {
        $salesBody.html(`<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary"></div> Cargando...</td></tr>`);
        ajax('GET', baseUrl + '?action=get_sales', null, function (r) {
            if (r && r.success) {
                renderSales(r.sales || []);
            } else {
                $salesBody.html(`<tr><td colspan="9" class="text-center py-4 text-muted">No se pudieron cargar las ventas</td></tr>`);
                toast('error', r?.message || 'Error al cargar ventas');
            }
        });
    }

    function loadClients() {
        ajax('GET', baseUrl + '?action=get_clients', null, function (r) {
            if (r && r.success) {
                clients = r.clients || [];
                const opts = clients.map(c => 
                    `<option value="${esc(c.cliente_ced)}" data-tipo="${esc(c.tipo)}">${esc(c.nombre_cliente)} (${esc(c.cliente_ced)}) ${c.tipo === 'vip' ? '⭐' : ''}</option>`
                ).join('');
                $clienteSelect.html('<option value="">Seleccione...</option>' + opts);
            } else {
                $clienteSelect.html('<option value="">No disponible</option>');
            }
        });
    }

    function loadEmployees() {
        ajax('GET', baseUrl + '?action=get_employees', null, function (r) {
            if (r && r.success) {
                employees = r.employees || [];
                const opts = employees.map(e => 
                    `<option value="${esc(e.empleado_ced)}">${esc(e.nombre)} - ${esc(e.cargo || '')}</option>`
                ).join('');
                $('#add_empleado').html('<option value="">Seleccione...</option>' + opts);
            } else {
                $('#add_empleado').html('<option value="">No disponible</option>');
            }
        });
    }

    function loadProducts() {
        ajax('GET', baseUrl + '?action=get_products', null, function (r) {
            if (r && r.success) {
                products = r.products || [];
                updateProductCount(products.length);
            } else {
                products = [];
                updateProductCount(0);
            }
        });
    }

    function updateProductCount(count) {
        const $badge = $('#productsCount');
        if ($badge.length) {
            $badge.text(`${count} disponibles`).toggleClass('text-danger', count === 0);
        }
    }

    // =============================================================
    // RENDER VENTAS
    // =============================================================
    
    function renderSales(sales) {
        if (!sales || sales.length === 0) {
            $salesBody.html(`<tr><td colspan="9" class="text-center py-5"><p class="text-muted mb-0">No hay ventas registradas.</p></td></tr>`);
            updateStats([]);
            return;
        }

        let rows = '';
        sales.forEach((s, i) => {
            const ventaId = s.venta_id ?? s.id ?? '';
            const estadoBadge = {
                'completada': 'success',
                'pendiente': 'warning',
                'cancelada': 'danger'
            }[(s.estado_venta || '').toLowerCase()] || 'secondary';

            rows += `<tr>
                <td class="text-center d-none d-md-table-cell">${i+1}</td>
                <td><code>${esc(s.referencia ?? 'N/A')}</code></td>
                <td class="d-none d-lg-table-cell">${esc(s.nombre_cliente ?? '')}</td>
                <td class="d-none d-xl-table-cell">${esc(s.nombre_empleado ?? '')}</td>
                <td class="d-none d-md-table-cell">${esc(fmtDate(s.fecha))}</td>
                <td class="text-end"><strong>${fmt(parseFloat(s.monto_total ?? 0))}</strong></td>
                <td class="text-center d-none d-sm-table-cell">
                    <span class="badge bg-${estadoBadge}">${esc(s.estado_venta ?? '')}</span>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm d-flex align-items-center justify-content-center">
                        <button class="btn btn-info btn-sm" onclick="window.viewSale(${ventaId})" title="Ver">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${ (s.estado_venta || '').toLowerCase() !== 'cancelada' ? `
                            <button class="btn btn-danger btn-sm" onclick="window.cancelSale(${ventaId})" title="Anular">
                                <i class="fas fa-ban"></i>
                            </button>` : '' }
                    </div>
                </td>
            </tr>`;
        });

        $salesBody.html(rows);
        updateStats(sales);
    }

    function updateStats(sales) {
        const totalSales = (sales || []).length;
        const revenue = (sales || []).reduce((acc, s) => acc + (parseFloat(s.monto_total ?? 0) || 0), 0);
        const pending = (sales || []).reduce((acc, s) => acc + (parseFloat(s.saldo_pendiente || 0) || 0), 0);
        const completed = (sales || []).filter(s => (s.estado_venta || '').toLowerCase() === 'completada').length;

        $('#totalSales').text(totalSales);
        $('#totalRevenue').text(fmt(revenue));
        $('#totalPending').text(fmt(pending));
        $('#completedSales').text(completed);
    }

    // =============================================================
    // BUSCADOR
    // =============================================================
    
    $('#searchInput').on('keyup', function () {
        const q = $(this).val().toLowerCase();
        $('#salesTableBody tr').each(function () {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(q) !== -1);
        });
    });

    // =============================================================
    // GESTIÓN DE PRODUCTOS EN VENTA
    // =============================================================
    
    $('#btnAddProduct').on('click', addProductRow);

    function addProductRow(productData = null) {
        if (!products || products.length === 0) {
            toast('info', 'No hay productos disponibles');
            return;
        }

        $('#noProductsAlert').hide();
        pid++;
        const rowId = `prod_${pid}`;

        const productSelect = products.map(p => {
            const price = parseFloat(p.precio ?? 0);
            const codigo = p.codigo_prenda ?? '';
            const name = p.nombre ?? `ID:${p.prenda_id || ''}`;
            return `<option value="${esc(codigo)}" data-price="${price}" data-name="${esc(name)}">${esc(codigo)} - ${esc(name)} - ${fmt(price)}</option>`;
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
                        <div class="fw-bold text-primary product-subtotal" data-row="${rowId}">${fmt(0)}</div>
                    </div>
                    <div class="col-2 col-md-1 text-end">
                        <label class="form-label small d-block">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-danger remove-product" data-row="${rowId}"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
                <input type="hidden" class="product-code" data-row="${rowId}">
            </div>
        `;

        $productsContainer.append(html);

        // Eventos
        $(`#${rowId} .product-select`).on('change', function () {
            const r = $(this).data('row');
            const codigo = $(this).val();
            const $opt = $(this).find('option:selected');
            const price = parseFloat($opt.data('price') || 0);
            const name = $opt.data('name') || '';
            
            // Evitar duplicados de código
            if (codigo && cart.some(c => c.codigo_prenda === codigo && c.id !== r)) {
                toast('warning', `El producto ${codigo} ya está agregado`);
                $(this).val('');
                updateProductRow(r, '', '', 0);
                return;
            }

            updateProductRow(r, codigo, name, price);
        });

        $(`#${rowId} .remove-product`).on('click', function () {
            removeProductRow($(this).data('row'));
        });

        calcTotals();
    }

    function updateProductRow(rowId, codigo, name, price) {
        const item = { id: rowId, codigo_prenda: codigo, name: name, price: price, subtotal: price };
        const existingIndex = cart.findIndex(c => c.id === rowId);
        
        if (existingIndex >= 0) {
            cart[existingIndex] = item;
        } else {
            cart.push(item);
        }

        $(`#${rowId} .product-code`).val(codigo);
        $(`#${rowId} .product-price`).val(price ? price.toFixed(2) : '');
        $(`#${rowId} .product-subtotal`).text(fmt(price));
        calcTotals();
    }

    function removeProductRow(rowId) {
        cart = cart.filter(c => c.id !== rowId);
        $(`#${rowId}`).fadeOut(200, function() { $(this).remove(); });
        if (cart.length === 0) $noProductsAlert.show();
        calcTotals();
    }

    function calcTotals() {
        const subtotal = cart.reduce((acc, i) => acc + (parseFloat(i.subtotal || 0) || 0), 0);
        const ivaPct = parseFloat($('#add_iva').val() || IVA_DEFAULT);
        const ivaAmount = subtotal * (ivaPct / 100);
        const total = subtotal + ivaAmount;

        $('#summary_subtotal').text(fmt(subtotal));
        $('#summary_iva').text(fmt(ivaAmount));
        $('#summary_total').text(fmt(total));
        // Calcular y mostrar total en Bs
        const totalBs = total * DOLAR_BCV_RATE;
        $('#summary_total_bs').text(fmtBs(totalBs));
        
        $('#iva_percentage').text(ivaPct.toFixed(2));
    }

    // Validar IVA en tiempo real
    $('#add_iva').on('input', function () {
        const val = parseFloat($(this).val());
        $(this).toggleClass('is-invalid', isNaN(val) || val < 0 || val > 100);
        $(this).toggleClass('is-valid', !isNaN(val) && val >= 0 && val <= 100);
        calcTotals();
    });

    // =============================================================
    // GUARDAR VENTA (CON VALIDACIÓN DE FECHA VENCIMIENTO)
    // =============================================================
    
    $addSaleForm.on('submit', function (e) {
        e.preventDefault();

        const cliente = $clienteSelect.val();
        const empleado = $('#add_empleado').val();
        const tipo = $tipoVentaSelect.val();

        if (!cliente || !empleado) {
            toast('error', 'Seleccione cliente y vendedor');
            return;
        }

        if (cart.length === 0) {
            toast('error', 'Agregue al menos un producto');
            return;
        }

        // ✅ Validar crédito y fecha de vencimiento
        if (tipo === 'credito') {
            const $opt = $clienteSelect.find('option:selected');
            const tipoCliente = $opt.data('tipo');
            
            if (tipoCliente !== 'vip') {
                toast('error', 'Solo clientes VIP pueden comprar a crédito');
                return;
            }

            const fechaVencimiento = $fechaVencimientoInput.val();
            if (!fechaVencimiento) {
                toast('error', 'Debe seleccionar una fecha de vencimiento');
                $fechaVencimientoInput.focus();
                return;
            }

            // Validar que la fecha sea futura
            const selectedDate = new Date(fechaVencimiento);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate <= today) {
                toast('error', 'La fecha de vencimiento debe ser posterior a hoy');
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
            referencia: $('#add_referencia').val().trim() || '',
            iva_porcentaje: $('#add_iva').val() || IVA_DEFAULT,
            observaciones: $('[name="observaciones"]').val() || '',
            productos: JSON.stringify(productosPayload)
        };

        // ✅ Agregar fecha de vencimiento si es crédito
        if (tipo === 'credito') {
            data.fecha_vencimiento = $fechaVencimientoInput.val();
        }

        // Deshabilitar botón
        const $btn = $addSaleForm.find('button[type="submit"]');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

        ajax('POST', baseUrl + '?action=add_sale', data, 
            function (r) {
                $btn.prop('disabled', false).html(btnText);
                
                if (r && r.success) {
                    toast('success', r.message || 'Venta registrada');
                    $addSaleForm.trigger('reset');
                    $productsContainer.empty();
                    cart = [];
                    pid = 0;
                    $noProductsAlert.show();
                    $fechaVencimientoGroup.hide();
                    $fechaVencimientoInput.prop('required', false).val('');
                    calcTotals();
                    $('#addSaleModal').modal('hide');
                    loadSales();
                    loadProducts();
                } else {
                    toast('error', r?.message || 'Error al guardar venta');
                }
            },
            function (msg) {
                $btn.prop('disabled', false).html(btnText);
                toast('error', msg);
            }
        );
    });

    // =============================================================
    // VER DETALLES
    // =============================================================
    
    window.viewSale = function (id) {
        if (!id) return;
        
        $('#saleDetailsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando...</p></div>');
        $('#viewSaleModal').modal('show');

        ajax('GET', baseUrl + `?action=get_by_id&id=${encodeURIComponent(id)}`, null, 
            function (r) {
                if (r && r.success && r.venta) {
                    renderSaleDetails(r.venta);
                } else {
                    $('#saleDetailsContent').html('<p class="text-center text-muted">No se encontraron detalles</p>');
                }
            },
            function (msg) {
                $('#saleDetailsContent').html(`<p class="text-center text-muted">${esc(msg)}</p>`);
            }
        );
    };

    function renderSaleDetails(s) {
        let html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Venta #${esc(s.venta_id ?? '')}</h5>
                    <p class="mb-1"><strong>Referencia:</strong> <code>${esc(s.referencia ?? 'N/A')}</code></p>
                    <p class="mb-1"><strong>Estado:</strong> <span class="badge bg-${(s.estado_venta === 'completada') ? 'success' : ((s.estado_venta === 'cancelada') ? 'danger' : 'warning')}">${esc(s.estado_venta ?? '')}</span></p>
                    <p class="mb-1"><strong>Tipo:</strong> <span class="badge bg-info">${esc(s.tipo_venta ?? '')}</span></p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1"><strong>Fecha:</strong> ${esc(fmtDate(s.fecha))}</p>
                    <p class="mb-1"><strong>Cliente:</strong> ${esc(s.nombre_cliente ?? '')}</p>
                    <p class="mb-1"><strong>Vendedor:</strong> ${esc(s.nombre_empleado ?? '')}</p>
                </div>
            </div>

            <h6 class="border-bottom pb-2">Productos</h6>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Categoría</th>
                        <th class="text-end">Precio</th>
                    </tr>
                </thead>
                <tbody>
        `;

        (s.prendas ?? s.items ?? []).forEach(it => {
            const codigo = it.codigo_prenda ?? it.codigo ?? 'N/A';
            const name = it.nombre_prenda ?? it.nombre ?? '';
            const tipo = it.tipo ?? it.tipo_prenda ?? 'N/A';
            const categoria = it.categoria ?? it.categoria_prenda ?? '';
            const precio = parseFloat(it.precio_unitario ?? it.subtotal ?? it.precio ?? 0);

            html += `<tr>
                <td><code>${esc(codigo)}</code></td>
                <td>${esc(name)}</td>
                <td>${esc(tipo)}</td>
                <td><small class="text-muted">${esc(categoria)}</small></td>
                <td class="text-end">${fmt(precio)}</td>
            </tr>`;
        });

        html += `</tbody></table>`;

        const subtotal = parseFloat(s.monto_subtotal ?? s.subtotal ?? 0);
        const iva = parseFloat(s.monto_iva ?? s.iva ?? 0);
        const total = parseFloat(s.monto_total ?? s.total ?? 0);
        const pagado = parseFloat(s.total_pagado ?? s.pagado ?? 0);
        const saldo = parseFloat(s.saldo_pendiente ?? s.saldo ?? 0);
        const totalBs = total * DOLAR_BCV_RATE;

        html += `
            <div class="row">
                <div class="col-md-6 offset-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><td>Subtotal:</td><td class="text-end">${fmt(subtotal)}</td></tr>
                        <tr><td>IVA (${s.iva_porcentaje ?? 16}%):</td><td class="text-end">${fmt(iva)}</td></tr>
                        <tr class="fw-bold"><td>Total:</td><td class="text-end">${fmt(total)}</td></tr>
                        ${pagado > 0 ? `<tr class="text-success"><td>Pagado:</td><td class="text-end">${fmt(pagado)}</td></tr>` : ''}
                        ${saldo > 0 ? `<tr class="text-danger"><td >Saldo:</td><td class="text-end">${fmt(saldo)}</td></tr>` : ''}
                        <tr><td>Tasa usada:</td><td class="text-end">${DOLAR_BCV_RATE.toFixed(2)} Bs</td></tr>
                        <tr class="fw-bold"><td>Total (Bs):</td><td class="text-end">${fmtBs(totalBs)}</td></tr>
                    </table>
                </div>
            </div>
        `;

        if (s.pagos && s.pagos.length > 0) {
            html += `<h6 class="border-bottom pb-2 mt-3">Historial de Pagos</h6>`;
            html += `<table class="table table-sm"><thead><tr><th>Fecha</th><th class="text-end">Monto</th><th>Observaciones</th></tr></thead><tbody>`;
            s.pagos.forEach(p => {
                html += `<tr>
                    <td>${esc(fmtDate(p.fecha_pago ?? p.fecha))}</td>
                    <td class="text-end">${fmt(parseFloat(p.monto ?? 0))}</td>
                    <td><small class="text-muted">${esc(p.observaciones ?? '-')}</small></td>
                </tr>`;
            });
            html += `</tbody></table>`;
        }

        $('#saleDetailsContent').html(html);
    }

    // =============================================================
    // ANULAR VENTA
    // =============================================================
    
    window.cancelSale = function (ventaId) {
        if (!ventaId) return;
        
        Swal.fire({
            title: '¿Anular venta?',
            text: 'Esto liberará las prendas y marcará la venta como anulada.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545'
        }).then(res => {
            if (!res.isConfirmed) return;
            
            ajax('POST', baseUrl + '?action=cancel_sale', { venta_id: ventaId }, 
                function (r) {
                    if (r && r.success) {
                        toast('success', r.message || 'Venta anulada');
                        loadSales();
                        loadProducts();
                    } else {
                        toast('error', r?.message || 'Error al anular venta');
                    }
                },
                function (msg) {
                    toast('error', msg);
                }
            );
        });
    };

    // =============================================================
    // LIMPIAR MODALES
    // =============================================================
    
    $('#addSaleModal, #viewSaleModal').on('hidden.bs.modal', function () {
        $(this).find('form').each(function () {
            if (this.reset) this.reset();
            $(this).find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
        });
        
        if ($(this).attr('id') === 'addSaleModal') {
            $productsContainer.empty();
            cart = [];
            pid = 0;
            $noProductsAlert.show();
            $fechaVencimientoGroup.hide();
            $fechaVencimientoInput.prop('required', false).val('');
            calcTotals();
        }
    });

    // =============================================================
    // INICIALIZACIÓN
    // =============================================================
    
    loadAll();
    
    // Establecer fecha mínima inicial y ocultar por defecto
    setMinDate();
    $fechaVencimientoGroup.hide();
    $fechaVencimientoInput.prop('required', false);

    // Exponer funciones globales
    window.calcTotals = calcTotals;
    window.addProductRow = addProductRow;
    window.removeProduct = function(rowId) { removeProductRow(rowId); };
});