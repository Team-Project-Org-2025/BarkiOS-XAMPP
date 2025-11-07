import * as Validations from '/BarkiOS/public/assets/js/utils/validation.js';
import * as Helpers from '/BarkiOS/public/assets/js/utils/helpers.js';
import * as Ajax from '/BarkiOS/public/assets/js/utils/ajax-handler.js';

$(document).ready(function() {
    const baseUrl = '/BarkiOS/admin/accounts-receivable';
    let accountsTable = null;
    let currentAccountBalance = 0;

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
                    return json.accounts || [];
                }
            },
            columns: [
                { data: 'referencia', render: ref => `<code>${Helpers.escapeHtml(ref)}</code>` },
                { data: 'cliente', render: c => Helpers.escapeHtml(c) },
                { data: 'fecha_emision', render: d => Helpers.formatDate(d) },
                { data: 'saldo_pendiente', render: m => `<strong>${Helpers.formatCurrency(m)}</strong>` },
                { 
                    data: null,
                    render: acc => {
                        const dias = acc.dias_restantes;
                        let info = '';
                        if (dias > 0) info = `<small class="text-muted d-block">Vence en ${dias} día${dias !== 1 ? 's' : ''}</small>`;
                        else if (dias === 0) info = `<small class="text-warning d-block"><strong>¡Vence hoy!</strong></small>`;
                        else info = `<small class="text-danger d-block">Vencida hace ${Math.abs(dias)} día${Math.abs(dias) !== 1 ? 's' : ''}</small>`;
                        return `${Helpers.formatDate(acc.fecha_vencimiento)}${info}`;
                    }
                },
                { 
                    data: 'estado_visual',
                    render: estado => {
                        const badges = { 'Vigente': 'bg-warning', 'Por vencer': 'bg-warning', 'Vencido': 'bg-danger', 'Pagado': 'bg-success' };
                        return `<span class="badge ${badges[estado] || 'bg-secondary'}">${Helpers.escapeHtml(estado)}</span>`;
                    }
                },
                { 
                    data: null,
                    orderable: false,
                    render: acc => {
                        const viewBtn = `<button class="btn btn-sm btn-outline-info btn-view" data-id="${acc.id}"><i class="fas fa-eye"></i></button>`;
                        if (acc.estado_visual === 'Pagado') return viewBtn;
                        return `
                            ${viewBtn}
                            <button class="btn btn-sm btn-outline-success btn-pay" data-id="${acc.id}"><i class="fas fa-money-bill-wave"></i></button>
                            <button class="btn btn-sm btn-outline-warning btn-extend" data-id="${acc.id}"><i class="fas fa-calendar-plus"></i></button>
                        `;
                    }
                }
            ],
            pageLength: 10,
            responsive: true,
            order: [[0, 'desc']],
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'},
            dom: '<"d-flex justify-content-between align-items-center mb-2"lfB>tip',
            buttons: [{
            text: '<i class="fas fa-sync-alt"></i> Actualizar',
            className: 'btn btn-outline-secondary btn-sm',
            action: () => {
                SkeletonHelper.showTableSkeleton('accountsTable', 5, 7);
                accountsTable.ajax.reload(null, false);
            }
        }]
            
        });
    };

    
    //Ver detalles
    $(document).on('click', '.btn-view', function() {
        const id = $(this).data('id');
        if (!id) return;

        $('#viewAccountModal').modal('show');
        
        SkeletonHelper.showModalSkeleton('accountDetailsContent');

        Ajax.get(`${baseUrl}?action=get_account_details&id=${id}`)
            .then(r => {
                if (r?.success && r.account) {
                    const html = renderAccountDetails(r.account);
                    SkeletonHelper.hideModalSkeleton('accountDetailsContent', html);
                } else {
                    $('#accountDetailsContent').html('<p class="text-center text-muted">No se encontraron detalles</p>');
                }
            })
            .catch(msg => {
                $('#accountDetailsContent').html(`<p class="text-center text-danger">${Helpers.escapeHtml(msg)}</p>`);
            });
    });

    const renderAccountDetails = (acc) => {
        const diasInfo = acc.dias_restantes > 0 ? `Faltan ${acc.dias_restantes} días` : `Vencida hace ${Math.abs(acc.dias_restantes)} días`;
        const badgeClass = { 'Vigente': 'bg-success', 'Por vencer': 'bg-warning', 'Vencido': 'bg-danger', 'Pagado': 'bg-secondary' }[acc.estado_visual || acc.estado] || 'bg-secondary';

        let html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Cuenta #${Helpers.escapeHtml(acc.cuenta_cobrar_id)}</h5>
                    <p class="mb-1"><strong>Referencia Venta:</strong> <code>${Helpers.escapeHtml(acc.referencia)}</code></p>
                    <p class="mb-1"><strong>Estado:</strong> <span class="badge ${badgeClass}">${Helpers.escapeHtml(acc.estado)}</span></p>
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
                    <p class="mb-1"><small class="text-muted">${diasInfo}</small></p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1"><strong>Monto Total:</strong> ${Helpers.formatCurrency(acc.monto_total)}</p>
                    <p class="mb-1"><strong>Saldo Pendiente:</strong> <span class="text-danger fs-5">${Helpers.formatCurrency(acc.saldo_pendiente)}</span></p>
                    ${acc.total_pagado > 0 ? `<p class="mb-1 text-success"><strong>Total Pagado:</strong> ${Helpers.formatCurrency(acc.total_pagado)}</p>` : ''}
                </div>
            </div>
        `;

        if (acc.pagos && acc.pagos.length > 0) {
            html += `
                <hr><h6 class="mb-3"><i class="fas fa-history me-2"></i>Historial de Pagos</h6>
                <table class="table table-sm table-hover">
                    <thead class="table-light"><tr><th>Fecha</th><th>Tipo</th><th class="text-end">Monto</th><th>Observaciones</th></tr></thead>
                    <tbody>
                        ${acc.pagos.map(p => `
                            <tr>
                                <td>${Helpers.formatDate(p.fecha_pago)}</td>
                                <td>
                                    <span class="badge bg-secondary">${Helpers.escapeHtml(p.tipo_pago)}</span>
                                    ${p.referencia_bancaria ? `<br><small>${Helpers.escapeHtml(p.referencia_bancaria)}</small>` : ''}
                                </td>
                                <td class="text-end"><strong>${Helpers.formatCurrency(p.monto)}</strong></td>
                                <td><small class="text-muted">${Helpers.escapeHtml(p.observaciones || '-')}</small></td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        } else {
            html += `<div class="alert alert-info mt-3"><i class="fas fa-info-circle me-2"></i>No se han registrado pagos aún</div>`;
        }

        return html;
    };

    // Registrar pago
    $(document).on('click', '.btn-pay', function() {
        const id = $(this).data('id');
        accountsTable.rows().every(function() {
            const d = this.data();
            if (d.id === id) {
                currentAccountBalance = parseFloat(d.saldo_pendiente);
                const saldoBS = currentAccountBalance * DOLAR_BCV_RATE;

                $('#payment_cuenta_id').val(id);
                $('#payment_cliente').text(d.cliente);
                $('#payment_saldo').html(`<strong>${Helpers.formatCurrency(currentAccountBalance)}</strong><br><small class="text-muted">≈ Bs ${saldoBS.toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</small>`);
                
                Helpers.resetForm($('#registerPaymentForm'));
                $('#equiv_info').html('').hide();
                cambiarMoneda("USD");
                $('#registerPaymentModal').modal('show');
                return false;
            }
        });
    });

    const cambiarMoneda = (moneda) => {
        const $tipo = $('[name="tipo_pago"]');
        $tipo.empty().append(`<option value="">Seleccione un método de pago</option>`);
        (moneda === "USD" ? METODOS_USD : METODOS_BS).forEach(m => $tipo.append(`<option value="${m}">${m}</option>`));
        $('#refBancariaGroup, #bancoGroup').hide();
        moneda === "BS" ? $('#equiv_info').show() : $('#equiv_info').hide().html('');
    };

    $('#payment_moneda').on('change', function() {
        cambiarMoneda($(this).val());
        $('[name="monto_general"]').val() && $('[name="monto_general"]').trigger('input');
    });

    // Validación de monto con margen
    $('[name="monto_general"]').on('input', function() {
        const moneda = $('#payment_moneda').val();
        const valor = parseFloat($(this).val());
        const $equiv = $('#equiv_info');

        $(this).siblings('.invalid-feedback').remove();

        if (!valor || valor <= 0 || isNaN(valor)) {
            $(this).addClass('is-invalid').removeClass('is-valid').after('<div class="invalid-feedback">Ingrese un monto válido</div>');
            $('[name="monto"]').val('');
            $equiv.html('').hide();
            return;
        }

        let montoUSD = 0, esValido = false;

        if (moneda === "USD") {
            montoUSD = valor;
            if (montoUSD > currentAccountBalance) {
                $(this).addClass('is-invalid').removeClass('is-valid').after(`<div class="invalid-feedback">Excede el saldo (${Helpers.formatCurrency(currentAccountBalance)})</div>`);
            } else {
                esValido = true;
                $equiv.html(`<span class="text-info"><i class="fas fa-dollar-sign me-1"></i>Monto USD válido.<br><small class="text-muted">Tasa BCV: Bs ${DOLAR_BCV_RATE.toLocaleString('es-VE', {minimumFractionDigits: 2})}</small></span>`).show();
            }
        } else if (moneda === "BS") {
            montoUSD = valor / DOLAR_BCV_RATE;
            const difBS = (montoUSD - currentAccountBalance) * DOLAR_BCV_RATE;

            if (Math.abs(difBS) <= MARGEN_ERROR_BS) {
                montoUSD = currentAccountBalance;
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
            $('[name="monto"]').val(montoUSD.toFixed(4));
        } else {
            $('[name="monto"]').val('');
        }
    });

    // Tipo de pago y campos bancarios
    $('[name="tipo_pago"]').on('change', function() {
        const tipo = $(this).val();
        Validations.validateSelect($(this));

        if (tipo === 'EFECTIVO' || !tipo) {
            $('#refBancariaGroup, #bancoGroup').hide().find('input').val('').removeClass('is-valid is-invalid');
        } else {
            $('#refBancariaGroup, #bancoGroup').show();
        }
    });

    $('[name="referencia_bancaria"]').on('input', function() {
        $(this).is(':visible') && Validations.validateField($(this), Validations.REGEX.referencia, Validations.MESSAGES.referencia);
    });

    $('[name="banco"]').on('input', function() {
        $(this).is(':visible') && Validations.validateField($(this), Validations.REGEX.banco, Validations.MESSAGES.banco);
    });

    // Submit pago
    $('#registerPaymentForm').on('submit', function(e) {
        e.preventDefault();

        const montoUSD = parseFloat($('[name="monto"]').val());
        if (!montoUSD || montoUSD <= 0) {
            Helpers.toast('error', 'Ingrese un monto válido');
            return;
        }

        const tipoPago = $('[name="tipo_pago"]').val();
        if (!tipoPago) {
            Helpers.toast('error', 'Seleccione un método de pago');
            return;
        }

        if (tipoPago !== 'EFECTIVO') {
            const ref = $('[name="referencia_bancaria"]').val().trim();
            const banco = $('[name="banco"]').val().trim();
            if (!Validations.REGEX.referencia.test(ref) || !Validations.REGEX.banco.test(banco)) {
                Helpers.toast('error', 'Datos bancarios inválidos');
                return;
            }
        }

        const $btn = $(this).find('button[type="submit"]');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Procesando...');

        Ajax.post(`${baseUrl}?action=register_payment`, $(this).serialize())
            .then(r => {
                if (r?.success) {
                    Helpers.toast('success', r.message || 'Pago registrado');
                    $('#registerPaymentModal').modal('hide');
                    SkeletonHelper.showTableSkeleton('accountsTable', 5, 7);
                    accountsTable.ajax.reload(null, false);
                } else {
                    Helpers.toast('error', r?.message || 'Error al registrar');
                }
            })
            .catch(msg => Helpers.toast('error', msg))
            .finally(() => $btn.prop('disabled', false).html(btnText));
    });

    //Extender fecha
    $(document).on('click', '.btn-extend', function() {
        const id = $(this).data('id');
        accountsTable.rows().every(function() {
            const d = this.data();
            if (d.id === id) {
                $('#extend_cuenta_id').val(id);
                $('#extend_cliente').text(d.cliente);
                $('#extend_fecha_actual').text(Helpers.formatDate(d.fecha_vencimiento));
                
                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                $('[name="nueva_fecha"]').attr('min', tomorrow.toISOString().split('T')[0]);
                
                Helpers.resetForm($('#extendDateForm'));
                $('#extend_cuenta_id').val(id);
                $('#extendDateModal').modal('show');
                return false;
            }
        });
    });

    $('[name="nueva_fecha"]').on('change', function() {
        const selected = new Date($(this).val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selected <= today) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            Helpers.toast('error', 'La fecha debe ser posterior a hoy');
        } else {
            $(this).addClass('is-valid').removeClass('is-invalid');
        }
    });

    $('#extendDateForm').on('submit', function(e) {
        e.preventDefault();

        const $btn = $(this).find('button[type="submit"]');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Actualizando...');

        Ajax.post(`${baseUrl}?action=update_due_date`, $(this).serialize())
            .then(r => {
                if (r?.success) {
                    Helpers.toast('success', r.message || 'Fecha actualizada');
                    $('#extendDateModal').modal('hide');
                    SkeletonHelper.showTableSkeleton('accountsTable', 5, 7);
                    accountsTable.ajax.reload(null, false);
                } else {
                    Helpers.toast('error', r?.message || 'Error al actualizar');
                }
            })
            .catch(msg => Helpers.toast('error', msg))
            .finally(() => $btn.prop('disabled', false).html(btnText));
    });
    
    $('.modal').on('hidden.bs.modal', function() {
    
        Helpers.resetForm($(this).find('form'));
    
        });

    SkeletonHelper.showTableSkeleton('accountsTable', 5, 7);

    initDataTable();
    setInterval(() => accountsTable.ajax.reload(null, false), 2 * 60 * 1000);
});