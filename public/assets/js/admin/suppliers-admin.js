import * as Validations from '/BarkiOS/public/assets/js/utils/validation.js';
import * as Helpers from '/BarkiOS/public/assets/js/utils/helpers.js';
import * as Ajax from '/BarkiOS/public/assets/js/utils/ajax-handler.js';

$(document).ready(function() {
    const baseUrl = '/BarkiOS/admin/supplier';
    let suppliersTable = null;


    const initDataTable = () => {
        SkeletonHelper.showTableSkeleton('suppliersTable', 5, 6);
        suppliersTable = $('#suppliersTable').DataTable({
            ajax: {
                url: `${baseUrl}?action=get_suppliers`,
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                dataSrc: (json) => {
                    if (!json.suppliers || json.suppliers.length === 0) {
                        Helpers.toast('info', 'No hay proveedores disponibles');
                        return [];
                    }
                    return json.suppliers;
                }
            },
            columns: [
                { 
                    data: null,
                    render: (data, type, row) => `${Helpers.escapeHtml(row.tipo_rif)}-${Helpers.escapeHtml(row.proveedor_rif)}`
                },
                { data: 'tipo_rif', render: d => Helpers.escapeHtml(d) },
                { data: 'nombre_contacto', render: d => Helpers.escapeHtml(d) },
                { data: 'nombre_empresa', render: d => Helpers.escapeHtml(d) },
                { data: 'direccion', render: d => Helpers.escapeHtml(d) },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: (data, type, row) => `
                        <button class="btn btn-sm btn-outline-primary btn-editar"
                            data-proveedor_rif="${Helpers.escapeHtml(row.proveedor_rif)}"
                            data-tipo_rif="${Helpers.escapeHtml(row.tipo_rif)}"
                            data-nombre_contacto="${Helpers.escapeHtml(row.nombre_contacto)}"
                            data-nombre_empresa="${Helpers.escapeHtml(row.nombre_empresa)}"
                            data-direccion="${Helpers.escapeHtml(row.direccion)}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-proveedor_rif="${Helpers.escapeHtml(row.proveedor_rif)}"
                            data-nombre="${Helpers.escapeHtml(row.nombre_contacto)}">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    `
                }
            ],
            pageLength: 5,
            responsive: true,
            language: { url: "https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json" },
            dom: '<"d-flex justify-content-between align-items-center mb-2"lfB>tip',
            buttons: [{
                text: '<i class="fas fa-sync-alt"></i> Actualizar',
                className: 'btn btn-outline-secondary btn-sm',
                action: () => {
                    SkeletonHelper.showTableSkeleton('suppliersTable', 5, 6);
                    suppliersTable.ajax.reload(null, false);
                }
            }]
        });
    };

    //Validacion
    const validarProveedor = ($form, validarRif = true) => {
        const rules = {
            tipo_rif: 'select',
            nombre_contacto: 'nombre',
            nombre_empresa: 'nombre',
            direccion: 'direccion'
        };

        if (validarRif) {
            rules.proveedor_rif = 'codigo';
        }

        return Validations.validateForm($form, rules);
    };

    //Agregar proveedor
    $('#addSupplierForm').on('submit', function(e) {
        e.preventDefault();

        if (!validarProveedor($(this))) {
            Helpers.toast('warning', 'Corrija los campos resaltados');
            return;
        }

        const $btn = $(this).find('button[type="submit"]');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Guardando...');

        Ajax.post(`${baseUrl}?action=add_ajax`, $(this).serialize())
            .then(data => {
                if (data.success) {
                    Helpers.toast('success', 'Proveedor agregado correctamente');
                    $('#addSupplierModal').modal('hide');
                    suppliersTable.ajax.reload(null, false);
                } else {
                    Helpers.toast('error', data.message);
                }
            })
            .catch(err => Helpers.toast('error', err))
            .finally(() => $btn.prop('disabled', false).html(btnText));
    });

    //Editar proveedor
    $(document).on('click', '.btn-editar', function() {
        const $btn = $(this);
        
        $('#editSupplierRif').val($btn.data('proveedor_rif'));
        $('#editSupplierRifHidden').val($btn.data('proveedor_rif'));
        $('#editSupplierTipoRif').val($btn.data('tipo_rif'));
        $('#editSupplierNombreContacto').val($btn.data('nombre_contacto'));
        $('#editSupplierNombreEmpresa').val($btn.data('nombre_empresa'));
        $('#editSupplierDireccion').val($btn.data('direccion'));
        
        Validations.clearValidation($('#editSupplierForm'));
        $('#editSupplierModal').modal('show');
    });

    $('#editSupplierForm').on('submit', function(e) {
        e.preventDefault();

        if (!validarProveedor($(this), false)) {
            Helpers.toast('warning', 'Corrija los campos resaltados');
            return;
        }

        const $btn = $(this).find('button[type="submit"]');
        const btnText = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Actualizando...');

        Ajax.post(`${baseUrl}?action=edit_ajax`, $(this).serialize())
            .then(data => {
                if (data.success) {
                    Helpers.toast('success', 'Proveedor actualizado correctamente');
                    $('#editSupplierModal').modal('hide');
                    suppliersTable.ajax.reload(null, false);
                } else {
                    Helpers.toast('error', data.message);
                }
            })
            .catch(err => Helpers.toast('error', err))
            .finally(() => $btn.prop('disabled', false).html(btnText));
    });

    //Eliminar proveedor
    $(document).on('click', '.btn-eliminar', function() {
        const proveedor_rif = $(this).data('proveedor_rif');
        const nombre = $(this).data('nombre');

        Helpers.confirmDialog(
            '¿Eliminar proveedor?',
            `¿Deseas eliminar <strong>${Helpers.escapeHtml(nombre)}</strong>?`,
            () => {
                Ajax.post(`${baseUrl}?action=delete_ajax`, { proveedor_rif })
                    .then(data => {
                        if (data.success) {
                            Helpers.toast('success', 'Proveedor eliminado correctamente');
                            suppliersTable.ajax.reload(null, false);
                        } else {
                            Helpers.toast('error', data.message);
                        }
                    })
                    .catch(err => Helpers.toast('error', err));
            },
            'Sí, eliminar'
        );
    });

    //Validacion
    const addRules = {
        tipo_rif: 'select',
        proveedor_rif: 'codigo',
        nombre_contacto: 'nombre',
        nombre_empresa: 'nombre',
        direccion: 'direccion'
    };

    const editRules = {
        tipo_rif: 'select',
        nombre_contacto: 'nombre',
        nombre_empresa: 'nombre',
        direccion: 'direccion'
    };

    Validations.setupRealTimeValidation($('#addSupplierForm'), addRules);
    Validations.setupRealTimeValidation($('#editSupplierForm'), editRules);

    $('#addSupplierModal, #editSupplierModal').on('hidden.bs.modal', function() {
        Helpers.resetForm($(this).find('form'));
    });

    initDataTable();
});