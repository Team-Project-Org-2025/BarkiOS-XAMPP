import * as Validations from '/BarkiOS/public/assets/js/utils/validation.js';
import * as Helpers from '/BarkiOS/public/assets/js/utils/helpers.js';
import * as Ajax from '/BarkiOS/public/assets/js/utils/ajax-handler.js';

$(document).ready(function() {
    const baseUrl = window.location.pathname;
    let accountsTable = null;
    let currentSaldo = 0;
    
    const METODOS_USD = ["EFECTIVO", "TRANSFERENCIA"];
    const METODOS_BS = ["EFECTIVO", "PAGO MOVIL", "TRANSFERENCIA"];
    const MARGEN_ERROR_BS = 10;

    const initDataTable = () => {
        accountsTable = $('#accountsTable').DataTable({
            ajax: {
                url: `${baseUrl}?action=get_accounts`,
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataSrc: (json) => {
                    if (!json.success) {
                        Helpers.toast('warning', json.message || 'No se pudieron cargar las cuentas');
                        return [];
                    }
                    updateStats(json.data || []);
                    return json.data || [];
                }
            },
            columns: [
                { data: 'factura_numero', render: d => `<strong>#${d}</strong>`, className: 'px-4' },
                { 
                    data: 'nombre_proveedor', 
                    render: (d, t, row) => `<div><strong>${Helpers.escapeHtml(d)}</strong></div><small class="text-muted">${row.tipo_rif}-${row.proveedor_rif}</small>`
                },
                { data: 'fecha_compra', render: d => `<small>${Helpers.formatDate(d)}</small>` },
                { data: 'fecha_vencimiento', render: d => `<small>${Helpers.formatDate(d)}</small>` },
                { data: 'monto_total', className: 'text-end', render: d => `<strong>${Helpers.formatCurrency(d)}</strong>` },
                { data: 'total_pagado', className: 'text-end', render: d => `<span class="text-success">${Helpers.formatCurrency(d)}</span>` },
                {
                    data: 'saldo_pendiente',
                    className: 'text-end',
                    render: (saldo) => {
                        const s = parseFloat(saldo) || 0;
                        return `<strong class="${s > 0 ? 'text-danger' : 'text-success'}">${Helpers.formatCurrency(s)}</strong>`;
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    render: (row) => {
                        const hoy = new Date();
                        const vencimiento = new Date(row.fecha_vencimiento);
                        const diasVencer = Math.ceil((vencimiento - hoy) / (1000 * 60 * 60 * 24));

                        if (row.estado === 'vencido' || row.vencida) return '<span class="badge bg-danger">Vencida</span>';
                        if (diasVencer <= 7 && diasVencer > 0) return `<span class="badge bg-warning">Vence en ${diasVencer}d</span>`;
                        if (row.estado === 'pagado') return '<span class="badge bg-success">Pagado</span>';
                        return '<span class="badge bg-warning">Pagando</span>';
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: (row) => `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-view" data-id="${row.cuenta_pagar_id}"><i class="fas fa-eye"></i></button>
                            ${parseFloat(row.saldo_pendiente) > 0 ? `
                                <button class="btn btn-outline-success btn-pay" 
                                        data-id="${row.cuenta_pagar_id}"
                                        data-proveedor="${Helpers.escapeHtml(row.nombre_proveedor)}"
                                        data-factura="${row.factura_numero}"
                                        data-total="${row.monto_total}"
                                        data-saldo="${row.saldo_pendiente}">
                                    <i class="fas fa-dollar-sign"></i>
                                </button>
                            ` : ''}
                        </div>
                    `
                }
            ],
            order: [[3, 'asc']],
            pageLength: 10,
            responsive: true,
            autoWidth: false,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json' },
            dom: '<"d-flex justify-content-between align-items-center mb-2"lfB>tip',
            buttons: [{
                text: '<i class="fas fa-sync-alt"></i> Actualizar',
                className: 'btn btn-outline-secondary btn-sm',
                action: () => accountsTable.ajax.reload(null, false)
            }]
        });
    };

    //Actualizar estadisticas
    const updateStats = (accounts) => {
        let totalCuentas = accounts.length;
        let deudaTotal = 0, porVencer = 0, vencidas = 0;
        
        const hoy = new Date();
        const en7Dias = new Date(hoy.getTime() + 7 * 24 * 60 * 60 * 1000);
        
        accounts.forEach(c => {
            const saldo = parseFloat(c.saldo_pendiente || 0);
            deudaTotal += saldo;
            
            if (c.estado === 'vencido' || c.vencida) {
                vencidas++;
            } else if (saldo > 0) {
                const vencimiento = new Date(c.fecha_vencimiento);
                if (vencimiento <= en7Dias && vencimiento > hoy) porVencer++;
            }
        });
        
        $('#statTotalCuentas').text(totalCuentas);
        $('#statDeudaTotal').text(Helpers.formatCurrency(deudaTotal));
        $('#statPorVencer').text(porVencer);
        $('#statVencidas').text(vencidas);
    };

    //Registrar pago
    $(document).on('click', '.btn-pay', function() {
        const $btn = $(this);
        currentSaldo = parseFloat($btn.data('saldo'));
        const saldoBS = currentSaldo * DOLAR_BCV_RATE;

        $('#paymentCuentaId').val($btn.data('id'));
        $('#paymentProveedor').text($btn.data('proveedor'));
        $('#paymentFactura').text($btn.data('factura'));
        $('#paymentMontoTotal').text(parseFloat($btn.data('total')).toFixed(2));
        $('#paymentSaldo').html(`<strong>${Helpers.formatCurrency(currentSaldo)}</strong><br><small class="text-muted">≈ Bs ${saldoBS.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</small>`);
        
        Helpers.resetForm($('#addPaymentForm'));
        $('#equivInfo').html('').hide();
        cambiarMoneda("USD");
        $('#addPaymentModal').modal('show');
    });

    const cambiarMoneda = (moneda) => {
    const $tipo = $('#paymentTipo');
    $tipo.empty().append(`<option value="">Seleccione un método de pago</option>`);
    (moneda === "USD" ? METODOS_USD : METODOS_BS).forEach(m => $tipo.append(`<option value="${m}">${m}</option>`));
    $('#referenciaField, #bancoField').hide().find('input').val('').removeClass('is-valid is-invalid');
    moneda === "BS" ? $('#equivInfo').show() : $('#equivInfo').hide().html('');
};

    $('#paymentMoneda').on('change', function() {
        cambiarMoneda($(this).val());
        $('#paymentMontoGeneral').val() && $('#paymentMontoGeneral').trigger('input');
    });

    // Validación de monto con margen
$('#paymentMontoGeneral').on('input', function() {
    const moneda = $('#paymentMoneda').val();
    const valor = parseFloat($(this).val());
    const $equiv = $('#equivInfo');

    $(this).siblings('.invalid-feedback').remove();

    if (!valor || valor <= 0 || isNaN(valor)) {
        $(this).addClass('is-invalid').removeClass('is-valid').after('<div class="invalid-feedback">Ingrese un monto válido</div>');
        $('#paymentMonto').val('');
        $equiv.html('').hide();
        return;
    }

    let montoUSD = 0, esValido = false;

    if (moneda === "USD") {
        montoUSD = valor;
        if (montoUSD > currentSaldo) {
            $(this).addClass('is-invalid').removeClass('is-valid').after(`<div class="invalid-feedback">Excede el saldo (${Helpers.formatCurrency(currentSaldo)})</div>`);
        } else {
            esValido = true;
            $equiv.html(`<span class="text-info"><i class="fas fa-dollar-sign me-1"></i>Monto USD válido.<br><small class="text-muted">Tasa BCV: Bs ${DOLAR_BCV_RATE.toLocaleString('es-VE', {minimumFractionDigits: 2})}</small></span>`).show();
        }
    } else if (moneda === "BS") {
        montoUSD = valor / DOLAR_BCV_RATE;
        const difBS = (montoUSD - currentSaldo) * DOLAR_BCV_RATE;

        if (Math.abs(difBS) <= MARGEN_ERROR_BS) {
            montoUSD = currentSaldo;
            esValido = true;
            $equiv.html(`<span class="text-success"><i class="fas fa-check-circle me-1"></i>Equivale a: <strong>${Helpers.formatCurrency(montoUSD)}</strong></span>`).show();
        } else if (difBS > MARGEN_ERROR_BS) {
            $(this).addClass('is-invalid').removeClass('is-valid').after('<div class="invalid-feedback">Excede el saldo</div>');
            $equiv.html(`<span class="text-danger">Equivale a: ${Helpers.formatCurrency(montoUSD)}</span>`).show();
        } else {
            esValido = true;
            $equiv.html(`<span class="text-info">Equivale a: <strong>${Helpers.formatCurrency(montoUSD)}</strong><br><small class="text-muted">Tasa BCV: Bs ${DOLAR_BCV_RATE.toLocaleString('es-VE', {minimumFractionDigits: 2})}</small></span>`).show();
        }
    }

    if (esValido) {
        $(this).addClass('is-valid').removeClass('is-invalid');
        $('#paymentMonto').val(montoUSD.toFixed(4));
    } else {
        $('#paymentMonto').val('');
    }
});

    // Tipo de pago y campos bancarios
    $('#paymentTipo').on('change', function() {
        const tipo = $(this).val();
        Validations.validateSelect($(this));

        if (tipo === 'EFECTIVO' || !tipo) {
            $('#referenciaField, #bancoField').hide().find('input').val('').removeClass('is-valid is-invalid');
        } else {
            $('#referenciaField, #bancoField').show();
        }
    });

    $('#paymentReferencia').on('input', function() {
        $(this).is(':visible') && Validations.validateField($(this), Validations.REGEX.referencia, Validations.MESSAGES.referencia);
    });

    $('#paymentBanco').on('input', function() {
        $(this).is(':visible') && Validations.validateField($(this), Validations.REGEX.banco, Validations.MESSAGES.banco);
    });

    // Submit pago
    $('#addPaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const montoUSD = parseFloat($('#paymentMonto').val());
        if (!montoUSD || montoUSD <= 0) {
            Helpers.toast('error', 'El monto debe ser acorde al saldo pendiente');
            return;
        }
        
        const tipoPago = $('#paymentTipo').val();
        if (!tipoPago) {
            Helpers.toast('error', 'Seleccione un método de pago');
            return;
        }

        if (tipoPago !== 'EFECTIVO') {
            const ref = $('#paymentReferencia').val().trim();
            const banco = $('#paymentBanco').val().trim();

            if (!Validations.REGEX.referencia.test(ref)) {
                Helpers.toast('error', 'Referencia bancaria inválida (8-10 dígitos)');
                return;
            }
            if (!Validations.REGEX.banco.test(banco)) {
                Helpers.toast('error', 'Banco inválido (mínimo 3 caracteres)');
                return;
            }
        }
        
        const $btn = $('#btnGuardarPago');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');
        
        Ajax.post(`${baseUrl}?action=add_payment`, $(this).serialize())
            .then(res => {
                if (res.success) {
                    Helpers.toast('success', res.message || 'Pago registrado');
                    $('#addPaymentModal').modal('hide');
                    accountsTable.ajax.reload(null, false);
                } else {
                    Helpers.toast('error', res.message);
                }
            })
            .catch(err => Helpers.toast('error', err))
            .finally(() => $btn.prop('disabled', false).html(btnText));
    });

    //Ver detalle
    $(document).on('click', '.btn-view', function() {
        const cuentaId = $(this).data('id');
        
        $('#viewAccountModal').modal('show');
        $('#viewAccountContent').html('<div class="text-center py-5"><div class="spinner-border"></div><p class="mt-2">Cargando...</p></div>');
        
        Ajax.get(`${baseUrl}?action=get_account_detail`, { cuenta_pagar_id: cuentaId })
            .then(data => {
                if (data.success) renderAccountDetails(data.data);
                else $('#viewAccountContent').html('<div class="alert alert-danger">Error al cargar datos</div>');
            })
            .catch(() => $('#viewAccountContent').html('<div class="alert alert-danger">Error de conexión</div>'));
    });

    const renderAccountDetails = (data) => {
        const c = data.cuenta;
        const pagos = data.pagos;
        const prendas = data.prendas;
        
        const saldo = parseFloat(c.saldo_pendiente || 0);
        const total = parseFloat(c.monto_total || 0);
        const pagado = parseFloat(c.total_pagado || 0);
        
        let pagosHtml = '';
        if (pagos.length > 0) {
            pagosHtml = pagos.map(p => `
                <div class="pago-item border-bottom py-2">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Fecha</small><br>
                            <strong>${Helpers.formatDate(p.fecha_pago)}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Monto</small><br>
                            <strong class="text-success">${Helpers.formatCurrency(p.monto)}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Tipo</small><br>
                            <span class="badge bg-info">${p.tipo_pago}</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Estado</small><br>
                            <span class="badge bg-${p.estado_pago === 'CONFIRMADO' ? 'success' : 'warning'}">${p.estado_pago}</span>
                        </div>
                    </div>
                    ${p.referencia_bancaria ? `<small class="text-muted d-block mt-2">Ref: ${p.referencia_bancaria} | ${p.banco || ''}</small>` : ''}
                    ${p.observaciones ? `<small class="text-muted d-block"><em>${p.observaciones}</em></small>` : ''}
                </div>
            `).join('');
        } else {
            pagosHtml = '<p class="text-muted text-center">No hay pagos registrados</p>';
        }
        
        let prendasHtml = '';
        if (prendas && prendas.length > 0) {
            prendasHtml = `
                <hr>
                <h6 class="text-primary mb-3"><i class="fas fa-box me-2"></i>Productos de la Compra (${prendas.length})</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr><th>Código</th><th>Nombre</th><th>Categoría</th><th class="text-end">P. Costo</th><th class="text-center">Estado</th></tr>
                        </thead>
                        <tbody>
                            ${prendas.map(pr => `
                                <tr>
                                    <td><code>${pr.codigo_prenda}</code></td>
                                    <td>${pr.nombre}</td>
                                    <td><span class="badge bg-info">${pr.categoria}</span></td>
                                    <td class="text-end">${Helpers.formatCurrency(pr.precio_costo)}</td>
                                    <td class="text-center">${Helpers.getBadge(pr.estado)}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
        }
        
        const html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">Información de la Compra</h6>
                    <p class="mb-2"><strong>Proveedor:</strong> ${c.nombre_proveedor}</p>
                    <p class="mb-2"><strong>RIF:</strong> ${c.tipo_rif}-${c.proveedor_rif}</p>
                    <p class="mb-2"><strong>Factura:</strong> #${c.factura_numero}</p>
                    <p class="mb-2"><strong>Fecha Compra:</strong> ${Helpers.formatDate(c.fecha_compra)}</p>
                    <p class="mb-2"><strong>Vencimiento:</strong> ${Helpers.formatDate(c.fecha_vencimiento)}</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary mb-3">Resumen Financiero</h6>
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Monto Total:</span>
                                <strong>${Helpers.formatCurrency(total)}</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Total Pagado:</span>
                                <strong class="text-success">${Helpers.formatCurrency(pagado)}</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span><strong>Saldo Pendiente:</strong></span>
                                <strong class="${saldo > 0 ? 'text-danger' : 'text-success'} fs-5">${Helpers.formatCurrency(saldo)}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            ${c.observaciones ? `
                <div class="alert alert-info">
                    <strong><i class="fas fa-info-circle me-2"></i>Observaciones:</strong>
                    <p class="mb-0 mt-2">${c.observaciones}</p>
                </div>
            ` : ''}
            
            <hr>
            <h6 class="text-primary mb-3"><i class="fas fa-money-bill-wave me-2"></i>Historial de Pagos (${pagos.length})</h6>
            ${pagosHtml}
            ${prendasHtml}
        `;
        
        $('#viewAccountContent').html(html);
    };

    //Busqueda y filtros
    $('#searchInput').on('input', Helpers.debounce(function() {
        accountsTable.search($(this).val()).draw();
    }, 300));

    $('.modal').on('hidden.bs.modal', function() {
        Helpers.resetForm($(this).find('form'));
    });

    initDataTable();
});