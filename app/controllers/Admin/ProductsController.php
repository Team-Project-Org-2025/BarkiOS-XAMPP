<?php
// app/controllers/admin/ProductsController.php
use Barkios\models\Product;
use Barkios\helpers\ImageUploader;

// Habilitar logs de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/product_errors.log');

// Función de log personalizada
function logDebug($message, $data = null) {
    $logFile = __DIR__ . '/../../logs/product_debug.log';
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message";
    if ($data !== null) {
        $logMessage .= " | Data: " . print_r($data, true);
    }
    file_put_contents($logFile, $logMessage . "\n", FILE_APPEND);
}

logDebug("=== INICIO DE REQUEST ===");
logDebug("GET", $_GET);
logDebug("POST", $_POST);
logDebug("FILES", $_FILES);

// Importa el controlador de login (para usar checkAuth)
require_once __DIR__ . '/LoginController.php';

// Cargar ImageUploader
$helperPath = __DIR__ . '/../../helpers/ImageUploader.php';
logDebug("Ruta ImageUploader", $helperPath);

if (file_exists($helperPath)) {
    require_once $helperPath;
    logDebug("ImageUploader cargado correctamente");
} else {
    logDebug("ERROR: No se encuentra ImageUploader.php");
    error_log("ERROR: No se encuentra ImageUploader.php en: " . $helperPath);
}

// Protege todo el módulo
checkAuth();

try {
    $productModel = new Product();
    logDebug("Modelo Product instanciado correctamente");
} catch (Exception $e) {
    logDebug("ERROR al instanciar Product", $e->getMessage());
    die(json_encode(['success' => false, 'message' => 'Error al conectar con la base de datos: ' . $e->getMessage()]));
}

function index() {
   return null;
}

handleRequest($productModel);

function handleRequest($productModel) {
    logDebug("Entrando a handleRequest");
    
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    logDebug("Action", $action);
    logDebug("Is AJAX", $isAjax ? 'YES' : 'NO');

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            $requestKey = "{$_SERVER['REQUEST_METHOD']}_$action";
            logDebug("Request Key", $requestKey);
            
            switch ($requestKey) {
                case 'POST_add_ajax':    
                    logDebug("Ejecutando handleAddEditAjax (ADD)");
                    handleAddEditAjax($productModel, 'add'); 
                    break;
                case 'POST_edit_ajax':   
                    logDebug("Ejecutando handleAddEditAjax (EDIT)");
                    handleAddEditAjax($productModel, 'edit'); 
                    break;
                case 'POST_delete_ajax': 
                    logDebug("Ejecutando handleDeleteAjax");
                    handleDeleteAjax($productModel); 
                    break;
                case 'POST_delete_image': 
                    logDebug("Ejecutando handleDeleteImageAjax");
                    handleDeleteImageAjax($productModel); 
                    break;
                case 'GET_get_products': 
                    logDebug("Ejecutando getProductsAjax");
                    getProductsAjax($productModel); 
                    break;
                default:                 
                    logDebug("Acción inválida", $requestKey);
                    echo json_encode(['success'=>false,'message'=>'Acción inválida: ' . $requestKey]); 
                    exit();
            }
        } else {
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add':    handleAddEdit($productModel, 'add'); break;
                case 'POST_edit':   handleAddEdit($productModel, 'edit'); break;
                case 'GET_delete':  handleDelete($productModel); break;
                default:            require __DIR__ . '/../../views/admin/products-admin.php';
            }
        }
    } catch (Exception $e) {
        logDebug("EXCEPCIÓN CAPTURADA", [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        if ($isAjax) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage(),
                'debug' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ]);
        } else {
            die("Error: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
        exit();
    }
}

/**
 * Agregar/Editar producto (AJAX)
 */
