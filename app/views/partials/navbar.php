<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
    <div class="container">
        <a class="navbar-brand" href="../front/inicio.php">
            <h1 class="m-0">GARAGE<span>BARKI</span></h1>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active' : '' ?>" href="/BarkiOS/inicio">Inicio</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'news.php' ? 'active' : '' ?>" href="/BarkiOS/novedades">Novedades</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>" href="/BarkiOS/productos">Productos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>" href="/BarkiOS/nosotros">Nosotros</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'locate.php' ? 'active' : '' ?>" href="/BarkiOS/ubicanos">Ubícanos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>" href="/BarkiOS/contacto">Contacto</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'help.php' ? 'active' : '' ?>" href="/BarkiOS/ayuda">Ayuda</a>
                </li>
            </ul>
        </div>
    </div>
</nav>