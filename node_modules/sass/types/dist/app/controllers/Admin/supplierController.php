<?php
use Barkios\models\Supplier;
use Barkios\helpers\Validation;

require_once __DIR__ . '/LoginController.php';
checkAuth();

$supplierModel = new Supplier();
handleRequest($supplierModel);

function index() {
    require __DIR__ . '/../../views/admin/supplier-admin.php';
}

// ============================================
// CORE REQUEST HANDLER
// ============================================

function handleRequest($supplierModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    try {
        if ($isAjax) {
            header('Content-Type: application/json');
            
            $routes = [
                'POST_add_ajax' => fn() => handleAddSupplierAjax($supplierModel),
                'POST_edit_ajax' => fn() => handleEditSupplierAjax($supplierModel),
                'POST_delete_ajax' => fn() => handleDeleteSupplierAjax($supplierModel),
                'GET_get_suppliers' => fn() => getSuppliersAjax($supplierModel)
            ];

            $route = "{$_SERVER['REQUEST_METHOD']}_$action";
            
            if (isset($routes[$route])) {
                $routes[$route]();
            } else {
                jsonResponse(['success' => false, 'message' => 'Acción no válida'], 400);
            }
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
                handleAddSupplier($supplierModel);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
                handleDeleteSupplier($supplierModel);
            }
        }
    } catch (Exception $e) {
        handleError($e, $isAjax);
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

function handleError($e, $isAjax) {
    $errorMsg = 'Error en handleRequest: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine();
    error_log($errorMsg);
    
    if ($isAjax) {
        jsonResponse([
            'success' => false, 
            'message' => 'Error en el servidor: ' . $e->getMessage()
        ], 500);
    } else {
        die("Error: " . $e->getMessage());
    }
}

// ============================================
// VALIDATION
// ============================================

function validateSupplierData($data) {
    $rules = [
        'proveedor_rif' => 'rif',
        'nombre_contacto' => 'nombre',
        'nombre_empresa' => 'nombre',
        'direccion' => 'direccion',
        'tipo_rif' => 'tipo_rif'
    ];
    
    $validation = Validation::validate($data, $rules);
    
    if (!$validation['valid']) {
        throw new Exception(implode(', ', $validation['errors']));
    }
}

// ============================================
// NON-AJAX HANDLERS
// ============================================

function handleAddSupplier($supplierModel) {
    try {
        validateSupplierData($_POST);
        
        $data = Validation::sanitize($_POST);
        
        if ($supplierModel->supplierExists($data['proveedor_rif'])) {
            header("Location: supplier-admin.php?error=rif_duplicado&rif=" . urlencode($data['proveedor_rif']));
            exit();
        }
        
        $success = $supplierModel->add(
            $data['proveedor_rif'],
            $data['nombre_contacto'],
            $data['nombre_empresa'],
            $data['direccion'],
            $data['tipo_rif']
        );
        
        if ($success) {
            header("Location: supplier-admin.php?success=add");
            exit();
        }
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

function handleDeleteSupplier($supplierModel) {
    try {
        if (!isset($_GET['proveedor_rif'])) {
            throw new Exception("ID de proveedor inválido");
        }
        
        $rif = trim($_GET['proveedor_rif']);
        $success = $supplierModel->delete($rif);
        
        if ($success) {
            header('Location: supplier-admin.php?success=delete');
            exit();
        }
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

// ============================================
// AJAX HANDLERS
// ============================================

function handleAddSupplierAjax($supplierModel) {
    try {
        validateSupplierData($_POST);
        
        $data = Validation::sanitize($_POST);
        
        if ($supplierModel->supplierExists($data['proveedor_rif'])) {
            throw new Exception('El RIF ingresado ya está registrado.');
        }
        
        $result = $supplierModel->add(
            $data['proveedor_rif'],
            $data['nombre_contacto'],
            $data['nombre_empresa'],
            $data['direccion'],
            $data['tipo_rif']
        );
        
        if ($result === false) {
            throw new Exception('No se pudo agregar el proveedor.');
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'Proveedor agregado correctamente'
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

function handleEditSupplierAjax($supplierModel) {
    try {
        validateSupplierData($_POST);
        
        $data = Validation::sanitize($_POST);
        $rif = $data['proveedor_rif'];
        
        if (!$supplierModel->supplierExists($rif)) {
            throw new Exception("El proveedor no existe");
        }
        
        $success = $supplierModel->update(
            $rif,
            $data['nombre_contacto'],
            $data['nombre_empresa'],
            $data['direccion'],
            $data['tipo_rif']
        );

        if (!$success) {
            throw new Exception("Error al actualizar el proveedor");
        }

        $supplier = $supplierModel->getById($rif);
        jsonResponse([
            'success' => true,
            'message' => 'Proveedor actualizado correctamente',
            'supplier' => $supplier
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

function handleDeleteSupplierAjax($supplierModel) {
    try {
        if (!isset($_POST['proveedor_rif']) || strlen(trim($_POST['proveedor_rif'])) !== 9) {
            throw new Exception('RIF de proveedor inválido');
        }
        
        $rif = trim($_POST['proveedor_rif']);
        
        if (!$supplierModel->supplierExists($rif)) {
            throw new Exception('El proveedor que intentas eliminar no existe');
        }
        
        $success = $supplierModel->delete($rif);
        
        if (!$success) {
            throw new Exception('No se pudo eliminar el proveedor. Inténtalo de nuevo.');
        }
        
        jsonResponse([
            'success' => true,
            'message' => 'Proveedor eliminado correctamente'
        ]);
        
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 400);
    }
}

function getSuppliersAjax($supplierModel) {
    try {
        $suppliers = $supplierModel->getAll();
        
        if ($suppliers === false) {
            throw new Exception('Error al cargar los proveedores');
        }
        
        jsonResponse([
            'success' => true, 
            'suppliers' => $suppliers, 
            'count' => count($suppliers)
        ]);
    } catch (Exception $e) {
        jsonResponse(['success' => false, 'message' => $e->getMessage()], 500);
    }
}

function getSupplierr($supplierModel) {
    return $supplierModel->getAll();
}