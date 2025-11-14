<?php $pageTitle = "Ayuda | Garage Barki"; ?>
<?php require_once __DIR__ . '/../partials/header-admin.php'; ?>
<?php require_once __DIR__ . '/../partials/navbar-admin.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
            <div>
                <h3 class="mb-1"><i class="fas fa-question-circle me-2 text-primary"></i>Centro de Ayuda</h3>
                <p class="text-muted mb-0 small">Manual de usuario y recursos del sistema</p>
            </div>
        </div>

        <!-- Tarjeta del Manual -->
        <div class="row g-4">
            <div class="col-lg-8 mx-auto">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="text-center mb-4">
                            <div class="mb-3">
                                <i class="fas fa-book fa-4x text-primary"></i>
                            </div>
                            <h4 class="mb-2">Manual de Usuario</h4>
                            <p class="text-muted">Sistema de Gestión de Ventas - Garage Barki</p>
                            <p class="text-muted small">Versión 1.0 - Octubre 2025</p>
                        </div>

                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Sobre este manual:</strong> Contiene instrucciones detalladas sobre el uso de todos los módulos del sistema, incluyendo capturas de pantalla y ejemplos prácticos.
                        </div>

                        <!-- Contenido del Manual -->
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="fas fa-list me-2"></i>Contenido</h5>
                            <div class="list-group">
                                <div class="list-group-item">
                                    <i class="fas fa-tachometer-alt text-primary me-2"></i>
                                    <strong>1. Inicio</strong> - Panel principal y métricas
                                </div>
                                <div class="list-group-item">
                                    <i class="fas fa-tshirt text-primary me-2"></i>
                                    <strong>2. Productos</strong> - Gestión de prendas e inventario
                                </div>
                                <div class="list-group-item">
                                    <i class="fas fa-truck text-primary me-2"></i>
                                    <strong>3. Proveedores</strong> - Registro y administración
                                </div>
                                <div class="list-group-item">
                                    <i class="fas fa-users text-primary me-2"></i>
                                    <strong>4. Clientes</strong> - Registro de datos de los clientes
                                </div>
                                <div class="list-group-item">
                                    <i class="fas fa-user-tie text-primary me-2"></i>
                                    <strong>5. Empleados</strong> - Gestión del personal
                                </div>
                                <div class="list-group-item">
                                    <i class="fas fa-user-shield text-primary me-2"></i>
                                    <strong>6. Usuarios</strong> - Control de acceso
                                </div>
                                <div class="list-group-item">
                                    <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                                    <strong>7. Cuentas por Cobrar</strong> - Seguimiento de créditos
                                </div>
                                <div class="list-group-item">
                                    <i class="fas fa-file-invoice text-primary me-2"></i>
                                    <strong>8. Cuentas por Pagar</strong> - Obligaciones pendientes
                                </div>
                                <div class="list-group-item">
                                    <i class="fas fa-receipt text-primary me-2"></i>
                                    <strong>9. Ventas</strong> - Registro y consulta
                                </div>
                                <div class="list-group-item">
                                    <i class="fas fa-shopping-bag text-primary me-2"></i>
                                    <strong>10. Compras</strong> - Adquisiciones a proveedores
                                </div>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="text-center">
                            <div class="d-grid gap-3 d-md-flex justify-content-md-center mb-3">
                                <a href="https://manual-de-usuario-barki-os.vercel.app/" 
                                   class="btn btn-primary btn-lg px-5" 
                                   target="_blank">
                                    <i class="fas fa-globe me-2"></i>Ver Manual Online
                                </a>
                                <a href="/BarkiOS/public/assets/pdf/Manual de Usuario - Garage Barki.pdf" 
                                   class="btn btn-outline-primary btn-lg px-5" 
                                   download>
                                    <i class="fas fa-download me-2"></i>Descargar PDF
                                </a>
                            </div>
                            <p class="text-muted small">Tamaño: 3.9 MB</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="/BarkiOS/public/assets/js/admin/logout.js"></script>

</body>
</html>