<nav class="sidebar h-100" id="sidebar">
    <div class="d-flex flex-column h-100"> <!-- Nuevo contenedor flexible -->
        
        <!-- HEADER (Fijo) -->
        <div class="sidebar-header">
            <h3><span>BARKIOS</span></h3>
            <p class="mb-0">Panel de Administración</p>
        </div>
        
        <!-- CONTENIDO DEL MENÚ (Scrollable) -->
        <div class="sidebar-sticky flex-grow-1 overflow-auto">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="/BarkiOS/home/">
                        <i class="fas fa-tachometer-alt"></i>
                        Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/BarkiOS/products/">
                        <i class="fas fa-tshirt"></i>
                        Productos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/BarkiOS/supplier/">
                        <i class="fas fa-shopping-cart"></i>
                        Proveedores
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/BarkiOS/clients/">
                        <i class="fas fa-users"></i>
                        Clientes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/BarkiOS/users">
                        <i class="fas fa-user-shield"></i>
                        Usuarios
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/BarkiOS/accounts-receivable/">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Cuentas por Cobrar
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/BarkiOS/accounts-payable/">
                        <i class="fas fa-file-invoice"></i>
                        Cuentas por Pagar
                    </a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="/BarkiOS/sale/">
                    <i class="fas fa-receipt"></i>
                    Notas de Crédito
                </a>
            </li>
                <li class="nav-item">
                    <a class="nav-link" href="/BarkiOS/purchase/">
                        <i class="fas fa-shopping-bag"></i>
                        Compras
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- FOOTER (Fijo: Logout) -->
        <div class="sidebar-footer border-top mt-auto">
            <a class="nav-link mx-3 mb-2 rounded logout-link" 
            href="/BarkiOS/login/logout">
                <i class="fas fa-sign-out-alt me-2"></i>
                Cerrar Sesión
            </a>
        </div>
        
    </div>
</nav>
