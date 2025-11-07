/**
 * Skeleton Helper para BarkiOS
 * Wrapper simplificado para manejar skeleton screens en toda la aplicación
 * Compatible con jQuery, DataTables y tu arquitectura existente
 * 
 * Uso:
 * - SkeletonHelper.showTableSkeleton('miTabla', 5, 8);
 * - SkeletonHelper.showModalSkeleton('contenidoModal');
 * - SkeletonHelper.hideModalSkeleton('contenidoModal', htmlNuevo);
 */

const SkeletonHelper = {
  
    /**
     * Generar HTML de skeleton para DataTable
     * @param {number} rows - Número de filas
     * @param {number} cols - Número de columnas
     * @returns {string} HTML del skeleton
     */
    createTableSkeleton(rows = 5, cols = 6) {
        let html = '';
        for (let i = 0; i < rows; i++) {
            html += '<tr class="skeleton-table-row">';
            for (let j = 0; j < cols; j++) {
                // Variar el ancho según la columna
                const width = j === 0 ? '60px' : 
                             j === cols - 1 ? '80px' : 
                             '100%';
                
                html += `
                    <td class="skeleton-table-cell">
                        <div class="skeleton-block" style="height: 20px; width: ${width}; border-radius: 4px;"></div>
                    </td>
                `;
            }
            html += '</tr>';
        }
        return html;
    },

    /**
     * Mostrar skeleton en DataTable
     * @param {string} tableId - ID de la tabla (sin #)
     * @param {number} rows - Número de filas skeleton
     * @param {number} cols - Número de columnas
     */
    showTableSkeleton(tableId, rows = 5, cols = 6) {
        const $tbody = $(`#${tableId} tbody`);
        
        // Guardar el contenido actual por si acaso
        const currentContent = $tbody.html();
        $tbody.data('skeleton-backup', currentContent);
        
        // Insertar skeleton
        $tbody.html(this.createTableSkeleton(rows, cols));
    },

    /**
     * Ocultar skeleton de tabla
     * (Normalmente DataTables lo hace automáticamente al cargar datos)
     * @param {string} tableId - ID de la tabla
     */
    hideTableSkeleton(tableId) {
        const $tbody = $(`#${tableId} tbody`);
        
        // Eliminar skeleton rows con animación
        $tbody.find('.skeleton-table-row').fadeOut(200, function() {
            $(this).remove();
        });
        
        // Limpiar backup
        $tbody.removeData('skeleton-backup');
    },

    /**
     * Generar HTML de skeleton para modal de detalles
     * @returns {string} HTML del skeleton
     */
    createDetailSkeleton() {
        return `
            <div class="skeleton-modal-content" style="padding: 20px;">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="skeleton-block mb-3" style="height: 24px; width: 60%; border-radius: 4px;"></div>
                        <div class="skeleton-block mb-2" style="height: 18px; width: 100%; border-radius: 4px;"></div>
                        <div class="skeleton-block mb-2" style="height: 18px; width: 80%; border-radius: 4px;"></div>
                        <div class="skeleton-block mb-2" style="height: 18px; width: 70%; border-radius: 4px;"></div>
                    </div>
                    <div class="col-md-6">
                        <div class="skeleton-block mb-3" style="height: 24px; width: 60%; border-radius: 4px;"></div>
                        <div class="skeleton-block mb-2" style="height: 18px; width: 100%; border-radius: 4px;"></div>
                        <div class="skeleton-block mb-2" style="height: 18px; width: 90%; border-radius: 4px;"></div>
                    </div>
                </div>
                <div class="skeleton-block mb-3" style="height: 1px; width: 100%; background: #dee2e6;"></div>
                <div class="skeleton-block mb-3" style="height: 22px; width: 40%; border-radius: 4px;"></div>
                <div class="skeleton-block mb-2" style="height: 18px; width: 100%; border-radius: 4px;"></div>
                <div class="skeleton-block mb-2" style="height: 18px; width: 95%; border-radius: 4px;"></div>
                <div class="skeleton-block mb-2" style="height: 18px; width: 85%; border-radius: 4px;"></div>
                <div class="skeleton-block mb-2" style="height: 18px; width: 75%; border-radius: 4px;"></div>
            </div>
        `;
    },

    /**
     * Mostrar skeleton en modal
     * @param {string} contentId - ID del contenedor de contenido (sin #)
     */
    showModalSkeleton(contentId) {
        const $content = $(`#${contentId}`);
        $content.html(this.createDetailSkeleton());
    },

    /**
     * Ocultar skeleton de modal con animación y mostrar contenido nuevo
     * @param {string} contentId - ID del contenedor
     * @param {string} newContent - Nuevo HTML a mostrar
     */
    hideModalSkeleton(contentId, newContent) {
        const $content = $(`#${contentId}`);
        
        // Fade out del skeleton, luego fade in del contenido
        $content.fadeOut(150, function() {
            $(this).html(newContent)
                   .addClass('content-loaded')
                   .fadeIn(150);
        });
    },

    /**
     * Generar skeleton para cards de estadísticas
     * @param {number} count - Número de cards
     * @returns {string} HTML del skeleton
     */
    createStatsSkeleton(count = 4) {
        let html = '';
        for (let i = 0; i < count; i++) {
            html += `
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="skeleton-block mb-2" style="height: 16px; width: 60%; border-radius: 4px;"></div>
                            <div class="skeleton-block mb-2" style="height: 32px; width: 80%; border-radius: 4px;"></div>
                            <div class="skeleton-block" style="height: 14px; width: 50%; border-radius: 4px;"></div>
                        </div>
                    </div>
                </div>
            `;
        }
        return html;
    },

    /**
     * Mostrar skeleton para estadísticas/dashboard
     * @param {string} containerId - ID del contenedor (sin #)
     * @param {number} count - Número de cards
     */
    showStatsSkeleton(containerId, count = 4) {
        $(`#${containerId}`).html(this.createStatsSkeleton(count));
    },

    /**
     * Generar skeleton para lista de items (búsquedas, resultados, etc)
     * @param {number} items - Número de items
     * @returns {string} HTML del skeleton
     */
    createListSkeleton(items = 5) {
        let html = '<div class="list-group">';
        for (let i = 0; i < items; i++) {
            html += `
                <div class="list-group-item" style="border-left: none; border-right: none;">
                    <div class="d-flex align-items-center">
                        <div class="skeleton-block me-3" style="height: 40px; width: 40px; border-radius: 50%; flex-shrink: 0;"></div>
                        <div class="flex-grow-1">
                            <div class="skeleton-block mb-2" style="height: 18px; width: 70%; border-radius: 4px;"></div>
                            <div class="skeleton-block" style="height: 14px; width: 50%; border-radius: 4px;"></div>
                        </div>
                    </div>
                </div>
            `;
        }
        html += '</div>';
        return html;
    },

    /**
     * Mostrar skeleton para búsquedas (autocomplete)
     * @param {string} resultContainerId - ID del contenedor de resultados
     * @param {number} items - Número de items
     */
    showSearchSkeleton(resultContainerId, items = 3) {
        $(`#${resultContainerId}`)
            .html(this.createListSkeleton(items))
            .show();
    },

    /**
     * Generar skeleton para gráficos/charts
     * @returns {string} HTML del skeleton
     */
    createChartSkeleton() {
        return `
            <div class="d-flex flex-column align-items-center justify-content-center" style="height: 300px;">
                <div class="skeleton-block mb-3" style="height: 200px; width: 80%; border-radius: 8px;"></div>
                <div class="d-flex gap-3 justify-content-center">
                    <div class="skeleton-block" style="height: 12px; width: 80px; border-radius: 4px;"></div>
                    <div class="skeleton-block" style="height: 12px; width: 80px; border-radius: 4px;"></div>
                </div>
            </div>
        `;
    },

    /**
     * Mostrar skeleton para gráficos
     * @param {string} containerId - ID del contenedor
     */
    showChartSkeleton(containerId) {
        $(`#${containerId}`).html(this.createChartSkeleton());
    },

    /**
     * Generar skeleton para formularios
     * @param {number} fields - Número de campos
     * @returns {string} HTML del skeleton
     */
    createFormSkeleton(fields = 4) {
        let html = '<div class="skeleton-form-content" style="padding: 20px;">';
        for (let i = 0; i < fields; i++) {
            html += `
                <div class="mb-3">
                    <div class="skeleton-block mb-2" style="height: 16px; width: 30%; border-radius: 4px;"></div>
                    <div class="skeleton-block" style="height: 38px; width: 100%; border-radius: 4px;"></div>
                </div>
            `;
        }
        html += '</div>';
        return html;
    },

    /**
     * Mostrar skeleton para formularios
     * @param {string} containerId - ID del contenedor
     * @param {number} fields - Número de campos
     */
    showFormSkeleton(containerId, fields = 4) {
        $(`#${containerId}`).html(this.createFormSkeleton(fields));
    },

    /**
     * Wrapper para peticiones AJAX con skeleton automático
     * Compatible con tu estructura Ajax.get/Ajax.post
     * 
     * @param {Promise} ajaxPromise - Promesa de tu petición AJAX
     * @param {Object} config - Configuración del skeleton
     * @returns {Promise} - Promesa de la petición original
     * 
     * @example
     * await SkeletonHelper.withSkeleton(
     *   Ajax.get('/api/data', {id: 123}),
     *   { type: 'modal', target: 'modalContent' }
     * );
     */
    async withSkeleton(ajaxPromise, config) {
        const {
            type = 'table',
            target,
            rows = 5,
            cols = 6,
            count = 4,
            items = 3,
            fields = 4,
            minDisplayTime = 300
        } = config;

        const startTime = Date.now();

        // Mostrar skeleton según tipo
        switch(type) {
            case 'table':
                this.showTableSkeleton(target, rows, cols);
                break;
            case 'modal':
                this.showModalSkeleton(target);
                break;
            case 'stats':
                this.showStatsSkeleton(target, count);
                break;
            case 'search':
                this.showSearchSkeleton(target, items);
                break;
            case 'chart':
                this.showChartSkeleton(target);
                break;
            case 'form':
                this.showFormSkeleton(target, fields);
                break;
        }

        try {
            const result = await ajaxPromise;

            // Asegurar tiempo mínimo de visualización (evita parpadeos)
            const elapsed = Date.now() - startTime;
            if (elapsed < minDisplayTime) {
                await new Promise(resolve => setTimeout(resolve, minDisplayTime - elapsed));
            }

            return result;

        } catch (error) {
            // El skeleton se ocultará automáticamente al mostrar mensaje de error
            throw error;
        }
    },

    /**
     * Ocultar todos los skeletons activos
     * Útil para limpiezas globales
     */
    hideAll() {
        $('.skeleton-table-row').fadeOut(200, function() { $(this).remove(); });
        $('.skeleton-modal-content').fadeOut(200, function() { $(this).remove(); });
        $('.skeleton-form-content').fadeOut(200, function() { $(this).remove(); });
    }
};

// Hacer disponible globalmente
window.SkeletonHelper = SkeletonHelper;

// Log de inicialización (puedes comentar esto en producción)
console.log('✅ SkeletonHelper cargado correctamente');
