<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\controllers\Admin\UserController.php

use Barkios\models\User;

// ✅ Importa el controlador de login (para usar checkAuth)
require_once __DIR__ . '/LoginController.php';

// ✅ Protege todo el módulo
checkAuth();

// ✅ Inicializa el modelo
// NOTA: Asumo que la clase User está correctamente configurada en tu sistema de autoloading/namespacing.
$userModel = new User();

// =================================================================
// 🔹 Acción principal (vista)
// =================================================================
function index() {
    // Esta función solo carga la plantilla de la vista
    require __DIR__ . '/../../views/admin/users-admin.php';
}

// 🚀 Enrutamiento principal (DEBE IR AL FINAL PARA QUE LAS FUNCIONES ESTÉN DISPONIBLES)
handleRequest($userModel);
// Si handleRequest procesa un POST o un AJAX, saldrá con exit().
// Si no, la ejecución continuará y caerá en el caso por defecto (index) si no se encontró una acción.


// =================================================================
// 🧭 Función principal de enrutamiento
// =================================================================
function handleRequest($userModel) {
    // Limpiamos la acción
    $action = $_GET['action'] ?? '';
    
    // Verificamos si es una solicitud AJAX
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            // ==================
            // CASOS AJAX
            // ==================
            header('Content-Type: application/json; charset=utf-8');
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add_ajax':    handleAddEditAjax($userModel, 'add'); break;
                case 'POST_edit_ajax':   handleAddEditAjax($userModel, 'edit'); break;
                case 'POST_delete_ajax': handleDeleteAjax($userModel); break;
                
                // Caso para cargar la tabla, solicitado por JS
                case 'GET_get_users':    getUsersAjax($userModel); break;
                
                default:
                    echo json_encode(['success' => false, 'message' => 'Acción AJAX inválida']);
                    http_response_code(400); // Bad Request
                    exit();
            }
        } else {
            // ==================
            // CASOS NORMALES (POST/GET con acción)
            // ==================
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add':   handleAddEdit($userModel, 'add'); break;
                case 'POST_edit':  handleAddEdit($userModel, 'edit'); break;
                case 'GET_delete': handleDelete($userModel); break;
                
                // 🔥 SOLUCIÓN: Si es GET y no hay acción, o la acción no fue manejada,
                // forzamos la carga de la vista principal.
                default:
            }
        }
    } catch (Exception $e) {
        if ($isAjax) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
        } else {
            // Redirigir o mostrar un error en la vista normal
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
        // Excepción: en edición, la contraseña puede ir vacía
        if ($mode === 'edit' && $f === 'password' && empty($_POST[$f])) continue;
        if (empty($_POST[$f])) throw new Exception("El campo '$f' es requerido");
    }

    $id = (int)($_POST['id'] ?? 0);
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'] ?? null;

    if ($mode === 'add') {
        if ($userModel->userExists(null, $email)) {
            // Usar una sesión flash o una variable GET para mostrar el error en la vista
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
        // Si la inserción fue exitosa, obtenemos el ID del último insertado si es posible.
        // Aquí simulamos un objeto de usuario simple
        $user = ['id' => $userModel->getLastInsertId() ?? 0, 'nombre' => $nombre, 'email' => $email];
        $msg = 'Usuario agregado';
    } else {
        $userModel->update($id, $nombre, $email, $password);
        $user = ['id' => $id, 'nombre' => $nombre, 'email' => $email];
        $msg = 'Usuario actualizado';
    }

    // Nota: El objeto 'user' devuelto en add/edit AJAX no es el objeto completo de la base de datos, 
    // pero es suficiente ya que el JS llama a AjaxUsers() para recargar la tabla.
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
