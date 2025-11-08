import * as Validations from '/BarkiOS/public/assets/js/utils/validation.js';
import * as Helpers from '/BarkiOS/public/assets/js/utils/helpers.js';
import * as Ajax from '/BarkiOS/public/assets/js/utils/ajax-handler.js';


$(document).ready(function() {
    const baseUrl = '/BarkiOS/admin/clients';
    let clientsTable = null;

    const initDataTable = () => {
        SkeletonHelper.showTableSkeleton('clientsTable', 5, 6);
        clientsTable = $('#clientsTable').DataTable({
            ajax: {
                url: `${baseUrl}?action=get_clients`,
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataSrc: 'clients'
            },
            columns: [
                { data: 'cliente_ced' },
                { data: 'nombre_cliente' },
                { data: 'direccion' },
                {
                    data: 'telefono',
                    render: (data) => Helpers.formatPhone(data)
                },
                {
                    data: 'tipo',
                    render: (data) => Helpers.getBadge(data)
                },
                {
                    data: null,
                    orderable: false,
                    render: (data) => {
                        return `
                            <button class="btn btn-sm btn-outline-primary btn-edit" 
                                    data-cedula="${Helpers.escapeHtml(data.cliente_ced)}"
                                    data-nombre="${Helpers.escapeHtml(data.nombre_cliente)}"
                                    data-direccion="${Helpers.escapeHtml(data.direccion)}"
                                    data-telefono="${Helpers.escapeHtml(data.telefono)}"
                                    data-tipo="${Helpers.escapeHtml(data.tipo)}">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn btn-sm btn-outline-danger btn-delete"
                                    data-cedula="${Helpers.escapeHtml(data.cliente_ced)}"
                                    data-nombre="${Helpers.escapeHtml(data.nombre_cliente)}">
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
            },
            dom: '<"d-flex justify-content-between align-items-center mb-2"lfB>tip',
            buttons: [{
            text: '<i class="fas fa-sync-alt"></i> Actualizar',
            className: 'btn btn-outline-secondary btn-sm',
            action: () => {
            SkeletonHelper.showTableSkeleton('clientsTable', 5, 6);
            clientsTable.ajax.reload(null, false);
    }
}]
            
        });
    };

    //Agregar Cliente
    $('#addClientForm').on('submit', function(e) {
        e.preventDefault();

        const rules = {
            cedula: 'cedula',
            nombre: 'nombre',
            direccion: 'direccion',
            telefono: 'telefono',
            membresia: 'select'
        };

        if (!Validations.validateForm($(this), rules)) {
            Helpers.toast('warning', 'Corrija los campos resaltados');
            return;
        }

        const formData = $(this).serialize();

        Ajax.post(`${baseUrl}?action=add_ajax`, formData)
            .then(response => {
                if (response.success) {
                    Helpers.toast('success', 'Cliente agregado correctamente');
                    $('#addClientModal').modal('hide');
                    clientsTable.ajax.reload(null, false);
                } else {
                    Helpers.toast('error', response.message);
                }
            })
            .catch(err => {
                Helpers.toast('error', err);
            });
    });

    //Editar cliente 
    $(document).on('click', '.btn-edit', function() {
        const $btn = $(this);
        
        $('#editClientCedula').val($btn.data('cedula'));
        $('#editClientCedulaHidden').val($btn.data('cedula'));
        $('#editClientNombre').val($btn.data('nombre'));
        $('#editClientDireccion').val($btn.data('direccion'));
        $('#editClientTelefono').val($btn.data('telefono'));
        $('#editClientMembresia').val($btn.data('tipo') || '');
        
        Validations.clearValidation($('#editClientForm'));
        $('#editClientModal').modal('show');
    });

    $('#editClientForm').on('submit', function(e) {
        e.preventDefault();

        const rules = {
            nombre: 'nombre',
            direccion: 'direccion',
            telefono: 'telefono',
            membresia: 'select'
        };

        if (!Validations.validateForm($(this), rules)) {
            Helpers.toast('warning', 'Corrija los campos resaltados');
            return;
        }

        const formData = $(this).serialize();

        Ajax.post(`${baseUrl}?action=edit_ajax`, formData)
            .then(response => {
                if (response.success) {
                    Helpers.toast('success', 'Cliente actualizado correctamente');
                    $('#editClientModal').modal('hide');
                    clientsTable.ajax.reload(null, false);
                } else {
                    Helpers.toast('error', response.message);
                }
            })
            .catch(err => {
                Helpers.toast('error', err);
            });
    });

    //Eliminar cliente
    $(document).on('click', '.btn-delete', function() {
        const cedula = $(this).data('cedula');
        const nombre = $(this).data('nombre');

        Helpers.confirmDialog(
            '¿Eliminar cliente?',
            `¿Deseas eliminar a <strong>${Helpers.escapeHtml(nombre)}</strong>?`,
            () => {
                Ajax.post(`${baseUrl}?action=delete_ajax`, { cedula })
                    .then(response => {
                        if (response.success) {
                            Helpers.toast('success', 'Cliente eliminado correctamente');
                            clientsTable.ajax.reload(null, false);
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

    //validacion
    const addRules = {
        cedula: 'cedula',
        nombre: 'nombre',
        direccion: 'direccion',
        telefono: 'telefono',
        membresia: 'select'
    };

    const editRules = {
        nombre: 'nombre',
        direccion: 'direccion',
        telefono: 'telefono',
        membresia: 'select'
    };

    Validations.setupRealTimeValidation($('#addClientForm'), addRules);
    Validations.setupRealTimeValidation($('#editClientForm'), editRules);

    $('#addClientModal, #editClientModal').on('hidden.bs.modal', function() {
        const $form = $(this).find('form');
        Helpers.resetForm($form);
    });

    initDataTable();
});