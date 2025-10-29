// ============================================
// PURCHASE ADMIN JS - OPTIMIZADO
// ============================================
$(document).ready(function() {
    let prendaIndex = 0;
    let allPurchases = [];

    // ============================================
    // CONFIGURACIÓN
    // ============================================
    const REGEX = {
        factura: /^\d{8}$/,
        codigo: /^\d{9}$/,
        nombre: /^[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9\s\-\.]{3,150}$/,
        precio: /^\d+(\.\d{1,2})?$/
    };

    const TIPOS_POR_CATEGORIA = {
        Formal: ["Vestido", "Camisa", "Pantalon", "Chaqueta"],
        Casual: ["Blusa", "Pantalon", "Short", "Falda"],
        Deportivo: ["Short", "Falda", "Chaqueta"],
        Invierno: ["Chaqueta", "Pantalon"],
        Verano: ["Vestido", "Short", "Blusa"],
        Fiesta: ["Vestido", "Falda", "Blusa", "Enterizo"]
    };

    // ============================================
    // UTILIDADES
    // ============================================
    const showError = msg => Swal.fire({ icon: 'error', title: 'Error', text: msg });
    
    const showSuccess = (msg, callback) => Swal.fire({ 
        icon: 'success', 
        title: '¡Éxito!', 
        text: msg, 
        timer: 2000, 
        showConfirmButton: false 
    }).then(callback);

    // ============================================
    // VALIDACIONES
    // ============================================
    function validarPrenda(prenda) {
        if (!REGEX.codigo.test(prenda.codigo_prenda || '')) 
            return 'Código inválido (9 dígitos)';
        if (!REGEX.nombre.test(prenda.nombre || '')) 
            return 'Nombre inválido (3-150 caracteres)';
        if (!prenda.categoria) 
            return 'Seleccione una categoría';
        if (!prenda.tipo) 
            return 'Seleccione un tipo';
        if (!REGEX.precio.test(prenda.precio_costo || '') || parseFloat(prenda.precio_costo) <= 0) 
            return 'Precio inválido';
        return null;
    }

    function validarCampo($input, regex) {
        const val = $input.val().trim();
        const ok = val && (!regex || regex.test(val));
        $input.toggleClass('is-invalid', !ok).toggleClass('is-valid', ok);
        return ok;
    }

    // ============================================
    // GESTIÓN DE PRENDAS
    // ============================================
    function actualizarTipos($cat, $tipo) {
        const categoria = $cat.val();
        $tipo.html('<option value="">Seleccione un tipo</option>');
        
        (TIPOS_POR_CATEGORIA[categoria] || []).forEach(tipo => {
            $tipo.append(`<option value="${tipo}">${tipo}</option>`);
        });
    }

    function aplicarValidacion($row) {
        const campos = [
            { sel: '.prenda-codigo', regex: REGEX.codigo },
            { sel: '.prenda-nombre', regex: REGEX.nombre },
            { sel: '.prenda-costo', regex: REGEX.precio },
            { sel: '.prenda-categoria', regex: null },
            { sel: '.prenda-tipo', regex: null }
        ];

        campos.forEach(({ sel, regex }) => {
            const $campo = $row.find(sel);
            $campo.on('input blur change', () => {
                validarCampo($campo, regex);
                if (sel === '.prenda-costo') updateSummary();
            });
        });
    }

    function addPrenda(containerId, data = null, editable = true) {
        const $container = $(`#${containerId}`);
        const $prenda = $(document.getElementById('prendaTemplate').innerHTML);
        
        // Configurar atributos
        const attrs = ['codigo_prenda', 'nombre', 'categoria', 'tipo', 'precio_costo', 'descripcion'];
        attrs.forEach(attr => {
            $prenda.find(`.prenda-${attr.replace('_', '-')}`).attr('name', `prendas[${prendaIndex}][${attr}]`);
        });
        
        $prenda.find('.prenda-number').text(prendaIndex + 1);

        // Llenar datos si existen
        if (data) {
            $prenda.find('.prenda-codigo').val(data.codigo_prenda || '');
            $prenda.find('.prenda-nombre').val(data.nombre || '');
            $prenda.find('.prenda-categoria').val(data.categoria || '');
            $prenda.find('.prenda-costo').val(data.precio_costo || '');
            $prenda.find('.prenda-descripcion').val(data.descripcion || '');
            
            const $cat = $prenda.find('.prenda-categoria');
            const $tipo = $prenda.find('.prenda-tipo');
            
            if (data.categoria) {
                actualizarTipos($cat, $tipo);
                if (data.tipo) $tipo.val(data.tipo);
            }

            if (!editable) {
                $prenda.addClass('prenda-existente')
                       .find('input, select, textarea').prop('disabled', true).end()
                       .find('.remove-prenda').remove();
                $prenda.css('background-color', '#f8f9fa');
            }
        }

        // Configurar eventos
        const $cat = $prenda.find('.prenda-categoria');
        const $tipo = $prenda.find('.prenda-tipo');

        $cat.on('change', function() {
            actualizarTipos($cat, $tipo);
            validarCampo($(this), null);
        });

        $tipo.on('change', function() {
            validarCampo($(this), null);
        });

        $container.append($prenda);
        prendaIndex++;

        if (editable) {
            $prenda.find('.remove-prenda').on('click', function() {
                $prenda.fadeOut(300, function() {
                    $(this).remove();
                    updateSummary();
                    $(`#${containerId} .prenda-row`).each((i, el) => {
                        $(el).find('.prenda-number').text(i + 1);
                    });
                });
            });

            $prenda.find('input, select').on('input change', updateSummary);
            aplicarValidacion($prenda);
        }

        updateSummary();
    }

    function updateSummary() {
        let total = 0;
        $('.prenda-row').each(function() {
            total += parseFloat($(this).find('.prenda-costo').val()) || 0;
        });
        
        const count = $('.prenda-row').length;
        $('#summaryTotalPrendas, #editSummaryTotalPrendas').text(count);
        $('#summaryMontoTotal, #editSummaryMontoTotal').text(total.toFixed(2));
        $('#montoTotal, #editMontoTotal').val(total.toFixed(2));
    }

    function recopilarPrendas(selector) {
        const prendas = [];
        let error = null;

        $(selector).each(function() {
            const $row = $(this);
            const prenda = {
                codigo_prenda: $row.find('.prenda-codigo').val().trim(),
                nombre: $row.find('.prenda-nombre').val().trim(),
                categoria: $row.find('.prenda-categoria').val(),
                tipo: $row.find('.prenda-tipo').val(),
                precio_costo: $row.find('.prenda-costo').val(),
                descripcion: $row.find('.prenda-descripcion').val().trim()
            };

            const err = validarPrenda(prenda);
            if (err) {
                error = err;
                return false;
            }
            prendas.push(prenda);
        });

        return { prendas, error };
    }

    // ============================================
    // BÚSQUEDA DE PROVEEDORES
    // ============================================
    function setupSupplierSearch(inputId, resultsId, hiddenId) {
        let timeout;
        const $input = $(`#${inputId}`);
        const $results = $(`#${resultsId}`);
        const $hidden = $(`#${hiddenId}`);

        $input.on('input', function() {
            clearTimeout(timeout);
            const query = $(this).val().trim();
            
            if (query.length < 2) {
                $results.hide();
                $hidden.val('');
                return;
            }
            
            $results.html('<div class="list-group-item"><i class="fas fa-spinner fa-spin"></i> Buscando...</div>').show();
            
            timeout = setTimeout(() => {
                $.ajax({
                    url: window.location.pathname + '?action=search_supplier',
                    data: { search: query },
                    dataType: 'json',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(data) {
                        if (data.success && data.results?.length) {
                            const html = data.results.map(s => `
                                <button type="button" class="list-group-item list-group-item-action supplier-item"
                                        data-id="${s.proveedor_rif || s.rif}" data-nombre="${s.nombre_empresa}">
                                    <strong>${s.nombre_empresa}</strong><br>
                                    <small class="text-muted">RIF: ${s.proveedor_rif || s.rif} | ${s.nombre_contacto}</small>
                                </button>
                            `).join('');
                            
                            $results.html(html).find('.supplier-item').on('click', function() {
                                $input.val($(this).data('nombre')).addClass('is-valid');
                                $hidden.val($(this).data('id'));
                                $results.hide();
                            });
                        } else {
                            $results.html('<div class="list-group-item text-muted">Sin resultados</div>');
                        }
                    },
                    error: () => $results.html('<div class="list-group-item text-danger">Error</div>')
                });
            }, 300);
        });

        // Cerrar al hacer clic fuera
        $(document).on('click', (e) => {
            if (!$(e.target).closest(`#${inputId}, #${resultsId}`).length) {
                $results.hide();
            }
        });
    }

    // ============================================
    // FORMULARIOS
    // ============================================
    function enviarFormulario(action, formData, $btn, onSuccess) {
        const btnText = $btn.html();
        $btn.prop('disabled', true).find('.spinner-border').removeClass('d-none');
        $btn.find('.btn-text').text('Guardando...');

        $.ajax({
            url: window.location.pathname + '?action=' + action,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                if (res.success) {
                    showSuccess(res.message, onSuccess);
                } else {
                    showError(res.message);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                const msg = xhr.responseJSON?.message || xhr.responseText || 'Error desconocido';
                showError(msg);
            },
            complete: () => {
                $btn.prop('disabled', false).html(btnText);
            }
        });
    }

    $('#addPurchaseForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validaciones
        const factura = $('#facturaNumero').val();
        const tracking = $('#tracking').val();
        const proveedor = $('#proveedorId').val();
        
        if (!REGEX.factura.test(factura)) return showError('Factura inválida (8 dígitos)');
        if (tracking && !REGEX.factura.test(tracking)) return showError('Tracking inválido (8 dígitos)');
        if (!proveedor) return showError('Seleccione un proveedor');
        
        const { prendas, error } = recopilarPrendas('.prenda-row');
        if (error) return showError(error);
        if (!prendas.length) return showError('Agregue al menos un producto');
        
        // Preparar FormData
        const formData = new FormData(this);
        formData.delete('prendas');
        prendas.forEach((p, i) => {
            Object.keys(p).forEach(k => formData.append(`prendas[${i}][${k}]`, p[k]));
        });
        
        enviarFormulario('add_ajax', formData, $('#btnGuardar'), () => {
            $('#addPurchaseModal').modal('hide');
            this.reset();
            $('#prendasContainer').empty();
            fetchPurchases();
            loadStats();
        });
    });

    $('#editPurchaseForm').on('submit', function(e) {
        e.preventDefault();

        const { prendas, error } = recopilarPrendas('#editPrendasContainer .prenda-row:not(.prenda-existente)');
        if (error) return showError(error);

        const formData = new FormData(this);
        prendas.forEach((p, i) => {
            Object.keys(p).forEach(k => formData.append(`nuevas_prendas[${i}][${k}]`, p[k]));
        });

        enviarFormulario('edit_ajax', formData, $('#btnGuardarEdit'), () => {
            $('#editPurchaseModal').modal('hide');
            fetchPurchases();
            loadStats();
        });
    });

    // ============================================
    // CARGA Y RENDERIZADO
    // ============================================
    function fetchPurchases() {
        $('#purchaseTableBody').html('<tr><td colspan="6" class="text-center py-4"><div class="spinner-border"></div></td></tr>');
        
        $.ajax({
            url: window.location.pathname + '?action=get_purchases',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                if (data.success && data.data?.length) {
                    allPurchases = data.data;
                    renderPurchases(allPurchases);
                    pintarMontoPagado();
                } else {
                    $('#purchaseTableBody').html('<tr><td colspan="6" class="text-center py-4"><i class="fas fa-inbox fa-3x text-muted mb-3"></i><p>No hay compras</p></td></tr>');
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
                <td class="text-end"><strong class="text-success">$${p.monto_total}</strong></td>
                <td class="text-center">
                    <span class="badge bg-success">${p.prendas_disponibles || 0}</span>
                    <span class="badge bg-secondary">${p.prendas_vendidas || 0}</span>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" onclick="viewPurchase(${p.compra_id})"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-outline-success" onclick="generarPdf(${p.compra_id})"><i class="fas fa-file-pdf"></i></button>
                        <button class="btn btn-outline-warning" onclick="editPurchase(${p.compra_id})"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-outline-danger" onclick="deletePurchase(${p.compra_id})"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
        $('#purchaseTableBody').html(html);
    }

    function pintarMontoPagado() {
        let totalPagado = 0, totalPendiente = 0, montoTotal = 0;

        allPurchases.forEach(p => {
            totalPagado += parseFloat(p.total_pagado || 0);
            totalPendiente += parseFloat(p.saldo_pendiente || 0);
            montoTotal += parseFloat(p.monto_total || 0);
        });

        $('#statMontoPagado').text(`Pagado: $${totalPagado.toFixed(2)}`);
        $('#statSaldoPendiente').text(`$${totalPendiente.toFixed(2)}`);
        $('#statMontoTotal').text(`$${montoTotal.toFixed(2)}`);

        const porcentaje = montoTotal > 0 ? (totalPagado / montoTotal) * 100 : 0;
        const offset = 220 - (220 * porcentaje / 100);

        $('#progressBar').css('stroke-dashoffset', offset);
        $('#progressPercent').text(Math.round(porcentaje) + '%');
    }

    function loadStats() {
        $.ajax({
            url: window.location.pathname + '?action=get_stats',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                if (data.success) {
                    const s = data.stats;
                    $('#statTotalCompras').text(s.total_compras || 0);
                    $('#statMontoTotal').text(`$${parseFloat(s.monto_total_compras || 0).toFixed(2)}`);
                    $('#statValorInventario').text(`$${parseFloat(s.valor_inventario || 0).toFixed(2)}`);
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
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover">
                                        <thead class="table-light">
                                            <tr><th>Código</th><th>Nombre</th><th>Categoría</th><th>Tipo</th><th class="text-end">P.Costo</th><th class="text-center">Estado</th></tr>
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
                    data.data.prendas.forEach(p => addPrenda('editPrendasContainer', p, false));

                    $('#editPurchaseModal').modal('show');
                }
            }
        });
    };

    window.deletePurchase = (id) => {
        Swal.fire({
            title: '¿Eliminar compra?',
            text: 'Las prendas también se eliminarán.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: window.location.pathname + '?action=delete_ajax',
                    method: 'POST',
                    data: { compra_id: id },
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(data) {
                        if (data.success) {
                            showSuccess('Eliminado correctamente', () => {
                                fetchPurchases();
                                loadStats();
                            });
                        } else {
                            showError(data.message);
                        }
                    },
                    error: (xhr) => showError(xhr.responseJSON?.message || 'Error al eliminar')
                });
            }
        });
    };

    window.generarPdf = (id) => {
        window.location.href = `?action=generate_pdf&compra_id=${id}`;
    };

    // ============================================
    // EVENTOS
    // ============================================
    $('#addPrendaBtn').on('click', () => addPrenda('prendasContainer'));
    $('#addEditPrendaBtn').on('click', () => addPrenda('editPrendasContainer', null, true));
    
    $('#searchInput').on('input', function() {
        const term = $(this).val().toLowerCase();
        const filtered = allPurchases.filter(p => 
            String(p.factura_numero || '').toLowerCase().includes(term) ||
            String(p.nombre_proveedor || '').toLowerCase().includes(term)
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
        $(this).find('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
    });

    // Configurar búsquedas
    setupSupplierSearch('searchSupplier', 'supplierResults', 'proveedorId');
    setupSupplierSearch('editSearchSupplier', 'editSupplierResults', 'editProveedorId');

    // ============================================
    // INICIALIZAR
    // ============================================
    fetchPurchases();
    loadStats();
});