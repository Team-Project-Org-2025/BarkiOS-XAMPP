/**
 * ============================================
 * MÓDULO DE USUARIOS - GARAGE BARKI
 * Versión refactorizada v2.0 (ES6 Module)
 * ============================================
 */

import * as Validations from '/BarkiOS/public/assets/js/utils/validation.js';
import * as Helpers from '/BarkiOS/public/assets/js/utils/helpers.js';
import * as Ajax from '/BarkiOS/public/assets/js/utils/ajax-handler.js';

$(document).ready(function() {
    const baseUrl = '/BarkiOS/admin/users';
    let usersTable = null;
    

    // ============================================
    // INICIALIZACIÓN DATATABLE
    // ============================================
    const initDataTable = () => {
        usersTable = $('#usersTable').DataTable({
            ajax: {
                url: `${baseUrl}?action=get_users`,
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataSrc: 'users'
            },
            columns: [
                { data: 'id' },
                { data: 'nombre' },
                { data: 'email' },
                {
                    data: null,
                    orderable: false,
                    render: (data) => {
                        return `
                            <button class="btn btn-sm btn-outline-primary btn-edit" 
                                    data-id="${Helpers.escapeHtml(data.id)}"
                                    data-nombre="${Helpers.escapeHtml(data.nombre)}"
                                    data-email="${Helpers.escapeHtml(data.email)}">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete"
                                    data-id="${Helpers.escapeHtml(data.id)}"
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
    // AGREGAR USUARIO
    // ============================================
    $('#addUserForm').on('submit', function(e) {
        e.preventDefault();

        const rules = {
            nombre: 'nombre',
            email: 'email',
            password: 'password'
        };

        if (!Validations.validateForm($(this), rules)) {
            Helpers.toast('warning', 'Corrija los campos resaltados');
            return;
        }

        const formData = $(this).serialize();

        Ajax.post(`${baseUrl}?action=add_ajax`, formData)
            .then(response => {
                if (response.success) {
                    Helpers.toast('success', 'Usuario agregado correctamente');
                    $('#addUserModal').modal('hide');
                    usersTable.ajax.reload(null, false);
                } else {
                    Helpers.toast('error', response.message);
                }
            })
            .catch(err => {
                Helpers.toast('error', err);
            });
    });

    // ============================================
    // EDITAR USUARIO
    // ============================================
    $(document).on('click', '.btn-edit', function() {
        const $btn = $(this);
        
        $('#editUserId').val($btn.data('id'));
        $('#editUserIdHidden').val($btn.data('id'));
        $('#editUserName').val($btn.data('nombre'));
        $('#editUserEmail').val($btn.data('email'));
        $('#editUserPassword').val('');
        
        Validations.clearValidation($('#editUserForm'));
        $('#editUserModal').modal('show');
    });

    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();

        const rules = {
            nombre: 'nombre',
            email: 'email',
            password: 'password'
        };

        if (!Validations.validateForm($(this), rules, true)) {
            Helpers.toast('warning', 'Corrija los campos resaltados');
            return;
        }

        // Filtrar password vacío
        let data = $(this).serializeArray();
        data = data.filter(item => item.name !== 'password' || item.value !== '');

        Ajax.post(`${baseUrl}?action=edit_ajax`, $.param(data))
            .then(response => {
                if (response.success) {
                    Helpers.toast('success', 'Usuario actualizado correctamente');
                    $('#editUserModal').modal('hide');
                    usersTable.ajax.reload(null, false);
                } else {
                    Helpers.toast('error', response.message);
                }
            })
            .catch(err => {
                Helpers.toast('error', err);
            });
    });

    // ============================================
    // ELIMINAR USUARIO
    // ============================================
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');

        Helpers.confirmDialog(
            '¿Eliminar usuario?',
            `¿Deseas eliminar a <strong>${Helpers.escapeHtml(nombre)}</strong>?`,
            () => {
                Ajax.post(`${baseUrl}?action=delete_ajax`, { id })
                    .then(response => {
                        if (response.success) {
                            Helpers.toast('success', 'Usuario eliminado correctamente');
                            usersTable.ajax.reload(null, false);
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
        nombre: 'nombre',
        email: 'email',
        password: 'password'
    };

    const editRules = {
        nombre: 'nombre',
        email: 'email',
        password: 'password'
    };

    Validations.setupRealTimeValidation($('#addUserForm'), addRules, false);
    Validations.setupRealTimeValidation($('#editUserForm'), editRules, true);

    // ============================================
    // LIMPIAR MODALES AL CERRAR
    // ============================================
    $('#addUserModal, #editUserModal').on('hidden.bs.modal', function() {
        const $form = $(this).find('form');
        Helpers.resetForm($form);
    });

    // ============================================
    // INICIALIZAR
    // ============================================
    initDataTable();
});