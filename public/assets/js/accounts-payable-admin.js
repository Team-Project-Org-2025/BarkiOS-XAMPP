// ============================================
// ACCOUNTS PAYABLE ADMIN JS
// ============================================
$(document).ready(function() {
    let allAccounts = [];
    let currentSaldo = 0;
    
    // Constantes de monedas
    const METODOS_USD = ["EFECTIVO", "TRANSFERENCIA"];
    const METODOS_BS = ["EFECTIVO", "PAGOMOVIL", "TRANSFERENCIA"];
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
                    allAccounts = data.data;
                    renderAccounts(allAccounts);
                    updateStats();
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
                estadoBadge = '<span class="badge bg-secondary badge-estado">Pagada</span>';
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
    // REGISTRAR PAGO
    // ============================================
    window.addPayment = function(cuentaId, proveedor, factura, montoTotal, saldo) {
        $('#paymentCuentaId').val(cuentaId);
        $('#paymentProveedor').text(proveedor);
        $('#paymentFactura').text(factura);
        $('#paymentMontoTotal').text(montoTotal.toFixed(2));
        $('#paymentSaldo').text(saldo.toFixed(2));
        $('#paymentMonto').attr('max', saldo).val('');
        currentSaldo = saldo;
        
        $('#addPaymentModal').modal('show');
    };

    // Mostrar/ocultar campos según tipo de pago
    $('#paymentTipo').on('change', function() {
        const tipo = $(this).val();
        if (tipo === 'TRANSFERENCIA' || tipo === 'PAGO_MOVIL' || tipo === 'CHEQUE' || tipo === 'ZELLE') {
            $('#referenciaField, #bancoField').show();
        } else {
            $('#referenciaField, #bancoField').hide();
        }
    });

    // Submit pago
    $('#addPaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const monto = parseFloat($('#paymentMonto').val());
        
        if (monto <= 0) {
            return Swal.fire({ icon: 'error', title: 'Error', text: 'El monto debe ser mayor a 0' });
        }
        
        if (monto > currentSaldo) {
            return Swal.fire({ icon: 'error', title: 'Error', text: 'El monto no puede ser mayor al saldo pendiente' });
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
                                        <br><strong class="text-success">${parseFloat(p.monto).toFixed(2)}</strong>
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
                                                <td class="text-end">${parseFloat(pr.precio_costo).toFixed(2)}</td>
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
                                            <strong>${total.toFixed(2)}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Pagado:</span>
                                            <strong class="text-success">${pagado.toFixed(2)}</strong>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <span><strong>Saldo Pendiente:</strong></span>
                                            <strong class="${saldo > 0 ? 'text-danger' : 'text-success'} fs-5">${saldo.toFixed(2)}</strong>
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

    // ============================================
    // INICIALIZAR
    // ============================================
    fetchAccounts();
});