// =========================================================
// SALES ADMIN - CORREGIDO
// =========================================================
const BASE = window.location.origin;

const REGEX = {
    cedula: /^\d{7,10}$/,
    ref: /^[A-Za-z0-9\-]+$/,
    num: /^\d+(\.\d{1,2})?$/
};

let clients = [], employees = [], products = [], cart = [], pid = 0;

// =========================================================
// INIT
// =========================================================
$(function() {
    loadSales();
    loadClients();
    loadEmployees();
    loadProducts();

    $('#addSaleForm').on('submit', handleAddSale);
    $('#addPaymentForm').on('submit', handleAddPayment);
    $('#searchInput').on('keyup', handleSearch);
    $('#btnAddProduct').on('click', addProductRow);
    $('#add_descuento').on('input', calcTotals);
    $('#payment_metodo').on('change', togglePaymentFields);

    // Validaciones en tiempo real
    $('#payment_monto').on('input', function() {
        const val = parseFloat($(this).val());
        const maxText = $('#payment_saldo_pendiente').text() || '';
        const max = parseFloat(maxText.replace(/[^0-9.-]+/g, '')) || 0;
        $(this).toggleClass('is-invalid', isNaN(val) || val <= 0 || val > max);
        $(this).toggleClass('is-valid', !isNaN(val) && val > 0 && val <= max);
    });

    $('#payment_referencia').on('input', function() {
        const val = $(this).val();
        $(this).toggleClass('is-invalid', val.length > 0 && !REGEX.ref.test(val));
        $(this).toggleClass('is-valid', REGEX.ref.test(val));
    });

    $('#add_descuento').on('input', function() {
        const val = parseFloat($(this).val());
        $(this).toggleClass('is-invalid', isNaN(val) || val < 0 || val > 100);
        $(this).toggleClass('is-valid', !isNaN(val) && val >= 0 && val <= 100);
    });
});

// =========================================================
// AJAX HELPERS
// =========================================================
function ajax(method, url, data, success) {
    const isFormData = (data instanceof FormData);
    $.ajax({
        url: BASE + url,
        method: method,
        data: data,
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        dataType: 'json',
        processData: !isFormData,
        contentType: isFormData ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
        success: success,
        error: (xhr) => {
            let msg = 'Error en la petición';
            try {
                const json = xhr.responseJSON || (xhr.responseText ? JSON.parse(xhr.responseText) : null);
                if (json && json.message) msg = json.message;
            } catch (e) {}
            toast('error', msg);
        }
    });
}

function toast(type, msg) {
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
}

function fmt(n) {
    const num = Number(n) || 0;
    return new Intl.NumberFormat('es-US', {style: 'currency', currency: 'USD'}).format(num);
}

