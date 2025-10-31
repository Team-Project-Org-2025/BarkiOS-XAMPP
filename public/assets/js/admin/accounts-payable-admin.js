/**
 * ============================================
 * MÓDULO DE CUENTAS POR PAGAR - GARAGE BARKI
 * Versión refactorizada v2.0 (ES6 Module)
 * ============================================
 */

import * as Validations from '/BarkiOS/public/assets/js/utils/validation.js';
import * as Helpers from '/BarkiOS/public/assets/js/utils/helpers.js';
import * as Ajax from '/BarkiOS/public/assets/js/utils/ajax-handler.js';

$(document).ready(function() {
    const baseUrl = window.location.pathname;
    let allAccounts = [];
    let currentSaldo = 0;
    
    const METODOS_USD = ["EFECTIVO", "TRANSFERENCIA"];
    const METODOS_BS = ["EFECTIVO", "PAGO MOVIL", "TRANSFERENCIA"];
    const MARGEN_ERROR_BS = 10;

    // ============================================
    // CARGAR CUENTAS POR PAGAR
    // ============================================
    // ============================================
// INICIALIZAR DATATABLE DE CUENTAS POR PAGAR
// ============================================
let accountsTable = null;

const initAccountsTable = () => {
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
                // Actualizar estadísticas
                allAccounts = json.data || [];
                if (typeof updateStats === 'function') updateStats();
                return json.data || [];
            }
        },
        columns: [
            { data: 'factura_numero', render: d => `<strong>#${d}</strong>`, className: 'px-4' },
            { data: 'nombre_proveedor', render: (d, t, row) => `
                <div><strong>${Helpers.escapeHtml(d)}</strong></div>
                <small class="text-muted">${row.tipo_rif}-${row.proveedor_rif}</small>
            `},
            { data: 'fecha_compra', render: d => `<small>${Helpers.formatDate(d)}</small>` },
            { data: 'fecha_vencimiento', render: d => `<small>${Helpers.formatDate(d)}</small>` },
            {
                data: 'monto_total',
                className: 'text-end',
                render: d => `<strong>${Helpers.formatCurrency(d)}</strong>`
            },
            {
                data: 'total_pagado',
                className: 'text-end',
                render: d => `<span class="text-success">${Helpers.formatCurrency(d)}</span>`
            },
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

                    let estadoBadge = '<span class="badge bg-success">Al día</span>';
                    if (row.estado === 'vencido' || row.vencida) {
                        estadoBadge = '<span class="badge bg-danger">Vencida</span>';
                    } else if (diasVencer <= 7 && diasVencer > 0) {
                        estadoBadge = `<span class="badge bg-warning">Vence en ${diasVencer}d</span>`;
                    } else if (row.estado === 'pagado') {
                        estadoBadge = '<span class="badge bg-secondary">Pagando</span>';
                    }
                    return estadoBadge;
                }
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                render: (row) => `
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary btn-view" data-id="${row.cuenta_pagar_id}">
                            <i class="fas fa-eye"></i>
                        </button>
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
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
        },
        dom: '<"d-flex justify-content-between align-items-center mb-2"lfB>tip',
        buttons: [
            {
                text: '<i class="fas fa-sync-alt"></i> Actualizar',
                className: 'btn btn-outline-secondary btn-sm',
                action: function() {
                    accountsTable.ajax.reload(null, false);
                }
            }
        ]
    });
};

    // ============================================
    // ACTUALIZAR ESTADÍSTICAS
    // ============================================
    const updateStats = () => {
        let totalCuentas = allAccounts.length;
        let deudaTotal = 0;
        let porVencer = 0;
        let vencidas = 0;
        
        const hoy = new Date();
        const en7Dias = new Date(hoy.getTime() + 7 * 24 * 60 * 60 * 1000);
        
        allAccounts.forEach(c => {
            const saldo = parseFloat(c.saldo_pendiente || 0);
            deudaTotal += saldo;
            
            if (c.estado === 'vencido' || c.vencida) {
                vencidas++;
            } else if (saldo > 0) {
                const vencimiento = new Date(c.fecha_vencimiento);
                if (vencimiento <= en7Dias && vencimiento > hoy) {
                    porVencer++;
                }
            }
        });
        
        $('#statTotalCuentas').text(totalCuentas);
        $('#statDeudaTotal').text(Helpers.formatCurrency(deudaTotal));
        $('#statPorVencer').text(porVencer);
        $('#statVencidas').text(vencidas);
    };

    // ============================================
    // REGISTRAR PAGO
    // ============================================
    $(document).on('click', '.btn-pay', function() {
        const $btn = $(this);
        const cuentaId = $btn.data('id');
        const proveedor = $btn.data('proveedor');
        const factura = $btn.data('factura');
        const montoTotal = parseFloat($btn.data('total'));
        const saldo = parseFloat($btn.data('saldo'));

        currentSaldo = saldo;
        const saldoPendienteBS = saldo * (window.DOLAR_BCV_RATE || 1);

        $('#paymentCuentaId').val(cuentaId);
        $('#paymentProveedor').text(proveedor);
        $('#paymentFactura').text(factura);
        $('#paymentMontoTotal').text(montoTotal.toFixed(2));
        $('#paymentSaldo').html(`
            <strong>${Helpers.formatCurrency(saldo)}</strong>
            <br><small class="text-muted">≈ Bs ${saldoPendienteBS.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</small>
        `);
        
        $('#addPaymentForm')[0].reset();
        $('#paymentMontoGeneral').val('').removeClass('is-valid is-invalid');
        $('#paymentMonto').val('');
        $('#equivInfo').html('').hide();
        $('#paymentTipo').empty().removeClass('is-valid is-invalid');
        
        cambiarMoneda("USD");
        $('#addPaymentModal').modal('show');
    });

    // ============================================
    // CAMBIO DE MONEDA
    // ============================================
    const cambiarMoneda = (moneda) => {
        const $tipoPago = $('#paymentTipo');
        $tipoPago.empty().removeClass('is-valid is-invalid');
        $tipoPago.append(`<option value="" disabled selected>Seleccione un método de pago</option>`);

        const metodos = moneda === "USD" ? METODOS_USD : METODOS_BS;
        metodos.forEach(m => $tipoPago.append(`<option value="${m}">${m}</option>`));
        
        $('#referenciaField, #bancoField').hide();
        $('#paymentReferencia, #paymentBanco').val('').removeClass('is-valid is-invalid');

        if (moneda === "BS") {
            $('#equivInfo').show();
        } else {
            $('#equivInfo').hide().html('');
        }
    };

    $('#paymentMoneda').on('change', function() {
        cambiarMoneda($(this).val());
        if ($('#paymentMontoGeneral').val()) {
            $('#paymentMontoGeneral').trigger('input');
        }
        if ($(this).val() === "BS") {
            $('#paymentSaldo small').text(`≈ Bs ${(currentSaldo * DOLAR_BCV_RATE).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`);
        } else {
            $('#paymentSaldo small').text('');
        }
    });

    // ============================================
    // VALIDACIÓN MONTO CON MARGEN
    // ============================================
    $('#paymentMontoGeneral').on('input', function() {
        const moneda = $('#paymentMoneda').val();
        const valorIngresado = parseFloat($(this).val());

        $(this).siblings('.invalid-feedback').remove();

        if (!valorIngresado || valorIngresado <= 0 || isNaN(valorIngresado)) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            $(this).after('<div class="invalid-feedback">Ingrese un monto válido</div>');
            $('#paymentMonto').val('');
            $('#equivInfo').html('').hide();
            return;
        }

        let montoUSD = 0;
        let esValido = false;

        if (moneda === "USD") {
            montoUSD = valorIngresado;
            
            if (montoUSD > currentSaldo) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                $(this).after(`<div class="invalid-feedback">El monto excede el saldo pendiente (${Helpers.formatCurrency(currentSaldo)})</div>`);
            } else {
                esValido = true;
            }

        } else if (moneda === "BS") {
            const valorBs = valorIngresado;
            montoUSD = valorBs / DOLAR_BCV_RATE;

            // Diferencia en Bs entre el monto ingresado y el saldo equivalente
            const saldoBs = currentSaldo * DOLAR_BCV_RATE;
            const diferenciaBs = valorBs - saldoBs;

            if (Math.abs(diferenciaBs) <= MARGEN_ERROR_BS) {
                // ✅ Si la diferencia está dentro del margen permitido, ajustar al saldo real
                montoUSD = currentSaldo;
                esValido = true;
            
                $('#equivInfo').html(`
                    <span class="text-success">
                        <i class="fas fa-check-circle me-1"></i>
                        Equivale a: <strong>$${montoUSD.toFixed(2)}</strong>
                        <small class="d-block">Monto dentro del margen de ±${MARGEN_ERROR_BS} Bs</small>
                    </span>
                `).show();
                
            } else if (valorBs > saldoBs) {
                // ❌ Monto excede el saldo
                $(this).addClass('is-invalid').removeClass('is-valid');
                $(this).after(`<div class="invalid-feedback">El monto excede el saldo pendiente (${saldoBs.toFixed(2)} Bs)</div>`);
                $('#equivInfo').html(`<span class="text-danger">Equivale a: $${montoUSD.toFixed(2)}</span>`).show();
            
            } else {
                // ✅ Monto menor al saldo
                esValido = true;
                $('#equivInfo').html(`
                    <span class="text-info">
                        Equivale a: <strong>$${montoUSD.toFixed(2)}</strong>
                        <small class="d-block">Saldo restante después del pago: 
                        ${(saldoBs - valorBs).toFixed(2)} Bs</small>
                    </span>
                `).show();
            }
        }
    });

    // ============================================
    // VALIDACIÓN TIPO PAGO Y CAMPOS BANCARIOS
    // ============================================
    $('#paymentTipo').on('change', function() {
        const tipo = $(this).val();
        const $refBancaria = $('#referenciaField');
        const $banco = $('#bancoField');
        const $refInput = $('#paymentReferencia');
        const $bancoInput = $('#paymentBanco');

        Validations.validateSelect($(this));

        if (tipo === 'EFECTIVO' || !tipo) {
            $refBancaria.hide();
            $banco.hide();
            $refInput.val('').removeClass('is-valid is-invalid');
            $bancoInput.val('').removeClass('is-valid is-invalid');
        } else {
            $refBancaria.show();
            $banco.show();
        }
    });

    // Validaciones en tiempo real
    const setupPaymentValidations = () => {
        $('#paymentReferencia').on('input', function() {
            if (!$(this).is(':visible')) return;
            Validations.validateField($(this), Validations.REGEX.referencia, Validations.MESSAGES.referencia);
        });

        $('#paymentBanco').on('input', function() {
            if (!$(this).is(':visible')) return;
            Validations.validateField($(this), Validations.REGEX.banco, Validations.MESSAGES.banco);
        });
    };

    setupPaymentValidations();

    // ============================================
    // SUBMIT PAGO
    // ============================================
    $('#addPaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const montoUSD = parseFloat($('#paymentMonto').val());
        
        if (!montoUSD || montoUSD <= 0) {
            Helpers.toast('error', 'El monto debe ser acorde al saldo pendiente');
            return;
        }
        
        if (!$('#paymentTipo').val()) {
            Helpers.toast('error', 'Seleccione un método de pago');
            return;
        }

        const tipoPago = $('#paymentTipo').val();
        if (tipoPago !== 'EFECTIVO') {
            const refBancaria = $('#paymentReferencia').val().trim();
            const banco = $('#paymentBanco').val().trim();

            if (!Validations.REGEX.referencia.test(refBancaria)) {
                $('#paymentReferencia').addClass('is-invalid');
                Helpers.toast('error', 'Referencia bancaria inválida (8-10 dígitos)');
                return;
            }

            if (!Validations.REGEX.banco.test(banco)) {
                $('#paymentBanco').addClass('is-invalid');
                Helpers.toast('error', 'El nombre del banco debe tener al menos 3 caracteres válidos');
                return;
            }
        }
        
        const $btn = $('#btnGuardarPago');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');
        
        Ajax.post(`${baseUrl}?action=add_payment`, $(this).serialize())
            .then(res => {
                if (res.success) {
                    Helpers.toast('success', res.message || 'Pago registrado correctamente');
                    $('#addPaymentModal').modal('hide');
                    if (accountsTable) accountsTable.ajax.reload(null, false);
                } else {
                    Helpers.toast('error', res.message);
                }
            })
            .catch(err => Helpers.toast('error', err))
            .finally(() => $btn.prop('disabled', false).html(btnText));
    });

    // ============================================
    // VER DETALLE
    // ============================================
    $(document).on('click', '.btn-view', function() {
        const cuentaId = $(this).data('id');
        
        $('#viewAccountModal').modal('show');
        $('#viewAccountContent').html('<div class="text-center py-5"><div class="spinner-border"></div><p class="mt-2">Cargando...</p></div>');
        
        Ajax.get(`${baseUrl}?action=get_account_detail`, { cuenta_pagar_id: cuentaId })
            .then(data => {
                if (data.success) {
                    renderAccountDetails(data.data);
                } else {
                    $('#viewAccountContent').html('<div class="alert alert-danger">Error al cargar los datos</div>');
                }
            })
            .catch(() => {
                $('#viewAccountContent').html('<div class="alert alert-danger">Error de conexión</div>');
            });
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
                            <small class="text-muted">Fecha</small>
                            <br><strong>${Helpers.formatDate(p.fecha_pago)}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Monto</small>
                            <br><strong class="text-success">${Helpers.formatCurrency(p.monto)}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Tipo</small>
                            <br><span class="badge bg-info">${p.tipo_pago}</span>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Estado</small>
                            <br><span class="badge bg-${p.estado_pago === 'CONFIRMADO' ? 'success' : 'warning'}">${p.estado_pago}</span>
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
                <h6 class="text-primary mb-3">
                    <i class="fas fa-box me-2"></i>Productos de la Compra (${prendas.length})
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th class="text-end">P. Costo</th>
                                <th class="text-center">Estado</th>
                            </tr>
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
            
            <h6 class="text-primary mb-3">
                <i class="fas fa-money-bill-wave me-2"></i>Historial de Pagos (${pagos.length})
            </h6>
            ${pagosHtml}
            
            ${prendasHtml}
        `;
        
        $('#viewAccountContent').html(html);
    };

    // ============================================
    // FILTROS Y BÚSQUEDA
    // ============================================
    $('#searchInput').on('input', Helpers.debounce(function() {
        const term = $(this).val().toLowerCase();
        const filtered = allAccounts.filter(c => 
            c.factura_numero.includes(term) || 
            c.nombre_proveedor.toLowerCase().includes(term) ||
            (c.proveedor_rif && c.proveedor_rif.toString().includes(term))
        );
        renderAccounts(filtered);
    }, 300));
    
    $('#filterEstado').on('change', function() {
        const estado = $(this).val();
        if (!estado) {
            renderAccounts(allAccounts);
            return;
        }
        
        const filtered = allAccounts.filter(c => {
            if (estado === 'vencido') {
                return c.estado === 'vencido' || c.vencida;
            }
            return c.estado === estado;
        });
        renderAccounts(filtered);
    });

    // ============================================
    // LIMPIAR MODALES
    // ============================================
    $('.modal').on('hidden.bs.modal', function() {
        Helpers.resetForm($(this).find('form'));
    });

    // ============================================
    // INICIALIZAR
    // ============================================
    initAccountsTable();
});