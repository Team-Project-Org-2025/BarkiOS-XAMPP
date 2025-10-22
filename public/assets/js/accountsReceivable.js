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

    // Validación en tiempo real para el número de factura
    const facturaNumeroInput = document.getElementById('facturaNumero');
    if (facturaNumeroInput) {
        facturaNumeroInput.addEventListener('input', function(e) {
            // Solo permitir números
            this.value = this.value.replace(/[^0-9]/g, '');
            
            // Validar longitud
            if (this.value.length === 8) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else if (this.value.length > 0) {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-valid', 'is-invalid');
            }
        });
    }

    // Búsqueda de clientes VIP con debounce de 300ms
    const searchClientInput = document.getElementById('searchClient');
    const clientResults = document.getElementById('clientResults');
    const clienteIdInput = document.getElementById('clienteId');
    let searchTimeout = null;

    if (searchClientInput) {
        searchClientInput.addEventListener('input', function(e) {
            // Limpiar el timeout anterior
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            // Limpiar el cliente seleccionado si el usuario modifica el texto
            if (clienteIdInput.value) {
                clienteIdInput.value = '';
            }
            
            // Si la consulta es muy corta, ocultar resultados
            if (query.length < 1) {
                clientResults.style.display = 'none';
                clientResults.innerHTML = '';
                return;
            }
            
            // Mostrar indicador de búsqueda
            clientResults.innerHTML = '<div class="list-group-item"><i class="fas fa-spinner fa-spin me-2"></i>Buscando clientes VIP...</div>';
            clientResults.style.display = 'block';
            
            // Implementar debounce de 300ms
            searchTimeout = setTimeout(() => {
                fetch(`/BarkiOS/clients/?action=search_vip_clients&q=${encodeURIComponent(query)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.clients && data.clients.length > 0) {
                        clientResults.innerHTML = data.clients.map(client => `
                            <button type="button" class="list-group-item list-group-item-action" 
                                    data-cedula="${escapeHtml(client.cliente_ced)}" 
                                    data-nombre="${escapeHtml(client.nombre_cliente)}">
                                <strong>${escapeHtml(client.nombre_cliente)}</strong>
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-id-card me-1"></i>Cédula: ${escapeHtml(client.cliente_ced)}
                                    ${client.telefono ? `<i class="fas fa-phone ms-2 me-1"></i>${escapeHtml(client.telefono)}` : ''}
                                </small>
                            </button>
                        `).join('');

                        // Agregar manejadores de eventos a los resultados
                        document.querySelectorAll('#clientResults button').forEach(btn => {
                            btn.addEventListener('click', (e) => {
                                e.preventDefault();
                                const client = e.currentTarget;
                                searchClientInput.value = client.dataset.nombre;
                                clienteIdInput.value = client.dataset.cedula;
                                clientResults.style.display = 'none';
                                clientResults.innerHTML = '';
                                
                                // Marcar el campo como válido
                                searchClientInput.classList.remove('is-invalid');
                                searchClientInput.classList.add('is-valid');
                            });
                        });
                    } else {
                        clientResults.innerHTML = '<div class="list-group-item text-muted"><i class="fas fa-info-circle me-2"></i>No se encontraron clientes VIP con ese nombre</div>';
                    }
                })
                .catch(error => {
                    console.error('Error al buscar clientes VIP:', error);
                    clientResults.innerHTML = '<div class="list-group-item text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error al realizar la búsqueda</div>';
                });
            }, 300); // Debounce de 300ms
        });

        // Ocultar resultados al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!searchClientInput.contains(e.target) && !clientResults.contains(e.target)) {
                clientResults.style.display = 'none';
            }
        });
    }

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
        
        // Validar número de factura (exactamente 8 dígitos)
        if (!facturaNumeroInput || !facturaNumeroInput.value || facturaNumeroInput.value.length !== 8) {
            showAlert('El número de factura debe tener exactamente 8 dígitos', 'warning');
            facturaNumeroInput.focus();
            return;
        }
        
        // Validar que se haya seleccionado un cliente VIP
        if (!clienteIdInput || !clienteIdInput.value) {
            showAlert('Por favor seleccione un cliente VIP de la lista', 'warning');
            searchClientInput.focus();
            return;
        }
        
        const formData = new FormData(addForm);
        
        // Mostrar indicador de carga
        const btnGuardar = document.getElementById('btnGuardar');
        const btnSpinner = btnGuardar.querySelector('.spinner-border');
        const btnText = btnGuardar.querySelector('.btn-text');
        btnGuardar.disabled = true;
        btnSpinner.classList.remove('d-none');
        btnText.textContent = 'Guardando...';
        
        try {
            const response = await fetch(window.location.pathname + '?action=add_ajax', {
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
                
                // Limpiar validaciones visuales
                if (facturaNumeroInput) facturaNumeroInput.classList.remove('is-valid', 'is-invalid');
                if (searchClientInput) searchClientInput.classList.remove('is-valid', 'is-invalid');
                if (clienteIdInput) clienteIdInput.value = '';
                
                const modal = bootstrap.Modal.getInstance(document.getElementById('addAccountModal'));
                if (modal) modal.hide();
                fetchAccounts();
            } else {
                showAlert(result.message || 'Error al agregar la cuenta', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showAlert('Error al procesar la solicitud', 'error');
        } finally {
            // Restaurar botón
            btnGuardar.disabled = false;
            btnSpinner.classList.add('d-none');
            btnText.textContent = 'Guardar';
        }
    }

    // Limpiar el formulario al cerrar el modal
    const addAccountModal = document.getElementById('addAccountModal');
    if (addAccountModal) {
        addAccountModal.addEventListener('hidden.bs.modal', () => {
            if (addForm) {
                addForm.reset();
            }
            if (facturaNumeroInput) {
                facturaNumeroInput.classList.remove('is-valid', 'is-invalid');
                facturaNumeroInput.value = '';
            }
            if (searchClientInput) {
                searchClientInput.classList.remove('is-valid', 'is-invalid');
                searchClientInput.value = '';
            }
            if (clienteIdInput) {
                clienteIdInput.value = '';
            }
            if (clientResults) {
                clientResults.style.display = 'none';
                clientResults.innerHTML = '';
            }
        });
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