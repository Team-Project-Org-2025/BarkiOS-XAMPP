<?php
// app/controllers/admin/LoginController.php

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
// 🔒 MIDDLEWARE DE AUTENTICACIÓN
// ===============================
function checkAuth() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id'])) {
        // ✅ CORREGIDO: Redirige al login del área admin
        header('Location: /BarkiOS/admin/login/show');
        exit();
    }
}

// ===============================
// [GET] Mostrar formulario de login
// ===============================
function show() {
    if (isset($_SESSION['user_id'])) {
        // ✅ CORREGIDO: Si ya tiene sesión, ir al dashboard
        header('Location: /BarkiOS/admin/login/dashboard');
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
        header('Location: /BarkiOS/admin/login/show');
        exit();
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $error = null;

    // ===============================
    // 🧹 Validaciones básicas
    // ===============================
    if ($email === '' || $password === '') {
        $error = "Por favor, complete todos los campos.";
        require_once ROOT_PATH . 'app/views/admin/login.php';
        return;
    }

    // ===============================
    // 🧩 Validación por expresiones regulares
    // ===============================
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $error = "El correo electrónico no tiene un formato válido.";
        require_once ROOT_PATH . 'app/views/admin/login.php';
        return;
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&._-])[A-Za-z\d@$!%*?&._-]{8,}$/', $password)) {
        $error = "La contraseña debe tener al menos 8 caracteres, con mayúsculas, minúsculas, números y símbolos.";
        require_once ROOT_PATH . 'app/views/admin/login.php';
        return;
    }

    // ===============================
    // 🔐 Autenticación real
    // ===============================
    $user = $userModel->authenticate($email, $password);

    if ($user) {
        $_SESSION['user_id'] = $user['id'] ?? $user['user_id'] ?? null;
        $_SESSION['user_email'] = $user['email'] ?? $user['user_email'] ?? null;
        $_SESSION['user_nombre'] = $user['nombre'] ?? $user['user_nombre'] ?? null;
        $_SESSION['is_admin'] = true; // ✅ Marcar como usuario administrador

        // ✅ CORREGIDO: Redirige al dashboard del admin
        header('Location: /BarkiOS/admin/login/dashboard');
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
// [GET] Logout normal (con redirección)
// ===============================
function logout() {
    session_unset();
    session_destroy();
    
    // ✅ CORREGIDO: Redirige al login del admin
    header('Location: /BarkiOS/admin/login/show');
    exit();
}

// ===============================
// [POST] Logout AJAX
// ===============================
function logout_ajax() {
    // Solo aceptamos peticiones AJAX
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['success' => false, 'message' => 'Petición inválida']);
        exit();
    }

    // Destruir la sesión de forma segura
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    session_unset();
    session_destroy();

    // Respuesta JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Sesión cerrada correctamente']);
    exit();
}

// ===============================
// [GET] Verificar sesión activa (AJAX)
// ===============================
function check_session() {
    if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('HTTP/1.1 400 Bad Request');
        exit();
    }

    header('Content-Type: application/json');
    
    $active = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    
    echo json_encode([
        'active' => $active,
        'user_id' => $_SESSION['user_id'] ?? null,
        'user_email' => $_SESSION['user_email'] ?? null,
        'user_nombre' => $_SESSION['user_nombre'] ?? null
    ]);
    exit();
}

