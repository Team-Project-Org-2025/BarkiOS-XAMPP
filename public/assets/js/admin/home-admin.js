$(document).ready(function() {
    
    //variables globales
    const baseUrl = '/BarkiOS/admin/home';
    let currentFilter = 'today';
    let ventasComprasChart = null;
    let cuentasChart = null;
    let allTransactions = [];

    //Formateadores
    const fmt = (n) => {
        return new Intl.NumberFormat('es-VE', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
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

    const fmtDateTime = (dateStr) => {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        if (isNaN(date)) return String(dateStr);
        return date.toLocaleString('es-ES', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    //Notificaciones
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

    //Ajax-helper
    function ajax(method, action, data, success, error) {
        $.ajax({
            url: `${baseUrl}?action=${action}`,
            method: method,
            data: data,
            dataType: 'json',
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

    //Cargar datos de dashboard
    function loadDashboard() {
        const params = getFilterParams();
        
        showLoading();
        updatePeriodLabel();

        ajax('GET', 'get_stats', params, function(response) {
            if (response.success) {
                updateStats(response.data);
                updateCharts(response.data);
                loadTransactions();
                updateAlerts(response.data);
            } else {
                toast('error', response.message || 'Error al cargar estadísticas');
            }
            hideLoading();
        }, function(msg) {
            toast('error', msg);
            hideLoading();
        });
    }

    //Actualizar estadisticas
    function updateStats(data) {
        // Ventas
        $('#statVentas').text(fmt(data.ventas.total));
        $('#statVentasCount').html(`<i class="fas fa-receipt me-1"></i>${data.ventas.cantidad} venta${data.ventas.cantidad !== 1 ? 's' : ''}`);
        
        if (data.ventas.tendencia !== undefined) {
            const tendencia = data.ventas.tendencia >= 0 ? 'success' : 'danger';
            const icono = data.ventas.tendencia >= 0 ? '↑' : '↓';
            const text = data.ventas.tendencia >= 0 ? '+' : '';
            $('#statVentasTrend')
                .removeClass('bg-success bg-danger bg-secondary')
                .addClass(`bg-${tendencia}`)
                .text(`${icono} ${text}${Math.abs(data.ventas.tendencia).toFixed(1)}%`)
                .show();
        } else {
            $('#statVentasTrend').hide();
        }

        // Compras
        $('#statCompras').text(fmt(data.compras.total));
        $('#statComprasCount').html(`<i class="fas fa-truck me-1"></i>${data.compras.cantidad} compra${data.compras.cantidad !== 1 ? 's' : ''}`);
        
        if (data.compras.tendencia !== undefined) {
            const tendencia = data.compras.tendencia <= 0 ? 'success' : 'danger';
            const icono = data.compras.tendencia >= 0 ? '↑' : '↓';
            const text = data.compras.tendencia >= 0 ? '+' : '';
            $('#statComprasTrend')
                .removeClass('bg-success bg-danger bg-secondary')
                .addClass(`bg-${tendencia}`)
                .text(`${icono} ${text}${Math.abs(data.compras.tendencia).toFixed(1)}%`)
                .show();
        } else {
            $('#statComprasTrend').hide();
        }

        // Cuentas por Cobrar
        $('#statCobrar').text(fmt(data.cuentas_cobrar.saldo_total));
        $('#statCobrarCount').html(`<i class="fas fa-file-invoice me-1"></i>${data.cuentas_cobrar.cantidad} cuenta${data.cuentas_cobrar.cantidad !== 1 ? 's' : ''}`);
        
        if (data.cuentas_cobrar.vencidas > 0) {
            $('#statCobrarVencidas')
                .text(`${data.cuentas_cobrar.vencidas} vencida${data.cuentas_cobrar.vencidas !== 1 ? 's' : ''}`)
                .show();
        } else {
            $('#statCobrarVencidas').hide();
        }

        // Cuentas por Pagar
        $('#statPagar').text(fmt(data.cuentas_pagar.saldo_total));
        $('#statPagarCount').html(`<i class="fas fa-file-invoice-dollar me-1"></i>${data.cuentas_pagar.cantidad} cuenta${data.cuentas_pagar.cantidad !== 1 ? 's' : ''}`);
        
        if (data.cuentas_pagar.vencidas > 0) {
            $('#statPagarVencidas')
                .text(`${data.cuentas_pagar.vencidas} vencida${data.cuentas_pagar.vencidas !== 1 ? 's' : ''}`)
                .show();
        } else {
            $('#statPagarVencidas').hide();
        }

        // Ganancia Neta y Margen
        const ganancia = data.ventas.total - data.compras.total;
        const margen = data.ventas.total > 0 ? ((ganancia / data.ventas.total) * 100) : 0;
        
        $('#statGananciaNeta')
            .text(fmt(ganancia))
            .removeClass('text-success text-danger text-warning')
            .addClass(ganancia >= 0 ? 'text-success' : 'text-danger');
        
        $('#statMargen')
            .text(`${margen.toFixed(1)}%`)
            .removeClass('text-success text-danger text-warning')
            .addClass(margen >= 30 ? 'text-success' : (margen >= 15 ? 'text-warning' : 'text-danger'));

        // Inventario (prendas vendidas y disponibles)
        $('#statPrendasVendidas').text(data.inventario.vendidas || 0);
        $('#statInventario').text(data.inventario.disponibles || 0);

        //Productos totales en el sistema
        if (data.productos) {
            const totalProductos = data.productos.total || 0;
            const disponibles = data.productos.disponibles || 0;
            const vendidas = data.productos.vendidas || 0;
            const valorInventario = data.productos.valor_inventario || 0;

            $('#statTotalProductos').text(totalProductos);
            
            // Mostrar status del inventario
            let statusText = '';
            let statusClass = '';
            
            if (disponibles < 10) {
                statusText = 'Stock Bajo';
                statusClass = 'bg-danger';
            } else if (disponibles < 50) {
                statusText = 'Stock Normal';
                statusClass = 'bg-warning';
            } else {
                statusText = 'Stock Alto';
                statusClass = 'bg-success';
            }
            
            $('#statProductosStatus')
                .text(statusText)
                .removeClass('bg-success bg-warning bg-danger')
                .addClass(statusClass);
        }
    }

    //Actualizar graficos
    function updateCharts(data) {
        updateVentasComprasChart(data);
        updateCuentasChart(data);
    }

    function updateVentasComprasChart(data) {
        const ctx = document.getElementById('ventasComprasChart');
        if (!ctx) return;

        if (ventasComprasChart) {
            ventasComprasChart.destroy();
        }

        const labels = data.chart_timeline?.labels || [];
        const ventasData = data.chart_timeline?.ventas || [];
        const comprasData = data.chart_timeline?.compras || [];

        ventasComprasChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Ventas',
                        data: ventasData,
                        backgroundColor: 'rgba(40, 167, 69, 0.8)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Compras',
                        data: comprasData,
                        backgroundColor: 'rgba(220, 53, 69, 0.8)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + fmt(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    function updateCuentasChart(data) {
        const ctx = document.getElementById('cuentasChart');
        if (!ctx) return;

        if (cuentasChart) {
            cuentasChart.destroy();
        }

        const cobrarPendiente = data.cuentas_cobrar.saldo_total || 0;
        const pagarPendiente = data.cuentas_pagar.saldo_total || 0;

        cuentasChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Por Cobrar', 'Por Pagar'],
                datasets: [{
                    data: [cobrarPendiente, pagarPendiente],
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(23, 162, 184, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 193, 7, 1)',
                        'rgba(23, 162, 184, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        callbacks: {
                            label: function(context) {
                                const total = cobrarPendiente + pagarPendiente;
                                const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(1) : 0;
                                return context.label + ': ' + fmt(context.parsed) + ` (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    function loadTransactions() {
        const params = getFilterParams();
        
        $('#transactionsTableBody').html(`
            <tr>
                <td colspan="6" class="text-center py-4">
                    <div class="spinner-border text-primary spinner-border-sm"></div>
                </td>
            </tr>
        `);

        ajax('GET', 'get_transactions', params, function(response) {
            if (response.success) {
                allTransactions = response.data || [];
                renderTransactions(allTransactions);
            } else {
                $('#transactionsTableBody').html(`
                    <tr>
                        <td colspan="6" class="text-center py-3 text-muted">
                            No hay transacciones en este período
                        </td>
                    </tr>
                `);
            }
        });
    }

    function renderTransactions(transactions, type = 'all') {
        if (!transactions || transactions.length === 0) {
            $('#transactionsTableBody').html(`
                <tr>
                    <td colspan="6" class="text-center py-3 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2"></i>
                        <p class="mb-0">No hay transacciones para mostrar</p>
                    </td>
                </tr>
            `);
            return;
        }

        // Filtrar por tipo
        let filtered = transactions;
        if (type !== 'all') {
            filtered = transactions.filter(t => t.tipo.toLowerCase() === type);
        }

        // Limitar a las últimas 10
        filtered = filtered.slice(0, 10);

        const html = filtered.map(t => {
            const tipoClass = t.tipo === 'VENTA' ? 'success' : 'danger';
            const tipoIcon = t.tipo === 'VENTA' ? 'fa-shopping-cart' : 'fa-box';
            
            let estadoBadge = '';
            const estado = (t.estado || '').toLowerCase();
            
            if (estado === 'completada' || estado === 'confirmado') {
                estadoBadge = '<span class="badge bg-success">Completada</span>';
            } else if (estado === 'pendiente') {
                estadoBadge = '<span class="badge bg-warning">Pendiente</span>';
            } else if (estado === 'cancelada' || estado === 'anulado') {
                estadoBadge = '<span class="badge bg-danger">Cancelada</span>';
            } else {
                estadoBadge = `<span class="badge bg-secondary">${t.estado || 'N/A'}</span>`;
            }

            return `
                <tr class="transaction-row">
                    <td class="px-3"><small>${fmtDateTime(t.fecha)}</small></td>
                    <td>
                        <span class="badge bg-${tipoClass}">
                            <i class="fas ${tipoIcon} me-1"></i>
                            ${t.tipo}
                        </span>
                    </td>
                    <td><code class="small">${t.referencia || 'N/A'}</code></td>
                    <td><small>${t.cliente_proveedor || '-'}</small></td>
                    <td class="text-end"><strong>${fmt(t.monto)}</strong></td>
                    <td class="text-center">${estadoBadge}</td>
                </tr>
            `;
        }).join('');

        $('#transactionsTableBody').html(html || '<tr><td colspan="6" class="text-center py-3 text-muted">Sin resultados</td></tr>');
    }

    function updateAlerts(data) {
        const alerts = [];

        // Cuentas por cobrar vencidas
        if (data.cuentas_cobrar.vencidas > 0) {
            alerts.push({
                type: 'danger',
                icon: 'fa-exclamation-triangle',
                text: `${data.cuentas_cobrar.vencidas} cuenta${data.cuentas_cobrar.vencidas !== 1 ? 's' : ''} por cobrar vencida${data.cuentas_cobrar.vencidas !== 1 ? 's' : ''}`,
                link: '/BarkiOS/admin/accounts-receivable'
            });
        }

        // Cuentas por pagar vencidas
        if (data.cuentas_pagar.vencidas > 0) {
            alerts.push({
                type: 'danger',
                icon: 'fa-file-invoice-dollar',
                text: `${data.cuentas_pagar.vencidas} cuenta${data.cuentas_pagar.vencidas !== 1 ? 's' : ''} por pagar vencida${data.cuentas_pagar.vencidas !== 1 ? 's' : ''}`,
                link: '/BarkiOS/admin/accounts-payable'
            });
        }

        // Cuentas por cobrar por vencer
        if (data.cuentas_cobrar.por_vencer > 0) {
            alerts.push({
                type: 'warning',
                icon: 'fa-clock',
                text: `${data.cuentas_cobrar.por_vencer} cuenta${data.cuentas_cobrar.por_vencer !== 1 ? 's' : ''} por cobrar próxima${data.cuentas_cobrar.por_vencer !== 1 ? 's' : ''} a vencer`,
                link: '/BarkiOS/admin/accounts-receivable'
            });
        }

        // Inventario bajo
        if (data.inventario.disponibles < 10) {
            alerts.push({
                type: 'danger',
                icon: 'fa-box-open',
                text: `Inventario crítico: solo ${data.inventario.disponibles} prenda${data.inventario.disponibles !== 1 ? 's' : ''} disponible${data.inventario.disponibles !== 1 ? 's' : ''}`,
                link: '/BarkiOS/admin/products'
            });
        } else if (data.inventario.disponibles < 30) {
            alerts.push({
                type: 'warning',
                icon: 'fa-box-open',
                text: `Inventario bajo: ${data.inventario.disponibles} prenda${data.inventario.disponibles !== 1 ? 's' : ''} disponible${data.inventario.disponibles !== 1 ? 's' : ''}`,
                link: '/BarkiOS/admin/products'
            });
        }

        // Renderizar alertas
        if (alerts.length === 0) {
            $('#alertsContainer').html(`
                <div class="text-center py-3 text-muted">
                    <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                    <p class="mb-0 small">Todo en orden</p>
                </div>
            `);
        } else {
            const html = alerts.map(alert => `
                <div class="alert alert-${alert.type} alert-dismissible fade show mb-2" role="alert">
                    <i class="fas ${alert.icon} me-2"></i>
                    <small>${alert.text}</small>
                    <a href="${alert.link}" class="alert-link ms-2">
                        <small>Ver <i class="fas fa-arrow-right"></i></small>
                    </a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `).join('');
            $('#alertsContainer').html(html);
        }
    }

    function getFilterParams() {
        const params = { filter: currentFilter };
        
        if (currentFilter === 'custom') {
            params.date_from = $('#dateFrom').val();
            params.date_to = $('#dateTo').val();
        }
        
        return params;
    }

    function updatePeriodLabel() {
        const labels = {
            'today': 'Hoy',
            'week': 'Esta Semana',
            'month': 'Este Mes',
            'year': 'Este Año',
            'custom': 'Período Personalizado'
        };
        $('#currentPeriod').text(labels[currentFilter] || 'Hoy');
    }

    // Cambio de filtro de período
    $('.filter-btn-group .btn').on('click', function() {
        $('.filter-btn-group .btn').removeClass('active');
        $(this).addClass('active');
        
        currentFilter = $(this).data('filter');
        
        if (currentFilter === 'custom') {
            $('#customDateRange').removeClass('d-none');
        } else {
            $('#customDateRange').addClass('d-none');
            loadDashboard();
        }
    });

    // Aplicar fechas personalizadas
    $('#applyCustomDates').on('click', function() {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        
        if (!dateFrom || !dateTo) {
            toast('warning', 'Seleccione ambas fechas');
            return;
        }
        
        if (new Date(dateFrom) > new Date(dateTo)) {
            toast('error', 'La fecha inicial no puede ser mayor a la final');
            return;
        }
        
        loadDashboard();
    });

    // Filtro de transacciones
    $('[data-trans-type]').on('click', function() {
        $('[data-trans-type]').removeClass('active');
        $(this).addClass('active');
        
        const type = $(this).data('trans-type');
        renderTransactions(allTransactions, type);
    });

    // Refresh manual de gráficos
    window.refreshCharts = function() {
        loadDashboard();
        toast('info', 'Datos actualizados');
    };

    function showLoading() {
        $('#loadingChart1, #loadingChart2').removeClass('d-none');
    }

    function hideLoading() {
        $('#loadingChart1, #loadingChart2').addClass('d-none');
    }


    // Establecer fecha de hoy como máximo en los inputs
    const today = new Date().toISOString().split('T')[0];
    $('#dateFrom, #dateTo').attr('max', today);
    $('#dateTo').val(today);
    
    // Primera carga
    loadDashboard();

    // Auto-refresh cada 5 minutos
    setInterval(loadDashboard, 5 * 60 * 1000);

    // Agregar esta función al final de home-admin.js

window.generateDashboardPdf = function() {
    // Obtener el filtro actual
    const activeFilter = $('.filter-btn-group .btn.active').data('filter');
    
    let url = '/BarkiOS/admin/home?action=generate_pdf_report&filter=' + activeFilter;
    
    // Si es filtro personalizado, agregar fechas
    if (activeFilter === 'custom') {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        
        if (!dateFrom || !dateTo) {
            Swal.fire({
                icon: 'warning',
                title: 'Fechas requeridas',
                text: 'Por favor seleccione el rango de fechas personalizado',
                confirmButtonColor: '#007bff'
            });
            return;
        }
        
        url += '&date_from=' + encodeURIComponent(dateFrom) + '&date_to=' + encodeURIComponent(dateTo);
    }
    
    // Mostrar mensaje de generación
    Swal.fire({
        title: 'Generando PDF',
        html: 'Por favor espere...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Abrir en nueva ventana
    setTimeout(() => {
        window.open(url, '_blank');
        Swal.close();
    }, 500);
};

window.downloadCsvReport = function() {
    const activeFilter = $('.filter-btn-group .btn.active').data('filter');
    
    let url = '/BarkiOS/admin/home?action=export_report&filter=' + activeFilter;
    
    if (activeFilter === 'custom') {
        const dateFrom = $('#dateFrom').val();
        const dateTo = $('#dateTo').val();
        
        if (!dateFrom || !dateTo) {
            Swal.fire({
                icon: 'warning',
                title: 'Fechas requeridas',
                text: 'Por favor seleccione el rango de fechas personalizado',
                confirmButtonColor: '#007bff'
            });
            return;
        }
        
        url += '&date_from=' + encodeURIComponent(dateFrom) + '&date_to=' + encodeURIComponent(dateTo);
    }
    
    window.location.href = url;
};

});