function handleAddEditAjax($productModel, $mode) {
    logDebug("=== INICIO handleAddEditAjax ===", $mode);
    
    // Validar campos requeridos
    $fields = ['prenda_id','nombre','tipo','categoria','precio'];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) {
            logDebug("Campo vacío", $f);
            throw new Exception("El campo $f es requerido");
        }
    }
    
    logDebug("Campos POST validados");
    
    $id = (int)$_POST['prenda_id'];
    $nombre = trim($_POST['nombre']);
    $tipo = trim($_POST['tipo']);
    $categoria = trim($_POST['categoria']);
    $precio = (float)$_POST['precio'];
    $precio_compra = !empty($_POST['precio_compra']) ? (float)$_POST['precio_compra'] : null;
    $descripcion = trim($_POST['descripcion'] ?? '');
    $imagen = null;
    $updateImage = false;

    logDebug("Datos procesados", [
        'id' => $id,
        'nombre' => $nombre,
        'tipo' => $tipo,
        'categoria' => $categoria,
        'precio' => $precio,
        'precio_compra' => $precio_compra
    ]);

    // Procesar imagen si existe
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        logDebug("Procesando imagen", $_FILES['imagen']);
        
        if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = 'Error al subir el archivo. Código: ' . $_FILES['imagen']['error'];
            logDebug("Error en subida", $errorMsg);
            throw new Exception($errorMsg);
        }
        
        try {
            $imageUploader = new ImageUploader();
            logDebug("ImageUploader instanciado");
            
            $uploadResult = $imageUploader->upload($_FILES['imagen'], $id);
            logDebug("Resultado de upload", $uploadResult);
            
            if ($uploadResult['success']) {
                $imagen = $uploadResult['data']['url'];
                $updateImage = true;
                logDebug("Imagen subida correctamente", $imagen);
                
                // Si es edición, eliminar imagen anterior
                if ($mode === 'edit') {
                    $oldProduct = $productModel->getById($id);
                    if ($oldProduct && !empty($oldProduct['imagen'])) {
                        $oldImagePath = __DIR__ . '/../../../' . ltrim($oldProduct['imagen'], '/');
                        if (file_exists($oldImagePath)) {
                            @unlink($oldImagePath);
                            logDebug("Imagen anterior eliminada", $oldImagePath);
                        }
                    }
                }
            } else {
                $errorMsg = 'Error al subir imagen: ' . implode(', ', $uploadResult['errors']);
                logDebug("Error en upload", $errorMsg);
                throw new Exception($errorMsg);
            }
        } catch (Exception $e) {
            logDebug("Excepción en imagen", $e->getMessage());
            throw $e;
        }
    } else {
        logDebug("No hay imagen para procesar", $_FILES['imagen']['error'] ?? 'NO_FILE');
    }

    // Guardar en BD
    try {
        if ($mode === 'add') {
            logDebug("Verificando si existe producto", $id);
            if ($productModel->productExists($id)) {
                throw new Exception("Ya existe un producto con este código");
            }
            
            logDebug("Llamando a productModel->add()");
            $result = $productModel->add($id, $nombre, $tipo, $categoria, $precio, $imagen, $descripcion, $precio_compra);
            logDebug("Resultado de add()", $result);
            $msg = 'Producto agregado correctamente';
        } else {
            logDebug("Verificando existencia para editar", $id);
            if (!$productModel->productExists($id)) {
                throw new Exception("No existe el producto");
            }
            
            logDebug("Llamando a productModel->update()");
            $result = $productModel->update($id, $nombre, $tipo, $categoria, $precio, $imagen, $descripcion, $updateImage, $precio_compra);
            logDebug("Resultado de update()", $result);
            $msg = 'Producto actualizado correctamente';
        }
        
        $product = $productModel->getById($id);
        logDebug("Producto obtenido", $product);
        
        $response = ['success'=>true, 'message'=>$msg, 'product'=>$product];
        logDebug("Respuesta final", $response);
        
        echo json_encode($response);
        exit();
        
    } catch (Exception $e) {
        logDebug("Error en BD", [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}

/**
 * Eliminar producto (AJAX)
 */
function handleDeleteAjax($productModel) {
    logDebug("=== handleDeleteAjax ===");
    
    if (empty($_POST['prenda_id']) || !is_numeric($_POST['prenda_id'])) {
        throw new Exception("ID inválido");
    }
    
    $id = (int)$_POST['prenda_id'];
    
    if (!$productModel->productExists($id)) {
        throw new Exception("No existe el producto");
    }
    
    $product = $productModel->getById($id);
    $productModel->delete($id);
    
    if ($product && !empty($product['imagen'])) {
        $imagePath = __DIR__ . '/../../../' . ltrim($product['imagen'], '/');
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }
    
    echo json_encode(['success'=>true, 'message'=>'Producto eliminado correctamente', 'productId'=>$id]); 
    exit();
}

/**
 * Eliminar solo la imagen de un producto (AJAX)
 */
function handleDeleteImageAjax($productModel) {
    if (empty($_POST['prenda_id']) || !is_numeric($_POST['prenda_id'])) {
        throw new Exception("ID inválido");
    }
    
    $id = (int)$_POST['prenda_id'];
    $product = $productModel->getById($id);
    
    if (!$product) {
        throw new Exception("No existe el producto");
    }
    
    if (empty($product['imagen'])) {
        throw new Exception("El producto no tiene imagen");
    }
    
    $imagePath = __DIR__ . '/../../../' . ltrim($product['imagen'], '/');
    if (file_exists($imagePath)) {
        @unlink($imagePath);
    }
    
    $productModel->removeImage($id);
    
    echo json_encode(['success'=>true, 'message'=>'Imagen eliminada correctamente']); 
    exit();
}

/**
 * Obtener productos (AJAX)
 */
function getProductsAjax($productModel) {
    logDebug("=== getProductsAjax ===");
    
    if (isset($_GET['prenda_id']) && is_numeric($_GET['prenda_id'])) {
        $product = $productModel->getById((int)$_GET['prenda_id']);
        if (!$product) throw new Exception("No existe el producto");
        echo json_encode(['success'=>true, 'products'=>[$product]]); 
        exit();
    }
    
    $products = $productModel->getAll();
    logDebug("Productos obtenidos", count($products));
    echo json_encode(['success'=>true, 'products'=>$products, 'count'=>count($products)]); 
    exit();
}

/**
 * Agregar/Editar producto (Form normal - sin AJAX)
 */
function handleAddEdit($productModel, $mode) {
    $fields = ['prenda_id','nombre','tipo','categoria','precio'];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) throw new Exception("El campo $f es requerido");
    }
    
    $id = (int)$_POST['prenda_id'];
    $nombre = trim($_POST['nombre']);
    $tipo = trim($_POST['tipo']);
    $categoria = trim($_POST['categoria']);
    $precio = (float)$_POST['precio'];
    $precio_compra = !empty($_POST['precio_compra']) ? (float)$_POST['precio_compra'] : null;
    $descripcion = trim($_POST['descripcion'] ?? '');
    $imagen = null;

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imageUploader = new ImageUploader();
        $uploadResult = $imageUploader->upload($_FILES['imagen'], $id);
        
        if ($uploadResult['success']) {
            $imagen = $uploadResult['data']['url'];
        } else {
            throw new Exception('Error al subir imagen: ' . implode(', ', $uploadResult['errors']));
        }
    }

    if ($mode === 'add') {
        if ($productModel->productExists($id)) {
            header("Location: products-admin.php?error=id_duplicado&prenda_id=$id"); exit();
        }
        $productModel->add($id, $nombre, $tipo, $categoria, $precio, $imagen, $descripcion, $precio_compra);
        header("Location: products-admin.php?success=add"); exit();
    } else {
        $updateImage = ($imagen !== null);
        $productModel->update($id, $nombre, $tipo, $categoria, $precio, $imagen, $descripcion, $updateImage, $precio_compra);
        header("Location: products-admin.php?success=edit"); exit();
    }
}

function handleDelete($productModel) {
    if (!isset($_GET['prenda_id']) || !is_numeric($_GET['prenda_id'])) {
        throw new Exception("ID inválido");
    }
    
    $id = (int)$_GET['prenda_id'];
    $product = $productModel->getById($id);
    $productModel->delete($id);
    
    if ($product && !empty($product['imagen'])) {
        $imagePath = __DIR__ . '/../../../' . ltrim($product['imagen'], '/');
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }
    
    header("Location: products-admin.php?success=delete"); exit();
}