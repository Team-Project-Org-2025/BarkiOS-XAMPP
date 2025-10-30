<?php
// Detecta la parte final de la URL
$current = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// Quita la carpeta base si existe (por ejemplo "BarkiOS/")
$current = str_replace('BarkiOS/', '', $current);
?>

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
                  <a class="nav-link <?= $current == 'inicio' ? 'active' : '' ?>" href="/BarkiOS/inicio">Inicio</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $current == 'novedades' ? 'active' : '' ?>" href="/BarkiOS/novedades">Novedades</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $current == 'productos' ? 'active' : '' ?>" href="/BarkiOS/productos">Productos</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $current == 'nosotros' ? 'active' : '' ?>" href="/BarkiOS/nosotros">Nosotros</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $current == 'ubicanos' ? 'active' : '' ?>" href="/BarkiOS/ubicanos">Ubícanos</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $current == 'contacto' ? 'active' : '' ?>" href="/BarkiOS/contacto">Contacto</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $current == 'ayuda' ? 'active' : '' ?>" href="/BarkiOS/ayuda">Ayuda</a>
                </li>
            </ul>
        </div>
    </div>
</nav>