<?php
// filepath: c:\xampp\htdocs\BarkiOS\app\controllers\Admin\EmployeesController.php
use Barkios\models\Employees;

// ✅ Importa el controlador de login (para usar checkAuth)
require_once __DIR__ . '/LoginController.php';

// ✅ Protege todo el módulo
checkAuth();

$employeeModel = new Employees();

function index() {
   return null;
}
handleRequest($employeeModel);

function handleRequest($employeeModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add_ajax':    handleAddEditAjax($employeeModel, 'add'); break;
                case 'POST_edit_ajax':   handleAddEditAjax($employeeModel, 'edit'); break;
                case 'POST_delete_ajax': handleDeleteAjax($employeeModel); break;
                case 'GET_get_employees': getEmployeesAjax($employeeModel); break;
                case 'GET_search_employees': searchEmployeesAjax($employeeModel); break;
                default:                 echo json_encode(['success'=>false,'message'=>'Acción inválida']); exit();
            }
        } else {
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add':    handleAddEdit($employeeModel, 'add'); break;
                case 'POST_edit':   handleAddEdit($employeeModel, 'edit'); break;
                case 'GET_delete':  handleDelete($employeeModel); break;
                default:            require __DIR__ . '/../../views/admin/employees-admin.php';
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

function handleAddEdit($employeeModel, $mode) {
    $fields = ['cedula','nombre','telefono'];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) throw new Exception("El campo $f es requerido");
    }
    
    $cedula = trim($_POST['cedula']);
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $cargo = trim($_POST['cargo'] ?? 'Empleado');

    if ($mode === 'add') {
        if ($employeeModel->employeeExists($cedula)) {
            header("Location: employees-admin.php?error=cedula_duplicada&cedula=$cedula"); exit();
        }
        $employeeModel->add($cedula, $nombre, $telefono, $cargo);
        header("Location: employees-admin.php?success=add"); exit();
    } else {
        $employeeModel->update($cedula, $nombre, $telefono, $cargo);
        header("Location: employees-admin.php?success=edit"); exit();
    }
}

function handleDelete($employeeModel) {
    if (!isset($_GET['cedula']) || !is_numeric($_GET['cedula'])) throw new Exception("Cédula inválida");
    $employeeModel->delete((int)$_GET['cedula']);
    header("Location: employees-admin.php?success=delete"); exit();
}

function handleAddEditAjax($employeeModel, $mode) {
    $fields = ['cedula','nombre','telefono'];
    $data = [];
    
    foreach ($fields as $f) {
        if (empty($_POST[$f])) throw new Exception("El campo $f es requerido");
        $data[$f] = trim($_POST[$f]);
    }
    
    // Cargo es opcional, por defecto 'Empleado'
    $data['cargo'] = trim($_POST['cargo'] ?? 'Empleado');
    
    if ($mode === 'add') {
        if ($employeeModel->employeeExists($data['cedula'])) throw new Exception("Cédula duplicada");
        $employeeModel->add($data['cedula'], $data['nombre'], $data['telefono'], $data['cargo']);
        $msg = 'Empleado agregado';
    } else {
        if (!$employeeModel->employeeExists($data['cedula'])) throw new Exception("No existe la cédula");
        $employeeModel->update($data['cedula'], $data['nombre'], $data['telefono'], $data['cargo']);
        $msg = 'Empleado actualizado';
    }
    
    $employee = $employeeModel->getById($data['cedula']);
    echo json_encode(['success'=>true, 'message'=>$msg, 'employee'=>$employee]); exit();
}

function handleDeleteAjax($employeeModel) {
    if (empty($_POST['cedula']) || !is_numeric($_POST['cedula'])) throw new Exception("Cédula inválida");
    $cedula = trim($_POST['cedula']);
    if (!$employeeModel->employeeExists($cedula)) throw new Exception("No existe el empleado");
    $employeeModel->delete($cedula);
    echo json_encode(['success'=>true, 'message'=>'Empleado eliminado', 'employeeId'=>$cedula]); exit();
}

function getEmployeesAjax($employeeModel) {
    if (isset($_GET['cedula'])) {
        $employee = $employeeModel->getById(trim($_GET['cedula']));
        if (!$employee) throw new Exception("No existe el empleado");
        echo json_encode(['success'=>true, 'employees'=>[$employee]]); exit();
    }
    $employees = $employeeModel->getAll();
    echo json_encode(['success'=>true, 'employees'=>$employees, 'count'=>count($employees)]); exit();
}

function searchEmployeesAjax($employeeModel) {
    $query = trim($_GET['q'] ?? '');
    
    if (strlen($query) < 1) {
        echo json_encode(['success'=>false, 'message'=>'Consulta muy corta', 'employees'=>[]]); 
        exit();
    }
    
    $employees = $employeeModel->searchEmployees($query);
    echo json_encode(['success'=>true, 'employees'=>$employees, 'count'=>count($employees)]); 
    exit();
}