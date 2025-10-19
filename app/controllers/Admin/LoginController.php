<?php

use Barkios\models\User;


// ===============================
// 🔧 CONFIGURACIÓN INICIAL
// ===============================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__, 3) . '/');
}

// ⚠️ Importante: declarar $userModel en el ámbito global real
$GLOBALS['userModel'] = new User();

// ===============================
// 🔐 MIDDLEWARE DE AUTENTICACIÓN
// ===============================
function checkAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        header('Location: /BarkiOS/login/show');
        exit();
    }
}

// ===============================
// [GET] Mostrar formulario de login
// ===============================
function show() {
    if (isset($_SESSION['user_id'])) {
        header('Location: /BarkiOS/admin');
        exit();
    }

    $error = null;
    require_once ROOT_PATH . 'app/views/admin/login.php';
}

// ===============================
// [POST] Procesar login
// ===============================
function login() {
    $userModel = $GLOBALS['userModel']; // ✅ acceso seguro a la instancia

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: /BarkiOS/login/show');
        exit();
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $error = null;

    if ($email === '' || $password === '') {
        $error = "Por favor, complete todos los campos.";
        require_once ROOT_PATH . 'app/views/admin/login.php';
        return;
    }

    $user = $userModel->authenticate($email, $password);

    if ($user) {
        $_SESSION['user_id'] = $user['id'] ?? $user['user_id'] ?? null;
        $_SESSION['user_email'] = $user['email'] ?? $user['user_email'] ?? null;
        $_SESSION['user_nombre'] = $user['nombre'] ?? $user['user_nombre'] ?? null;

        header('Location: /BarkiOS/admin');
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
        require_once ROOT_PATH . 'app/views/admin/login.php';
    }
}

// ===============================
// [GET] Dashboard protegido
// ===============================
function dashboard() {
    checkAuth();
    require_once ROOT_PATH . 'app/views/admin/home-admin.php';
}

// ===============================
// [GET] Logout
// ===============================
function logout() {
    session_unset();
    session_destroy();
    header('Location: /BarkiOS/login/show');
    exit();
}
