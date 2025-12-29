<?php
// Detecta la parte final de la URL
$current = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/inicio">
            <h1 class="m-0">GARAGE<span>BARKI</span></h1>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                  <a class="nav-link <?php echo ($current === 'inicio' || $current === '') ? 'active' : ''; ?>" href="/inicio">Inicio</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?php echo ($current === 'novedades') ? 'active' : ''; ?>" href="/novedades">Novedades</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?php echo ($current === 'productos') ? 'active' : ''; ?>" href="/productos">Productos</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?php echo ($current === 'nosotros') ? 'active' : ''; ?>" href="/nosotros">Nosotros</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?php echo ($current === 'ubicanos') ? 'active' : ''; ?>" href="/ubicanos">Ubícanos</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?php echo ($current === 'contacto') ? 'active' : ''; ?>" href="/contacto">Contacto</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?php echo ($current === 'ayuda') ? 'active' : ''; ?>" href="/ayuda">Ayuda</a>
                </li>
            </ul>
        </div>
    </div>
</nav>