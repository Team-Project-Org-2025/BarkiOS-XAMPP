<?php
use Barkios\models\Supplier;
$supplierModel = New Supplier();


handleRequest($supplierModel);
/**
 * index
 * 
 * Acción principal.
 * Muestra la vista de administración de proveedores.
 * 
 * Palabras clave: vista, administración, proveedores.
 * 
 * @return void
 */
function index() {
    require __DIR__ . '/../../views/admin/supplier-admin.php';
}

/**
 * handleRequest
 * 
 * Enrutador principal de solicitudes.
 * Determina el tipo de solicitud (AJAX o normal) y la acción a ejecutar.
 * 
 * Palabras clave: enrutamiento, AJAX, POST, GET, acción, logging, manejo de errores.
 * 
 * @param Supplier $supplierModel Instancia del modelo de proveedores.
 * @return void
 */
function handleRequest($supplierModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    
    // Logging de la solicitud recibida
    error_log("Solicitud recibida - Acción: $action, Método: " . $_SERVER['REQUEST_METHOD'] . ", AJAX: " . ($isAjax ? 'Sí' : 'No'));
    
    try {
        // Solicitudes AJAX
        if ($isAjax) {
            header('Content-Type: application/json');
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add_ajax') {
                error_log("Invocando handleAddSupplierAjax");
                handleAddSupplierAjax($supplierModel);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit_ajax') {
                error_log("Invocando handleEditSupplierAjax");
                handleEditSupplierAjax($supplierModel);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'delete_ajax') {
                error_log("Invocando handleDeleteSupplierAjax");
                handleDeleteSupplierAjax($supplierModel);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_suppliers') {
                error_log("Invocando getSuppliersAjax");
                getSuppliersAjax($supplierModel);
            } else {
                error_log("Acción AJAX no reconocida: $action");
                throw new Exception('Acción no válida');
            }
        } else {
            // Solicitudes normales (no AJAX)
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'add') {
                handleAddSupplier($supplierModel);
            } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'delete') {
                handleDeleteSupplier($supplierModel);
            }
        }
    } catch (Exception $e) {
        // Manejo global de errores
        $errorMsg = 'Error en handleRequest: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine();
        error_log($errorMsg);
        
        if ($isAjax) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => 'Error en el servidor: ' . $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
            exit();
        } else {
            die("Error: " . $e->getMessage());
        }
    }
}

/**
 * getSupplierr
 * 
 * Devuelve todos los proveedores (para uso interno y vistas).
 * 
 * Palabras clave: consulta, listado, proveedores.
 * 
 * @param Supplier $supplierModel Instancia del modelo de proveedores.
 * @return array
 */
function getSupplierr($supplierModel) {
    return $supplierModel->getAll();
}

/**
 * handleAddSupplier
 * 
 * Maneja la adición de un proveedor desde formulario regular.
 * Valida los datos recibidos, verifica duplicados y agrega el proveedor.
 * Redirige según el resultado.
 * 
 * Palabras clave: agregar, validación, duplicados, redirección.
 * 
 * @param Supplier $supplierModel Instancia del modelo de proveedores.
 * @throws Exception Si falta algún campo o hay duplicado.
 * @return void
 */
function handleAddSupplier($supplierModel) {
    $required = ['proveedor_rif', 'nombre_contacto', 'nombre_empresa', 'direccion', 'tipo_rif'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }
    $rif = trim($_POST['proveedor_rif']);
    $nombre_contacto = htmlspecialchars(trim($_POST['nombre_contacto']));
    $nombre_empresa = htmlspecialchars(trim($_POST['nombre_empresa']));
    $direccion = htmlspecialchars(trim($_POST['direccion']));
    $tipo_rif = htmlspecialchars(trim($_POST['tipo_rif']));
    // Verifica duplicados
    if ($supplierModel->supplierExists($rif)) {
        header("Location: supplier-admin.php?error=rif_duplicado&rif=" . urlencode($rif));
        exit();
    }
    // Inserta proveedor
    $success = $supplierModel->add($rif, $nombre_contacto, $nombre_empresa, $direccion, $tipo_rif);
    if ($success) {
        header("Location: supplier-admin.php?success=add");
        exit();
    }
}