function fmtDate(d) {
    if (!d) return '';
    const dt = new Date(d);
    if (isNaN(dt)) return String(d);
    return dt.toLocaleDateString('es-ES', {year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'});
}

function esc(t) {
    const div = document.createElement('div');
    div.textContent = t ?? '';
    return div.innerHTML;
}

// =========================================================
// LOADERS
// =========================================================
function loadSales() {
    ajax('GET', '?action=get_sales', null, (r) => {
        if (r && r.success) {
            renderSales(r.sales || []);
            updateStats(r.sales || []);
        } else {
            toast('error', r?.message || 'No se pudieron cargar las ventas');
        }
    });
}

function loadClients() {
    ajax('GET', '?action=get_clients', null, (r) => {
        if (r && r.success) {
            clients = r.clients || [];
            const opts = clients.map(c => `<option value="${esc(c.cliente_ced)}">${esc(c.nombre_cliente)} (${esc(c.cliente_ced)})</option>`).join('');
            $('#add_cliente').html('<option value="">Seleccione...</option>' + opts);
        } else {
            toast('error', r?.message || 'No se pudieron cargar clientes');
        }
    });
}

function loadEmployees() {
    ajax('GET', '?action=get_employees', null, (r) => {
        if (r && r.success) {
            employees = r.employees || [];
            const opts = employees.map(e => `<option value="${esc(e.empleado_ced)}">${esc(e.nombre_empleado)} (${esc(e.empleado_ced)})</option>`).join('');
            $('#add_empleado').html('<option value="">Seleccione...</option>' + opts);
        } else {
            toast('error', r?.message || 'No se pudieron cargar empleados');
        }
    });
}

function loadProducts() {
    ajax('GET', '?action=get_products', null, (r) => {
        if (r && r.success) {
            products = r.products || [];
        } else {
            toast('error', r?.message || 'No se pudieron cargar productos');
        }
    });
}

// =========================================================
// RENDER / UI
// =========================================================
function renderSales(sales) {
    const $body = $('#salesTableBody');
    if (!sales || sales.length === 0) {
        $body.html(`<tr><td colspan="8" class="text-center py-5"><p class="text-muted mb-0">No hay ventas registradas.</p></td></tr>`);
        return;
    }
    let rows = '';
    sales.forEach((s, i) => {
        const ventaNum = s.venta_numero ?? s.id ?? '';
        rows += `<tr>
            <td class="text-center">${i+1}</td>
            <td>${esc(s.cliente_nombre ?? s.cliente ?? '')}</td>
            <td>${esc(s.vendedor_nombre ?? s.vendedor ?? '')}</td>
            <td class="text-center">${esc(fmtDate(s.fecha))}</td>
            <td class="text-end">${fmt(parseFloat(s.monto_total ?? s.total ?? 0))}</td>
            <td class="text-center">${esc(s.tipo_venta ?? s.tipo ?? '')}</td>
            <td class="text-center">${esc(s.estado ?? '')}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-info me-1" onclick="viewSale(${s.id})" title="Ver"><i class="fas fa-eye"></i></button>
                <button class="btn btn-sm btn-success" onclick="openAddPayment(${s.id}, '${esc(ventaNum)}', ${parseFloat(s.saldo_pendiente || 0)})" title="Pagar"><i class="fas fa-money-bill-wave"></i></button>
            </td>
        </tr>`;
    });
    $body.html(rows);
}

function updateStats(sales) {
    const totalSales = (sales || []).length;
    const revenue = (sales || []).reduce((acc, s) => acc + (parseFloat(s.monto_total ?? s.total ?? 0) || 0), 0);
    const pending = (sales || []).reduce((acc, s) => acc + (parseFloat(s.saldo_pendiente || 0) || 0), 0);
    const completed = (sales || []).filter(s => (s.estado || '').toLowerCase().includes('complet')).length;
    $('#totalSales').text(totalSales);
    $('#totalRevenue').text(fmt(revenue));
    $('#totalPending').text(fmt(pending));
    $('#completedSales').text(completed);
}

// =========================================================
// SEARCH
// =========================================================
function handleSearch(e) {
    const q = $(e.target).val().toLowerCase();
    $('#salesTableBody tr').each(function() {
        const text = $(this).text().toLowerCase();
        $(this).toggle(text.indexOf(q) !== -1);
    });
}

// =========================================================
// CART / PRODUCTS
// =========================================================
function addProductRow() {
    if (!products || products.length === 0) {
        toast('info', 'No hay productos cargados.');
        return;
    }
    $('#noProductsAlert').hide();
    pid++;
    const rowId = `prod_${pid}`;
    cart.push({ id: rowId, product_id: '', qty: 1, price: 0, subtotal: 0 });

    const options = products.map(p => {
        const price = parseFloat(p.precio ?? p.price ?? 0);
        const name = p.nombre ?? p.nombre_prenda ?? p.name ?? `ID:${p.id}`;
        return `<option value="${esc(p.id)}" data-price="${price}">${esc(name)} - ${fmt(price)}</option>`;
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
    </div>`;

    $('#productsContainer').append(html);

    // eventos
    $(`#${rowId} .product-select`).on('change', function() {
        const r = $(this).data('row');
        const prodId = $(this).val();
        const price = parseFloat($(this).find('option:selected').data('price') || 0);
        updateProductRow(r, prodId, price);
    });

    $(`#${rowId} .product-qty`).on('input', function() {
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

    $(`#${rowId} .remove-product`).on('click', function() {
        const r = $(this).data('row');
        removeProductRow(r);
    });

    calcTotals();
}

function updateProductRow(rowId, productId, price) {
    const item = cart.find(c => c.id === rowId);
    if (!item) return;
    item.product_id = productId;
    item.price = parseFloat(price || 0);
    item.subtotal = item.price * (item.qty || 1);
    $(`#${rowId} .product-price`).val(item.price.toFixed(2));
    $(`#${rowId} .product-subtotal`).text(fmt(item.subtotal));
    calcTotals();
}

function removeProductRow(rowId) {
    cart = cart.filter(c => c.id !== rowId);
    $(`#${rowId}`).remove();
    if (cart.length === 0) $('#noProductsAlert').show();
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

// =========================================================
// FORM: ADD SALE
// =========================================================
function handleAddSale(e) {
    e.preventDefault();

    const cliente = $('#add_cliente').val();
    const empleado = $('#add_empleado').val();
    if (!cliente || !empleado) {
        toast('error', 'Seleccione cliente y vendedor.');
        return;
    }

    const productosPayload = cart
        .filter(i => i.product_id)
        .map(i => ({
            prenda_id: i.product_id,
            cantidad: i.qty,
            precio_unitario: i.price
        }));

    if (productosPayload.length === 0) {
        toast('error', 'Agregue al menos un producto válido.');
        return;
    }

    // Construir FormData para que PHP reciba $_POST['productos'] como array multidimensional
    const formData = new FormData();
    formData.append('cliente_ced', cliente);
    formData.append('empleado_ced', empleado);
    formData.append('tipo_venta', $('select[name="tipo_venta"]').val() || '');
    formData.append('metodo_pago_principal', $('select[name="metodo_pago_principal"]').val() || '');
    formData.append('descuento', $('#add_descuento').val() || 0);
    formData.append('observaciones', $('textarea[name="observaciones"]').val() || '');

    productosPayload.forEach((p, i) => {
        formData.append(`productos[${i}][prenda_id]`, p.prenda_id);
        formData.append(`productos[${i}][cantidad]`, p.cantidad);
        formData.append(`productos[${i}][precio_unitario]`, p.precio_unitario);
    });

    // Enviar a action=add_ajax (tal como espera tu controlador)
    $.ajax({
        url: BASE + '?action=add_ajax',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        dataType: 'json',
        success: (r) => {
            if (r && r.success) {
                toast('success', r.message || 'Venta registrada.');
                $('#addSaleForm')[0].reset();
                $('#productsContainer').empty();
                cart = [];
                pid = 0;
                $('#noProductsAlert').show();
                calcTotals();
                $('#addSaleModal').modal('hide');
                loadSales();
            } else {
                toast('error', r?.message || 'Error al guardar venta');
            }
        },
        error: (xhr) => {
            let msg = 'Error en la petición';
            try { msg = xhr.responseJSON?.message || JSON.parse(xhr.responseText)?.message || msg; } catch(e){}
            toast('error', msg);
        }
    });
}

// =========================================================
// PAYMENTS
// =========================================================
function openAddPayment(ventaId, ventaNumero, saldo) {
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
}

function handleAddPayment(e) {
    e.preventDefault();
    const venta_id = $('#payment_venta_id').val();
    const monto = parseFloat($('#payment_monto').val() || 0);
    const saldoText = $('#payment_saldo_pendiente').text() || '';
    const saldo = parseFloat(saldoText.replace(/[^0-9.-]+/g, '')) || 0;

    if (!venta_id || isNaN(monto) || monto <= 0 || monto > saldo) {
        toast('error', 'Monto inválido o mayor que el saldo pendiente.');
        return;
    }

    const metodo = $('#payment_metodo').val();
    const referencia = $('#payment_referencia').val() || '';
    const banco = $('input[name="banco"]').val() || '';
    const observaciones = $('textarea[name="payment_observaciones"]').val() || '';

    const form = new FormData();
    form.append('venta_id', venta_id);
    form.append('monto', monto);
    form.append('metodo_pago', metodo);
    if (referencia) form.append('referencia', referencia);
    if (banco) form.append('banco', banco);
    if (observaciones) form.append('observaciones', observaciones);

    $.ajax({
        url: BASE + '?action=add_payment',
        method: 'POST',
        data: form,
        processData: false,
        contentType: false,
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        dataType: 'json',
        success: (r) => {
            if (r && r.success) {
                toast('success', r.message || 'Pago registrado.');
                $('#addPaymentModal').modal('hide');
                loadSales();
            } else {
                toast('error', r?.message || 'Error al registrar pago');
            }
        },
        error: (xhr) => {
            let msg = 'Error en la petición';
            try { msg = xhr.responseJSON?.message || JSON.parse(xhr.responseText)?.message || msg; } catch(e){}
            toast('error', msg);
        }
    });
}

function togglePaymentFields() {
    const m = $('#payment_metodo').val();
    if (['transferencia', 'pago_movil', 'cheque', 'tarjeta_debito', 'tarjeta_credito'].includes(m)) {
        $('#payment_ref_group').show();
        $('#payment_bank_group').show();
    } else {
        $('#payment_ref_group').hide();
        $('#payment_bank_group').hide();
    }
}

// =========================================================
// DETAILS
// =========================================================
function viewSale(id) {
    if (!id) return;
    ajax('GET', `?action=get_by_id&id=${encodeURIComponent(id)}`, null, (r) => {
        if (r && r.success) {
            const s = r.sale || {};
            let html = `<h5>Venta #${esc(s.venta_numero ?? s.id ?? '')}</h5>`;
            html += `<p><strong>Cliente:</strong> ${esc(s.cliente_nombre ?? s.cliente ?? '')}</p>`;
            html += `<p><strong>Vendedor:</strong> ${esc(s.vendedor_nombre ?? s.vendedor ?? '')}</p>`;
            html += `<p><strong>Fecha:</strong> ${esc(fmtDate(s.fecha))}</p>`;
            html += `<table class="table"><thead><tr><th>Producto</th><th class="text-end">Precio</th><th class="text-center">Cant</th><th class="text-end">Subtotal</th></tr></thead><tbody>`;
            (s.productos ?? s.items ?? []).forEach(it => {
                const name = it.nombre ?? it.nombre_prenda ?? it.nombre_producto ?? `ID:${it.prenda_id ?? it.id}`;
                const precio = parseFloat(it.precio_unitario ?? it.precio ?? it.precio_venta ?? 0);
                const qty = parseInt(it.cantidad ?? it.qty ?? 0);
                const sub = parseFloat(it.subtotal ?? ((precio * qty) || 0));
                html += `<tr><td>${esc(name)}</td><td class="text-end">${fmt(precio)}</td><td class="text-center">${esc(qty)}</td><td class="text-end">${fmt(sub)}</td></tr>`;
            });
            html += `</tbody></table>`;
            html += `<div class="d-flex justify-content-between"><b>Total:</b><b>${fmt(parseFloat(s.monto_total ?? s.total ?? 0))}</b></div>`;
            $('#saleDetailsContent').html(html);
            $('#viewSaleModal').modal('show');
        } else {
            toast('error', r?.message || 'No se encontraron detalles de la venta');
        }
    });
}
