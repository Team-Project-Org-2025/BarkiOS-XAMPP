// ============================================
// PURCHASE ADMIN JS - CON AGREGAR PRENDAS EN EDICIÓN
// ============================================
$(document).ready(function() {
    let prendaIndex = 0;
    let allPurchases = [];

    // ============================================
    // VALIDACIONES
    // ============================================
    const REGEX = {
        factura: /^\d{8}$/,
        precio: /^\d+(\.\d{1,2})?$/,
        nombre: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-\.]{3,150}$/
    };

    const validar = {
        factura: val => REGEX.factura.test(val) ? null : 'Debe tener 8 dígitos',
        precio: val => {
            const num = parseFloat(val);
            return isNaN(num) || num <= 0 ? 'Debe ser mayor a 0' : null;
        },
        nombre: val => val.length < 3 ? 'Mínimo 3 caracteres' : 
                      val.length > 150 ? 'Máximo 150 caracteres' : null
    };

    // ============================================
    // GESTIÓN DE PRENDAS
    // ============================================
    function addPrenda(containerId, data = null, editable = true) {
        const container = $(`#${containerId}`);
        const template = $('#prendaTemplate').html();
        const newPrenda = $(template);
        
        newPrenda.find('.prenda-codigo').attr('name', `prendas[${prendaIndex}][codigo_prenda]`);
        newPrenda.find('.prenda-nombre').attr('name', `prendas[${prendaIndex}][nombre]`);
        newPrenda.find('.prenda-categoria').attr('name', `prendas[${prendaIndex}][categoria]`);
        newPrenda.find('.prenda-tipo').attr('name', `prendas[${prendaIndex}][tipo]`);
        newPrenda.find('.prenda-costo').attr('name', `prendas[${prendaIndex}][precio_costo]`);
        newPrenda.find('.prenda-descripcion').attr('name', `prendas[${prendaIndex}][descripcion]`);
        newPrenda.find('.prenda-number').text(prendaIndex + 1);

        if (data) {
            newPrenda.find('.prenda-codigo').val(data.codigo_prenda || '');
            newPrenda.find('.prenda-nombre').val(data.nombre || '');
            newPrenda.find('.prenda-categoria').val(data.categoria || '');
            newPrenda.find('.prenda-tipo').val(data.tipo || '');
            newPrenda.find('.prenda-costo').val(data.precio_costo || '');
            newPrenda.find('.prenda-descripcion').val(data.descripcion || '');
            
            // Marcar como prenda existente (no editable)
            if (!editable) {
                newPrenda.addClass('prenda-existente');
                newPrenda.find('input, select, textarea').prop('disabled', true);
                newPrenda.find('.remove-prenda').remove();
                newPrenda.css('background-color', '#f8f9fa');
            }
        }
        
        container.append(newPrenda);
        prendaIndex++;
        
        if (editable) {
            newPrenda.find('.remove-prenda').on('click', function() {
                $(this).closest('.prenda-row').fadeOut(300, function() {
                    $(this).remove();
                    updateSummary();
                    renumberPrendas(containerId);
                });
            });
            
            newPrenda.find('input, select').on('input change', function() {
                updateSummary();
            });
        }
        
        updateSummary();
    }

    function renumberPrendas(containerId) {
        $(`#${containerId} .prenda-row`).each(function(index) {
            $(this).find('.prenda-number').text(index + 1);
        });
    }

    function updateSummary() {
        let total = 0;
        $('.prenda-row').each(function() {
            const costo = parseFloat($(this).find('.prenda-costo').val()) || 0;
            total += costo;
        });
        
        const count = $('.prenda-row').length;
        
        $('#summaryTotalPrendas, #editSummaryTotalPrendas').text(count);
        $('#summaryMontoTotal, #editSummaryMontoTotal').text(total.toFixed(2));
        $('#montoTotal, #editMontoTotal').val(total.toFixed(2));
    }

    // ============================================
    // BÚSQUEDA DE PROVEEDORES
    // ============================================
    function setupSupplierSearch(inputId, resultsId, hiddenId) {
        let timeout;
        $(`#${inputId}`).on('input', function() {
            clearTimeout(timeout);
            const query = $(this).val().trim();
            
            if (query.length < 2) {
                $(`#${resultsId}`).hide();
                $(`#${hiddenId}`).val('');
                return;
            }
            
            $(`#${resultsId}`).html('<div class="list-group-item"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>').show();
            
            timeout = setTimeout(() => {
                $.ajax({
                    url: window.location.pathname + '?action=search_supplier',
                    data: { search: query },
                    dataType: 'json',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(data) {
                        if (data.success && data.results && data.results.length) {
                            let html = data.results.map(s => `
                                <button type="button" class="list-group-item list-group-item-action"
                                        data-id="${s.rif}" data-nombre="${s.nombre_empresa}">
                                    <strong>${s.nombre_empresa}</strong><br>
                                    <small class="text-muted">RIF: ${s.rif} | ${s.nombre_contacto}</small>
                                </button>
                            `).join('');
                            
                            $(`#${resultsId}`).html(html).find('button').on('click', function() {
                                $(`#${inputId}`).val($(this).data('nombre'));
                                $(`#${hiddenId}`).val($(this).data('id'));
                                $(`#${resultsId}`).hide();
                            });
                        } else {
                            $(`#${resultsId}`).html('<div class="list-group-item text-muted">Sin resultados</div>');
                        }
                    }
                });
            }, 300);
        });
    }

    setupSupplierSearch('searchSupplier', 'supplierResults', 'proveedorId');
    setupSupplierSearch('editSearchSupplier', 'editSupplierResults', 'editProveedorId');

    // ============================================
    // FORMULARIO: AGREGAR COMPRA
    // ============================================
    $('#addPurchaseForm').on('submit', function(e) {
        e.preventDefault();
        
        const factura = $('#facturaNumero').val();
        const tracking = $('#tracking').val();
        const proveedor = $('#proveedorId').val();
        
        if (validar.factura(factura)) return showError(validar.factura(factura));
        if (tracking && validar.factura(tracking)) return showError('Tracking: ' + validar.factura(tracking));
        if (!proveedor) return showError('Seleccione un proveedor');
        
        const prendas = [];
        let error = false;
        
        $('.prenda-row').each(function() {
            const row = $(this);
            const prenda = {
                codigo_prenda: row.find('.prenda-codigo').val().trim(),
                nombre: row.find('.prenda-nombre').val().trim(),
                categoria: row.find('.prenda-categoria').val(),
                tipo: row.find('.prenda-tipo').val(),
                precio_costo: row.find('.prenda-costo').val(),
                descripcion: row.find('.prenda-descripcion').val().trim()
            };
            
            if (!prenda.codigo_prenda || !prenda.nombre || !prenda.categoria || !prenda.tipo || !prenda.precio_costo) {
                showError('Complete todos los campos obligatorios de las prendas');
                error = true;
                return false;
            }
            
            if (parseFloat(prenda.precio_costo) <= 0) {
                showError('El precio de costo debe ser mayor a 0');
                error = true;
                return false;
            }
            
            prendas.push(prenda);
        });
        
        if (error || !prendas.length) {
            if (!prendas.length) showError('Agregue al menos un producto');
            return;
        }
        
        const btn = $('#btnGuardar');
        btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');
        btn.find('.btn-text').text('Guardando...');
        
        const formData = new FormData(this);
        formData.delete('prendas');
        prendas.forEach((p, i) => {
            Object.keys(p).forEach(k => formData.append(`prendas[${i}][${k}]`, p[k]));
        });
        
        $.ajax({
            url: window.location.pathname + '?action=add_ajax',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                if (res.success) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: '¡Éxito!', 
                        text: res.message, 
                        timer: 2000, 
                        showConfirmButton: false 
                    });
                    $('#addPurchaseModal').modal('hide');
                    $('#addPurchaseForm')[0].reset();
                    $('#prendasContainer').empty();
                    fetchPurchases();
                    loadStats();
                } else {
                    showError(res.message);
                }
            },
            error: function(xhr) {
                // AGREGA ESTO PARA VER EL ERROR
                console.error('Error completo:', xhr);
                console.error('Respuesta:', xhr.responseText);
                let errorMsg = 'Error desconocido';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {
                    errorMsg = xhr.responseText || errorMsg;
                }
                showError(errorMsg);
            },
            complete: () => btn.prop('disabled', false).find('.spinner-border').addClass('d-none')
        });
    });

    // ============================================
    // FORMULARIO: EDITAR COMPRA (CON AGREGAR PRENDAS)
    // ============================================
    $('#editPurchaseForm').on('submit', function(e) {
        e.preventDefault();

        // Recopilar solo las prendas NUEVAS (editables)
        const nuevasPrendas = [];
        $('#editPrendasContainer .prenda-row:not(.prenda-existente)').each(function() {
            const row = $(this);
            nuevasPrendas.push({
                codigo_prenda: row.find('.prenda-codigo').val().trim(),
                nombre: row.find('.prenda-nombre').val().trim(),
                categoria: row.find('.prenda-categoria').val(),
                tipo: row.find('.prenda-tipo').val(),
                precio_costo: row.find('.prenda-costo').val(),
                descripcion: row.find('.prenda-descripcion').val().trim()
            });
        });

        const btn = $('#btnGuardarEdit');
        btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

        const formData = new FormData(this);
        
        // Agregar las nuevas prendas al FormData
        nuevasPrendas.forEach((p, i) => {
            Object.keys(p).forEach(k => formData.append(`nuevas_prendas[${i}][${k}]`, p[k]));
        });

        $.ajax({
            url: window.location.pathname + '?action=edit_ajax',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Actualizado', timer: 2000, showConfirmButton: false });
                    $('#editPurchaseModal').modal('hide');
                    fetchPurchases();
                    loadStats();
                } else {
                    showError(res.message);
                }
            },
            error: function(xhr) {
                // AGREGA ESTO PARA VER EL ERROR
                console.error('Error completo:', xhr);
                console.error('Respuesta:', xhr.responseText);
                let errorMsg = 'Error desconocido';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMsg = response.message || errorMsg;
                } catch(e) {
                    errorMsg = xhr.responseText || errorMsg;
                }
                showError(errorMsg);
            },
            complete: () => btn.prop('disabled', false).find('.spinner-border').addClass('d-none')
        });
    });

    // Botón para agregar nuevas prendas en edición
    $('#addEditPrendaBtn').on('click', function() {
        addPrenda('editPrendasContainer', null, true);
    });

    // ============================================
    // CARGAR Y RENDERIZAR COMPRAS
    // ============================================
    function fetchPurchases() {
        $('#purchaseTableBody').html('<tr><td colspan="6" class="text-center py-4"><div class="spinner-border"></div></td></tr>');
        
        $.ajax({
            url: window.location.pathname + '?action=get_purchases',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                if (data.success && data.data.length) {
                    allPurchases = data.data;
                    renderPurchases(allPurchases);
                    pintarMontoPagadoDashboard();
                } else {
                    $('#purchaseTableBody').html('<tr><td colspan="6" class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><p>No hay compras registradas</p></td></tr>');
                }
            },
            error: () => $('#purchaseTableBody').html('<tr><td colspan="6" class="text-center text-danger">Error al cargar</td></tr>')
        });
    }

    function renderPurchases(purchases) {
        const html = purchases.map(p => `
            <tr class="purchase-row">
                <td class="px-4">
                    <strong>#${p.factura_numero}</strong>
                    ${p.pdf_generado == 1 ? '<i class="fas fa-check-circle text-success ms-2" title="PDF generado"></i>' : ''}
                </td>
                <td>
                    <div><strong>${p.nombre_proveedor}</strong></div>
                    <small class="text-muted">${p.total_prendas} prenda(s)</small>
                </td>
                <td>
                    <div>${new Date(p.fecha_compra).toLocaleDateString('es-ES')}</div>
                    ${p.tracking ? `<small class="text-muted">Tracking: ${p.tracking}</small>` : ''}
                </td>
                <td class="text-end">
                    <strong class="text-success">$${p.monto_total}</strong>
                </td>
                <td class="text-center">
                    <span class="badge bg-success">${p.prendas_disponibles || 0}</span>
                    <span class="badge bg-secondary">${p.prendas_vendidas || 0}</span>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewPurchase(${p.compra_id})" title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-success" onclick="generarPdf(${p.compra_id})" title="Descargar PDF">
                            <i class="fas fa-file-pdf"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="editPurchase(${p.compra_id})" title="Editar">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-outline-danger" onclick="deletePurchase(${p.compra_id})" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
        $('#purchaseTableBody').html(html);
    }

    // ============================================
    // CARGAR ESTADÍSTICAS
    // ============================================
    function pintarMontoPagadoDashboard() {
        let totalPagado = 0;
        let totalPendiente = 0;
        let montoTotal = 0;

        allPurchases.forEach(p => {
            totalPagado    += parseFloat(p.total_pagado || 0);
            totalPendiente += parseFloat(p.saldo_pendiente || 0);
            montoTotal     += parseFloat(p.monto_total || 0);
        });

        // Actualizar textos
        $('#statMontoPagado').text('Pagado: $' + totalPagado.toFixed(2));
        $('#statSaldoPendiente').text('$' + totalPendiente.toFixed(2));
        $('#statMontoTotal').text('$' + montoTotal.toFixed(2));

        // Calcular porcentaje pagado
        const porcentajePagado = montoTotal > 0 ? (totalPagado / montoTotal) * 100 : 0;

        // Actualizar círculo SVG
        const circunferencia = 220; // stroke-dasharray del círculo
        const offset = circunferencia - (circunferencia * porcentajePagado / 100);

        $('#progressBar').css('stroke-dashoffset', offset);
        $('#progressPercent').text(Math.round(porcentajePagado) + '%');
    }

    function loadStats() {
        $.ajax({
            url: window.location.pathname + '?action=get_stats',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                if (data.success) {
                    const stats = data.stats;
                    $('#statTotalCompras').text(stats.total_compras || 0);
                    $('#statMontoTotal').text('$' + parseFloat(stats.monto_total_compras || 0).toFixed(2));
                    $('#statValorInventario').text('$' + parseFloat(stats.valor_inventario || 0).toFixed(2));
                }
            }
        });
    }

    // ============================================
    // FUNCIONES GLOBALES
    // ============================================
    window.viewPurchase = (id) => {
        Swal.fire({ title: 'Cargando...', didOpen: () => Swal.showLoading() });
        
        $.ajax({
            url: window.location.pathname + '?action=get_purchase_detail',
            data: { compra_id: id },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                if (data.success) {
                    const c = data.data.compra;
                    const p = data.data.prendas;
                    
                    const prendasHtml = `
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Código</th>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Tipo</th>
                                        <th class="text-end">P.Costo</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${p.map(pr => `
                                        <tr>
                                            <td><code>${pr.codigo_prenda}</code></td>
                                            <td>${pr.nombre}</td>
                                            <td><span class="badge bg-info">${pr.categoria}</span></td>
                                            <td><span class="badge bg-secondary">${pr.tipo}</span></td>
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
                    
                    Swal.fire({
                        title: `Compra #${c.factura_numero}`,
                        html: `
                            <div class="text-start">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <p class="mb-2"><strong>Proveedor:</strong><br>${c.nombre_proveedor}</p>
                                        <p class="mb-2"><strong>RIF:</strong> ${c.tipo_rif}-${c.proveedor_rif}</p>
                                    </div>
                                    <div class="col-6">
                                        <p class="mb-2"><strong>Fecha:</strong><br>${new Date(c.fecha_compra).toLocaleDateString('es-ES')}</p>
                                        <p class="mb-2"><strong>Tracking:</strong> ${c.tracking || 'N/A'}</p>
                                    </div>
                                </div>
                                <div class="alert alert-success">
                                    <strong>Monto Total:</strong> $${parseFloat(c.monto_total).toFixed(2)}
                                </div>
                                ${c.observaciones ? `<p class="text-muted"><em>${c.observaciones}</em></p>` : ''}
                                <hr>
                                <h6>Prendas (${p.length})</h6>
                                ${prendasHtml}
                            </div>
                        `,
                        width: '900px',
                        showConfirmButton: false,
                        showCloseButton: true
                    });
                }
            }
        });
    };

    window.editPurchase = (id) => {
        $.ajax({
            url: window.location.pathname + '?action=get_purchase_detail',
            data: { compra_id: id },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                if (data.success) {
                    const c = data.data.compra;
                    $('#editCompraId').val(c.compra_id);
                    $('#editFacturaNumero').val(c.factura_numero);
                    $('#editFechaCompra').val(c.fecha_compra.split(' ')[0]);
                    $('#editTracking').val(c.tracking);
                    $('#editObservaciones').val(c.observaciones);
                    $('#editSearchSupplier').val(c.nombre_proveedor);
                    $('#editProveedorId').val(c.proveedor_rif);
                    
                    $('#editPrendasContainer').empty();
                    prendaIndex = 0;
                    
                    // Agregar prendas existentes (no editables)
                    data.data.prendas.forEach(p => addPrenda('editPrendasContainer', p, false));

                    $('#editPurchaseModal').modal('show');
                }
            }
        });
    };

    window.deletePurchase = (id) => {
        Swal.fire({
            title: '¿Eliminar compra?',
            text: 'Esta acción no se puede deshacer. Las prendas asociadas también se eliminarán.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: window.location.pathname + '?action=delete_ajax',
                    method: 'POST',
                    data: { compra_id: id },
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(data) {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Eliminado', timer: 2000, showConfirmButton: false });
                            fetchPurchases();
                            loadStats();
                        } else {
                            showError(data.message);
                        }
                    }, error: function(xhr) {
                        // AGREGA ESTO PARA VER EL ERROR
                        console.error('Error completo:', xhr);
                        console.error('Respuesta:', xhr.responseText);
                        let errorMsg = 'Error desconocido';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMsg = response.message || errorMsg;
                        } catch(e) {
                            errorMsg = xhr.responseText || errorMsg;
                        }
                        showError(errorMsg);
                    },
                    complete: () => btn.prop('disabled', false).find('.spinner-border').addClass('d-none')            
                })
            }
        });
    };

    window.generarPdf = (compra_id) => {
        const url = `?action=generate_pdf&compra_id=${compra_id}`;
        window.location.href = url;
    };

    function showError(msg) {
        Swal.fire({ icon: 'error', title: 'Error', text: msg });
    }

    // ============================================
    // EVENTOS
    // ============================================
    $('#addPrendaBtn').on('click', () => addPrenda('prendasContainer'));
    
    $('#searchInput').on('input', function() {
        const term = $(this).val().toLowerCase();
        const filtered = allPurchases.filter(p => 
            p.factura_numero.includes(term) || 
            p.nombre_proveedor.toLowerCase().includes(term)
        );
        renderPurchases(filtered);
    });
    
    $('#addPurchaseModal').on('show.bs.modal', function() {
        $('#addPurchaseForm')[0].reset();
        $('#prendasContainer').empty();
        prendaIndex = 0;
        setTimeout(() => addPrenda('prendasContainer'), 100);
    });

    $('#addPurchaseModal, #editPurchaseModal').on('hidden.bs.modal', function() {
        $(this).find('.is-invalid').removeClass('is-invalid');
    });

    // ============================================
    // INICIALIZAR
    // ============================================
    fetchPurchases();
    loadStats();
});