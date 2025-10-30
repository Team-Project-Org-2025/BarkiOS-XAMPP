/**
 * ============================================
 * MÓDULO DE EMPLEADOS - GARAGE BARKI
 * Versión refactorizada v2.0 (ES6 Module)
 * ============================================
 */

import * as Validations from '/BarkiOS/public/assets/js/utils/validation.js';
import * as Helpers from '/BarkiOS/public/assets/js/utils/helpers.js';
import * as Ajax from '/BarkiOS/public/assets/js/utils/ajax-handler.js';

$(document).ready(function() {
    const baseUrl = '/BarkiOS/admin/employees';
    let employeesTable = null;

    // ============================================
    // INICIALIZACIÓN DATATABLE
    // ============================================
    const initDataTable = () => {
        employeesTable = $('#employeesTable').DataTable({
            ajax: {
                url: `${baseUrl}?action=get_employees`,
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataSrc: 'employees'
            },
            columns: [
                { data: 'empleado_ced' },
                { data: 'nombre' },
                {
                    data: 'telefono',
                    render: (data) => Helpers.formatPhone(data)
                },
                { 
                    data: 'cargo',
                    render: (data) => data || 'Empleado'
                },
                {
                    data: 'fecha_ingreso',
                    render: (data) => Helpers.formatDate(data)
                },
                {
                    data: null,
                    orderable: false,
                    render: (data) => {
                        return `
                            <button class="btn btn-sm btn-outline-primary btn-edit" 
                                    data-cedula="${Helpers.escapeHtml(data.empleado_ced)}"
                                    data-nombre="${Helpers.escapeHtml(data.nombre)}"
                                    data-telefono="${Helpers.escapeHtml(data.telefono)}"
                                    data-cargo="${Helpers.escapeHtml(data.cargo || 'Empleado')}">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete"
                                    data-cedula="${Helpers.escapeHtml(data.empleado_ced)}"
                                    data-nombre="${Helpers.escapeHtml(data.nombre)}">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        `;
                    }
                }
            ],
            pageLength: 10,
            responsive: true,
            autoWidth: false,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
            }
        });
    };

    // ============================================
    // AGREGAR EMPLEADO
    // ============================================
    $('#addEmployeeForm').on('submit', function(e) {
        e.preventDefault();

        const rules = {
            cedula: 'cedula',
            nombre: 'nombre',
            telefono: 'telefono',
            cargo: 'cargo'
        };

        if (!Validations.validateForm($(this), rules)) {
            Helpers.toast('warning', 'Corrija los campos resaltados');
            return;
        }

        const formData = $(this).serialize();

        Ajax.post(`${baseUrl}?action=add_ajax`, formData)
            .then(response => {
                if (response.success) {
                    Helpers.toast('success', 'Empleado agregado correctamente');
                    $('#addEmployeeModal').modal('hide');
                    employeesTable.ajax.reload(null, false);
                } else {
                    Helpers.toast('error', response.message);
                }
            })
            .catch(err => {
                Helpers.toast('error', err);
            });
    });

    // ============================================
    // EDITAR EMPLEADO
    // ============================================
    $(document).on('click', '.btn-edit', function() {
        const $btn = $(this);
        
        $('#editEmployeeCedula').val($btn.data('cedula'));
        $('#editEmployeeCedulaHidden').val($btn.data('cedula'));
        $('#editEmployeeNombre').val($btn.data('nombre'));
        $('#editEmployeeTelefono').val($btn.data('telefono'));
        $('#editEmployeeCargo').val($btn.data('cargo'));
        
        Validations.clearValidation($('#editEmployeeForm'));
        $('#editEmployeeModal').modal('show');
    });

    $('#editEmployeeForm').on('submit', function(e) {
        e.preventDefault();

        const rules = {
            nombre: 'nombre',
            telefono: 'telefono',
            cargo: 'cargo'
        };

        if (!Validations.validateForm($(this), rules)) {
            Helpers.toast('warning', 'Corrija los campos resaltados');
            return;
        }

        const formData = $(this).serialize();

        Ajax.post(`${baseUrl}?action=edit_ajax`, formData)
            .then(response => {
                if (response.success) {
                    Helpers.toast('success', 'Empleado actualizado correctamente');
                    $('#editEmployeeModal').modal('hide');
                    employeesTable.ajax.reload(null, false);
                } else {
                    Helpers.toast('error', response.message);
                }
            })
            .catch(err => {
                Helpers.toast('error', err);
            });
    });

    // ============================================
    // ELIMINAR EMPLEADO
    // ============================================
    $(document).on('click', '.btn-delete', function() {
        const cedula = $(this).data('cedula');
        const nombre = $(this).data('nombre');

        Helpers.confirmDialog(
            '¿Eliminar empleado?',
            `¿Deseas eliminar a <strong>${Helpers.escapeHtml(nombre)}</strong>?`,
            () => {
                Ajax.post(`${baseUrl}?action=delete_ajax`, { cedula })
                    .then(response => {
                        if (response.success) {
                            Helpers.toast('success', 'Empleado eliminado correctamente');
                            employeesTable.ajax.reload(null, false);
                        } else {
                            Helpers.toast('error', response.message);
                        }
                    })
                    .catch(err => {
                        Helpers.toast('error', err);
                    });
            },
            'Sí, eliminar'
        );
    });

    // ============================================
    // VALIDACIÓN EN TIEMPO REAL
    // ============================================
    const addRules = {
        cedula: 'cedula',
        nombre: 'nombre',
        telefono: 'telefono',
        cargo: 'cargo'
    };

    const editRules = {
        nombre: 'nombre',
        telefono: 'telefono',
        cargo: 'cargo'
    };

    Validations.setupRealTimeValidation($('#addEmployeeForm'), addRules);
    Validations.setupRealTimeValidation($('#editEmployeeForm'), editRules);

    // ============================================
    // LIMPIAR MODALES AL CERRAR
    // ============================================
    $('#addEmployeeModal, #editEmployeeModal').on('hidden.bs.modal', function() {
        const $form = $(this).find('form');
        Helpers.resetForm($form);
    });

    // ============================================
    // INICIALIZAR
    // ============================================
    initDataTable();
});