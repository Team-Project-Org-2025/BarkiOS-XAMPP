document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('accountTableBody');
    const addForm = document.getElementById('addAccountForm');
    const editForm = document.getElementById('editAccountForm');

    // Utilidades
    const escapeHtml = str => String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    
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

    // Búsqueda de clientes VIP
    const searchClientInput = document.getElementById('searchClient');
    const clientResults = document.getElementById('clientResults');
    const clienteIdInput = document.getElementById('clienteId');

    if (searchClientInput) {
        searchClientInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                clientResults.style.display = 'none';
                return;
            }
            
            clientResults.innerHTML = '<div class="list-group-item">Buscando...</div>';
            clientResults.style.display = 'block';
            
            searchTimeout = setTimeout(() => {
                fetch(`/BarkiOS/app/controllers/Admin/ClientsController.php?action=search_vip&q=${encodeURIComponent(query)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.clients.length > 0) {
                        clientResults.innerHTML = data.clients.map(client => `
                            <button type="button" class="list-group-item list-group-item-action" 
                                    data-id="${client.id}" 
                                    data-nombre="${escapeHtml(client.nombre)} ${escapeHtml(client.apellido || '')}">
                                ${escapeHtml(client.nombre)} ${escapeHtml(client.apellido || '')}
                                <small class="text-muted">${escapeHtml(client.cedula || '')}</small>
                            </button>
                        `).join('');

                        document.querySelectorAll('#clientResults button').forEach(btn => {
                            btn.addEventListener('click', (e) => {
                                const client = e.currentTarget;
                                searchClientInput.value = client.dataset.nombre;
                                clienteIdInput.value = client.dataset.id;
                                clientResults.style.display = 'none';
                            });
                        });
                    } else {
                        clientResults.innerHTML = '<div class="list-group-item">No se encontraron clientes VIP</div>';
                    }
                })
                .catch(() => {
                    clientResults.innerHTML = '<div class="list-group-item text-danger">Error al buscar</div>';
                });
            }, 300);
        });

        // Ocultar resultados al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!searchClientInput.contains(e.target) && !clientResults.contains(e.target)) {
                clientResults.style.display = 'none';
            }
        });
    } // Resto del código Original

    // Cargar cuentas
    function fetchAccounts() {
        tableBody.innerHTML = `<tr><td colspan="7" class="text-center">
            <div class="spinner-border text-primary"></div> Cargando...</td></tr>`;
        
        fetch(window.location.pathname + '?action=get_accounts', {
            headers: {'X-Requested-With':'XMLHttpRequest'}
        })
        .then(r => r.json())
        .then(data => {
            if (!data.accounts || !data.accounts.length) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="alert alert-info mb-0">No hay cuentas por cobrar registradas</div>
                        </td>
                    </tr>`;
                return;
            }

            tableBody.innerHTML = data.accounts.map(account => `
                <tr id="account-${escapeHtml(account.id)}">
                    <td>${escapeHtml(account.factura_numero)}</td>
                    <td>${escapeHtml(account.nombre_cliente || 'N/A')}</td>
                    <td>${formatDate(account.fecha_emision)}</td>
                    <td class="text-end">${formatCurrency(account.monto_total)}</td>
                    <td>${formatDate(account.fecha_vencimiento)}</td>
                    <td><span class="badge bg-${getStatusBadgeClass(account.estado)}">${escapeHtml(account.estado)}</span></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary btn-edit"
                            data-id="${escapeHtml(account.id)}">
                            <i class="fas fa-edit"></i> Editar
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-delete"
                            data-id="${escapeHtml(account.id)}">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </td>
                </tr>
            `).join('');

            // Agregar manejadores de eventos
            document.querySelectorAll('.btn-edit').forEach(btn => {
                btn.addEventListener('click', () => loadAccountForEdit(btn.dataset.id));
            });
            
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', () => handleDelete(btn.dataset.id));
            });
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-danger">
                        Error al cargar las cuentas por cobrar
                    </td>
                </tr>`;
        });
    }

    // Funciones de utilidad
    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const options = { year: 'numeric', month: 'short', day: 'numeric' };
        return new Date(dateString).toLocaleDateString('es-ES', options);
    }

    function formatCurrency(amount) {
        return new Intl.NumberFormat('es-VE', {
            style: 'currency',
            currency: 'VES'
        }).format(amount || 0);
    }

    function getStatusBadgeClass(status) {
        const statusClasses = {
            'Pendiente': 'warning',
            'Pagado': 'success',
            'Vencido': 'danger',
            'Parcial': 'info'
        };
        return statusClasses[status] || 'secondary';
    }

    // Inicialización
    if (tableBody) {
        fetchAccounts();
    }

    // Agregar manejadores de eventos para los formularios
    if (addForm) {
        addForm.addEventListener('submit', handleAdd);
    }

    if (editForm) {
        editForm.addEventListener('submit', handleEdit);
    }

    // Funciones de manejo de eventos
    async function handleAdd(e) {
        e.preventDefault();
        const formData = new FormData(addForm);
        
        try {
            const response = await fetch(window.location.pathname + '?action=add', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const result = await response.json();
            
            if (result.success) {
                showAlert('Cuenta por cobrar agregada correctamente', 'success');
                addForm.reset();
                const modal = bootstrap.Modal.getInstance(document.getElementById('addAccountModal'));
                if (modal) modal.hide();
                fetchAccounts();
            } else {
                showAlert(result.message || 'Error al agregar la cuenta', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error al procesar la solicitud', 'error');
        }
    }

    // Implementar handleEdit, loadAccountForEdit y handleDelete de manera similar
    // ...

});

// Inicialización de tooltips de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});