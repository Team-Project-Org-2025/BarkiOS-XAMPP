/**
 * ============================================
 * SISTEMA DE UTILIDADES Y HELPERS
 * Garage Barki - v2.0 (ES6 Module)
 * ============================================
 */

/**
 * Escapa HTML para prevenir XSS
 * @param {string} str
 * @returns {string}
 */
export const escapeHtml = (str) => {
    const div = document.createElement('div');
    div.textContent = String(str ?? '');
    return div.innerHTML;
};

/**
 * Muestra alertas Toast con SweetAlert2
 * @param {string} type - 'success', 'error', 'warning', 'info'
 * @param {string} msg - Mensaje a mostrar
 */
export const toast = (type, msg) => {
    if (typeof Swal === 'undefined') {
        console.warn('SweetAlert2 no está cargado');
        console[type === 'error' ? 'error' : 'log'](msg);
        return;
    }

    const icons = {
        'success': 'success',
        'error': 'error',
        'danger': 'error',
        'warning': 'warning',
        'info': 'info'
    };

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icons[type] || 'info',
        title: msg,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
};

/**
 * Formatea un número como moneda USD
 * @param {number|string} n
 * @returns {string}
 */
export const formatCurrency = (n) => {
    const num = Number(n) || 0;
    return new Intl.NumberFormat('es-VE', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2
    }).format(num);
};

/**
 * Formatea un número como moneda Bs
 * @param {number|string} n
 * @returns {string}
 */
export const formatCurrencyBs = (n) => {
    const num = Number(n) || 0;
    return new Intl.NumberFormat('es-VE', {
        style: 'currency',
        currency: 'VES',
        minimumFractionDigits: 2
    }).format(num);
};

/**
 * Formatea una fecha
 * @param {string} dateStr
 * @param {boolean} includeTime
 * @returns {string}
 */
export const formatDate = (dateStr, includeTime = false) => {
    if (!dateStr) return 'N/A';
    const date = new Date(dateStr);
    if (isNaN(date)) return String(dateStr);

    const options = {
        year: 'numeric',
        month: includeTime ? 'short' : '2-digit',
        day: 'numeric'
    };

    if (includeTime) {
        options.hour = '2-digit';
        options.minute = '2-digit';
    }

    return date.toLocaleString('es-ES', options);
};

/**
 * Formatea un teléfono venezolano
 * @param {string} tel
 * @returns {string}
 */
export const formatPhone = (tel) => {
    if (!tel) return '';
    const str = String(tel);
    return str.length === 11 
        ? str.replace(/(\d{4})(\d{7})/, '$1-$2') 
        : str;
};

/**
 * Muestra modal de confirmación
 * @param {string} title
 * @param {string} text
 * @param {Function} onConfirm
 * @param {string} confirmText
 */
export const confirmDialog = (title, text, onConfirm, confirmText = 'Sí, continuar') => {
    if (typeof Swal === 'undefined') {
        if (confirm(`${title}\n${text}`)) onConfirm();
        return;
    }

    Swal.fire({
        title,
        html: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) onConfirm();
    });
};

/**
 * Muestra spinner de carga
 * @param {string} title
 */
export const showLoading = (title = 'Cargando...') => {
    if (typeof Swal === 'undefined') return;
    Swal.fire({
        title,
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading()
    });
};

/**
 * Cierra el loading
 */
export const closeLoading = () => {
    if (typeof Swal !== 'undefined') Swal.close();
};

/**
 * Genera HTML de spinner para DataTables
 * @param {number} colspan
 * @returns {string}
 */
export const spinnerHtml = (colspan = 5) => {
    return `
        <tr>
            <td colspan="${colspan}" class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Cargando...</p>
            </td>
        </tr>
    `;
};

/**
 * Genera HTML de mensaje vacío
 * @param {number} colspan
 * @param {string} msg
 * @returns {string}
 */
export const emptyHtml = (colspan = 5, msg = 'No hay datos disponibles') => {
    return `
        <tr>
            <td colspan="${colspan}" class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p>${msg}</p>
            </td>
        </tr>
    `;
};

/**
 * Debounce para búsquedas
 * @param {Function} func
 * @param {number} wait
 * @returns {Function}
 */
export const debounce = (func, wait = 300) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

/**
 * Limpia formulario y validaciones
 * @param {jQuery} $form
 */
export const resetForm = ($form) => {
    $form[0].reset();
    $form.find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
    $form.find('.invalid-feedback').remove();
};

/**
 * Genera badge de estado
 * @param {string} estado
 * @returns {string}
 */
export const getBadge = (estado) => {
    const badges = {
        'DISPONIBLE': 'badge bg-success',
        'VENDIDO': 'badge bg-secondary',
        'completada': 'badge bg-success',
        'pendiente': 'badge bg-warning',
        'cancelada': 'badge bg-danger',
        'Vigente': 'badge bg-success',
        'Por vencer': 'badge bg-warning',
        'Vencido': 'badge bg-danger',
        'Pagado': 'badge bg-secondary',
        'vip': 'badge bg-warning',
        'regular': 'badge bg-info'
    };

    const cls = badges[estado] || 'badge bg-secondary';
    return `<span class="${cls}">${escapeHtml(estado)}</span>`;
};

/**
 * Obtiene los parámetros de la URL
 * @returns {Object}
 */
export const getUrlParams = () => {
    const params = new URLSearchParams(window.location.search);
    const obj = {};
    for (const [key, value] of params) {
        obj[key] = value;
    }
    return obj;
};