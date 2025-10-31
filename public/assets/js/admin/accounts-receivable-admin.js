/**
 * ============================================
 * MÓDULO DE CUENTAS POR COBRAR - GARAGE BARKI
 * Versión refactorizada v2.0 (ES6 Module)
 * ============================================
 */

import * as Validations from '/BarkiOS/public/assets/js/utils/validation.js';
import * as Helpers from '/BarkiOS/public/assets/js/utils/helpers.js';
import * as Ajax from '/BarkiOS/public/assets/js/utils/ajax-handler.js';

$(document).ready(function() {

    const baseUrl = '/BarkiOS/admin/accounts-receivable';
    let accounts = [];
    let currentAccountBalance = 0;

    const METODOS_USD = ["EFECTIVO", "TRANSFERENCIA"];
    const METODOS_BS = ["EFECTIVO", "PAGO MOVIL", "TRANSFERENCIA"];
    const MARGEN_ERROR_BS = 10;
    let currentSaldo = 0;
    

    // ============================================
    // CARGAR CUENTAS
    // ============================================
    const loadAccounts = () => {
        $('#accountsTableBody').html(Helpers.spinnerHtml(7));

        Ajax.get(`${baseUrl}?action=get_accounts`)
            .then(r => {
                if (r?.success) {
                    accounts = r.accounts || [];
                    initDataTable(accounts);
                } else {
                    $('#accountsTableBody').html(Helpers.emptyHtml(7, 'No hay cuentas por cobrar registradas'));
                }
            })
            .catch(msg => {
                $('#accountsTableBody').html(`
                    <tr>
                        <td colspan="7" class="text-center py-4 text-danger">
                            Error al cargar cuentas: ${Helpers.escapeHtml(msg)}
                        </td>
                    </tr>
                `);
            });
    };

// ============================================
// INICIALIZAR Y RENDERIZAR DATATABLE
// ============================================
let accountsTable = null;

const initDataTable = (data = []) => {
    const $table = $('#accountsTable');

    // Si ya existe, destruir antes de volver a crear
    if ($.fn.DataTable.isDataTable($table)) {
        $table.DataTable().clear().destroy();
    }

    // Vaciar tbody para evitar duplicados visuales
    $('#accountsTableBody').empty();

    // Inicializar DataTable
    accountsTable = $table.DataTable({
        data,
        pageLength: 10,
        responsive: true,
        order: [[0, 'desc']],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
        },
        columns: [
            { data: 'referencia', render: ref => `<code>${Helpers.escapeHtml(ref)}</code>` },
            { data: 'cliente', render: c => Helpers.escapeHtml(c) },
            { data: 'fecha_emision', render: d => Helpers.formatDate(d) },
            { data: 'saldo_pendiente', render: m => `<strong>${Helpers.formatCurrency(m)}</strong>` },
            { 
                data: null,
                render: acc => {
                    let diasInfo = '';
                    if (acc.dias_restantes > 0) {
                        diasInfo = `<small class="text-muted d-block">Vence en ${acc.dias_restantes} día${acc.dias_restantes !== 1 ? 's' : ''}</small>`;
                    } else if (acc.dias_restantes === 0) {
                        diasInfo = `<small class="text-warning d-block"><strong>¡Vence hoy!</strong></small>`;
                    } else {
                        diasInfo = `<small class="text-danger d-block">Vencida hace ${Math.abs(acc.dias_restantes)} día${Math.abs(acc.dias_restantes) !== 1 ? 's' : ''}</small>`;
                    }
                    return `${Helpers.formatDate(acc.fecha_vencimiento)}${diasInfo}`;
                }
            },
            { 
                data: 'estado_visual',
                render: estado => {
                    const badges = {
                        'Vigente': 'bg-success',
                        'Por vencer': 'bg-warning',
                        'Vencido': 'bg-danger',
                        'Pagado': 'bg-secondary'
                    };
                    return `<span class="badge ${badges[estado] || 'bg-secondary'}">${Helpers.escapeHtml(estado)}</span>`;
                }
            },
            { 
                data: null,
                orderable: false,
                render: acc => {
                    const viewBtn = `
                        <button class="btn btn-sm btn-outline-info btn-view" data-id="${acc.id}">
                            <i class="fas fa-eye"></i>
                        </button>`;
                    
                    if (acc.estado_visual === 'Pagado') return viewBtn;

                    return `
                        ${viewBtn}
                        <button class="btn btn-sm btn-outline-success btn-pay" data-id="${acc.id}">
                            <i class="fas fa-money-bill-wave"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning btn-extend" data-id="${acc.id}">
                            <i class="fas fa-calendar-plus"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${acc.id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });
};

    const getBadgeClass = (estado) => {
        const badges = {
            'Vigente': 'bg-success',
            'Por vencer': 'bg-warning',
            'Vencido': 'bg-danger',
            'Pagado': 'bg-secondary'
        };
        return badges[estado] || 'bg-secondary';
    };

    // ============================================
    // VER DETALLES
    // ============================================
    $(document).on('click', '.btn-view', function() {
        const id = $(this).data('id');
        if (!id) return;

        $('#accountDetailsContent').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2">Cargando detalles...</p></div>');
        $('#viewAccountModal').modal('show');

        Ajax.get(`${baseUrl}?action=get_account_details&id=${id}`)
            .then(r => {
                if (r?.success && r.account) {
                    renderAccountDetails(r.account);
                } else {
                    $('#accountDetailsContent').html('<p class="text-center text-muted">No se encontraron detalles</p>');
                }
            })
            .catch(msg => {
                $('#accountDetailsContent').html(`<p class="text-center text-danger">${Helpers.escapeHtml(msg)}</p>`);
            });
    });

    const renderAccountDetails = (acc) => {
        const diasRestantes = acc.dias_restantes > 0 
            ? `Faltan ${acc.dias_restantes} días`
            : `Vencida hace ${Math.abs(acc.dias_restantes)} días`;

        let html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Cuenta #${Helpers.escapeHtml(acc.cuenta_cobrar_id)}</h5>
                    <p class="mb-1">
                        <strong>Referencia Venta:</strong> 
                        <code>${Helpers.escapeHtml(acc.referencia)}</code>
                    </p>
                    <p class="mb-1">
                        <strong>Estado:</strong> 
                        <span class="badge ${getBadgeClass(acc.estado_visual || acc.estado)}">
                            ${Helpers.escapeHtml(acc.estado)}
                        </span>
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1"><strong>Cliente:</strong> ${Helpers.escapeHtml(acc.nombre_cliente)}</p>
                    <p class="mb-1"><strong>Cédula:</strong> ${Helpers.escapeHtml(acc.cliente_ced)}</p>
                    ${acc.telefono ? `<p class="mb-1"><strong>Teléfono:</strong> ${Helpers.formatPhone(acc.telefono)}</p>` : ''}
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Fecha Emisión:</strong> ${Helpers.formatDate(acc.emision)}</p>
                    <p class="mb-1"><strong>Fecha Vencimiento:</strong> ${Helpers.formatDate(acc.vencimiento)}</p>
                    <p class="mb-1"><small class="text-muted">${diasRestantes}</small></p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1"><strong>Monto Total:</strong> ${Helpers.formatCurrency(acc.monto_total)}</p>
                    <p class="mb-1"><strong>Saldo Pendiente:</strong> 
                        <span class="text-danger fs-5">${Helpers.formatCurrency(acc.saldo_pendiente)}</span>
                    </p>
                    ${acc.total_pagado > 0 ? `
                        <p class="mb-1 text-success"><strong>Total Pagado:</strong> ${Helpers.formatCurrency(acc.total_pagado)}</p>
                    ` : ''}
                </div>
            </div>
        `;

        if (acc.pagos && acc.pagos.length > 0) {
            html += `
                <hr>
                <h6 class="mb-3"><i class="fas fa-history me-2"></i>Historial de Pagos</h6>
                <table class="table table-sm table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th class="text-end">Monto</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
            `;

            acc.pagos.forEach(p => {
                html += `
                    <tr>
                        <td>${Helpers.formatDate(p.fecha_pago)}</td>
                        <td>
                            <span class="badge bg-secondary">${Helpers.escapeHtml(p.tipo_pago)}</span>
                            ${p.referencia_bancaria ? `<br><small>${Helpers.escapeHtml(p.referencia_bancaria)}</small>` : ''}
                        </td>
                        <td class="text-end"><strong>${Helpers.formatCurrency(p.monto)}</strong></td>
                        <td><small class="text-muted">${Helpers.escapeHtml(p.observaciones || '-')}</small></td>
                    </tr>
                `;
            });

            html += `</tbody></table>`;
        } else {
            html += `
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    No se han registrado pagos aún
                </div>
            `;
        }

        $('#accountDetailsContent').html(html);
    };

    // ============================================
    // REGISTRAR PAGO
    // ============================================
    $(document).on('click', '.btn-pay', function() {
        const id = $(this).data('id');
        const account = accounts.find(a => a.id === id);
        if (!account) {
            Helpers.toast('error', 'Cuenta no encontrada');
            return;
        }
        
        currentAccountBalance = parseFloat(account.saldo_pendiente);
        const saldoPendienteBS = currentAccountBalance * DOLAR_BCV_RATE;

        $('#payment_cuenta_id').val(account.id);
        $('#payment_cliente').text(account.cliente);
        $('#payment_saldo').html(`
            <strong>${Helpers.formatCurrency(currentAccountBalance)}</strong>
            <br><small class="text-muted">≈ Bs ${saldoPendienteBS.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</small>
        `);

        $('#registerPaymentForm')[0].reset();
        $('[name="monto_general"]').val('').removeClass('is-valid is-invalid');
        $('[name="monto"]').val('');
        $('#equiv_info').html('').hide();
        $('[name="tipo_pago"]').empty().removeClass('is-valid is-invalid');

        $('#registerPaymentModal').modal('show');
        $('#payment_moneda').trigger('change');
    });

    // ============================================
    // CAMBIO DE MONEDA
    // ============================================
    const cambiarMoneda = (moneda) => {
        const $tipoPago = $('[name="tipo_pago"]');
        $tipoPago.empty().removeClass('is-valid is-invalid');
        $tipoPago.append(`<option value="" disabled selected>Seleccione un método de pago</option>`);

        const metodos = moneda === "USD" ? METODOS_USD : METODOS_BS;
        metodos.forEach(m => $tipoPago.append(`<option value="${m}">${m}</option>`));
        
        $('#refBancariaGroup, #bancoGroup').hide();

        if (moneda === "BS") {
            $('#equiv_info').show();
        } else {
            $('#equiv_info').hide().html('');
        }
    };

    $('#payment_moneda').on('change', function() {
        cambiarMoneda($(this).val());
        if ($('[name="monto_general"]').val()) {
            $('[name="monto_general"]').trigger('input');
        }
    });

    // ============================================
    // VALIDACIÓN MONTO CON MARGEN
    // ============================================
$('[name="monto_general"]').on('input', function() {
    const moneda = $('#payment_moneda').val();
    const valorIngresado = parseFloat($(this).val());
    const $equivInfo = $('#equiv_info');

    $(this).siblings('.invalid-feedback').remove();

    if (!valorIngresado || valorIngresado <= 0 || isNaN(valorIngresado)) {
        $(this).addClass('is-invalid').removeClass('is-valid');
        $(this).after('<div class="invalid-feedback">Ingrese un monto válido</div>');
        $('[name="monto"]').val('');
        $equivInfo.html('').hide();
        return;
    }

    let montoUSD = 0;
    let esValido = false;

    if (moneda === "USD") {
        montoUSD = valorIngresado;

        if (montoUSD > currentAccountBalance) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            $(this).after(`<div class="invalid-feedback">El monto excede el saldo pendiente (${Helpers.formatCurrency(currentAccountBalance)})</div>`);
        } else {
            esValido = true;
            $equivInfo.html(`
                <span class="text-info">
                    <i class="fas fa-dollar-sign me-1"></i>
                    Monto en USD válido.<br>
                    <small class="text-muted">Tasa BCV: Bs ${DOLAR_BCV_RATE.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</small>
                </span>
            `).show();
        }

    } else if (moneda === "BS") {
        montoUSD = valorIngresado / DOLAR_BCV_RATE;
        const diferenciaBs = (montoUSD - currentAccountBalance) * DOLAR_BCV_RATE;

        if (Math.abs(diferenciaBs) <= MARGEN_ERROR_BS) {
            montoUSD = currentAccountBalance;
            esValido = true;

            $equivInfo.html(`
                <span class="text-success">
                    <i class="fas fa-check-circle me-1"></i>
                    Equivale a: <strong>${Helpers.formatCurrency(montoUSD)}</strong><br>
                </span>
            `).show();

        } else if (diferenciaBs > MARGEN_ERROR_BS) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            $(this).after('<div class="invalid-feedback">El monto excede el saldo pendiente</div>');
            $equivInfo.html(`
                <span class="text-danger">
                    Equivale a: ${Helpers.formatCurrency(montoUSD)}<br>
                </span>
            `).show();

        } else {
            esValido = true;
            $equivInfo.html(`
                <span class="text-info">
                    Equivale a: <strong>${Helpers.formatCurrency(montoUSD)}</strong><br>
                    <small class="text-muted">Tasa BCV: Bs ${DOLAR_BCV_RATE.toLocaleString('es-VE', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</small>
                </span>
            `).show();
        }
    }

    if (esValido) {
        $(this).addClass('is-valid').removeClass('is-invalid');
        $('[name="monto"]').val(montoUSD.toFixed(4));
    } else {
        $('[name="monto"]').val('');
    }
});


    // ============================================
    // TIPO DE PAGO Y CAMPOS BANCARIOS
    // ============================================
    $('[name="tipo_pago"]').on('change', function() {
        const tipo = $(this).val();
        const $refBancaria = $('#refBancariaGroup');
        const $banco = $('#bancoGroup');

        Validations.validateSelect($(this));

        if (tipo === 'EFECTIVO' || !tipo) {
            $refBancaria.hide();
            $banco.hide();
            $refBancaria.find('input').val('').removeClass('is-valid is-invalid');
            $banco.find('input').val('').removeClass('is-valid is-invalid');
        } else {
            $refBancaria.show();
            $banco.show();
        }
    });

    // Validaciones en tiempo real
    $('[name="referencia_bancaria"]').on('input', function() {
        if (!$(this).is(':visible')) return;
        Validations.validateField($(this), Validations.REGEX.referencia, Validations.MESSAGES.referencia);
    });

    $('[name="banco"]').on('input', function() {
        if (!$(this).is(':visible')) return;
        Validations.validateField($(this), Validations.REGEX.banco, Validations.MESSAGES.banco);
    });

    // ============================================
    // SUBMIT PAGO
    // ============================================
    $('#registerPaymentForm').on('submit', function(e) {
        e.preventDefault();

        const montoUSD = parseFloat($('[name="monto"]').val());
        
        if (!montoUSD || montoUSD <= 0) {
            Helpers.toast('error', 'Ingrese un monto válido');
            $('[name="monto_general"]').focus();
            return;
        }

        if (!$('[name="tipo_pago"]').val()) {
            Helpers.toast('error', 'Seleccione un método de pago');
            $('[name="tipo_pago"]').focus();
            return;
        }

        const tipoPago = $('[name="tipo_pago"]').val();
        if (tipoPago !== 'EFECTIVO') {
            const refBancaria = $('[name="referencia_bancaria"]').val().trim();
            const banco = $('[name="banco"]').val().trim();

            if (!Validations.REGEX.referencia.test(refBancaria)) {
                Helpers.toast('error', 'Referencia bancaria inválida (8-10 dígitos)');
                return;
            }

            if (!Validations.REGEX.banco.test(banco)) {
                Helpers.toast('error', 'Nombre del banco inválido');
                return;
            }
        }

        const formData = $(this).serialize();
        const $btn = $(this).find('button[type="submit"]');
        const btnText = $btn.html();

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Procesando...');
        
        Ajax.post(`${baseUrl}?action=register_payment`, formData)
            .then(r => {
                if (r?.success) {
                    Helpers.toast('success', r.message || 'Pago registrado correctamente');
                    $('#registerPaymentModal').modal('hide');
                    loadAccounts();
                } else {
                    Helpers.toast('error', r?.message || 'Error al registrar el pago');
                }
            })
            .catch(msg => Helpers.toast('error', msg))
            .finally(() => $btn.prop('disabled', false).html(btnText));
    });

    // ============================================
    // EXTENDER FECHA
    // ============================================
    $(document).on('click', '.btn-extend', function() {
        const id = $(this).data('id');
        const account = accounts.find(a => a.id === id);
        if (!account) {
            Helpers.toast('error', 'Cuenta no encontrada');
            return;
        }

        $('#extend_cuenta_id').val(account.id);
        $('#extend_cliente').text(account.cliente);
        $('#extend_fecha_actual').text(Helpers.formatDate(account.fecha_vencimiento));
        
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const minDate = tomorrow.toISOString().split('T')[0];
        $('[name="nueva_fecha"]').attr('min', minDate);
        
        $('#extendDateForm')[0].reset();
        $('#extend_cuenta_id').val(account.id);
        $('#extendDateModal').modal('show');
    });

    $('[name="nueva_fecha"]').on('change', function() {
        const selectedDate = new Date($(this).val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate <= today) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            Helpers.toast('error', 'La fecha de vencimiento debe ser posterior a hoy');
        } else {
            $(this).addClass('is-valid').removeClass('is-invalid');
        }
    });

    $('#extendDateForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();
        const $btn = $(this).find('button[type="submit"]');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Actualizando...');

        Ajax.post(`${baseUrl}?action=update_due_date`, formData)
            .then(r => {
                if (r?.success) {
                    Helpers.toast('success', r.message || 'Fecha actualizada correctamente');
                    $('#extendDateModal').modal('hide');
                    loadAccounts();
                } else {
                    Helpers.toast('error', r?.message || 'Error al actualizar la fecha');
                }
            })
            .catch(msg => Helpers.toast('error', msg))
            .finally(() => $btn.prop('disabled', false).html(btnText));
    });

    // ============================================
    // ELIMINAR CUENTA
    // ============================================
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        const account = accounts.find(a => a.id === id);
        if (!account) {
            Helpers.toast('error', 'Cuenta no encontrada');
            return;
        }

        Helpers.confirmDialog(
            '¿Eliminar cuenta por cobrar?',
            `<p>Esta acción eliminará la cuenta de <strong>${Helpers.escapeHtml(account.cliente)}</strong></p>
             <p class="text-danger"><strong>ADVERTENCIA:</strong> Se anulará la venta asociada.</p>`,
            () => {
                Ajax.post(`${baseUrl}?action=delete`, { cuenta_id: id, confirmar: 'si' })
                    .then(r => {
                        if (r?.success) {
                            Helpers.toast('success', r.message || 'Cuenta eliminada correctamente');
                            loadAccounts();
                        } else {
                            Helpers.toast('error', r?.message || 'Error al eliminar la cuenta');
                        }
                    })
                    .catch(msg => Helpers.toast('error', msg));
            },
            'Sí, eliminar'
        );
    });

    // ============================================
    // PROCESAR VENCIDOS
    // ============================================
    window.processExpiredAccounts = function() {
        Helpers.confirmDialog(
            '¿Procesar cuentas vencidas?',
            `<p>Esta acción procesará todas las cuentas vencidas:</p>
             <ul class="text-start">
                 <li>Marcará cuentas vencidas</li>
                 <li>Anulará ventas asociadas</li>
                 <li>Liberará prendas para venta</li>
             </ul>`,
            () => {
                Helpers.showLoading('Procesando...');

                Ajax.post(`${baseUrl}?action=process_expired`, {})
                    .then(r => {
                        Helpers.closeLoading();
                        if (r?.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Proceso completado',
                                html: `<p>${Helpers.escapeHtml(r.message)}</p>`,
                                confirmButtonText: 'Aceptar'
                            });
                            loadAccounts();
                        } else {
                            Helpers.toast('error', r?.message || 'Error al procesar cuentas vencidas');
                        }
                    })
                    .catch(msg => {
                        Helpers.closeLoading();
                        Helpers.toast('error', msg);
                    });
            },
            'Sí, procesar'
        );
    };

    // ============================================
    // LIMPIAR MODALES
    // ============================================
    $('.modal').on('hidden.bs.modal', function() {
        Helpers.resetForm($(this).find('form'));
    });

    // ============================================
    // INICIALIZAR
    // ============================================
    loadAccounts();

    // Actualizar cada 2 minutos
    setInterval(loadAccounts, 2 * 60 * 1000);
});