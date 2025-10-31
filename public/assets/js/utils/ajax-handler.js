/**
 * ============================================
 * GESTOR CENTRALIZADO DE PETICIONES AJAX
 * Garage Barki - v2.0 (ES6 Module)
 * ============================================
 */

/**
 * Petición AJAX genérica
 * @param {Object} config
 * @returns {Promise}
 */
export const request = (config) => {
    const defaults = {
        method: 'GET',
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    };

    const settings = { ...defaults, ...config };

    return new Promise((resolve, reject) => {
        $.ajax({
            ...settings,
            success: (response) => {
                resolve(response);
            },
            error: (xhr, status, error) => {
                let errorMsg = 'Error en la petición';
                
                try {
                    const json = xhr.responseJSON || JSON.parse(xhr.responseText);
                    if (json && json.message) {
                        errorMsg = json.message;
                    }
                } catch (e) {
                    errorMsg = xhr.statusText || error || errorMsg;
                }
                
                reject(errorMsg);
            }
        });
    });
};

/**
 * GET request simplificado
 * @param {string} url
 * @param {Object} params
 * @returns {Promise}
 */
export const get = (url, params = null) => {
    return request({
        url,
        method: 'GET',
        data: params
    });
};

/**
 * POST request simplificado
 * @param {string} url
 * @param {Object|FormData} data
 * @returns {Promise}
 */
export const post = (url, data) => {
    const isFormData = (data instanceof FormData);
    
    return request({
        url,
        method: 'POST',
        data,
        processData: !isFormData,
        contentType: isFormData ? false : 'application/x-www-form-urlencoded; charset=UTF-8'
    });
};

/**
 * DELETE request simplificado
 * @param {string} url
 * @param {Object} data
 * @returns {Promise}
 */
export const del = (url, data) => {
    return post(url, data); // En muchos casos usamos POST con action=delete
};

/**
 * Wrapper para operaciones CRUD comunes
 */
export const crud = (baseUrl) => {
    return {
        getAll: () => get(`${baseUrl}?action=get_all`),
        getById: (id) => get(`${baseUrl}?action=get_by_id&id=${id}`),
        create: (data) => post(`${baseUrl}?action=add_ajax`, data),
        update: (data) => post(`${baseUrl}?action=edit_ajax`, data),
        delete: (id) => post(`${baseUrl}?action=delete_ajax`, { id })
    };
};