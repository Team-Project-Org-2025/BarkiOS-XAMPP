// =========================================================
// PURCHASE-ADMIN.JS - Gestión de Compras (VERSIÓN SIMPLIFICADA)
// Cada prenda es única con su propio precio
// ⚠️ 100% JQUERY - NO VANILLA JS
// =========================================================

$(document).ready(function() {
    let searchTimeout;
    let allPurchases = [];
    let categorias = [];
    let prendaCounter = 0;

    // ========== UTILIDADES ==========
    const escapeHtml = str => String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    
    const showAlert = (msg, type = 'info') => {
        let icon = 'info';
        if (type === 'success') icon = 'success';
        else if (type === 'danger' || type === 'error') icon = 'error';
        else if (type === 'warning') icon = 'warning';
        
        Swal.fire({
            text: msg,
            icon: icon,
            timer: 3000,
            showConfirmButton: false,
            timerProgressBar: true,
            position: 'top',
            toast: true
        });
    };

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('es-VE', {
            style: 'currency',
            currency: 'USD'
        }).format(amount || 0);
    };

    const formatDate = (dateString) => {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleDateString('es-VE', { year: 'numeric', month: 'short', day: 'numeric' });
    };

    // ========== CARGAR CATEGORÍAS ==========
    function loadCategorias() {
        $.ajax({
            url: '/BarkiOS/app/controllers/Admin/PurchaseController.php?action=get_categorias',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (data.success && data.data) {
                    categorias = data.data;
                }
            },
            error: function() {
                console.error('Error al cargar categorías');
            }
        });
    }

    // ========== BÚSQUEDA DE PROVEEDORES ==========
    $('#searchSupplier').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        
        if (query.length < 2) {
            $('#supplierResults').hide();
            $('#proveedorId').val('');
            return;
        }
        
        $('#supplierResults').html('<div class="list-group-item">Buscando...</div>').show();
        
        searchTimeout = setTimeout(function() {
            $.ajax({
                url: '/BarkiOS/app/controllers/Admin/PurchaseController.php',
                type: 'GET',
                data: { action: 'search_supplier', search: query },
                dataType: 'json',
                success: function(data) {
                    if (data.success && data.data.length > 0) {
                        let html = '';
                        $.each(data.data, function(i, supplier) {
                            html += `<button type="button" class="list-group-item list-group-item-action supplier-option" 
                                    data-id="${escapeHtml(supplier.id)}" 
                                    data-nombre="${escapeHtml(supplier.nombre_empresa)}">
                                <strong>${escapeHtml(supplier.nombre_empresa)}</strong><br>
                                <small class="text-muted">RIF: ${escapeHtml(supplier.id)} | Contacto: ${escapeHtml(supplier.nombre_contacto)}</small>
                            </button>`;
                        });
                        $('#supplierResults').html(html);
                    } else {
                        $('#supplierResults').html('<div class="list-group-item">No se encontraron comercios</div>');
                    }
                },
                error: function() {
                    $('#supplierResults').html('<div class="list-group-item text-danger">Error al buscar</div>');
                }
            });
        }, 300);
    });

    // Seleccionar proveedor
    $(document).on('click', '.supplier-option', function() {
        $('#searchSupplier').val($(this).data('nombre'));
        $('#proveedorId').val($(this).data('id'));
        $('#supplierResults').hide();
    });

    // Ocultar resultados al hacer clic fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#searchSupplier, #supplierResults').length) {
            $('#supplierResults').hide();
        }
    });

    // ========== GESTIÓN DE PRENDAS ==========
    // Usar delegación de eventos para evitar duplicación
    $(document).on('click', '#btnAddPrenda', function(e) {
        e.preventDefault();
        e.stopPropagation();
        addPrendaRow();
    });

    function addPrendaRow() {
        prendaCounter++;
        const categoriasOptions = categorias.map(cat => 
            `<option value="${escapeHtml(cat)}">${escapeHtml(cat)}</option>`
        ).join('');
        
        const rowHtml = `
            <div class="prenda-row" id="prenda-${prendaCounter}">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <label class="form-label small">Prenda <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-sm" 
                               name="prendas[${prendaCounter}][producto_nombre]" 
                               placeholder="Ej: Pantalón Levi's 501 Talla 32 Azul" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Categoría <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm" 
                                name="prendas[${prendaCounter}][categoria]" required>
                            <option value="">Seleccione...</option>
                            ${categoriasOptions}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Precio Costo <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm precio-input" 
                               name="prendas[${prendaCounter}][precio_costo]" 
                               placeholder="0.00" min="0.01" step="0.01" required>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label small">&nbsp;</label>
                        <button type="button" class="btn btn-sm btn-danger w-100 btn-remove-prenda" data-id="${prendaCounter}" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('#prendasContainer').append(rowHtml);
        $('#noPrendasAlert').hide();
    }

    // Eliminar prenda
    $(document).on('click', '.btn-remove-prenda', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const id = $(this).data('id');
        const $row = $(`#prenda-${id}`);
        
        // Verificar que existe antes de eliminar
        if ($row.length > 0) {
            $row.remove();
            
            // Mostrar alerta si no hay prendas
            if ($('#prendasContainer').children().length === 0) {
                $('#noPrendasAlert').show();
            }
            
            updateSummary();
        }
    });

    // Calcular al cambiar precio
    $(document).on('input', '.precio-input', function() {
        updateSummary();
    });

    // ========== CALCULAR RESUMEN ==========
    function updateSummary() {
        let total = 0;
        let cantidad = 0;
        
        $('.prenda-row').each(function() {
            const precio = parseFloat($(this).find('.precio-input').val()) || 0;
            if (precio > 0) {
                total += precio;
                cantidad++;
            }
        });
        
        $('#summary_cantidad').text(cantidad);
        $('#summary_total').text(formatCurrency(total));
    }

    // ========== CARGAR COMPRAS ==========
    function fetchPurchases() {
        $('#purchasesTableBody').html(`<tr><td colspan="7" class="text-center">
            <div class="spinner-border text-purple"></div> Cargando...</td></tr>`);
        
        $.ajax({
            url: '/BarkiOS/app/controllers/Admin/PurchaseController.php?action=get_purchases',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if (!data.success || !data.data || !data.data.length) {
                    $('#purchasesTableBody').html(`
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="alert alert-info mb-0">No hay compras registradas</div>
                            </td>
                        </tr>`);
                    return;
                }
                allPurchases = data.data;
                renderPurchases(allPurchases);
            },
            error: function() {
                $('#purchasesTableBody').html(`
                    <tr>
                        <td colspan="7" class="text-center text-danger">
                            Error al cargar las compras
                        </td>
                    </tr>`);
            }
        });
    }

    function renderPurchases(purchases) {
        let html = '';
        $.each(purchases, function(index, purchase) {
            html += `
                <tr id="purchase-${escapeHtml(purchase.compra_id)}">
                    <td class="text-center">${index + 1}</td>
                    <td><strong>${escapeHtml(purchase.factura_numero)}</strong></td>
                    <td>${purchase.tracking ? `<span class="badge bg-info">${escapeHtml(purchase.tracking)}</span>` : '<small class="text-muted">Sin tracking</small>'}</td>
                    <td>${escapeHtml(purchase.proveedor || 'N/A')}</td>
                    <td class="text-center"><small>${formatDate(purchase.fecha_compra)}</small></td>
                    <td class="text-end fw-bold text-success">${formatCurrency(purchase.monto_total)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-success btn-download-pdf" data-id="${purchase.compra_id}" title="Descargar PDF">
                            <i class="fas fa-file-pdf me-1"></i>Descargar
                        </button>
                    </td>
                </tr>
            `;
        });
        $('#purchasesTableBody').html(html);
    }

    // ========== DESCARGAR PDF ==========
    $(document).on('click', '.btn-download-pdf', function() {
        const id = $(this).data('id');
        window.open(`/BarkiOS/app/controllers/Admin/PurchaseController.php?action=download_pdf&id=${id}`, '_blank');
        showAlert('Generando PDF...', 'info');
    });

    // ========== GUARDAR COMPRA ==========
    $('#addPurchaseForm').on('submit', function(e) {
        e.preventDefault();
        
        // Validar que se haya seleccionado un proveedor
        if (!$('#proveedorId').val()) {
            showAlert('Por favor seleccione un comercio', 'error');
            return;
        }
        
        // Validar que haya prendas
        if ($('#prendasContainer').children().length === 0) {
            showAlert('Debe agregar al menos una prenda', 'error');
            return;
        }
        
        // Deshabilitar botón
        const $btnGuardar = $('#btnGuardar');
        $btnGuardar.prop('disabled', true);
        $btnGuardar.find('.spinner-border').removeClass('d-none');
        $btnGuardar.find('.btn-text').text('Guardando...');
        
        const formData = $(this).serialize();
        
        $.ajax({
            url: '/BarkiOS/app/controllers/Admin/PurchaseController.php?action=add_ajax',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    showAlert('Compra registrada correctamente. Generando PDF...', 'success');
                    
                    // Cerrar modal
                    $('#addPurchaseModal').modal('hide');
                    
                    // Limpiar formulario
                    $('#addPurchaseForm')[0].reset();
                    $('#proveedorId').val('');
                    $('#searchSupplier').val('');
                    $('#prendasContainer').html('');
                    $('#noPrendasAlert').show();
                    prendaCounter = 0;
                    updateSummary();
                    
                    // Recargar tabla
                    fetchPurchases();
                    
                    // Descargar PDF automáticamente
                    setTimeout(function() {
                        window.open(`/BarkiOS/app/controllers/Admin/PurchaseController.php?action=download_pdf&id=${result.compra_id}`, '_blank');
                    }, 1000);
                } else {
                    showAlert(result.message || 'Error al guardar', 'error');
                }
            },
            error: function() {
                showAlert('Error al procesar la solicitud', 'error');
            },
            complete: function() {
                $btnGuardar.prop('disabled', false);
                $btnGuardar.find('.spinner-border').addClass('d-none');
                $btnGuardar.find('.btn-text').text('Guardar Compra y Generar PDF');
            }
        });
    });

    // ========== BÚSQUEDA EN TABLA ==========
    $('#searchInput').on('input', function() {
        const query = $(this).val().toLowerCase();
        const filtered = allPurchases.filter(p => 
            (p.factura_numero && p.factura_numero.toLowerCase().includes(query)) ||
            (p.proveedor && p.proveedor.toLowerCase().includes(query)) ||
            (p.tracking && p.tracking.toLowerCase().includes(query))
        );
        renderPurchases(filtered);
    });

    // ========== LIMPIAR FORMULARIO AL CERRAR MODAL ==========
    $('#addPurchaseModal').on('hidden.bs.modal', function () {
        $('#addPurchaseForm')[0].reset();
        $('#proveedorId').val('');
        $('#searchSupplier').val('');
        $('#prendasContainer').html('');
        $('#noPrendasAlert').show();
        $('#supplierResults').hide();
        prendaCounter = 0;
        updateSummary();
    });

    // ========== INICIALIZACIÓN ==========
    loadCategorias();

    // Verificar si ya se cargaron las compras para evitar duplicación
    if (!$('#purchasesTableBody').hasClass('loaded')) {
        fetchPurchases();
        $('#purchasesTableBody').addClass('loaded');
    }

    // Inicializar tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
});
