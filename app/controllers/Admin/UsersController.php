<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\controllers\Admin\UserController.php

use Barkios\models\User;

// ✅ Importa el controlador de login (para usar checkAuth)
require_once __DIR__ . '/LoginController.php';

// ✅ Protege todo el módulo
checkAuth();

// ✅ Inicializa el modelo
$userModel = new User();

// =================================================================
// 🔹 Acción principal (vista)
// =================================================================
function index() {
    require __DIR__ . '/../../views/admin/users-admin.php';
}

// 🚀 Enrutamiento principal
handleRequest($userModel);

// =================================================================
// 🧭 Función principal de enrutamiento
// =================================================================
function handleRequest($userModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add_ajax':    handleAddEditAjax($userModel, 'add'); break;
                case 'POST_edit_ajax':   handleAddEditAjax($userModel, 'edit'); break;
                case 'POST_delete_ajax': handleDeleteAjax($userModel); break;
                case 'GET_get_users':    getUsersAjax($userModel); break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Acción AJAX inválida']);
                    exit();
            }
        } else {
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add':   handleAddEdit($userModel, 'add'); break;
                case 'POST_edit':  handleAddEdit($userModel, 'edit'); break;
                case 'GET_delete': handleDelete($userModel); break;
            }
        }
    } catch (Exception $e) {
        if ($isAjax) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } else {
            die("Error: " . $e->getMessage());
        }
        exit();
    }
}

// =================================================================
// 🧱 Funciones CRUD normales (no AJAX)
// =================================================================
function handleAddEdit($userModel, $mode) {
    $fields = ['nombre', 'email'];
    if ($mode === 'add') $fields[] = 'password';
    if ($mode === 'edit') $fields[] = 'id';

    foreach ($fields as $f) {
        if ($mode === 'edit' && $f === 'password' && empty($_POST[$f])) continue;
        if (empty($_POST[$f])) throw new Exception("El campo $f es requerido");
    }

    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? null;

    if ($mode === 'add') {
        if ($userModel->userExists(null, $email)) {
            header("Location: users-admin.php?error=email_duplicado&email=$email");
            exit();
        }
        $userModel->add($nombre, $email, $password);
        header("Location: users-admin.php?success=add");
        exit();
    } else {
        $userModel->update($id, $nombre, $email, $password);
        header("Location: users-admin.php?success=edit");
        exit();
    }
}

function handleDelete($userModel) {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) throw new Exception("ID inválido");
    $id = (int)$_GET['id'];
    $userModel->delete($id);
    header("Location: users-admin.php?success=delete");
    exit();
}

// =================================================================
// ⚡ Funciones AJAX
// =================================================================
function handleAddEditAjax($userModel, $mode) {
    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? null;

    if (empty($nombre) || empty($email)) throw new Exception("Nombre y email son requeridos");
    if ($mode === 'add' && empty($password)) throw new Exception("La contraseña es requerida");
    if ($mode === 'edit' && $id === 0) throw new Exception("ID de usuario inválido");

    if ($mode === 'add') {
        if ($userModel->userExists(null, $email)) throw new Exception("El email ya está registrado");
        $userModel->add($nombre, $email, $password);
        $msg = 'Usuario agregado';
    } else {
        $userModel->update($id, $nombre, $email, $password);
        $msg = 'Usuario actualizado';
    }

    $user = ['id' => $id, 'nombre' => $nombre, 'email' => $email];
    echo json_encode(['success' => true, 'message' => $msg, 'user' => $user]);
    exit();
}

function handleDeleteAjax($userModel) {
    if (empty($_POST['id']) || !is_numeric($_POST['id'])) throw new Exception("ID inválido");
    $id = (int)$_POST['id'];
    if (!$userModel->userExists($id)) throw new Exception("No existe el usuario");
    $userModel->delete($id);
    echo json_encode(['success' => true, 'message' => 'Usuario eliminado', 'userId' => $id]);
    exit();
}

function getUsersAjax($userModel) {
    if (isset($_GET['id'])) {
        $user = $userModel->getById((int)$_GET['id']);
        if (!$user) throw new Exception("Usuario no encontrado");
        echo json_encode(['success' => true, 'users' => [$user]]);
        exit();
    }

    $users = $userModel->getAll();
    echo json_encode(['success' => true, 'users' => $users, 'count' => count($users)]);
    exit();
}
