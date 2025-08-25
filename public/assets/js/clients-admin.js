document.addEventListener('DOMContentLoaded', () => {
        const clientsTableBody = document.getElementById('clientesTableBody');
        const addClientForm = document.getElementById('addClientForm');
        const editClientForm = document.getElementById('editClientForm');

        // Utilidades
        const escapeHtml = str => String(str ?? '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        // showAlert solo usa SweetAlert2 (pop-up)
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

        // CRUD AJAX
        function fetchClients() {
            clientsTableBody.innerHTML = `<tr><td colspan="6" class="text-center">
                <div class="spinner-border text-primary"></div> Cargando...</td></tr>`;
            fetch(window.location.pathname + '?action=get_clients', {headers: {'X-Requested-With':'XMLHttpRequest'}})
            .then(r => r.json()).then(data => {
                if (!data.clients?.length) return clientsTableBody.innerHTML =
                    `<td colspan="6" class="text-center">
                        <div class="alert alert-info mb-0">No hay clientes disponibles</div>
                    </td>`;
            clientsTableBody.innerHTML = data.clients.map(c => `
                <tr id="cliente-${escapeHtml(c.cliente_ced)}">
                    <td class="text-center">${escapeHtml(c.cliente_ced)}</td>
                    <td>${escapeHtml(c.nombre_cliente)}</td>
                    <td>${escapeHtml(c.direccion)}</td>
                    <td class="text-end">${formatearTelefono(c.telefono)}</td>
                    <td class="text-center">${escapeHtml(c.tipo)}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary btn-editar"
                            data-cedula="${escapeHtml(c.cliente_ced)}"
                            data-nombre="${escapeHtml(c.nombre_cliente)}"
                            data-direccion="${escapeHtml(c.direccion)}"
                            data-telefono="${escapeHtml(c.telefono)}"
                            data-membresia="${escapeHtml(c.tipo)}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-eliminar"
                            data-cedula="${escapeHtml(c.cliente_ced)}"
                            data-nombre="${escapeHtml(c.nombre_cliente)}">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>`).join('');
                document.querySelectorAll('.btn-eliminar').forEach(btn => btn.onclick = handleDelete);
                document.querySelectorAll('.btn-editar').forEach(btn => btn.onclick = () => loadClientForEdit(btn));
            }).catch(() => showAlert('Error al cargar clientes', 'danger'));
        }

        function handleAdd(e) {
            e.preventDefault();
            const fd = new URLSearchParams(new FormData(addClientForm));
            fetch('clients-admin.php?action=add_ajax', {
                method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
                body: fd
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    showAlert('Cliente agregado', 'success');
                    addClientForm.reset();
                    bootstrap.Modal.getInstance(document.getElementById('addClientModal')).hide();
                    fetchClients();
                } else showAlert(data.message, 'danger');
            }).catch(() => showAlert('Error al agregar', 'danger'));
        }

        function loadClientForEdit(btn) {
            const cedula = btn.getAttribute('data-cedula') || '';
            document.getElementById('editClientCedula').value = btn.getAttribute('data-cedula') || '';
            document.getElementById('editClientCedulaHidden').value = cedula;
            document.getElementById('editClientNombre').value = btn.getAttribute('data-nombre') || '';
            document.getElementById('editClientDireccion').value = btn.getAttribute('data-direccion') || '';
            document.getElementById('editClientTelefono').value = btn.getAttribute('data-telefono') || '';
            document.getElementById('editClientMembresia').value = btn.getAttribute('data-membresia') || '';
            const modal = new bootstrap.Modal(document.getElementById('editClientModal'));
            modal.show();
        }

        function handleEdit(e) {
            e.preventDefault();
            const fd = new URLSearchParams(new FormData(editClientForm));
            fetch('clients-admin.php?action=edit_ajax', {
                method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
                body: fd
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    showAlert('Cliente actualizado', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('editClientModal')).hide();
                    fetchClients();
                } else showAlert(data.message, 'danger');
            }).catch(() => showAlert('Error al actualizar', 'danger'));
        }

        function handleDelete(e) {
            const cedula = e.currentTarget.dataset.cedula;
            const nombre = e.currentTarget.dataset.nombre;
            Swal.fire({
                title: '¿Eliminar cliente?',
                html: `¿Deseas eliminar <strong>${escapeHtml(nombre)}</strong>?`,
                icon: 'warning', showCancelButton: true,
                confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
            }).then(res => {
                if (res.isConfirmed) {
                    fetch('clients-admin.php?action=delete_ajax', {
                        method: 'POST',
                        headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
                        body: `cedula=${encodeURIComponent(cedula)}`
                    }).then(r => r.json()).then(data => {
                        if (data.success) {
                            showAlert('Cliente eliminado', 'success');
                            fetchClients();
                        } else showAlert(data.message, 'danger');
                    }).catch(() => showAlert('Error al eliminar', 'danger'));
                }
            });
        }

        // Utilidad para formatear teléfono
        function formatearTelefono(telefono) {
            if (!telefono) return '';
            const telefonoStr = String(telefono);
            if (telefonoStr.length === 11) {
                return telefonoStr.replace(/(\d{4})(\d{7})/, '$1-$2');
            }
            return telefonoStr;
        }

        // Inicialización
        if (addClientForm) addClientForm.onsubmit = handleAdd;
        if (editClientForm) editClientForm.onsubmit = handleEdit;
        fetchClients();
    });