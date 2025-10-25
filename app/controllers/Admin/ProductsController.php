<?php
// app/controllers/admin/ProductsController.php
use Barkios\models\Product;
use Barkios\helpers\ImageUploader;

// ✅ Importa el controlador de login (para usar checkAuth)
require_once __DIR__ . '/LoginController.php';

// Cargar ImageUploader
$helperPath = __DIR__ . '/../../helpers/ImageUploader.php';
if (file_exists($helperPath)) {
    require_once $helperPath;
} else {
    error_log("ERROR: No se encuentra ImageUploader.php en: " . $helperPath);
}

// ✅ Protege todo el módulo
checkAuth();

$productModel = new Product();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function index() {
   return null;
}
handleRequest($productModel);

function handleRequest($productModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add_ajax':    handleAddEditAjax($productModel, 'add'); break;
                case 'POST_edit_ajax':   handleAddEditAjax($productModel, 'edit'); break;
                case 'POST_delete_ajax': handleDeleteAjax($productModel); break;
                case 'POST_delete_image': handleDeleteImageAjax($productModel); break;
                case 'GET_get_products': getProductsAjax($productModel); break;
                default:                 echo json_encode(['success'=>false,'message'=>'Acción inválida']); exit();
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
        if ($isAjax) {
            http_response_code(500);
            echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
        } else {
            die("Error: " . $e->getMessage());
        }
        exit();
    }
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
    $descripcion = trim($_POST['descripcion'] ?? '');
    $imagen = null;

    // Procesar imagen si existe
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
        $productModel->add($id, $nombre, $tipo, $categoria, $precio, $imagen, $descripcion);
        header("Location: products-admin.php?success=add"); exit();
    } else {
        // En edición, solo actualizar imagen si hay una nueva
        $updateImage = ($imagen !== null);
        $productModel->update($id, $nombre, $tipo, $categoria, $precio, $imagen, $descripcion, $updateImage);
        header("Location: products-admin.php?success=edit"); exit();
    }
}

function handleDelete($productModel) {
    if (!isset($_GET['prenda_id']) || !is_numeric($_GET['prenda_id'])) {
        throw new Exception("ID inválido");
    }
    
    $id = (int)$_GET['prenda_id'];
    
    // Obtener producto para eliminar imagen
    $product = $productModel->getById($id);
    
    // Eliminar producto
    $productModel->delete($id);
    
    // Eliminar imagen física si existe
    if ($product && !empty($product['imagen'])) {
        $imagePath = __DIR__ . '/../../../' . ltrim($product['imagen'], '/');
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }
    
    header("Location: products-admin.php?success=delete"); exit();
}

/**
 * Agregar/Editar producto (AJAX)
 */
function handleAddEditAjax($productModel, $mode) {
    $fields = ['prenda_id','nombre','tipo','categoria','precio'];
    $data = [];
    
    foreach ($fields as $f) {
        if (empty($_POST[$f])) {
            throw new Exception("El campo $f es requerido");
        }
        $data[$f] = $f === 'precio' ? (float)$_POST[$f] : trim($_POST[$f]);
    }
    
    $id = $data['prenda_id'];
    $descripcion = trim($_POST['descripcion'] ?? '');
    $imagen = null;
    $updateImage = false;

    // Procesar imagen si existe
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        $imageUploader = new ImageUploader();
        $uploadResult = $imageUploader->upload($_FILES['imagen'], $id);
        
        if ($uploadResult['success']) {
            $imagen = $uploadResult['data']['url'];
            $updateImage = true;
            
            // Si es edición, eliminar imagen anterior
            if ($mode === 'edit') {
                $oldProduct = $productModel->getById($id);
                if ($oldProduct && !empty($oldProduct['imagen'])) {
                    $oldImagePath = __DIR__ . '/../../../' . ltrim($oldProduct['imagen'], '/');
                    if (file_exists($oldImagePath)) {
                        @unlink($oldImagePath);
                    }
                }
            }
        } else {
            throw new Exception('Error al subir imagen: ' . implode(', ', $uploadResult['errors']));
        }
    }

    if ($mode === 'add') {
        if ($productModel->productExists($id)) {
            throw new Exception("ID duplicado");
        }
        $productModel->add($id, $data['nombre'], $data['tipo'], $data['categoria'], $data['precio'], $imagen, $descripcion);
        $msg = 'Producto agregado';
    } else {
        if (!$productModel->productExists($id)) {
            throw new Exception("No existe el producto");
        }
        $productModel->update($id, $data['nombre'], $data['tipo'], $data['categoria'], $data['precio'], $imagen, $descripcion, $updateImage);
        $msg = 'Producto actualizado';
    }
    
    $product = $productModel->getById($id);
    echo json_encode(['success'=>true, 'message'=>$msg, 'product'=>$product]); 
    exit();
}

/**
 * Eliminar producto (AJAX)
 */
function handleDeleteAjax($productModel) {
    if (empty($_POST['prenda_id']) || !is_numeric($_POST['prenda_id'])) {
        throw new Exception("ID inválido");
    }
    
    $id = (int)$_POST['prenda_id'];
    
    if (!$productModel->productExists($id)) {
        throw new Exception("No existe el producto");
    }
    
    // Obtener producto para eliminar imagen
    $product = $productModel->getById($id);
    
    // Eliminar producto
    $productModel->delete($id);
    
    // Eliminar imagen física si existe
    if ($product && !empty($product['imagen'])) {
        $imagePath = __DIR__ . '/../../../' . ltrim($product['imagen'], '/');
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }
    
    echo json_encode(['success'=>true, 'message'=>'Producto eliminado', 'productId'=>$id]); 
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
    
    // Eliminar archivo físico
    $imagePath = __DIR__ . '/../../../' . ltrim($product['imagen'], '/');
    if (file_exists($imagePath)) {
        @unlink($imagePath);
    }
    
    // Eliminar referencia en BD
    $productModel->removeImage($id);
    
    echo json_encode(['success'=>true, 'message'=>'Imagen eliminada']); 
    exit();
}

/**
 * Obtener productos (AJAX)
 */
function getProductsAjax($productModel) {
    if (isset($_GET['prenda_id']) && is_numeric($_GET['prenda_id'])) {
        $product = $productModel->getById((int)$_GET['prenda_id']);
        if (!$product) throw new Exception("No existe el producto");
        echo json_encode(['success'=>true, 'products'=>[$product]]); exit();
    }
    
    $products = $productModel->getAll();
    echo json_encode(['success'=>true, 'products'=>$products, 'count'=>count($products)]); 
    exit();
}