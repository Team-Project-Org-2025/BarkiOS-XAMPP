$(document).ready(function() {
    console.log('Purchase admin JS loaded');

    let prendaIndex = 0;

    // Función para actualizar el resumen
    function updateSummary() {
        const addPrendas = $('.prenda-row').length;
        let addTotal = 0;

        $('.prenda-row').each(function() {
            const precioInput = $(this).find('input[name*="[precio_costo]"]');
            const precio = parseFloat(precioInput.val()) || 0;
            addTotal += precio;
        });

        $('#summaryTotalPrendas').text(addPrendas);
        $('#summaryMontoTotal').text(addTotal.toFixed(2));

        // Actualizar el campo de monto total (hidden, no visible para el usuario)
        $('#montoTotal').val(addTotal.toFixed(2));
        $('#editMontoTotal').val(addTotal.toFixed(2));

        console.log('Summary updated:', addPrendas, 'prendas, $' + addTotal.toFixed(2));
    }

    // Función simplificada para agregar prenda usando template
    function addPrenda(containerId) {
        const container = $(`#${containerId}`);
        console.log(`Adding prenda to ${containerId}, exists:`, container.length);

        if (container.length === 0) {
            console.error(`Container ${containerId} not found`);
            return;
        }

        // Usar el template del HTML
        const template = $('#prendaTemplate').html();
        const prendaHtml = template.replace(/INDEX/g, prendaIndex);

        container.append(prendaHtml);
        prendaIndex++;

        // Configurar evento para remover
        container.find('.prenda-row:last .remove-prenda').on('click', function() {
            $(this).closest('.prenda-row').remove();
            updateSummary();
        });

        // Configurar eventos para actualizar resumen
        container.find('.prenda-row:last').find('input, select').on('input change', updateSummary);

        updateSummary();
        console.log('Prenda added successfully');
    }

    // Configurar botones
    $('#addPrendaBtn').on('click', function() {
        console.log('Add button clicked');
        addPrenda('prendasContainer');
    });

    $('#editAddPrendaBtn').on('click', function() {
        console.log('Edit button clicked');
        addPrenda('editPrendasContainer');
    });

    // Búsqueda de proveedores
    let searchTimeout;
    $('#searchSupplier').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $('#supplierResults').hide();
            return;
        }

        $('#supplierResults').html('<div class="list-group-item">Buscando...</div>').show();

        searchTimeout = setTimeout(() => {
            $.ajax({
                url: window.location.pathname + '?action=search_supplier',
                data: { search: query },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(data) {
                    if (data.success && data.data.length > 0) {
                        let html = '';
                        data.data.forEach(supplier => {
                            html += `
                                <button type="button" class="list-group-item list-group-item-action"
                                        data-id="${supplier.rif}"
                                        data-nombre="${supplier.nombre_empresa}">
                                    <strong>${supplier.nombre_empresa}</strong><br>
                                    <small class="text-muted">RIF: ${supplier.rif} | Contacto: ${supplier.nombre_contacto}</small>
                                </button>
                            `;
                        });
                        $('#supplierResults').html(html);

                        $('#supplierResults button').on('click', function() {
                            const supplier = $(this);
                            $('#searchSupplier').val(supplier.data('nombre'));
                            $('#proveedorId').val(supplier.data('id'));
                            $('#supplierResults').hide();
                        });
                    } else {
                        $('#supplierResults').html('<div class="list-group-item">No se encontraron proveedores</div>');
                    }
                },
                error: function() {
                    $('#supplierResults').html('<div class="list-group-item text-danger">Error al buscar</div>');
                }
            });
        }, 300);
    });

    // Búsqueda de proveedores para edición
    $('#editSearchSupplier').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $('#editSupplierResults').hide();
            return;
        }

        $('#editSupplierResults').html('<div class="list-group-item">Buscando...</div>').show();

        searchTimeout = setTimeout(() => {
            $.ajax({
                url: window.location.pathname + '?action=search_supplier',
                data: { search: query },
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(data) {
                    if (data.success && data.data.length > 0) {
                        let html = '';
                        data.data.forEach(supplier => {
                            html += `
                                <button type="button" class="list-group-item list-group-item-action"
                                        data-id="${supplier.rif}"
                                        data-nombre="${supplier.nombre_empresa}">
                                    <strong>${supplier.nombre_empresa}</strong><br>
                                    <small class="text-muted">RIF: ${supplier.rif} | Contacto: ${supplier.nombre_contacto}</small>
                                </button>
                            `;
                        });
                        $('#editSupplierResults').html(html);

                        $('#editSupplierResults button').on('click', function() {
                            const supplier = $(this);
                            $('#editSearchSupplier').val(supplier.data('nombre'));
                            $('#editProveedorId').val(supplier.data('id'));
                            $('#editSupplierResults').hide();
                        });
                    } else {
                        $('#editSupplierResults').html('<div class="list-group-item">No se encontraron proveedores</div>');
                    }
                },
                error: function() {
                    $('#editSupplierResults').html('<div class="list-group-item text-danger">Error al buscar</div>');
                }
            });
        }, 300);
    });

    // Ocultar resultados al hacer clic fuera
    $(document).on('click', function(e) {
        if (!$('#searchSupplier').is(e.target) && !$('#supplierResults').is(e.target)) {
            $('#supplierResults').hide();
        }
        if (!$('#editSearchSupplier').is(e.target) && !$('#editSupplierResults').is(e.target)) {
            $('#editSupplierResults').hide();
        }
    });

    // Funciones globales para botones de la tabla
    window.editPurchase = (compraId) => {
        $.ajax({
            url: window.location.pathname + '?action=get_purchase_detail',
            data: { compra_id: compraId },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                if (data.success) {
                    const purchase = data.data.compra;
                    const prendas = data.data.prendas;

                    // Llenar formulario de edición
                    $('#editCompraId').val(purchase.compra_id);
                    $('#editFacturaNumero').val(purchase.factura_numero);
                    $('#editFechaCompra').val(purchase.fecha_compra.split(' ')[0]);
                    $('#editTracking').val(purchase.tracking);
                    $('#editMontoTotal').val(purchase.monto_total);

                    // Llenar proveedor
                    $('#editSearchSupplier').val(purchase.nombre_proveedor);
                    $('#editProveedorId').val(purchase.proveedor_rif);

                    // Llenar prendas
                    $('#editPrendasContainer').empty();
                    prendaIndex = 0;
                    prendas.forEach(prenda => {
                        addPrenda('editPrendasContainer');
                        const lastRow = $('#editPrendasContainer .prenda-row:last');
                        lastRow.find('input[name*="[producto_nombre]"]').val(prenda.producto_nombre);
                        lastRow.find('select[name*="[categoria]"]').val(prenda.categoria);
                        lastRow.find('input[name*="[precio_costo]"]').val(prenda.precio_costo);
                    });

                    // Mostrar modal
                    const modal = new bootstrap.Modal(document.getElementById('editPurchaseModal'));
                    modal.show();
                } else {
                    Swal.fire({
                        text: data.message || 'Error al cargar la compra',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                        position: 'top',
                        toast: true
                    });
                }
            },
            error: function() {
                Swal.fire({
                    text: 'Error al cargar la compra',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false,
                    timerProgressBar: true,
                    position: 'top',
                    toast: true
                });
            }
        });
    };

    window.deletePurchase = (compraId) => {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: window.location.pathname + '?action=delete_ajax',
                    method: 'POST',
                    data: { compra_id: compraId },
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function(data) {
                        if (data.success) {
                            Swal.fire({
                                text: 'Compra eliminada correctamente',
                                icon: 'success',
                                timer: 3000,
                                showConfirmButton: false,
                                timerProgressBar: true,
                                position: 'top',
                                toast: true
                            });
                            fetchPurchases();
                        } else {
                            Swal.fire({
                                text: data.message || 'Error al eliminar la compra',
                                icon: 'error',
                                timer: 3000,
                                showConfirmButton: false,
                                timerProgressBar: true,
                                position: 'top',
                                toast: true
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            text: 'Error al eliminar la compra',
                            icon: 'error',
                            timer: 3000,
                            showConfirmButton: false,
                            timerProgressBar: true,
                            position: 'top',
                            toast: true
                        });
                    }
                });
            }
        });
    };

    window.downloadPdf = (compraId) => {
        $.ajax({
            url: window.location.pathname + '?action=download_pdf',
            data: { compra_id: compraId },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                if (data.success) {
                    Swal.fire({
                        text: 'PDF generado correctamente',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                        position: 'top',
                        toast: true
                    });
                } else {
                    Swal.fire({
                        text: data.message || 'Error al generar el PDF',
                        icon: 'error',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true,
                        position: 'top',
                        toast: true
                    });
                }
            },
            error: function() {
                Swal.fire({
                    text: 'Error al generar el PDF',
                    icon: 'error',
                    timer: 3000,
                    showConfirmButton: false,
                    timerProgressBar: true,
                    position: 'top',
                    toast: true
                });
            }
        });
    };

    // Cargar compras al iniciar
    const fetchPurchases = () => {
        $('#purchaseTableBody').html(`
            <tr>
                <td colspan="4" class="text-center">
                    <div class="spinner-border text-primary"></div> Cargando...
                </td>
            </tr>
        `);

        $.ajax({
            url: window.location.pathname + '?action=get_purchases',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(data) {
                if (!data.success || !data.data || !data.data.length) {
                    $('#purchaseTableBody').html(`
                        <tr>
                            <td colspan="4" class="text-center">
                                <div class="alert alert-info mb-0">No hay compras registradas</div>
                            </td>
                        </tr>
                    `);
                    return;
                }

                let html = '';
                data.data.forEach(purchase => {
                    html += `
                        <tr id="purchase-${purchase.compra_id}">
                            <td><strong>${purchase.factura_numero}</strong></td>
                            <td>${purchase.nombre_proveedor}</td>
                            <td>${purchase.fecha_compra}</td>
                            <td>
                                <button class="btn btn-sm btn-success me-2" onclick="downloadPdf(${purchase.compra_id})">
                                    <i class="fas fa-download me-1"></i>PDF
                                </button>
                                <button class="btn btn-sm btn-primary me-2" onclick="editPurchase(${purchase.compra_id})">
                                    <i class="fas fa-edit me-1"></i>Editar
                                </button>
                                <button class="btn btn-sm btn-danger" onclick="deletePurchase(${purchase.compra_id})">
                                    <i class="fas fa-trash me-1"></i>Eliminar
                                </button>
                            </td>
                        </tr>
                    `;
                });
                $('#purchaseTableBody').html(html);
            },
            error: function() {
                $('#purchaseTableBody').html(`
                    <tr>
                        <td colspan="4" class="text-center text-danger">
                            Error al cargar las compras
                        </td>
                    </tr>
                `);
            }
        });
    };

    // Inicialización
    $('#addPurchaseModal').on('show.bs.modal', function() {
        console.log('Modal show event triggered');
        setTimeout(() => {
            if ($('#prendasContainer .prenda-row').length === 0) {
                addPrenda('prendasContainer');
            }
        }, 500);
    });

    fetchPurchases();
    console.log('Purchase admin JS initialized');
});
