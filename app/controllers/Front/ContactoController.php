<?php
// app/controllers/front/InicioController.php

/**
 * Controlador de la página de inicio (Front)
 */
function index() {
    // Cargar la vista de inicio desde la carpeta public
    require __DIR__ . '/../../views/front/contacto.php';
}