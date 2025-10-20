<?php

// ✅ Importa el controlador de login (para usar checkAuth)
require_once __DIR__ . '/LoginController.php';


// ✅ Protege todo el módulo
checkAuth();

function index() {
    require __DIR__ . '/../../views/admin/home-admin.php';
    
}