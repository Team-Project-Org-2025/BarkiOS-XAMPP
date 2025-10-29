// ============================================
// ACCOUNTS PAYABLE ADMIN JS - MEJORADO
// Incluye: USD/BS, validaciones regex, filtrado de cuentas pagadas
// ============================================
$(document).ready(function() {
    let allAccounts = [];
    let currentSaldo = 0;
    
    // Constantes de monedas
    const METODOS_USD = ["EFECTIVO", "TRANSFERENCIA"];
    const METODOS_BS = ["EFECTIVO", "PAGO MOVIL", "TRANSFERENCIA"];
    const MARGEN_ERROR_BS = 10; // Margen de ±10 Bs

    // ============================================
    // CARGAR CUENTAS POR PAGAR
    // ============================================
    function fetchAccounts() {
        $('#accountsTableBody').html('<tr><td colspan="9" class="text-center py-4"><div class="spinner-border"></div></td></tr>');
        
        $.ajax({
            url: window.location.pathname + '?action=get_accounts',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                if (data.success && data.data.length) {
                    // ✅ FILTRAR CUENTAS PAGADAS (saldo_pendiente <= 0)
                    allAccounts = data.data.filter(c => parseFloat(c.saldo_pendiente || 0) > 0);
                    
                    if (allAccounts.length > 0) {
                        renderAccounts(allAccounts);
                        updateStats();
                    } else {
                        $('#accountsTableBody').html('<tr><td colspan="9" class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><p>No hay cuentas por pagar pendientes</p></td></tr>');
                    }
                } else {
                    $('#accountsTableBody').html('<tr><td colspan="9" class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><p>No hay cuentas por pagar</p></td></tr>');
                }
            },
            error: () => $('#accountsTableBody').html('<tr><td colspan="9" class="text-center text-danger">Error al cargar</td></tr>')
        });
    }

    // ============================================
    // RENDERIZAR CUENTAS
    // ============================================
    function renderAccounts(accounts) {
        const html = accounts.map(c => {
            const saldo = parseFloat(c.saldo_pendiente || 0);
            const pagado = parseFloat(c.total_pagado || 0);
            const total = parseFloat(c.monto_total || 0);
            
            // Determinar estado visual
            let estadoClass = 'al-dia';
            let estadoBadge = '<span class="badge bg-success badge-estado">Al día</span>';
            
            if (c.estado === 'vencido' || c.vencida) {
                estadoClass = 'vencida';
                estadoBadge = '<span class="badge bg-danger badge-estado">Vencida</span>';
            } else if (c.estado === 'pagado') {
                estadoBadge = '<span class="badge bg-secondary badge-estado">Pagando</span>';
            } else {
                const diasVencer = Math.ceil((new Date(c.fecha_vencimiento) - new Date()) / (1000 * 60 * 60 * 24));
                if (diasVencer <= 7 && diasVencer > 0) {
                    estadoClass = 'por-vencer';
                    estadoBadge = `<span class="badge bg-warning badge-estado">Vence en ${diasVencer}d</span>`;
                }
            }
            
            return `
                <tr class="cuenta-row ${estadoClass}">
                    <td class="px-4">
                        <strong>#${c.factura_numero}</strong>
                    </td>
                    <td>
                        <div><strong>${c.nombre_proveedor}</strong></div>
                        <small class="text-muted">${c.tipo_rif}-${c.proveedor_rif}</small>
                    </td>
                    <td>
                        <small>${new Date(c.fecha_compra).toLocaleDateString('es-ES')}</small>
                    </td>
                    <td>
                        <small>${new Date(c.fecha_vencimiento).toLocaleDateString('es-ES')}</small>
                    </td>
                    <td class="text-end">
                        <strong>$${total.toFixed(2)}</strong>
                    </td>
                    <td class="text-end">
                        <span class="text-success">$${pagado.toFixed(2)}</span>
                    </td>
                    <td class="text-end">
                        <strong class="${saldo > 0 ? 'text-danger' : 'text-success'}">$${saldo.toFixed(2)}</strong>
                    </td>
                    <td class="text-center">
                        ${estadoBadge}
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="viewAccount(${c.cuenta_pagar_id})" title="Ver detalle">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${saldo > 0 ? `
                            <button class="btn btn-outline-success" onclick="addPayment(${c.cuenta_pagar_id}, '${c.nombre_proveedor}', '${c.factura_numero}', ${total}, ${saldo})" title="Registrar pago">
                                <i class="fas fa-dollar-sign"></i>
                            </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
        $('#accountsTableBody').html(html);
    }

    // ============================================
    // ACTUALIZAR ESTADÍSTICAS
    // ============================================
    function updateStats() {
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
        $('#statDeudaTotal').text('$' + deudaTotal.toFixed(2));
        $('#statPorVencer').text(porVencer);
        $('#statVencidas').text(vencidas);
    }

    // ============================================
    // REGISTRAR PAGO - MEJORADO CON USD/BS
    // ============================================
    window.addPayment = function(cuentaId, proveedor, factura, montoTotal, saldo) {
        $('#paymentCuentaId').val(cuentaId);
        $('#paymentProveedor').text(proveedor);
        $('#paymentFactura').text(factura);
        $('#paymentMontoTotal').text(montoTotal.toFixed(2));
        $('#paymentSaldo').html(`
            <strong>$${saldo.toFixed(2)}</strong>
            <br><small class="text-muted">≈ Bs ${(saldo * DOLAR_BCV_RATE).toLocaleString('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</small>
        `);
        
        currentSaldo = saldo;
        
        $('#addPaymentForm')[0].reset();
        $('#paymentMontoGeneral').val('').removeClass('is-valid is-invalid');
        $('#paymentMonto').val('');
        $('#equivInfo').html('').hide();
        $('#paymentTipo').empty().removeClass('is-valid is-invalid');
        
        cambiarMoneda("USD");
        $('#addPaymentModal').modal('show');
    };

    // ============================================
    // CAMBIO DE MONEDA
    // ============================================
    $('#paymentMoneda').on('change', function() {
        const moneda = $(this).val();
        cambiarMoneda(moneda);
        
        if ($('#paymentMontoGeneral').val()) {
            $('#paymentMontoGeneral').trigger('input');
        }
    });

    function cambiarMoneda(moneda) {
        const $tipoPago = $('#paymentTipo');
        $tipoPago.empty().removeClass('is-valid is-invalid');
        $tipoPago.append(`<option value="" disabled selected>Seleccione un método de pago</option>`);

        const metodos = moneda === "USD" ? METODOS_USD : METODOS_BS;
        metodos.forEach(m => $tipoPago.append(`<option value="${m}">${m}</option>`));
        
        $('#referenciaField, #bancoField').hide();
        $('#paymentReferencia, #paymentBanco').val('').removeClass('is-valid is-invalid');

        const $symbol = $('#currency-symbol');
        if (moneda === "BS") {
            $symbol.text('Bs');
            $('#equivInfo').show();
        } else {
            $symbol.text('$');
            $('#equivInfo').hide().html('');
        }
    }

    // ============================================
    // VALIDACIÓN EN TIEMPO REAL
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
                $(this).after(`<div class="invalid-feedback">El monto excede el saldo pendiente ($${currentSaldo.toFixed(2)})</div>`);
            } else {
                esValido = true;
            }

        } else if (moneda === "BS") {
            montoUSD = valorIngresado / DOLAR_BCV_RATE;
            const diferenciaBs = (montoUSD - currentSaldo) * DOLAR_BCV_RATE;

            if (Math.abs(diferenciaBs) <= MARGEN_ERROR_BS) {
                montoUSD = currentSaldo;
                esValido = true;
                
                $('#equivInfo').html(`
                    <span class="text-success">
                        <i class="fas fa-check-circle me-1"></i>
                        Equivale a: <strong>$${montoUSD.toFixed(2)}</strong>
                        ${Math.abs(diferenciaBs) > 0.01 ? '<small class="d-block">Se ajustará al saldo exacto</small>' : ''}
                    </span>
                `).show();
                
            } else if (diferenciaBs > MARGEN_ERROR_BS) {
                $(this).addClass('is-invalid').removeClass('is-valid');
                $(this).after(`<div class="invalid-feedback">El monto excede el saldo pendiente</div>`);
                $('#equivInfo').html(`<span class="text-danger">Equivale a: $${montoUSD.toFixed(2)}</span>`).show();
                
            } else {
                esValido = true;
                $('#equivInfo').html(`<span class="text-info">Equivale a: <strong>$${montoUSD.toFixed(2)}</strong></span>`).show();
            }
        }

        if (esValido) {
            $(this).addClass('is-valid').removeClass('is-invalid');
            $('#paymentMonto').val(montoUSD.toFixed(4));
        } else {
            $('#paymentMonto').val('');
        }
    });

    // ============================================
    // VALIDACIONES DE TIPO DE PAGO
    // ============================================
    $('#paymentTipo').on('change', function() {
        const tipo = $(this).val();
        const $refBancaria = $('#referenciaField');
        const $banco = $('#bancoField');
        const $refInput = $('#paymentReferencia');
        const $bancoInput = $('#paymentBanco');

        if (!tipo) {
            $(this).addClass('is-invalid').removeClass('is-valid');
        } else {
            $(this).addClass('is-valid').removeClass('is-invalid');
        }

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

    // ============================================
    // VALIDACIONES CON REGEX
    // ============================================
    const REGEX_REFERENCIA = /^\d{8,10}$/;
    const REGEX_BANCO = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-\.]{3,30}$/;

    $('#paymentReferencia').on('input', function() {
        if (!$(this).is(':visible')) return;

        const valor = $(this).val().trim();
        $(this).siblings('.invalid-feedback').remove();

        if (valor.length === 0) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            $(this).after('<div class="invalid-feedback">La referencia bancaria es requerida</div>');
        } else if (!REGEX_REFERENCIA.test(valor)) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            if (!/^\d+$/.test(valor)) {
                $(this).after('<div class="invalid-feedback">Solo se permiten números</div>');
            } else if (valor.length < 8) {
                $(this).after('<div class="invalid-feedback">Mínimo 8 dígitos</div>');
            } else {
                $(this).after('<div class="invalid-feedback">Máximo 10 dígitos</div>');
            }
        } else {
            $(this).addClass('is-valid').removeClass('is-invalid');
        }
    });

    $('#paymentBanco').on('input', function() {
        if (!$(this).is(':visible')) return;

        const valor = $(this).val().trim();
        $(this).siblings('.invalid-feedback').remove();

        if (valor.length === 0) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            $(this).after('<div class="invalid-feedback">El nombre del banco es requerido</div>');
        } else if (!REGEX_BANCO.test(valor)) {
            $(this).addClass('is-invalid').removeClass('is-valid');
            if (valor.length < 3) {
                $(this).after('<div class="invalid-feedback">Mínimo 3 caracteres</div>');
            } else if (valor.length > 30) {
                $(this).after('<div class="invalid-feedback">Máximo 30 caracteres (actual: ' + valor.length + ')</div>');
            } else {
                $(this).after('<div class="invalid-feedback">Solo se permiten letras, números, espacios, guiones y puntos</div>');
            }
        } else {
            $(this).addClass('is-valid').removeClass('is-invalid');
        }
    });

    // ============================================
    // SUBMIT CON CONVERSIÓN A USD
    // ============================================
    $('#addPaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const montoUSD = parseFloat($('#paymentMonto').val());
        
        if (!montoUSD || montoUSD <= 0) {
            return Swal.fire({ icon: 'error', title: 'Error', text: 'El monto debe ser al acorde al saldo pendiente' });
        }
        
        if (!$('#paymentTipo').val()) {
            return Swal.fire({ icon: 'error', title: 'Error', text: 'Seleccione un método de pago' });
        }

        // Validar campos bancarios si son visibles
        const tipoPago = $('#paymentTipo').val();
        if (tipoPago !== 'EFECTIVO') {
            const refBancaria = $('#paymentReferencia').val().trim();
            const banco = $('#paymentBanco').val();

            if (!refBancaria || !REGEX_REFERENCIA.test(refBancaria)) {
                $('#paymentReferencia').addClass('is-invalid');
                return Swal.fire({ icon: 'error', title: 'Error', text: 'Referencia bancaria inválida (8-10 dígitos)' });
            }

            if (!banco || banco.trim().length < 4 || !REGEX_BANCO.test(banco.trim())) {
                $('#paymentBanco').addClass('is-invalid');
                return Swal.fire({ 
                    icon: 'error', 
                    title: 'Error', 
                    text: 'El nombre del banco debe tener al menos 4 caracteres válidos' 
                });
            }

        }
        
        const btn = $('#btnGuardarPago');
        btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');
        
        $.ajax({
            url: window.location.pathname + '?action=add_payment',
            method: 'POST',
            data: $(this).serialize(),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                if (res.success) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: '¡Pago registrado!', 
                        text: res.message, 
                        timer: 2000, 
                        showConfirmButton: false 
                    });
                    $('#addPaymentModal').modal('hide');
                    $('#addPaymentForm')[0].reset();
                    fetchAccounts();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                }
            },
            error: () => Swal.fire({ icon: 'error', title: 'Error', text: 'Error al registrar el pago' }),
            complete: () => {
                btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
            }
        });
    });

    // ============================================
    // VER DETALLE DE CUENTA
    // ============================================
    window.viewAccount = function(cuentaId) {
        $('#viewAccountModal').modal('show');
        $('#viewAccountContent').html('<div class="text-center py-5"><div class="spinner-border"></div><p class="mt-2">Cargando...</p></div>');
        
        $.ajax({
            url: window.location.pathname + '?action=get_account_detail',
            data: { cuenta_pagar_id: cuentaId },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                if (data.success) {
                    const c = data.data.cuenta;
                    const pagos = data.data.pagos;
                    const prendas = data.data.prendas;
                    
                    const saldo = parseFloat(c.saldo_pendiente || 0);
                    const total = parseFloat(c.monto_total || 0);
                    const pagado = parseFloat(c.total_pagado || 0);
                    
                    let pagosHtml = '';
                    if (pagos.length > 0) {
                        pagosHtml = pagos.map(p => `
                            <div class="pago-item">
                                <div class="row">
                                    <div class="col-md-3">
                                        <small class="text-muted">Fecha</small>
                                        <br><strong>${new Date(p.fecha_pago).toLocaleDateString('es-ES')}</strong>
                                    </div>
                                    <div class="col-md-3">
                                        <small class="text-muted">Monto</small>
                                        <br><strong class="text-success">$${parseFloat(p.monto).toFixed(2)}</strong>
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
                                                <td class="text-end">$${parseFloat(pr.precio_costo).toFixed(2)}</td>
                                                <td class="text-center">
                                                    ${pr.estado === 'DISPONIBLE' 
                                                        ? '<span class="badge bg-success">Disponible</span>' 
                                                        : '<span class="badge bg-secondary">Vendida</span>'}
                                                </td>
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
                                <p class="mb-2"><strong>Fecha Compra:</strong> ${new Date(c.fecha_compra).toLocaleDateString('es-ES')}</p>
                                <p class="mb-2"><strong>Vencimiento:</strong> ${new Date(c.fecha_vencimiento).toLocaleDateString('es-ES')}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Resumen Financiero</h6>
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Monto Total:</span>
                                            <strong>$${total.toFixed(2)}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Pagado:</span>
                                            <strong class="text-success">$${pagado.toFixed(2)}</strong>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <span><strong>Saldo Pendiente:</strong></span>
                                            <strong class="${saldo > 0 ? 'text-danger' : 'text-success'} fs-5">$${saldo.toFixed(2)}</strong>
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
                        
                        ${prendasHtml ? `
                        <hr>
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-box me-2"></i>Productos de la Compra (${prendas.length})
                        </h6>
                        ${prendasHtml}
                        ` : ''}
                    `;
                    
                    $('#viewAccountContent').html(html);
                } else {
                    $('#viewAccountContent').html('<div class="alert alert-danger">Error al cargar los datos</div>');
                }
            },
            error: () => {
                $('#viewAccountContent').html('<div class="alert alert-danger">Error de conexión</div>');
            }
        });
    };

    // ============================================
    // FILTROS Y BÚSQUEDA
    // ============================================
    $('#searchInput').on('input', function() {
        const term = $(this).val().toLowerCase();
        const filtered = allAccounts.filter(c => 
            c.factura_numero.includes(term) || 
            c.nombre_proveedor.toLowerCase().includes(term) ||
            (c.proveedor_rif && c.proveedor_rif.toString().includes(term))
        );
        renderAccounts(filtered);
    });
    
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

    // Limpiar modales al cerrar
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form').each(function() {
            if (this.reset) this.reset();
            $(this).find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
            $(this).find('.invalid-feedback').remove();
        });
    });

    // ============================================
    // INICIALIZAR
    // ============================================
    fetchAccounts();
});