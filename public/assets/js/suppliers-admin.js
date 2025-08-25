document.addEventListener('DOMContentLoaded', () => {
    const suppliersTableBody = document.getElementById('suppliersTableBody');
    const addSupplierForm = document.getElementById('addSupplierForm');
    const editSupplierForm = document.getElementById('editSupplierForm');

    // Utilidad para escapar HTML
    const escapeHtml = str => String(str ?? '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;')
        .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    // showAlert ahora usa SweetAlert2 (pop-up)
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

    function fetchSuppliers() {
        suppliersTableBody.innerHTML = `<tr><td colspan="6" class="text-center">
            <div class="spinner-border text-primary"></div> Cargando...</td></tr>`;
        fetch(window.location.pathname + '?action=get_suppliers', {headers: {'X-Requested-With':'XMLHttpRequest'}})
        .then(r => r.json()).then(data => {
            if (!data.suppliers?.length) return suppliersTableBody.innerHTML =
                `<td colspan="6" class="text-center">
                    <div class="alert alert-info mb-0">No hay proveedores disponibles</div>
                </td>`;
            suppliersTableBody.innerHTML = data.suppliers.map(s => `
                <tr id="proveedor-${escapeHtml(s.proveedor_rif)}">
                    <td>${escapeHtml(s.tipo_rif)}-${escapeHtml(s.proveedor_rif)}</td>
                    <td>${escapeHtml(s.tipo_rif)}</td>
                    <td>${escapeHtml(s.nombre_contacto)}</td>
                    <td>${escapeHtml(s.nombre_empresa)}</td>
                    <td>${escapeHtml(s.direccion)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary btn-editar"
                            data-proveedor_rif="${escapeHtml(s.proveedor_rif)}"
                            data-tipo_rif="${escapeHtml(s.tipo_rif)}"
                            data-nombre_contacto="${escapeHtml(s.nombre_contacto)}"
                            data-nombre_empresa="${escapeHtml(s.nombre_empresa)}"
                            data-direccion="${escapeHtml(s.direccion)}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-proveedor_rif="${escapeHtml(s.proveedor_rif)}"
                            data-nombre="${escapeHtml(s.nombre_contacto)}">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>`).join('');
            document.querySelectorAll('.btn-eliminar').forEach(btn => btn.onclick = handleDelete);
            document.querySelectorAll('.btn-editar').forEach(btn => btn.onclick = loadSupplierForEdit);
        }).catch(() => showAlert('Error al cargar proveedores', 'danger'));
    }

    function handleAdd(e) {
        e.preventDefault();
        const fd = new URLSearchParams(new FormData(addSupplierForm));
        fetch('supplier-admin.php?action=add_ajax', {
            method: 'POST',
            headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
            body: fd
        }).then(r => r.json()).then(data => {
            if (data.success) {
                showAlert('Proveedor agregado', 'success');
                addSupplierForm.reset();
                bootstrap.Modal.getInstance(document.getElementById('addSupplierModal')).hide();
                fetchSuppliers();
            } else showAlert(data.message, 'danger');
        }).catch(() => showAlert('Error al agregar', 'danger'));
    }

    function loadSupplierForEdit(e) {
        // Permitir que funcione tanto si recibe un evento como un botón
        const btn = e.currentTarget || e;
        document.getElementById('editSupplierRif').value = btn.getAttribute('data-proveedor_rif');
        document.getElementById('editSupplierRifHidden').value = btn.getAttribute('data-proveedor_rif');
        document.getElementById('editSupplierTipoRif').value = btn.getAttribute('data-tipo_rif');
        document.getElementById('editSupplierNombreContacto').value = btn.getAttribute('data-nombre_contacto');
        document.getElementById('editSupplierNombreEmpresa').value = btn.getAttribute('data-nombre_empresa');
        document.getElementById('editSupplierDireccion').value = btn.getAttribute('data-direccion');
        const modal = new bootstrap.Modal(document.getElementById('editSupplierModal'));
        modal.show();
    }

    function handleEdit(e) {
        e.preventDefault();
        const fd = new URLSearchParams(new FormData(editSupplierForm));
        fetch('supplier-admin.php?action=edit_ajax', {
            method: 'POST',
            headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
            body: fd
        }).then(r => r.json()).then(data => {
            if (data.success) {
                showAlert('Proveedor actualizado', 'success');
                bootstrap.Modal.getInstance(document.getElementById('editSupplierModal')).hide();
                fetchSuppliers();
            } else showAlert(data.message, 'danger');
        }).catch(() => showAlert('Error al actualizar', 'danger'));
    }

    function handleDelete(e) {
        const proveedor_rif = e.currentTarget.dataset.proveedor_rif;
        const nombre = e.currentTarget.dataset.nombre;
        Swal.fire({
            title: '¿Eliminar proveedor?',
            html: `¿Deseas eliminar <strong>${escapeHtml(nombre)}</strong>?`,
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(res => {
            if (res.isConfirmed) {
                fetch('supplier-admin.php?action=delete_ajax', {
                    method: 'POST',
                    headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
                    body: `proveedor_rif=${encodeURIComponent(proveedor_rif)}`
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        showAlert('Proveedor eliminado', 'success');
                        fetchSuppliers();
                    } else showAlert(data.message, 'danger');
                }).catch(() => showAlert('Error al eliminar', 'danger'));
            }
        });
    }

    // Inicialización
    if (addSupplierForm) addSupplierForm.onsubmit = handleAdd;
    if (editSupplierForm) editSupplierForm.onsubmit = handleEdit;
    fetchSuppliers();
});