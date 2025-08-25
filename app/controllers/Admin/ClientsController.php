<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\controllers\Admin\ProductsController.php

use Barkios\models\Clients;
use Barkios\models\Product;
$clienttModel = new Clients();

function index() {
   return null;
}
handleRequest($clienttModel);

function handleRequest($clientModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add_ajax':    handleAddEditAjax($clientModel, 'add'); break;
                case 'POST_edit_ajax':   handleAddEditAjax($clientModel, 'edit'); break;
                case 'POST_delete_ajax': handleDeleteAjax($clientModel); break;
                case 'GET_get_clients': getClientsAjax($clientModel); break;
                default:                 echo json_encode(['success'=>false,'message'=>'Acción inválida']); exit();
            }
        } else {
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add':    handleAddEdit($clientModel, 'add'); break;
                case 'POST_edit':   handleAddEdit($clientModel, 'edit'); break;
                case 'GET_delete':  handleDelete($clientModel); break;
                default:            require __DIR__ . '/../../views/admin/clients-admin.php';
            }
        }
    } catch (Exception $e) {
        if ($isAjax) {
            http_response_code(500);
            echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
        } else {
            die("Error: " . $e->getMessage());
        }
        exit();
    }
}

function handleAddEdit($clientModel, $mode) {
    $fields = ['cedula','nombre','direccion','telefono','membresia'];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) throw new Exception("El campo $f es requerido");
    }
    $cedula = trim($_POST['cedula']);
    $nombre = trim($_POST['nombre']);
    $direccion = trim($_POST['direccion']);
    $telefono = trim($_POST['telefono']);
    $membresia = trim($_POST['membresia']); // <-- CORREGIDO

    if ($mode === 'add') {
        if ($clientModel->clientExists($cedula)) {
            header("Location: clients-admin.php?error=cedula_duplicada&cedula=$cedula"); exit();
        }
        $clientModel->add($cedula, $nombre, $direccion, $telefono, $membresia);
        header("Location: clients-admin.php?success=add"); exit();
    } else {
        $clientModel->update($cedula, $nombre, $direccion, $telefono, $membresia);
        header("Location: clients-admin.php?success=edit"); exit();
    }
}

function handleDelete($productModel) {
    if (!isset($_GET['cedula']) || !is_numeric($_GET['cedula'])) throw new Exception("cedula inválida");
    $productModel->delete((int)$_GET['cedula']);
    header("Location: clients-admin.php?success=delete"); exit();
}


function handleAddEditAjax($clientModel, $mode) {
    $fields = ['cedula','nombre','direccion','telefono','membresia'];
    $data = [];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) throw new Exception("El campo $f es requerido");
        $data[$f] = trim($_POST[$f]); // <-- Solo texto
    }
    if ($mode === 'add') {
        if ($clientModel->clientExists($data['cedula'])) throw new Exception("cedula duplicada");
        $clientModel->add(...array_values($data));
        $msg = 'Cliente agregado';
    } else {
        if (!$clientModel->clientExists($data['cedula'])) throw new Exception("No existe la cedula");
        $clientModel->update(...array_values($data));
        $msg = 'Cedula actualizada';
    }
    $client = $clientModel->getById($data['cedula']);
    echo json_encode(['success'=>true, 'message'=>$msg, 'client'=>$client]); exit();
}

function handleDeleteAjax($clientModel) {
    if (empty($_POST['cedula']) || !is_numeric($_POST['cedula'])) throw new Exception("Cedula inválida");
    $cedula = trim($_POST['cedula']);
    if (!$clientModel->clientExists($cedula)) throw new Exception("No existe el producto");
    $clientModel->delete($cedula);
    echo json_encode(['success'=>true, 'message'=>'Cliente eliminado', 'clientId'=>$cedula]); exit();
}

function getClientsAjax($clientModel) {
    if (isset($_GET['cedula'])) {
        $client = $clientModel->getById(trim($_GET['cedula']));
        if (!$client) throw new Exception("No existe el producto");
        echo json_encode(['success'=>true, 'products'=>[$client]]); exit();
    }
    $client = $clientModel->getAll();
    echo json_encode(['success'=>true, 'clients'=>$client, 'count'=>count($client)]); exit();
}