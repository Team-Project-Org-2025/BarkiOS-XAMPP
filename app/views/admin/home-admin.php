<?php $pageTitle = "Inicio | Garage Barki"; ?>
<?= require_once __DIR__ . '/../partials/header-admin.php'; ?>
<!-- Barra lateral de navegación -->
<?= require_once __DIR__ . '/../partials/navbar-admin.php'; ?> 

<div class="main-content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">Inicio</h1>
        </div>
    </div>
</div>

<div class="main-content">
    <div class="container-fluid">
        <!-- Encabezado -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold text-dark">Bienvenido al Panel de Control</h1>
        </div>

        <!-- Tarjeta de bienvenida -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-5 text-center">
                <div class="mb-4">
                    <i class="fas fa-tachometer-alt fa-4x text-primary mb-3"></i>
                    <h2 class="fw-bold">BarkiOS</h2>
                    <p class="lead text-muted">Panel de Administración</p>
                </div>

                <div class="alert alert-info bg-light border-0">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x text-info me-3"></i>
                        <div>
                            <h5 class="alert-heading mb-2" style="text-align: left;">¿Cómo empezar?</h5>
                            <p class="mb-0">Selecciona una opción del menú lateral para gestionar diferentes áreas de tu negocio.</p>
                        </div>
                    </div>
                </div>

                <!-- Secciones rápidas -->
                <div class="row mt-5">
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-body">
                                <i class="fas fa-tshirt fa-2x text-primary mb-3"></i>
                                <h5>Productos</h5>
                                <p class="text-muted small">Gestiona tu catálogo de productos</p>
                                <a href="/BarkiOS/products/" class="btn btn-sm btn-outline-primary mt-2">Ir a Productos</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-body">
                                <i class="fas fa-shopping-cart fa-2x text-primary mb-3"></i>
                                <h5>Proveedores</h5>
                                <p class="text-muted small">Proveedores</p>
                                <a href="/BarkiOS/supplier/" class="btn btn-sm btn-outline-primary mt-2">Ir a proveedores</a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 border-0 bg-light">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x text-primary mb-3"></i>
                                <h5>Clientes</h5>
                                <p class="text-muted small">Administra tu base de clientes</p>
                                <a href="/BarkiOS/clients/" class="btn btn-sm btn-outline-primary mt-2">Ir a Clientes</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 para alertas bonitas -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>