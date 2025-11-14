<?php
/**
 * HelpController.php
 * Controlador simple para la página de ayuda
 */

require_once __DIR__ . '/LoginController.php';
checkAuth();

if (session_status() === PHP_SESSION_NONE) session_start();

// Verificar autenticación
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
    header("Location: /BarkiOS/login");
    exit();
}

// Mostrar la vista de ayuda
$paths = [
    __DIR__ . '/../../views/admin/help-admin.php',
    dirname(__DIR__, 2) . '/views/admin/help-admin.php'
];

foreach ($paths as $path) {
    if (file_exists($path)) {
        require $path;
        exit();
    }
}

die("Vista de ayuda no encontrada");