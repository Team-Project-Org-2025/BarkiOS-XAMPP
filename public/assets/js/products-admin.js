document.addEventListener('DOMContentLoaded', () => {
    const productsTableBody = document.getElementById('productsTableBody');
    const addProductForm = document.getElementById('addProductForm');
    const editProductForm = document.getElementById('editProductForm');

    // --- Tipos de prenda por categoría ---
    const tiposPorCategoria = {
        Formal:    ["Vestido", "Camisa", "Pantalon", "Chaqueta"],
        Casual:    ["Blusa", "Pantalon", "Short", "Falda"],
        Deportivo: ["Short", "Falda", "Chaqueta"],
        Invierno:  ["Chaqueta", "Pantalon"],
        Verano:    ["Vestido", "Short", "Blusa"],
        Fiesta:    ["Vestido", "Falda", "Blusa", "Enterizo"]
    };

    function actualizarTipos(selectCategoria, selectTipo) {
        const categoria = selectCategoria.value;
        selectTipo.innerHTML = '<option value="">Seleccione un tipo</option>';
        if (tiposPorCategoria[categoria]) {
            tiposPorCategoria[categoria].forEach(tipo => {
                const opt = document.createElement('option');
                opt.value = tipo;
                opt.textContent = tipo;
                selectTipo.appendChild(opt);
            });
        }
    }

    // Para el formulario de agregar
    const catAdd = document.getElementById('productCategory');
    const tipoAdd = document.getElementById('productType');
    if (catAdd && tipoAdd) {
        catAdd.addEventListener('change', () => actualizarTipos(catAdd, tipoAdd));
    }

    // Para el formulario de editar
    const catEdit = document.getElementById('editProductCategory');
    const tipoEdit = document.getElementById('editProductType');
    if (catEdit && tipoEdit) {
        catEdit.addEventListener('change', () => actualizarTipos(catEdit, tipoEdit));
    }
    // --- Fin dependencias tipo-categoría ---

    // Utilidades
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

    // CRUD AJAX
    function fetchProducts() {
        productsTableBody.innerHTML = `<tr><td colspan="6" class="text-center">
            <div class="spinner-border text-primary"></div> Cargando...</td></tr>`;
        fetch(window.location.pathname + '?action=get_products', {headers: {'X-Requested-With':'XMLHttpRequest'}})
        .then(r => r.json()).then(data => {
            
            if (!data.products?.length) return productsTableBody.innerHTML =
                `<td colspan="6" class="text-center">
                                            <div class="alert alert-info mb-0">No hay productos disponibles</div>
                                        </td>`;
            productsTableBody.innerHTML = data.products.map(p => `
    <tr id="producto-${escapeHtml(p.prenda_id)}">
        <td>${escapeHtml(p.prenda_id)}</td>
        <td>${escapeHtml(p.nombre)}</td>
        <td>${escapeHtml(p.tipo)}</td>
        <td>${escapeHtml(p.categoria)}</td>
        <td>$${parseFloat(p.precio).toFixed(2)}</td>
        <td>
            <button class="btn btn-sm btn-outline-primary btn-edit"
                data-id="${escapeHtml(p.prenda_id)}"
                data-nombre="${escapeHtml(p.nombre)}"
                data-tipo="${escapeHtml(p.tipo)}"
                data-categoria="${escapeHtml(p.categoria)}"
                data-precio="${p.precio}">
                <i class="fas fa-edit"></i> Editar
            </button>
            <button class="btn btn-sm btn-outline-danger btn-delete"
                data-product-id="${escapeHtml(p.prenda_id)}"
                data-product-name="${escapeHtml(p.nombre)}">
                <i class="fas fa-trash"></i> Eliminar
            </button>
        </td>
    </tr>
`).join('');
            document.querySelectorAll('.btn-delete').forEach(btn => btn.onclick = handleDelete);
            document.querySelectorAll('.btn-edit').forEach(btn => btn.onclick = () => loadProductForEdit(btn));
        }).catch(() => showAlert('Error al cargar productos', 'danger'));
    }

    function handleAdd(e) {
        e.preventDefault();
        const fd = new URLSearchParams(new FormData(addProductForm));
        fetch('index.php?controller=products&action=add_ajax', {
            method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
            body: fd
        }).then(r => r.json()).then(data => {
            if (data.success) {
                showAlert('Producto agregado', 'success');
                addProductForm.reset();
                bootstrap.Modal.getInstance(document.getElementById('addProductModal')).hide();
                fetchProducts();
            } else showAlert(data.message, 'danger');
        }).catch(() => showAlert('Error al agregar', 'danger'));
    }

    function loadProductForEdit(btn) {
        document.getElementById('editProductId').value = btn.getAttribute('data-id') || '';
        document.getElementById('editProductIdHidden').value = btn.getAttribute('data-id') || '';
        document.getElementById('editProductName').value = btn.getAttribute('data-nombre') || '';
        document.getElementById('editProductCategory').value = btn.getAttribute('data-categoria') || '';
        // Actualiza tipos según la categoría seleccionada
        actualizarTipos(catEdit, tipoEdit);
        document.getElementById('editProductType').value = btn.getAttribute('data-tipo') || '';
        document.getElementById('editProductPrice').value = btn.getAttribute('data-precio') || '';
        const modal = new bootstrap.Modal(document.getElementById('editProductModal'));
        modal.show();
    }

    function handleEdit(e) {
        e.preventDefault();
        const fd = new URLSearchParams(new FormData(editProductForm));
        fetch(window.location.pathname + '?action=edit_ajax', {
            method: 'POST', headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
            body: fd
        }).then(r => r.json()).then(data => {
            if (data.success) {
                showAlert('Producto actualizado', 'success');
                bootstrap.Modal.getInstance(document.getElementById('editProductModal')).hide();
                fetchProducts();
            } else showAlert(data.message, 'danger');
        }).catch(() => showAlert('Error al actualizar', 'danger'));
    }

    function handleDelete(e) {
        const prenda_id = e.currentTarget.dataset.productId;
        const name = e.currentTarget.dataset.productName;
        Swal.fire({
            title: '¿Eliminar producto?',
            html: `¿Deseas eliminar <strong>${escapeHtml(name)}</strong>?`,
            icon: 'warning', showCancelButton: true,
            confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(res => {
            if (res.isConfirmed) {
                fetch('index.php?controller=products&action=delete_ajax', {
                    method: 'POST',
                    headers: {'X-Requested-With':'XMLHttpRequest','Content-Type':'application/x-www-form-urlencoded'},
                    body: `prenda_id=${encodeURIComponent(prenda_id)}`
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        showAlert('Producto eliminado', 'success');
                        fetchProducts();
                    } else showAlert(data.message, 'danger');
                }).catch(() => showAlert('Error al eliminar', 'danger'));
            }
        });
    }

    // Inicialización
    if (addProductForm) addProductForm.onsubmit = handleAdd;
    if (editProductForm) editProductForm.onsubmit = handleEdit;
    fetchProducts();
});