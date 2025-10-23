document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.getElementById('accountTableBody');
    const addForm = document.getElementById('addAccountForm');
    let searchTimeout;

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

    // Búsqueda de proveedores
    const searchSupplierInput = document.getElementById('searchSupplier');
    const supplierResults = document.getElementById('supplierResults');
    const proveedorIdInput = document.getElementById('proveedorId');

    if (searchSupplierInput) {
        searchSupplierInput.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            const query = this.value.trim();
            
            if (query.length < 2) {
                supplierResults.style.display = 'none';
                return;
            }
            
            supplierResults.innerHTML = '<div class="list-group-item">Buscando...</div>';
            supplierResults.style.display = 'block';
            
            searchTimeout = setTimeout(() => {
                fetch(`/BarkiOS/app/controllers/Admin/AccountsPayableController.php?action=search_supplier&search=${encodeURIComponent(query)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        supplierResults.innerHTML = data.data.map(supplier => `
                            <button type="button" class="list-group-item list-group-item-action" 
                                    data-id="${escapeHtml(supplier.id)}" 
                                    data-nombre="${escapeHtml(supplier.nombre_empresa)}">
                                <strong>${escapeHtml(supplier.nombre_empresa)}</strong><br>
                                <small class="text-muted">RIF: ${escapeHtml(supplier.rif)} | Contacto: ${escapeHtml(supplier.nombre_contacto)}</small>
                            </button>
                        `).join('');

                        document.querySelectorAll('#supplierResults button').forEach(btn => {
                            btn.addEventListener('click', (e) => {
                                const supplier = e.currentTarget;
                                searchSupplierInput.value = supplier.dataset.nombre;
                                proveedorIdInput.value = supplier.dataset.id;
                                supplierResults.style.display = 'none';
                            });
                        });
                    } else {
                        supplierResults.innerHTML = '<div class="list-group-item">No se encontraron proveedores</div>';
                    }
                })
                .catch(() => {
                    supplierResults.innerHTML = '<div class="list-group-item text-danger">Error al buscar</div>';
                });
            }, 300);
        });

        // Ocultar resultados al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!searchSupplierInput.contains(e.target) && !supplierResults.contains(e.target)) {
                supplierResults.style.display = 'none';
            }
        });
    }

    // Cargar cuentas por pagar
    function fetchAccounts() {
        tableBody.innerHTML = `<tr><td colspan="6" class="text-center">
            <div class="spinner-border text-primary"></div> Cargando...</td></tr>`;
        
        fetch(window.location.pathname + '?action=get_accounts', {
            headers: {'X-Requested-With':'XMLHttpRequest'}
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.data || !data.data.length) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="alert alert-info mb-0">No hay cuentas por pagar registradas</div>
                        </td>
                    </tr>`;
                return;
            }

            tableBody.innerHTML = data.data.map(account => `
                <tr id="account-${escapeHtml(account.id)}">
                    <td>${escapeHtml(account.factura_numero)}</td>
                    <td>${escapeHtml(account.nombre_proveedor || 'N/A')}</td>
                    <td>${escapeHtml(account.fecha_emision)}</td>
                    <td class="text-end">${escapeHtml(account.monto_total)}</td>
                    <td>${escapeHtml(account.fecha_vencimiento)}</td>
                    <td>
                        <span class="badge bg-${getStatusBadgeClass(account.estado)}">${escapeHtml(account.estado)}</span>
                    </td>
                </tr>
            `).join('');
        })
        .catch(error => {
            console.error('Error:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        Error al cargar las cuentas por pagar
                    </td>
                </tr>`;
        });
    }

    // Funciones de utilidad
    function getStatusBadgeClass(status) {
        const statusClasses = {
            'Pendiente': 'warning',
            'Pagada': 'success',
            'Vencida': 'danger',
            'Parcial': 'info'
        };
        return statusClasses[status] || 'secondary';
    }

    // Inicialización
    if (tableBody) {
        fetchAccounts();
    }

    // Manejador del formulario de agregar
    if (addForm) {
        addForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const btnGuardar = document.getElementById('btnGuardar');
            const btnText = btnGuardar.querySelector('.btn-text');
            const spinner = btnGuardar.querySelector('.spinner-border');
            
            // Validar que se haya seleccionado un proveedor
            if (!proveedorIdInput.value) {
                showAlert('Por favor seleccione un proveedor', 'error');
                return;
            }
            
            // Deshabilitar botón y mostrar spinner
            btnGuardar.disabled = true;
            spinner.classList.remove('d-none');
            btnText.textContent = 'Guardando...';
            
            const formData = new FormData(addForm);
            
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
                    showAlert('Cuenta por pagar agregada correctamente', 'success');
                    addForm.reset();
                    proveedorIdInput.value = '';
                    searchSupplierInput.value = '';
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
                // Rehabilitar botón y ocultar spinner
                btnGuardar.disabled = false;
                spinner.classList.add('d-none');
                btnText.textContent = 'Guardar';
            }
        });
    }

    // Limpiar formulario al cerrar el modal
    const addAccountModal = document.getElementById('addAccountModal');
    if (addAccountModal) {
        addAccountModal.addEventListener('hidden.bs.modal', function () {
            addForm.reset();
            proveedorIdInput.value = '';
            searchSupplierInput.value = '';
            supplierResults.style.display = 'none';
        });
    }
});

// Inicialización de tooltips de Bootstrap
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
