// ============================================================
// MÓDULO DE CUENTAS POR COBRAR - GARAGE BARKI
// ============================================================

$(document).ready(function() {
    
    // --- ESTADO GLOBAL ---
    const $tableBody = $('#accountsTableBody');
    let accounts = [];

    // --- UTILIDADES ---
    const esc = (text) => {
        const div = document.createElement('div');
        div.textContent = String(text ?? '');
        return div.innerHTML;
    };

    const toast = (type, msg) => {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: msg,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    };

    const fmt = (n) => {
        return new Intl.NumberFormat('es-VE', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2
        }).format(Number(n) || 0);
    };

    const fmtDate = (dateStr) => {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        if (isNaN(date)) return String(dateStr);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    };

    // Helper AJAX
    function ajax(method, url, data, success, error) {
        const isFormData = (data instanceof FormData);
        $.ajax({
            url: url,
            method: method,
            data: data,
            dataType: 'json',
            processData: !isFormData,
            contentType: isFormData ? false : 'application/x-www-form-urlencoded; charset=UTF-8',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: success,
            error: function(xhr) {
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

    // --- CARGA DE CUENTAS ---
    function loadAccounts() {
        $tableBody.html(`
            <tr>
                <td colspan="7" class="text-center py-4">
                    <div class="spinner-border text-primary"></div>
                </td>
            </tr>
        `);

        ajax('GET', window.location.pathname + '?action=get_accounts', null, function(r) {
            if (r && r.success) {
                accounts = r.accounts || [];
                renderAccounts();
            } else {
                $tableBody.html(`
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <div class="alert alert-info mb-0">
                                No hay cuentas por cobrar registradas
                            </div>
                        </td>
                    </tr>
                `);
            }
        }, function(msg) {
            $tableBody.html(`
                <tr>
                    <td colspan="7" class="text-center py-4 text-danger">
                        Error al cargar cuentas: ${esc(msg)}
                    </td>
                </tr>
            `);
        });
    }

    // --- RENDERIZADO ---
    function renderAccounts() {
        if (accounts.length === 0) {
            $tableBody.html(`
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <div class="alert alert-info mb-0">
                            No hay cuentas por cobrar registradas
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        let rows = '';
        accounts.forEach((acc) => {
            const badgeClass = getBadgeClass(acc.estado_visual);
            
            // Calcular días restantes con mejor formato
            let diasInfo = '';
            if (acc.dias_restantes > 0) {
                diasInfo = `<small class="text-muted d-block">Vence en ${acc.dias_restantes} día${acc.dias_restantes !== 1 ? 's' : ''}</small>`;
            } else if (acc.dias_restantes === 0) {
                diasInfo = `<small class="text-warning d-block"><strong>¡Vence hoy!</strong></small>`;
            } else {
                diasInfo = `<small class="text-danger d-block">Vencida hace ${Math.abs(acc.dias_restantes)} día${Math.abs(acc.dias_restantes) !== 1 ? 's' : ''}</small>`;
            }
            
            rows += `
                <tr>
                    <td>
                        <code>${esc(acc.referencia)}</code>
                    </td>
                    <td>${esc(acc.cliente)}</td>
                    <td>${fmtDate(acc.fecha_emision)}</td>
                    <td><strong>${fmt(acc.saldo_pendiente)}</strong></td>
                    <td>
                        ${fmtDate(acc.fecha_vencimiento)}
                        ${diasInfo}
                    </td>
                    <td>
                        <span class="badge ${badgeClass}">
                            ${esc(acc.estado_visual)}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-info" 
                                onclick="viewAccountDetails(${acc.id})" 
                                title="Ver detalles">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${acc.estado_visual !== 'Pagado' && acc.estado_visual !== 'Vencido' ? `
                            <button class="btn btn-sm btn-outline-success" 
                                    onclick="openPaymentModal(${acc.id})" 
                                    title="Registrar pago">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>
                        ` : ''}
                        ${acc.estado_visual === 'Vencido' || acc.estado_visual === 'Por vencer' ? `
                            <button class="btn btn-sm btn-outline-warning" 
                                    onclick="openExtendDateModal(${acc.id})" 
                                    title="Extender fecha">
                                <i class="fas fa-calendar-plus"></i>
                            </button>
                        ` : ''}
                        ${acc.estado_visual !== 'Pagado' ? `
                            <button class="btn btn-sm btn-outline-danger" 
                                    onclick="deleteAccount(${acc.id})" 
                                    title="Eliminar">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </td>
                </tr>
            `;
        });

        $tableBody.html(rows);
    }

    function getBadgeClass(estado) {
        const badges = {
            'Vigente': 'badge-vigente',
            'Por vencer': 'badge-por-vencer',
            'Vencido': 'badge-vencido',
            'Pagado': 'badge-pagado'
        };
        return badges[estado] || 'bg-secondary';
    }

    // --- VER DETALLES ---
    window.viewAccountDetails = function(id) {
        if (!id) return;

        $('#accountDetailsContent').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Cargando detalles...</p>
            </div>
        `);
        $('#viewAccountModal').modal('show');

        ajax('GET', window.location.pathname + `?action=get_account_details&id=${id}`, null, function(r) {
            if (r && r.success && r.account) {
                renderAccountDetails(r.account);
            } else {
                $('#accountDetailsContent').html(`
                    <p class="text-center text-muted">No se encontraron detalles</p>
                `);
            }
        }, function(msg) {
            $('#accountDetailsContent').html(`
                <p class="text-center text-danger">${esc(msg)}</p>
            `);
        });
    };

    function renderAccountDetails(acc) {
        const diasRestantes = acc.dias_restantes > 0 
            ? `Faltan ${acc.dias_restantes} días`
            : `Vencida hace ${Math.abs(acc.dias_restantes)} días`;

        let html = `
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5>Cuenta #${esc(acc.cuenta_cobrar_id)}</h5>
                    <p class="mb-1">
                        <strong>Referencia Venta:</strong> 
                        <code>${esc(acc.referencia)}</code>
                    </p>
                    <p class="mb-1">
                        <strong>Estado:</strong> 
                        <span class="badge ${getBadgeClass(acc.estado_visual || acc.estado)}">
                            ${esc(acc.estado)}
                        </span>
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1"><strong>Cliente:</strong> ${esc(acc.nombre_cliente)}</p>
                    <p class="mb-1"><strong>Cédula:</strong> ${esc(acc.cliente_ced)}</p>
                    ${acc.telefono ? `<p class="mb-1"><strong>Teléfono:</strong> ${esc(acc.telefono)}</p>` : ''}
                </div>
            </div>

            <hr>

            <div class="row mb-3">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Fecha Emisión:</strong> ${fmtDate(acc.emision)}</p>
                    <p class="mb-1"><strong>Fecha Vencimiento:</strong> ${fmtDate(acc.vencimiento)}</p>
                    <p class="mb-1"><small class="text-muted">${diasRestantes}</small></p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1"><strong>Monto Total:</strong> ${fmt(acc.monto_total)}</p>
                    <p class="mb-1"><strong>Saldo Pendiente:</strong> 
                        <span class="text-danger fs-5">${fmt(acc.saldo_pendiente)}</span>
                    </p>
                    ${acc.total_pagado > 0 ? `
                        <p class="mb-1 text-success"><strong>Total Pagado:</strong> ${fmt(acc.total_pagado)}</p>
                    ` : ''}
                </div>
            </div>
        `;

        // Historial de pagos
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
                        <td>${fmtDate(p.fecha_pago)}</td>
                        <td>
                            <span class="badge bg-secondary">${esc(p.tipo_pago)}</span>
                            ${p.referencia_bancaria ? `<br><small>${esc(p.referencia_bancaria)}</small>` : ''}
                        </td>
                        <td class="text-end"><strong>${fmt(p.monto)}</strong></td>
                        <td><small class="text-muted">${esc(p.observaciones || '-')}</small></td>
                    </tr>
                `;
            });

            html += `
                    </tbody>
                </table>
            `;
        } else {
            html += `
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    No se han registrado pagos aún
                </div>
            `;
        }

        $('#accountDetailsContent').html(html);
    }

    // --- REGISTRAR PAGO ---
    window.openPaymentModal = function(id) {
        const account = accounts.find(a => a.id === id);
        if (!account) {
            toast('error', 'Cuenta no encontrada');
            return;
        }

        $('#payment_cuenta_id').val(account.id);
        $('#payment_cliente').text(account.cliente);
        $('#payment_saldo').text(fmt(account.saldo_pendiente));
        
        $('input[name="monto"]').attr('max', account.saldo_pendiente);
        
        $('#registerPaymentForm')[0].reset();
        $('#payment_cuenta_id').val(account.id);
        $('#registerPaymentModal').modal('show');
    };

    $('#registerPaymentForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();
        const monto = parseFloat($('input[name="monto"]').val());
        const saldoMax = parseFloat($('input[name="monto"]').attr('max'));

        if (monto > saldoMax) {
            toast('error', `El monto no puede ser mayor al saldo pendiente (${fmt(saldoMax)})`);
            return;
        }

        const $btn = $(this).find('button[type="submit"]');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Procesando...');

        ajax('POST', window.location.pathname + '?action=register_payment', formData, 
            function(r) {
                $btn.prop('disabled', false).html(btnText);
                
                if (r && r.success) {
                    toast('success', r.message || 'Pago registrado correctamente');
                    $('#registerPaymentModal').modal('hide');
                    loadAccounts();
                } else {
                    toast('error', r?.message || 'Error al registrar el pago');
                }
            },
            function(msg) {
                $btn.prop('disabled', false).html(btnText);
                toast('error', msg);
            }
        );
    });

    // --- EXTENDER FECHA ---
    window.openExtendDateModal = function(id) {
        const account = accounts.find(a => a.id === id);
        if (!account) {
            toast('error', 'Cuenta no encontrada');
            return;
        }

        $('#extend_cuenta_id').val(account.id);
        $('#extend_cliente').text(account.cliente);
        $('#extend_fecha_actual').text(fmtDate(account.fecha_vencimiento));
        
        $('#extendDateForm')[0].reset();
        $('#extend_cuenta_id').val(account.id);
        $('#extendDateModal').modal('show');
    };

    $('#extendDateForm').on('submit', function(e) {
        e.preventDefault();

        const formData = $(this).serialize();
        const $btn = $(this).find('button[type="submit"]');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Actualizando...');

        ajax('POST', window.location.pathname + '?action=update_due_date', formData, 
            function(r) {
                $btn.prop('disabled', false).html(btnText);
                
                if (r && r.success) {
                    toast('success', r.message || 'Fecha actualizada correctamente');
                    $('#extendDateModal').modal('hide');
                    loadAccounts();
                } else {
                    toast('error', r?.message || 'Error al actualizar la fecha');
                }
            },
            function(msg) {
                $btn.prop('disabled', false).html(btnText);
                toast('error', msg);
            }
        );
    });

    // --- ELIMINAR CUENTA ---
    window.deleteAccount = function(id) {
        const account = accounts.find(a => a.id === id);
        if (!account) {
            toast('error', 'Cuenta no encontrada');
            return;
        }

        Swal.fire({
            title: '¿Eliminar cuenta por cobrar?',
            html: `
                <p>Esta acción eliminará la cuenta de <strong>${esc(account.cliente)}</strong></p>
                <p class="text-danger"><strong>ADVERTENCIA:</strong> Se anulará la venta asociada.</p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                ajax('POST', window.location.pathname + '?action=delete', 
                    { cuenta_id: id, confirmar: 'si' }, 
                    function(r) {
                        if (r && r.success) {
                            toast('success', r.message || 'Cuenta eliminada correctamente');
                            loadAccounts();
                        } else {
                            toast('error', r?.message || 'Error al eliminar la cuenta');
                        }
                    }
                );
            }
        });
    };

    // --- PROCESAR VENCIDOS ---
    window.processExpiredAccounts = function() {
        Swal.fire({
            title: '¿Procesar cuentas vencidas?',
            html: `
                <p>Esta acción procesará todas las cuentas vencidas:</p>
                <ul class="text-start">
                    <li>Marcará cuentas vencidas</li>
                    <li>Anulará ventas asociadas</li>
                    <li>Liberará prendas para venta</li>
                </ul>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            confirmButtonText: 'Sí, procesar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando...',
                    html: 'Verificando cuentas vencidas',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                ajax('POST', window.location.pathname + '?action=process_expired', {}, 
                    function(r) {
                        Swal.close();
                        if (r && r.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Proceso completado',
                                html: `<p>${esc(r.message)}</p>`,
                                confirmButtonText: 'Aceptar'
                            });
                            loadAccounts();
                        } else {
                            toast('error', r?.message || 'Error al procesar cuentas vencidas');
                        }
                    },
                    function(msg) {
                        Swal.close();
                        toast('error', msg);
                    }
                );
            }
        });
    };

    // --- VALIDACIÓN EN TIEMPO REAL ---
    $('input[name="monto"]').on('input', function() {
        const val = parseFloat($(this).val());
        const max = parseFloat($(this).attr('max'));
        
        if (isNaN(val) || val <= 0) {
            $(this).removeClass('is-valid').addClass('is-invalid');
        } else if (val > max) {
            $(this).removeClass('is-valid').addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }
    });

    // Mostrar/ocultar campos bancarios según tipo de pago
    $('select[name="tipo_pago"]').on('change', function() {
        const tipo = $(this).val();
        const $refBancaria = $('#refBancariaGroup');
        const $banco = $('#bancoGroup');
        
        if (tipo === 'EFECTIVO') {
            $refBancaria.hide();
            $banco.hide();
            $('input[name="referencia_bancaria"]').prop('required', false);
            $('input[name="banco"]').prop('required', false);
        } else {
            $refBancaria.show();
            $banco.show();
            $('input[name="referencia_bancaria"]').prop('required', false);
            $('input[name="banco"]').prop('required', false);
        }
    });

    // Disparar evento al cargar
    $('select[name="tipo_pago"]').trigger('change');

    // --- LIMPIAR MODALES AL CERRAR ---
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form').each(function() {
            if (this.reset) this.reset();
            $(this).find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
        });
    });

    // --- INICIALIZACIÓN ---
    loadAccounts();

    // Actualizar cada 2 minutos
    setInterval(loadAccounts, 2 * 60 * 1000);
});