/**
 * handleDeleteSupplier
 * 
 * Maneja la eliminación de un proveedor por GET.
 * Valida el ID recibido y elimina el proveedor si existe.
 * Redirige según el resultado.
 * 
 * Palabras clave: eliminar, validación, redirección, proveedor.
 * 
 * @param Supplier $supplierModel Instancia del modelo de proveedores.
 * @throws Exception Si el ID es inválido.
 * @return void
 */
function handleDeleteSupplier($supplierModel) {
    if (!isset($_GET['proveedor_rif'])) {
        throw new Exception("ID de proveedor inválido");
    }
    $rif = trim($_GET['proveedor_rif']);
    $success = $supplierModel->delete($rif);
    if ($success) {
        header('Location: supplier-admin.php?success=delete');
        exit();
    }
}
/**
 * handleAddSupplierAjax
 * 
 * Maneja la adición de proveedor vía AJAX.
 * Valida campos y responde en JSON.
 * 
 * Palabras clave: AJAX, agregar, validación, JSON, proveedor.
 * 
 * @param Supplier $supplierModel Instancia del modelo de proveedores.
 * @return void
 */
function handleAddSupplierAjax($supplierModel) {
    try {
        $required = ['proveedor_rif', 'nombre_contacto', 'nombre_empresa', 'direccion', 'tipo_rif'];
        $data = [];
        
        // Validar campos requeridos
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo $field es requerido");
            }
            $data[$field] = trim($_POST[$field]);
        }
        // Validar formato del RIF
        $rif = $data['proveedor_rif'];
        if (strlen($rif) !== 9) {
            throw new Exception("El RIF debe tener exactamente 9 caracteres");
        }
        
        // Verificar duplicado
        if ($supplierModel->supplierExists($rif)) {
            throw new Exception('El RIF ingresado ya está registrado.');
        }
        // Agregar proveedor
        $result = $supplierModel->add(
            $rif,
            $data['nombre_contacto'],
            $data['nombre_empresa'],
            $data['direccion'],
            $data['tipo_rif']
        );
        if ($result === false) {
            throw new Exception('No se pudo agregar el proveedor. Inténtalo de nuevo.');
        }
        echo json_encode([
            'success' => true,
            'message' => 'Proveedor agregado correctamente'
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}


function handleDeleteSupplierAjax($supplierModel) {
    try {
        if (!isset($_POST['proveedor_rif']) || strlen(trim($_POST['proveedor_rif'])) !== 9) {
            throw new Exception('RIF de proveedor inválido');
        }
        $rif = trim($_POST['proveedor_rif']);
        // Verificar existencia
        if (!$supplierModel->supplierExists($rif)) {
            throw new Exception('El proveedor que intentas eliminar no existe');
        }
        $success = $supplierModel->delete($rif);
        if (!$success) {
            throw new Exception('No se pudo eliminar el proveedor. Inténtalo de nuevo.');
        }
        echo json_encode([
            'success' => true,
            'message' => 'Proveedor eliminado correctamente'
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}


function getSuppliersAjax($supplierModel) {
     try {
         $suppliers = $supplierModel->getAll();
         if ($suppliers === false) {
             throw new Exception('Error al cargar los proveedores');
         }
         echo json_encode(['success'=>true, 'suppliers'=>$suppliers, 'count'=>count($suppliers)]); exit();
     } catch (Exception $e) {
         http_response_code(500);
         echo json_encode([
             'success' => false,
             'message' => $e->getMessage()
         ]);
     }
     exit();
}

function handleEditSupplierAjax($supplierModel) {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $required = ['proveedor_rif', 'nombre_contacto', 'nombre_empresa', 'direccion', 'tipo_rif'];
        $data = [];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo $field es requerido");
            }
            $data[$field] = trim($_POST[$field]);
        }
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

        if ($success) {
            $supplier = $supplierModel->getById($rif);
            echo json_encode([
                'success' => true,
                'message' => 'Proveedor actualizado correctamente',
                'supplier' => $supplier
            ]);
        } else {
            throw new Exception("Error al actualizar el proveedor");
        }
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}