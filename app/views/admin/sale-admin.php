<?php $pageTitle = "Ventas | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?> 


<div class="main-content">
  <div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
      <h3 class="mb-0"><i class="fas fa-shopping-cart me-2"></i>Gestión de Ventas</h3>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSaleModal">
        <i class="fas fa-plus me-1"></i> Nueva Venta
      </button>
    </div>

    <!-- Estadísticas -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0 stat-card">
          <div class="card-body text-center p-3">
            <i class="fas fa-receipt fa-2x text-primary mb-2"></i>
            <h6 class="text-muted small mb-1">Ventas</h6>
            <h4 class="mb-0" id="totalSales">0</h4>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0 stat-card">
          <div class="card-body text-center p-3">
            <i class="fas fa-dollar-sign fa-2x text-success mb-2"></i>
            <h6 class="text-muted small mb-1">Total</h6>
            <h4 class="mb-0 text-success small" id="totalRevenue">$0.00</h4>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0 stat-card">
          <div class="card-body text-center p-3">
            <i class="fas fa-clock fa-2x text-warning mb-2"></i>
            <h6 class="text-muted small mb-1">Pendiente</h6>
            <h4 class="mb-0 text-warning small" id="totalPending">$0.00</h4>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card shadow-sm border-0 stat-card">
          <div class="card-body text-center p-3">
            <i class="fas fa-check-circle fa-2x text-info mb-2"></i>
            <h6 class="text-muted small mb-1">Completadas</h6>
            <h4 class="mb-0 text-info" id="completedSales">0</h4>
          </div>
        </div>
      </div>
    </div>

    <!-- Buscador -->
    <div class="row mb-3">
      <div class="col-12 col-md-8 col-lg-6">
        <div class="input-group">
          <span class="input-group-text"><i class="fas fa-search"></i></span>
          <input type="text" id="searchInput" class="form-control" placeholder="Buscar...">
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="card shadow-sm border-0">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 table-modern">
            <thead class="table-light">
              <tr>
                <th class="text-center d-none d-md-table-cell">#</th>
                <th>Ref.</th>
                <th class="d-none d-lg-table-cell">Cliente</th>
                <th class="d-none d-xl-table-cell">Empleado</th>
                <th class="d-none d-md-table-cell">Fecha</th>
                <th class="text-end">Monto</th>
                <th class="text-center d-none d-sm-table-cell">Estado</th>
                <th class="text-center" width="150">Acciones</th>
              </tr>
            </thead>
            <tbody id="salesTableBody"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- MODAL: Registrar Venta -->
<div class="modal fade" id="addSaleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-md-down">
    <div class="modal-content">
      <form id="addSaleForm" autocomplete="off">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title"><i class="fas fa-shopping-cart me-2"></i>Registrar Nueva Venta</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <!-- Información básica -->
          <div class="row g-3 mb-3">
            <div class="col-12 col-md-6 col-lg-4">
              <label class="form-label fw-bold">Cliente <span class="text-danger">*</span></label>
              <select id="add_cliente" name="cliente_ced" class="form-select" required>
                <option value="">Seleccione...</option>
              </select>
              <small class="text-muted"><i class="fas fa-star text-warning"></i> Cliente VIP</small>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
              <label class="form-label fw-bold">Vendedor <span class="text-danger">*</span></label>
              <select id="add_empleado" name="empleado_ced" class="form-select" required>
                <option value="">Seleccione...</option>
              </select>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
              <label class="form-label fw-bold">Tipo de venta <span class="text-danger">*</span></label>
              <select name="tipo_venta" class="form-select" required>
                <option value="contado">Contado</option>
                <option value="credito">Crédito (Solo VIP)</option>
              </select>
            </div>
            <div class="col-12 col-md-6 col-lg-4" id="fechaVencimientoGroup" style="display: none;">
                <label class="form-label fw-bold">
                      Fecha de Vencimiento <span class="text-danger">*</span>
                </label>
                <input type="date" 
                        id="add_fecha_vencimiento" 
                        name="fecha_vencimiento" 
                        class="form-control"
                        min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                <small class="text-muted">Fecha límite de pago del crédito</small>
            </div>
          <div class="col-12 col-md-6 col-lg-4">
            <label class="form-label fw-bold">Referencia (opcional)</label>
            <input type="text" id="add_referencia" name="referencia" 
              class="form-control" maxlength="30" 
              placeholder="Ej: VEN-001" autocomplete="off">
          </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
              <label class="form-label fw-bold">IVA (%)</label>
              <input type="number" id="add_iva" class="form-control" name="iva_porcentaje" 
                     min="0" max="100" step="0.01" value="16.00">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label fw-bold">Observaciones</label>
              <textarea name="observaciones" class="form-control" rows="1"></textarea>
            </div>
          </div>

          <hr>

          <!-- Sección Productos -->
          <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="mb-0 fw-bold"><i class="fas fa-box me-2"></i>Productos</h6>
              <span class="badge bg-info" id="productsCount">0 disponibles</span>
            </div>

            <!-- Contenedor de productos agregados -->
            <div id="productsContainer"></div>
            <div id="noProductsAlert" class="alert alert-info text-center py-2 mb-2">
              <i class="fas fa-info-circle me-2"></i>No se han agregado productos
            </div>

            <!-- Botón agregar de lista -->
            <button type="button" id="btnAddProduct" class="btn btn-sm btn-outline-primary w-100">
              <i class="fas fa-plus me-1"></i> Agregar de lista completa
            </button>
          </div>

          <hr>

          <!-- Resumen de totales -->
          <div class="row">
            <div class="col-12 col-lg-6 offset-lg-6">
              <div class="card bg-light border-0">
                <div class="card-body">
                  <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <strong id="summary_subtotal">$0.00</strong>
                  </div>
                  <div class="d-flex justify-content-between mb-2">
                    <span>IVA (<span id="iva_percentage">16</span>%):</span>
                    <strong id="summary_iva">$0.00</strong>
                  </div>
                  <hr class="my-2">
                  <div class="d-flex justify-content-between">
                    <span class="fs-5 fw-bold">Total:</span>
                    <strong class="fs-5 text-primary" id="summary_total">$0.00</strong>
                  </div>
                  <div class="d-flex justify-content-between mt-1">
                    <span class="fw-bold text-success">Total en Bs:</span>
                    <strong class="text-success" id="summary_total_bs">Bs. 0,00</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="fas fa-times"></i> Cancelar
          </button>
          <button type="submit" class="btn btn-success" id="btnSaveSale">
            <i class="fas fa-save"></i> Guardar Venta
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL: Detalle de Venta -->
<div class="modal fade" id="viewSaleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-md-down">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Detalle de Venta</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="saleDetailsContent">
        <div class="text-center py-4">
          <div class="spinner-border text-primary"></div>
          <p class="mt-2">Cargando...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
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
    const DOLAR_BCV_RATE = <?php echo getDolarRate(); ?>;
</script>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/BarkiOS/public/assets/js/sales-admin.js"></script>

</body>
</html>