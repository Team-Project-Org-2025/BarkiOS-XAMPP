<?php $pageTitle = "Productos | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<!-- Barra lateral de navegación -->
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?> 
<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">Productos</h1>
        </div>
        <button class="btn btn-primary rounded-pill px-4 me-3" data-bs-toggle="modal" data-bs-target="#addProductModal">
            <i class="fas fa-plus me-1"></i> Agregar producto
        </button>
        
        <!-- Mensajes de éxito/error dinámicos -->
        <!-- <div id="alertContainer" class="mt-3"></div> -->

        <!-- Tabla de Productos -->
        <div class="card mt-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle table-hover text-center">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Categoría</th>
                                <th>Precio</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <tr>
                                <td colspan="6" class="text-center">
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
                <form id="addProductForm">
                    <div class="modal-body">
                        <div id="addProductErrors" class="alert alert-danger d-none"></div>
                        <div class="mb-3">
                            <label class="form-label">Código</label>
                            <input type="text" class="form-control" 
                            id="productId"
                            name="prenda_id" 
                            placeholder="Ingrese código del producto" 
                            inputmode="numeric"
                            maxlength="9"
                            minlength="9"
                            pattern="^\d{0,9}$"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0,9);"
                            required>
                            <div class="invalid-feedback">Por favor ingrese un código válido</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" 
                            id="productName"
                            name="nombre" 
                            placeholder="Ingrese nombre del producto" 
                            maxlength="40"
                            pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]{1,40}$"
                            oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '').slice(0,40);"
                            required>
                            <div class="invalid-feedback">Por favor ingrese un nombre válido (solo letras y espacios)</div>
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
                                <option value="Pantalon">Pantalone</option>
                                <option value="Camisa">Camisa</option>
                                <option value="Falda">Falda</option>
                                <option value="Shorts">Short</option>
                                <option value="Enterizo">Enterizo</option>
                                <option value="Chaqueta">Chaqueta</option>
                            </select>
                            <div class="invalid-feedback">Por favor seleccione un tipo de prenda</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Precio</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control" 
                                step="0.01" 
                                class="form-control" 
                                id="productPrice" 
                                name="precio" 
                                min="0"
                                max="999.99"
                                oninput="if(this.value.length > 11) this.value = this.value.slice(0,11);"
                                required>
                                <div class="invalid-feedback">Por favor ingrese un precio válido</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="addProductBtn">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="btn-text">Guardar</span>
                        </button>
                    </div>
                </form>
            </div>
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
            <form id="editProductForm">
                <input type="hidden" name="prenda_id" id="editProductIdHidden">
                <div class="modal-body">
                    <div id="editProductErrors" class="alert alert-danger d-none"></div>
                    <div class="mb-3">
                        <label class="form-label">Código</label>
                        <input type="text" class="form-control" 
                            id="editProductId"
                            name="prenda_id" 
                            placeholder="Ingrese código del producto" 
                            inputmode="numeric"
                            maxlength="9"
                            minlength="9"
                            pattern="^\d{9}$"
                            disabled>
                        <div class="invalid-feedback">Por favor ingrese un código válido</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" class="form-control" 
                            name="nombre" 
                            id="editProductName"
                            placeholder="Ingrese nombre del producto" 
                            pattern="^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$"
                            oninput="this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, '');"
                            required>
                        <div class="invalid-feedback">Por favor ingrese un nombre válido (solo letras y espacios)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Categoría</label>
                        <select class="form-select" name="categoria" id="editProductCategory" required>
                            <!-- Opciones para Categoría -->
                            <option value="">Seleccione una categoría</option>
                            <option value="Formal">Formal</option>
                            <option value="Casual">Casual</option>
                            <option value="Deportivo">Deportivo</option>
                            <option value="Fiesta">Fiesta</option>
                            <option value="Invierno">Invierno</option>
                            <option value="Verano">Verano</option>
                        </select>
                        <div class="invalid-feedback">Por favor seleccione una categoría</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipo de prenda</label>
                        <select class="form-select" name="tipo" id="editProductType" required>
                            <option value="">Seleccione un tipo</option>
                        </select>
                        <div class="invalid-feedback">Por favor seleccione un tipo de prenda</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Precio</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" 
                                step="0.01" 
                                class="form-control" 
                                name="precio" 
                                id="editProductPrice" 
                                min="0"
                                required>
                            <div class="invalid-feedback">Por favor ingrese un precio válido</div>
                        </div>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 para alertas bonitas -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="/BarkiOS/public/assets/js/products-admin.js"></script>
</body>
</html>