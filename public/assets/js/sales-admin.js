// /public/assets/js/sales-admin.js
// Requiere jQuery y SweetAlert2 cargados en la vista.

$(document).ready(function () {
    // --- Selectores / Estado global ---
    const $salesBody = $('#salesTableBody');
    const $addSaleForm = $('#addSaleForm');
    const $addPaymentForm = $('#addPaymentForm');
    const $productsContainer = $('#productsContainer');
    const $noProductsAlert = $('#noProductsAlert');

    const REGEX = {
        cedula: /^\d{7,10}$/,
        ref: /^[A-Za-z0-9\-]+$/,
        money: /^\d+(\.\d{1,2})?$/
    };

    let clients = [], employees = [], products = [], cart = [], pid = 0;

    // ---------------------------
    // UTILIDADES
    // ---------------------------
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
        return new Intl.NumberFormat('es-US', { style: 'currency', currency: 'USD' }).format(num);
    };

    const fmtDate = (d) => {
        if (!d) return '';
        const dt = new Date(d);
        if (isNaN(dt)) return String(d);
        return dt.toLocaleString('es-ES', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
    };

    // AJAX helper (con manejo de errores limpio)
    function ajax(method, url, data, success, opts = {}) {
        const isFormData = (data instanceof FormData);
        $.ajax({
            url: url,
            method: method,
            data: data,
            dataType: 'json',
            processData: !isFormData && !(data && data instanceof Object && opts.stringify),
            contentType: isFormData ? false : (opts.contentType || 'application/x-www-form-urlencoded; charset=UTF-8'),
            headers: {'X-Requested-With': 'XMLHttpRequest'},
            success: success,
            error: function (xhr) {
                let msg = 'Error en la petición';
                try {
                    const json = xhr.responseJSON || (xhr.responseText ? JSON.parse(xhr.responseText) : null);
                    if (json && json.message) msg = json.message;
                } catch (e) {}
                toast('error', msg);
            }
        });
    }

    // ---------------------------
    // CARGADORES INICIALES
    // ---------------------------
    function loadAll() {
        loadSales();
        loadClients();
        loadEmployees();
        loadProducts();
    }

    function loadSales() {
        $salesBody.html(`<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary"></div> Cargando...</td></tr>`);
        ajax('GET', window.location.pathname + '?action=get_sales', null, function (r) {
            if (r && r.success) {
                renderSales(r.sales || []);
            } else {
                $salesBody.html(`<tr><td colspan="8" class="text-center py-4 text-muted">No se pudieron cargar las ventas</td></tr>`);
                toast('error', r?.message || 'No se pudieron cargar las ventas');
            }
        });
    }

    function loadClients() {
        ajax('GET', window.location.pathname + '?action=get_clients', null, function (r) {
            if (r && r.success) {
                clients = r.clients || [];
                const opts = clients.map(c => `<option value="${esc(c.cliente_ced)}">${esc(c.nombre_cliente)} (${esc(c.cliente_ced)})</option>`).join('');
                $('#add_cliente').html('<option value="">Seleccione...</option>' + opts);
            } else {
                // si endpoint no existe: permitir continuar (fallback vacío)
                $('#add_cliente').html('<option value="">No disponible</option>');
                if (r && r.message) toast('error', r.message);
            }
        });
    }

    function loadEmployees() {
        ajax('GET', window.location.pathname + '?action=get_employees', null, function (r) {
            if (r && r.success) {
                employees = r.employees || [];
                const opts = employees.map(e => `<option value="${esc(e.empleado_ced)}">${esc(e.nombre)} (${esc(e.empleado_ced)})</option>`).join('');
                $('#add_empleado').html('<option value="">Seleccione...</option>' + opts);
            } else {
                $('#add_empleado').html('<option value="">No disponible</option>');
                if (r && r.message) toast('error', r.message);
            }
        });
    }

    function loadProducts() {
        ajax('GET', window.location.pathname + '?action=get_products', null, function (r) {
            if (r && r.success) {
                products = r.products || [];
            } else {
                products = [];
                if (r && r.message) toast('error', r.message);
            }
        });
    }

    // ---------------------------
    // RENDER / UI VENTAS
    // ---------------------------
    function renderSales(sales) {
        if (!sales || sales.length === 0) {
            $salesBody.html(`<tr><td colspan="8" class="text-center py-5"><p class="text-muted mb-0">No hay ventas registradas.</p></td></tr>`);
            updateStats([]);
            return;
        }
        let rows = '';
        sales.forEach((s, i) => {
            const ventaId = s.venta_id ?? s.id ?? '';
            const saldo = parseFloat(s.saldo_pendiente || 0);
            rows += `<tr>
                <td class="text-center">${i+1}</td>
                <td>${esc(s.nombre_cliente ?? s.cliente_nombre ?? '')}</td>
                <td>${esc(s.nombre_empleado ?? s.empleado_nombre ?? s.nombre_empleado ?? '')}</td>
                <td class="text-center">${esc(fmtDate(s.fecha ?? s.created_at ?? s.fec_creacion))}</td>
                <td class="text-end">${fmt(parseFloat(s.monto_total ?? s.monto ?? 0))}</td>
                <td class="text-center">${esc((s.tipo_venta ?? '').toString())}</td>
                <td class="text-center">${esc((s.estado_venta ?? s.estado ?? '').toString())}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info me-1" onclick="window.viewSale && window.viewSale(${ventaId})" title="Ver"><i class="fas fa-eye"></i></button>
                    <button class="btn btn-sm btn-success me-1" onclick="window.openAddPayment && window.openAddPayment(${ventaId}, '${esc(ventaId)}', ${saldo})" title="Pagar"><i class="fas fa-money-bill-wave"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="window.cancelSale && window.cancelSale(${ventaId})" title="Anular"><i class="fas fa-ban"></i></button>
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
        const completed = (sales || []).filter(s => (s.estado_venta || '').toLowerCase().includes('complet')).length;
        $('#totalSales').text(totalSales);
        $('#totalRevenue').text(fmt(revenue));
        $('#totalPending').text(fmt(pending));
        $('#completedSales').text(completed);
    }

    // ---------------------------
    // BUSCADOR
    // ---------------------------
    $('#searchInput').on('keyup', function (e) {
        const q = $(this).val().toLowerCase();
        $('#salesTableBody tr').each(function () {
            const text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(q) !== -1);
        });
    });

    // ---------------------------
    // CARRO DE PRODUCTOS (AGREGAR FILA)
    // ---------------------------
    $('#btnAddProduct').on('click', function () {
        addProductRow();
    });

    function addProductRow() {
        if (!products || products.length === 0) {
            toast('info', 'No hay productos disponibles para agregar.');
            return;
        }
        $('#noProductsAlert').hide();
        pid++;
        const rowId = `prod_${pid}`;
        cart.push({ id: rowId, prenda_id: '', qty: 1, price: 0, subtotal: 0 });

        const options = products.map(p => {
            const price = parseFloat(p.precio ?? p.price ?? 0);
            const name = p.nombre ?? p.name ?? `ID:${p.prenda_id ?? p.id}`;
            return `<option value="${esc(p.prenda_id ?? p.id)}" data-price="${price}">${esc(name)} - ${fmt(price)}</option>`;
        }).join('');

        const html = `
            <div class="product-row mb-2" id="${rowId}">
                <div class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <select class="form-select product-select" data-row="${rowId}">
                            <option value="">Seleccione producto...</option>
                            ${options}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" min="1" step="1" class="form-control product-qty" data-row="${rowId}" value="1">
                    </div>
                    <div class="col-md-2">
                        <input type="number" step="0.01" class="form-control product-price" data-row="${rowId}" placeholder="Precio" readonly>
                    </div>
                    <div class="col-md-1 text-end">
                        <div class="fw-bold product-subtotal" data-row="${rowId}">${fmt(0)}</div>
                    </div>
                    <div class="col-md-1 text-end">
                        <button type="button" class="btn btn-sm btn-danger remove-product" data-row="${rowId}" title="Eliminar"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            </div>
        `;

        $productsContainer.append(html);

        // eventos
        $(`#${rowId} .product-select`).on('change', function () {
            const r = $(this).data('row');
            const prodId = $(this).val();
            const price = parseFloat($(this).find('option:selected').data('price') || 0);
            updateProductRow(r, prodId, price);
        });

        $(`#${rowId} .product-qty`).on('input', function () {
            const r = $(this).data('row');
            const qty = parseInt($(this).val()) || 1;
            const item = cart.find(c => c.id === r);
            if (item) {
                item.qty = qty;
                item.subtotal = (item.price || 0) * item.qty;
                $(`#${r} .product-subtotal`).text(fmt(item.subtotal));
                calcTotals();
            }
        });

        $(`#${rowId} .remove-product`).on('click', function () {
            const r = $(this).data('row');
            removeProductRow(r);
        });

        calcTotals();
    }

    function updateProductRow(rowId, prendaId, price) {
        const item = cart.find(c => c.id === rowId);
        if (!item) return;
        item.prenda_id = prendaId;
        item.price = parseFloat(price || 0);
        item.subtotal = item.price * (item.qty || 1);
        $(`#${rowId} .product-price`).val(item.price.toFixed(2));
        $(`#${rowId} .product-subtotal`).text(fmt(item.subtotal));
        calcTotals();
    }

    function removeProductRow(rowId) {
        cart = cart.filter(c => c.id !== rowId);
        $(`#${rowId}`).remove();
        if (cart.length === 0) $noProductsAlert.show();
        calcTotals();
    }

    function calcTotals() {
        const subtotal = cart.reduce((acc, i) => acc + (parseFloat(i.subtotal || 0) || 0), 0);
        const descPct = parseFloat($('#add_descuento').val() || 0);
        const discount = isNaN(descPct) ? 0 : subtotal * (descPct / 100);
        const total = subtotal - discount;
        $('#summary_subtotal').text(fmt(subtotal));
        $('#summary_discount').text(fmt(discount));
        $('#summary_total').text(fmt(total));
    }

    // validar descuento tiempo real
    $('#add_descuento').on('input', function () {
        const val = parseFloat($(this).val());
        $(this).toggleClass('is-invalid', isNaN(val) || val < 0 || val > 100);
        $(this).toggleClass('is-valid', !isNaN(val) && val >= 0 && val <= 100);
        calcTotals();
    });

    // ---------------------------
    // GUARDAR VENTA (envía productos como JSON en POST 'productos')
    // ---------------------------
    $addSaleForm.on('submit', function (e) {
        e.preventDefault();

        const cliente = $('#add_cliente').val();
        const empleado = $('#add_empleado').val();
        const tipo = $addSaleForm.find('[name="tipo_venta"]').val();

        if (!cliente || !empleado) {
            toast('error', 'Seleccione cliente y vendedor.');
            return;
        }

        const productosPayload = cart
            .filter(i => i.prenda_id)
            .map(i => ({
                prenda_id: i.prenda_id,
                cantidad: i.qty,
                precio_unitario: i.price
            }));

        if (productosPayload.length === 0) {
            toast('error', 'Agregue al menos un producto válido.');
            return;
        }

        // calcular totales por seguridad
        let montoTotal = productosPayload.reduce((acc, p) => acc + (parseFloat(p.precio_unitario || 0) * (parseInt(p.cantidad || 1))), 0);
        montoTotal = Math.round(montoTotal * 100) / 100;

        // construir payload
        const data = {
            cliente_ced: cliente,
            empleado_ced: empleado,
            tipo_venta: tipo,
            metodo_pago_principal: $addSaleForm.find('[name="metodo_pago_principal"]').val() || '',
            descuento: $addSaleForm.find('[name="descuento"]').val() || 0,
            observaciones: $addSaleForm.find('[name="observaciones"]').val() || '',
            productos: JSON.stringify(productosPayload) // el controlador espera POST['productos'] (json)
        };

        ajax('POST', window.location.pathname + '?action=add_sale', data, function (r) {
            if (r && r.success) {
                toast('success', r.message || 'Venta registrada.');
                $addSaleForm.trigger('reset');
                $productsContainer.empty();
                cart = [];
                pid = 0;
                $noProductsAlert.show();
                calcTotals();
                $('#addSaleModal').modal('hide');
                loadSales();
                loadProducts();
            } else {
                toast('error', r?.message || 'Error al guardar venta');
            }
        });
    });

    // ---------------------------
    // PAGOS
    // ---------------------------
    // abrir modal pago (expuesto en window para botones generados dinámicamente)
    window.openAddPayment = function (ventaId, ventaNumero, saldo) {
        $('#payment_venta_id').val(ventaId);
        $('#payment_sale_number').text(ventaNumero || '');
        $('#payment_saldo_pendiente').text(fmt(parseFloat(saldo || 0)));
        $('#payment_monto').val('');
        $('#payment_referencia').val('');
        $('input[name="banco"]').val('');
        $('#payment_ref_group').hide();
        $('#payment_bank_group').hide();
        $('#payment_metodo').val('efectivo');
        $('#addPaymentModal').modal('show');
    };

    $('#payment_metodo').on('change', function () {
        const m = $(this).val();
        if (['transferencia', 'pago_movil', 'cheque', 'tarjeta_debito', 'tarjeta_credito'].includes(m)) {
            $('#payment_ref_group').show();
            $('#payment_bank_group').show();
        } else {
            $('#payment_ref_group').hide();
            $('#payment_bank_group').hide();
        }
    });

    $('#payment_monto').on('input', function () {
        const val = parseFloat($(this).val());
        const maxText = $('#payment_saldo_pendiente').text() || '';
        const max = parseFloat(maxText.replace(/[^0-9.-]+/g, '')) || 0;
        $(this).toggleClass('is-invalid', isNaN(val) || val <= 0 || val > max);
        $(this).toggleClass('is-valid', !isNaN(val) && val > 0 && val <= max);
    });

    $('#payment_referencia').on('input', function () {
        const val = $(this).val();
        $(this).toggleClass('is-invalid', val.length > 0 && !REGEX.ref.test(val));
        $(this).toggleClass('is-valid', REGEX.ref.test(val));
    });

    $addPaymentForm.on('submit', function (e) {
        e.preventDefault();
        const venta_id = $('#payment_venta_id').val();
        const monto = parseFloat($('#payment_monto').val() || 0);
        const saldoText = $('#payment_saldo_pendiente').text() || '';
        const saldo = parseFloat(saldoText.replace(/[^0-9.-]+/g, '')) || 0;

        if (!venta_id || isNaN(monto) || monto <= 0 || monto > saldo) {
            toast('error', 'Monto inválido o mayor que el saldo pendiente.');
            return;
        }

        const data = {
            venta_id: venta_id,
            monto: monto,
            metodo_pago: $('#payment_metodo').val() || '',
            referencia: $('#payment_referencia').val() || '',
            banco: $('input[name="banco"]').val() || '',
            observaciones: $('textarea[name="payment_observaciones"]').val() || ''
        };

        ajax('POST', window.location.pathname + '?action=add_payment', data, function (r) {
            if (r && r.success) {
                toast('success', r.message || 'Pago registrado.');
                $('#addPaymentModal').modal('hide');
                loadSales();
            } else {
                toast('error', r?.message || 'Error al registrar pago');
            }
        });
    });

    // ---------------------------
    // VER DETALLES (expuesto)
    // ---------------------------
    window.viewSale = function (id) {
        if (!id) return;
        $('#saleDetailsContent').html('<p class="text-center text-muted">Cargando...</p>');
        ajax('GET', window.location.pathname + `?action=get_by_id&id=${encodeURIComponent(id)}`, null, function (r) {
            if (r && r.success) {
                const s = r.venta || r.sale || {};
                let html = `<h5>Venta #${esc(s.venta_id ?? s.id ?? '')}</h5>`;
                html += `<p><strong>Cliente:</strong> ${esc(s.nombre_cliente ?? s.cliente_nombre ?? '')}</p>`;
                html += `<p><strong>Vendedor:</strong> ${esc(s.nombre_empleado ?? s.empleado_nombre ?? '')}</p>`;
                html += `<p><strong>Fecha:</strong> ${esc(fmtDate(s.fecha ?? s.created_at ?? s.fec_creacion))}</p>`;
                html += `<table class="table"><thead><tr><th>Producto</th><th class="text-end">Precio</th><th class="text-center">Cant</th><th class="text-end">Subtotal</th></tr></thead><tbody>`;
                (s.prendas ?? s.productos ?? []).forEach(it => {
                    const name = it.nombre_prenda ?? it.nombre ?? `ID:${it.prenda_id ?? it.id}`;
                    const precio = parseFloat(it.precio_unitario ?? it.precio ?? 0);
                    const qty = parseInt(it.cantidad ?? 1);
                    const sub = parseFloat(it.subtotal ?? (precio * qty));
                    html += `<tr><td>${esc(name)}</td><td class="text-end">${fmt(precio)}</td><td class="text-center">${esc(qty)}</td><td class="text-end">${fmt(sub)}</td></tr>`;
                });
                html += `</tbody></table>`;
                html += `<div class="d-flex justify-content-between"><b>Total:</b><b>${fmt(parseFloat(s.monto_total ?? s.monto ?? s.monto_calculado ?? 0))}</b></div>`;
                $('#saleDetailsContent').html(html);
                $('#viewSaleModal').modal('show');
            } else {
                $('#saleDetailsContent').html('<p class="text-center text-muted">No se encontraron detalles.</p>');
                toast('error', r?.message || 'No se encontraron detalles de la venta');
            }
        });
    };

    // ---------------------------
    // ANULAR VENTA (expuesto)
    // ---------------------------
    window.cancelSale = function (ventaId) {
        if (!ventaId) return;
        Swal.fire({
            title: '¿Anular venta?',
            text: 'Esto liberará las prendas asociadas y marcará la venta como anulada.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, anular',
            cancelButtonText: 'Cancelar'
        }).then(res => {
            if (!res.isConfirmed) return;
            ajax('POST', window.location.pathname + '?action=cancel_sale', { venta_id: ventaId }, function (r) {
                if (r && r.success) {
                    toast('success', r.message || 'Venta anulada');
                    loadSales();
                    loadProducts();
                } else {
                    toast('error', r?.message || 'Error al anular venta');
                }
            });
        });
    };

    // ---------------------------
    // Inicialización final
    // ---------------------------
    // limpiar modales al cerrarlos
    $('#addSaleModal, #addPaymentModal, #viewSaleModal').on('hidden.bs.modal', function () {
        $(this).find('form').each(function () {
            this.reset && this.reset();
            $(this).find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
        });
        // limpiar container productos en modal registrar (cuando se cierre)
        if ($(this).attr('id') === 'addSaleModal') {
            $productsContainer.empty();
            cart = [];
            pid = 0;
            $noProductsAlert.show();
            calcTotals();
        }
    });

    // exponer algunas funciones globales por compatibilidad
    window.calcTotals = calcTotals;
    window.addProductRow = addProductRow;

    // carga inicial
    loadAll();
});
