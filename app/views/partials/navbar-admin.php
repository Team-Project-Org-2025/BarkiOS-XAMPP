<nav class="sidebar h-100" id="sidebar">
    <div class="d-flex flex-column h-100">
        
        <!-- HEADER (Fijo) -->
        <div class="sidebar-header">
            <h3><span>BARKIOS</span></h3>
            <p class="mb-0">Panel de Administración</p>
        </div>
        
        <!-- CONTENIDO DEL MENÚ (Scrollable) -->
        <div class="sidebar-sticky flex-grow-1 overflow-auto">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?php echo isActiveRoute('admin/login/dashboard') ? 'active' : ''; ?>" 
                       href="/BarkiOS/admin/login/dashboard">
                        <i class="fas fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActiveRoute('admin/products') ? 'active' : ''; ?>" 
                       href="/BarkiOS/admin/products">
                        <i class="fas fa-tshirt"></i>
                        Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActiveRoute('admin/supplier') ? 'active' : ''; ?>" 
                       href="/BarkiOS/admin/supplier">
                        <i class="fas fa-truck"></i>
                        Proveedores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActiveRoute('admin/clients') ? 'active' : ''; ?>" 
                       href="/BarkiOS/admin/clients">
                        <i class="fas fa-users"></i>
                        Clientes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActiveRoute('admin/users') ? 'active' : ''; ?>" 
                       href="/BarkiOS/admin/users">
                        <i class="fas fa-user-shield"></i>
                        Usuarios
                    </a>
                </li>
                
                <!-- Sección de Finanzas -->
                <li class="nav-item mt-3">
                    <span class="nav-section-title text-muted px-3 small fw-bold">
                        FINANZAS
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActiveRoute('admin/accounts-receivable') ? 'active' : ''; ?>" 
                       href="/BarkiOS/admin/accounts-receivable">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Cuentas por Cobrar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActiveRoute('admin/accounts-payable') ? 'active' : ''; ?>" 
                       href="/BarkiOS/admin/accounts-payable">
                        <i class="fas fa-file-invoice"></i>
                        Cuentas por Pagar
                    </a>
                </li>
                
                <!-- Sección de Operaciones -->
                <li class="nav-item mt-3">
                    <span class="nav-section-title text-muted px-3 small fw-bold">
                        OPERACIONES
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActiveRoute('admin/sale') ? 'active' : ''; ?>" 
                       href="/BarkiOS/admin/sale">
                        <i class="fas fa-receipt"></i>
                        Ventas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo isActiveRoute('admin/purchase') ? 'active' : ''; ?>" 
                       href="/BarkiOS/admin/purchase">
                        <i class="fas fa-shopping-bag"></i>
                        Compras
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- FOOTER (Fijo: Usuario y Logout) -->
        <div class="sidebar-footer border-top mt-auto">
            <?php if (isset($_SESSION['user_nombre'])): ?>
            <div class="px-3 py-2 text-muted small border-bottom">
                <i class="fas fa-user-circle me-2"></i>
                <span><?php echo htmlspecialchars($_SESSION['user_nombre']); ?></span>
            </div>
            <?php endif; ?>
            
            <a class="nav-link mx-3 mb-2 mt-2 rounded logout-link" 
               href="/BarkiOS/admin/login/logout" 
               id="logoutBtn">
                <i class="fas fa-sign-out-alt me-2"></i>
                Cerrar Sesión
            </a>
        </div>
        
    </div>
</nav>

<?php
/**
 * Función helper para determinar si una ruta está activa
 */
function isActiveRoute($route) {
    $currentUri = $_SERVER['REQUEST_URI'] ?? '';
    $currentUri = parse_url($currentUri, PHP_URL_PATH);
    $currentUri = str_replace('/BarkiOS/', '', $currentUri);
    $currentUri = rtrim($currentUri, '/');
    $route = rtrim($route, '/');
    
    // Comparación exacta o coincidencia de inicio
    return $currentUri === $route || strpos($currentUri, $route) === 0;
}
?>
