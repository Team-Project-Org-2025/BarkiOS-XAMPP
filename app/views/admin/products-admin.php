<?php 
$pageTitle = "Productos | Garage Barki"; 
require_once __DIR__ . '/../partials/header-admin.php'; 
?>

<!-- Barra lateral de navegación -->
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?> 

<div class="main-content">
    <div class="container-fluid">
        <!-- Header con Tasa BCV -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">Productos</h1>

            <?php include __DIR__ . '/../partials/exchange-rate-widget.php'; ?>
        </div>
        <!-- Tabla de Productos -->
        <div class="card mt-3">
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table id="productsTable" class="table table-hover align-middle text-center">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Categoría</th>
                                <th>Precio Compra</th>
                                <th>Precio Venta</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Añadir Producto -->
<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addProductModalLabel">Añadir Nuevo Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addProductForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div id="addProductErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">Código</label>
                        <input type="text" class="form-control" 
                            id="productId"
                            name="prenda_id" 
                            placeholder="Ingrese código del producto"
                            pattern="^\d{9}$"
                            maxlength="9"
                            required>
                        <div class="invalid-feedback">Por favor ingrese un código válido</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" 
                            id="productName"
                            name="nombre" 
                            placeholder="Ingrese nombre del producto"
                            required>
                        <div class="invalid-feedback">Por favor ingrese un nombre válido</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" id="productCategory" name="categoria" required>
                            <option value="">Seleccione una categoría</option>
                            <option value="Formal">Formal</option>
                            <option value="Casual">Casual</option>
                            <option value="Deportivo">Deportivo</option>
                            <option value="Invierno">Invierno</option>
                            <option value="Verano">Verano</option>
                            <option value="Fiesta">Fiesta</option>
                        </select>
                        <div class="invalid-feedback">Por favor seleccione una categoría</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de prenda</label>
                        <select class="form-select" id="productType" name="tipo" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="Vestido">Vestido</option>
                            <option value="Blusa">Blusa</option>
                            <option value="Pantalon">Pantalón</option>
                            <option value="Camisa">Camisa</option>
                            <option value="Falda">Falda</option>
                            <option value="Shorts">Short</option>
                            <option value="Enterizo">Enterizo</option>
                            <option value="Chaqueta">Chaqueta</option>
                        </select>
                        <div class="invalid-feedback">Por favor seleccione un tipo de prenda</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio de Compra (USD) - Opcional</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number"
                                step="0.01" 
                                class="form-control" 
                                name="precio_compra"
                                id="productPriceCompra"
                                placeholder="0.00">
                        </div>
                        <small class="text-muted">Precio al que se compró el producto</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio de Venta (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number"
                                step="0.01" 
                                class="form-control" 
                                name="precio"
                                id="productPrice"
                                oninput="calculateBolivares(this.value, 'addPriceBs')"
                                required>
                        </div>
                        <small class="text-muted">
                            Equivalente: <span id="addPriceBs" class="fw-bold">Bs. 0.00</span>
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imagen del Producto (Opcional)</label>
                        <input type="file" 
                            class="form-control" 
                            name="imagen" 
                            id="productImage"
                            accept="image/*">
                        <small class="text-muted">JPG, PNG, GIF, WEBP - Máximo 5MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary" id="addProductBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        <span class="btn-text">Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Editar Producto -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProductModalLabel">Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editProductForm" enctype="multipart/form-data">
                <input type="hidden" name="prenda_id" id="editProductIdHidden">
                <div class="modal-body">
                    <div id="editProductErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">Código</label>
                        <input type="text" class="form-control" 
                            id="editProductId"
                            disabled>
                        <small class="text-muted">El código no se puede modificar</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" 
                            name="nombre" 
                            id="editProductName"
                            required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" name="categoria" id="editProductCategory" required>
                            <option value="">Seleccione una categoría</option>
                            <option value="Formal">Formal</option>
                            <option value="Casual">Casual</option>
                            <option value="Deportivo">Deportivo</option>
                            <option value="Fiesta">Fiesta</option>
                            <option value="Invierno">Invierno</option>
                            <option value="Verano">Verano</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de prenda</label>
                        <select class="form-select" name="tipo" id="editProductType" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="Vestido">Vestido</option>
                            <option value="Blusa">Blusa</option>
                            <option value="Pantalon">Pantalón</option>
                            <option value="Camisa">Camisa</option>
                            <option value="Falda">Falda</option>
                            <option value="Shorts">Short</option>
                            <option value="Enterizo">Enterizo</option>
                            <option value="Chaqueta">Chaqueta</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio de Compra (USD) - Opcional</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number"
                                step="0.01" 
                                class="form-control" 
                                name="precio_compra"
                                id="editProductPriceCompra"
                                placeholder="0.00">
                        </div>
                        <small class="text-muted">Precio al que se compró el producto</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio de Venta (USD)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" 
                                step="0.01" 
                                class="form-control" 
                                name="precio" 
                                id="editProductPrice" 
                                min="0"
                                oninput="calculateBolivares(this.value, 'editPriceBs')"
                                required>
                        </div>
                        <small class="text-muted">
                            Equivalente: <span id="editPriceBs" class="fw-bold">Bs. 0.00</span>
                        </small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Imagen del Producto</label>
                        <input type="file" 
                            class="form-control" 
                            name="imagen" 
                            id="editProductImage"
                            accept="image/*">
                        <small class="text-muted">Dejar vacío para mantener la imagen actual</small>
                        <div id="currentImagePreview" class="mt-2"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php 
// Asegurarse de que las funciones JS estén disponibles
if (!function_exists('getDolarRate')) {
    require_once __DIR__ . '/../../core/AdminContext.php';
}
?>
<script>
    // Definir la variable ANTES de cargar products-admin.js
    const DOLAR_BCV_RATE = <?php echo getDolarRate(); ?>;
</script>

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.3/dist/sweetalert2.all.min.js"></script>
<script type="module" src="/BarkiOS/public/assets/js/admin/products-admin.js"></script>
<script src="/BarkiOS/public/assets/js/admin/logout.js"></script>

</body>
</html>