// ============================================
// PURCHASE ADMIN JS - OPTIMIZADO
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
    function addPrenda(containerId, data = null) {
        const container = $(`#${containerId}`);
        const template = $('#prendaTemplate').html();
        const newPrenda = $(template);
        
        // Configurar nombres de inputs
        newPrenda.find('.prenda-codigo').attr('name', `prendas[${prendaIndex}][codigo]`);
        newPrenda.find('.prenda-nombre').attr('name', `prendas[${prendaIndex}][nombre]`);
        newPrenda.find('.prenda-categoria').attr('name', `prendas[${prendaIndex}][categoria]`);
        newPrenda.find('.prenda-tipo').attr('name', `prendas[${prendaIndex}][tipo]`);
        newPrenda.find('.prenda-costo').attr('name', `prendas[${prendaIndex}][precio_costo]`);
        newPrenda.find('.prenda-venta').attr('name', `prendas[${prendaIndex}][precio_venta]`);
        newPrenda.find('.prenda-descripcion').attr('name', `prendas[${prendaIndex}][descripcion]`);
        newPrenda.find('.prenda-number').text(prendaIndex + 1);
        
        // Llenar datos si existen
        if (data) {
            newPrenda.find('.prenda-codigo').val(data.codigo_prenda || '');
            newPrenda.find('.prenda-nombre').val(data.nombre || '');
            newPrenda.find('.prenda-categoria').val(data.categoria || '');
            newPrenda.find('.prenda-tipo').val(data.tipo || '');
            newPrenda.find('.prenda-costo').val(data.precio_costo || '');
            newPrenda.find('.prenda-venta').val(data.precio_venta || '');
            newPrenda.find('.prenda-descripcion').val(data.descripcion || '');
        }
        
        container.append(newPrenda);
        prendaIndex++;
        
        // Eventos
        newPrenda.find('.remove-prenda').on('click', function() {
            $(this).closest('.prenda-row').fadeOut(300, function() {
                $(this).remove();
                updateSummary();
                renumberPrendas(containerId);
            });
        });
        
        newPrenda.find('input, select').on('input change', function() {
            calcularMargen(newPrenda);
            updateSummary();
        });
        
        if (data) calcularMargen(newPrenda);
        updateSummary();
    }

    function calcularMargen(row) {
        const costo = parseFloat(row.find('.prenda-costo').val()) || 0;
        const venta = parseFloat(row.find('.prenda-venta').val()) || 0;
        const margenEl = row.find('.margen-display');
        
        if (costo > 0 && venta > costo) {
            const ganancia = venta - costo;
            const porcentaje = ((ganancia / costo) * 100).toFixed(1);
            margenEl.val(`$${ganancia.toFixed(2)} (${porcentaje}%)`).css('color', '#28a745');
            row.find('.prenda-venta').removeClass('is-invalid');
        } else if (venta > 0) {
            margenEl.val('Precio inválido').css('color', '#dc3545');
            row.find('.prenda-venta').addClass('is-invalid');
        }
    }

    function renumberPrendas(containerId) {
        $(`#${containerId} .prenda-row`).each(function(index) {
            $(this).find('.prenda-number').text(index + 1);
        });
    }

    function updateSummary() {
        let total = 0, ganancia = 0;
        $('.prenda-row').each(function() {
            const costo = parseFloat($(this).find('.prenda-costo').val()) || 0;
            const venta = parseFloat($(this).find('.prenda-venta').val()) || 0;
            total += costo;
            ganancia += (venta - costo);
        });
        
        const count = $('.prenda-row').length;
        const pct = total > 0 ? ((ganancia / total) * 100).toFixed(1) : 0;
        
        $('#summaryTotalPrendas, #editSummaryTotalPrendas').text(count);
        $('#summaryMontoTotal, #editSummaryMontoTotal').text(total.toFixed(2));
        $('#montoTotal, #editMontoTotal').val(total.toFixed(2));
        
        if (count > 0 && !$('#summaryGanancia').length) {
            $('#purchaseSummary').append(`
                <div id="summaryGanancia" class="row mt-2">
                    <div class="col-12 text-center">
                        <small class="text-success">
                            Ganancia estimada: <strong>$${ganancia.toFixed(2)}</strong> (${pct}%)
                        </small>
                    </div>
                </div>
            `);
        } else if (count > 0) {
            $('#summaryGanancia small').html(`
                Ganancia estimada: <strong>$${ganancia.toFixed(2)}</strong> (${pct}%)
            `);
        } else {
            $('#summaryGanancia').remove();
        }
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
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(data) {
                        if (data.success && data.data.length) {
                            let html = data.data.map(s => `
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
        
        // Validaciones
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
                precio_venta: row.find('.prenda-venta').val(),
                descripcion: row.find('.prenda-descripcion').val().trim()
            };
            
            if (!prenda.nombre || !prenda.categoria || !prenda.tipo) {
                showError('Complete todos los campos obligatorios');
                error = true;
                return false;
            }
            
            if (parseFloat(prenda.precio_venta) <= parseFloat(prenda.precio_costo)) {
                showError('El precio de venta debe ser mayor al costo');
                error = true;
                return false;
            }
            
            prendas.push(prenda);
        });
        
        if (error || !prendas.length) {
            if (!prendas.length) showError('Agregue al menos un producto');
            return;
        }
        
        // Enviar
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
            dataType: 'json', // 👈 MUY IMPORTANTE
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                console.log('Respuesta servidor:', res); // 👈 útil para depurar
                if (res.success) {
                    Swal.fire({ icon: 'success', title: '¡Éxito!', text: res.message, timer: 2000, showConfirmButton: false });
                    $('#addPurchaseModal').modal('hide');
                    $('#addPurchaseForm')[0].reset();
                    $('#prendasContainer').empty();
                    fetchPurchases();
                } else {
                    showError(res.message);
                }
            },
            error: (xhr) => {
                console.error(xhr.responseText);
                showError('Error al guardar la compra');
            },
            complete: function() {
                btn.prop('disabled', false).find('.spinner-border').addClass('d-none');
                btn.find('.btn-text').html('<i class="fas fa-save me-1"></i>Guardar Compra');
            }
        });

    });

    // ============================================
    // FORMULARIO: EDITAR COMPRA
    // ============================================
    $('#editPurchaseForm').on('submit', function(e) {
        e.preventDefault();

        const btn = $('#btnGuardarEdit');
        btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');

        const formData = new FormData(this);
        formData.delete('prendas'); // Previene que llegue accidentalmente

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
                } else {
                    showError(res.message);
                }
            },
            complete: () => btn.prop('disabled', false).find('.spinner-border').addClass('d-none')
        });
    });

    // ============================================
    // CARGAR Y RENDERIZAR COMPRAS
    // ============================================
    function fetchPurchases() {
        $('#purchaseTableBody').html('<tr><td colspan="4" class="text-center py-4"><div class="spinner-border"></div></td></tr>');
        
        $.ajax({
            url: window.location.pathname + '?action=get_purchases',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                if (data.success && data.data.length) {
                    allPurchases = data.data;
                    renderPurchases(allPurchases);
                } else {
                    $('#purchaseTableBody').html('<tr><td colspan="4" class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><p>No hay compras</p></td></tr>');
                }
            },
            error: () => $('#purchaseTableBody').html('<tr><td colspan="4" class="text-center text-danger">Error al cargar</td></tr>')
        });
    }

    function renderPurchases(purchases) {
        const html = purchases.map(p => `
            <tr class="purchase-row">
                <td><strong>#${p.factura_numero}</strong> ${p.pdf_generado == 1 ? '<i class="fas fa-check-circle text-success"></i>' : ''}</td>
                <td><div>${p.nombre_proveedor}</div><small class="text-muted">${p.total_prendas} prenda(s)</small></td>
                <td><div>${p.fecha_compra}</div><small class="text-muted">${p.monto_total}</small></td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewPurchase(${p.compra_id})" title="Ver"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-outline-success" onclick="downloadPdf(${p.compra_id})" title="PDF"><i class="fas fa-file-pdf"></i></button>
                        <button class="btn btn-outline-warning" onclick="editPurchase(${p.compra_id})" title="Editar"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-outline-danger" onclick="deletePurchase(${p.compra_id})" title="Eliminar"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
        $('#purchaseTableBody').html(html);
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
                    
                    const prendasHtml = `<table class="table table-sm"><thead><tr><th>Código</th><th>Nombre</th><th>Categoría</th><th>P.Costo</th><th>P.Venta</th><th>Estado</th></tr></thead><tbody>
                        ${p.map(pr => `<tr><td><code>${pr.codigo_prenda}</code></td><td>${pr.nombre}</td><td><span class="badge bg-info">${pr.categoria}</span></td><td>$${parseFloat(pr.precio_costo).toFixed(2)}</td><td>$${parseFloat(pr.precio_venta).toFixed(2)}</td><td>${pr.estado === 'DISPONIBLE' ? '<span class="badge bg-success">Disponible</span>' : '<span class="badge bg-secondary">Vendida</span>'}</td></tr>`).join('')}
                    </tbody></table>`;
                    
                    Swal.fire({
                        title: `Compra #${c.factura_numero}`,
                        html: `<div class="text-start"><p><strong>Proveedor:</strong> ${c.nombre_proveedor}</p><p><strong>Fecha:</strong> ${new Date(c.fecha_compra).toLocaleDateString()}</p><p><strong>Monto:</strong> $${parseFloat(c.monto_total).toFixed(2)}</p><hr>${prendasHtml}</div>`,
                        width: '800px',
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
                    data.data.prendas.forEach(p => addPrenda('editPrendasContainer', p));
                    // --- BLOQUEAR CAMPOS DE PRENDAS ---
                    $('#editPrendasContainer').find('input, select, textarea, button.remove-prenda').each(function(){
                        $(this).prop('disabled', true);
                    });
                    $('#editPurchaseModal').modal('show');
                }
            }
        });
    };

    window.deletePurchase = (id) => {
        Swal.fire({
            title: '¿Eliminar?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Eliminar',
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
                        } else {
                            showError(data.message);
                        }
                    }
                });
            }
        });
    };

window.downloadPdf = (id) => {
    window.location.href = window.location.pathname + `?action=generate_pdf&compra_id=${id}`;
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

    // Agregar la primera línea de prenda automáticamente
    setTimeout(() => addPrenda('prendasContainer'), 100);
});

$('#addPurchaseModal').on('hidden.bs.modal', function() {
    $(this).find('.is-invalid').removeClass('is-invalid');
});

// ============================================
// MODAL EDITAR COMPRA
// ============================================
$('#editPurchaseModal').on('hidden.bs.modal', function() {
    $('#editPrendasContainer').empty();
    prendaIndex = 0;
});

// ============================================
// INICIALIZAR LISTADO DE COMPRAS
// ============================================
fetchPurchases();
});