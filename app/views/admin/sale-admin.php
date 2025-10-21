<?php $pageTitle = "Ventas | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?>

<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Gestión de Ventas</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSaleModal">
      <i class="fas fa-plus"></i> Nueva Venta
    </button>
  </div>

  <!-- Estadísticas -->
  <div class="row mb-4">
    <div class="col-md-3">
      <div class="card shadow-sm">
        <div class="card-body text-center">
          <h6 class="text-muted">Ventas Totales</h6>
          <h4 id="totalSales">0</h4>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm">
        <div class="card-body text-center">
          <h6 class="text-muted">Monto Total</h6>
          <h4 id="totalRevenue">$0.00</h4>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm">
        <div class="card-body text-center">
          <h6 class="text-muted">Pendiente por Cobrar</h6>
          <h4 id="totalPending">$0.00</h4>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card shadow-sm">
        <div class="card-body text-center">
          <h6 class="text-muted">Completadas</h6>
          <h4 id="completedSales">0</h4>
        </div>
      </div>
    </div>
  </div>

  <!-- Filtro de búsqueda -->
  <div class="row mb-3">
    <div class="col-md-6">
      <input type="text" id="searchInput" class="form-control" placeholder="Buscar venta...">
    </div>
  </div>

  <!-- Tabla de ventas -->
  <div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Cliente</th>
          <th>Empleado</th>
          <th>Fecha</th>
          <th class="text-end">Monto</th>
          <th>Tipo</th>
          <th>Estado</th>
          <th class="text-center">Acciones</th>
        </tr>
      </thead>
      <tbody id="salesTableBody"></tbody>
    </table>
  </div>
</div>

<!-- ===========================
     MODAL: Registrar Venta
=========================== -->
<div class="modal fade" id="addSaleModal" tabindex="-1" aria-labelledby="addSaleLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <form id="addSaleForm" autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title" id="addSaleLabel">Registrar Venta</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Cliente</label>
              <select id="add_cliente" name="cliente_ced" class="form-select" required></select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Empleado</label>
              <select id="add_empleado" name="empleado_ced" class="form-select" required></select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Tipo de venta</label>
              <select name="tipo_venta" class="form-select" required>
                <option value="contado">Contado</option>
                <option value="credito">Crédito</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Método de Pago Principal</label>
              <select name="metodo_pago_principal" class="form-select" required>
                <option value="">Seleccione...</option>
                <option value="efectivo">Efectivo</option>
                <option value="tarjeta_debito">Tarjeta Débito</option>
                <option value="tarjeta_credito">Tarjeta Crédito</option>
                <option value="transferencia">Transferencia</option>
                <option value="pago_movil">Pago Móvil</option>
                <option value="cheque">Cheque</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Descuento (%)</label>
              <input type="number" id="add_descuento" class="form-control" name="descuento" min="0" max="100" step="0.01" value="0">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea name="observaciones" class="form-control" rows="2"></textarea>
          </div>

          <hr>

          <!-- Productos -->
          <div id="productsContainer"></div>
          <div id="noProductsAlert" class="alert alert-info text-center py-2">Sin productos agregados</div>
          <button type="button" id="btnAddProduct" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-plus"></i> Agregar producto
          </button>

          <hr>

          <!-- Totales -->
          <div class="row text-end">
            <div class="col-md-4 offset-md-8">
              <div class="border rounded p-2 bg-light">
                <div><strong>Subtotal:</strong> <span id="summary_subtotal">$0.00</span></div>
                <div><strong>Descuento:</strong> <span id="summary_discount">$0.00</span></div>
                <div class="fs-5"><strong>Total:</strong> <span id="summary_total">$0.00</span></div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="fas fa-save"></i> Guardar Venta
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ===========================
     MODAL: Registrar Pago
=========================== -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="addPaymentForm">
        <div class="modal-header">
          <h5 class="modal-title" id="addPaymentLabel">Registrar Pago</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" id="payment_venta_id" name="venta_id">
          <p><strong>Venta:</strong> <span id="payment_sale_number"></span></p>
          <p><strong>Saldo Pendiente:</strong> <span id="payment_saldo_pendiente">$0.00</span></p>

          <div class="mb-3">
            <label class="form-label">Monto (Bs)</label>
            <input type="number" id="payment_monto" name="monto" class="form-control" min="0.01" step="0.01" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Método de Pago</label>
            <select id="payment_metodo" name="metodo_pago" class="form-select" required>
              <option value="efectivo">Efectivo</option>
              <option value="tarjeta_debito">Tarjeta Débito</option>
              <option value="tarjeta_credito">Tarjeta Crédito</option>
              <option value="transferencia">Transferencia</option>
              <option value="pago_movil">Pago Móvil</option>
              <option value="cheque">Cheque</option>
            </select>
          </div>

          <div class="mb-3" id="payment_ref_group" style="display:none;">
            <label class="form-label">Referencia</label>
            <input type="text" id="payment_referencia" name="referencia" class="form-control">
          </div>

          <div class="mb-3" id="payment_bank_group" style="display:none;">
            <label class="form-label">Banco</label>
            <input type="text" name="banco" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Observaciones</label>
            <textarea name="payment_observaciones" class="form-control" rows="2"></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">
            <i class="fas fa-money-bill"></i> Registrar Pago
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ===========================
     MODAL: Detalle de Venta
=========================== -->
<div class="modal fade" id="viewSaleModal" tabindex="-1" aria-labelledby="viewSaleLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewSaleLabel">Detalle de Venta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="saleDetailsContent">
        <p class="text-center text-muted">Cargando...</p>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="/BarkiOS/public/assets/js/sales-admin.js"></script>

</body>
</html>
