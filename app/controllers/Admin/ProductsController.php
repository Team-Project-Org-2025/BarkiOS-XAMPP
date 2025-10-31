<?php

use Barkios\models\Product;
use Barkios\helpers\ImageUploader;

// Importa el controlador de login (para usar checkAuth)
require_once __DIR__ . '/LoginController.php';

// Cargar ImageUploader
$helperPath = __DIR__ . '/../../helpers/ImageUploader.php';
if (file_exists($helperPath)) {
    require_once $helperPath;
}

checkAuth();

try {
    $productModel = new Product();
} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => 'Error al conectar con la base de datos: ' . $e->getMessage()]));
}

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
            $requestKey = "{$_SERVER['REQUEST_METHOD']}_$action";
            
            switch ($requestKey) {
                case 'POST_add_ajax':    
                    handleAddEditAjax($productModel, 'add'); 
                    break;
                case 'POST_edit_ajax':   
                    handleAddEditAjax($productModel, 'edit'); 
                    break;
                case 'POST_delete_ajax': 
                    handleDeleteAjax($productModel); 
                    break;
                case 'POST_delete_image': 
                    handleDeleteImageAjax($productModel); 
                    break;
                case 'GET_get_products': 
                    getProductsAjax($productModel); 
                    break;
                default:                 
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
        if ($isAjax) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        } else {
            die("Error: " . $e->getMessage());
        }
        exit();
    }
}

function handleAddEditAjax($productModel, $mode) {
    // Validar campos requeridos
    $fields = ['prenda_id','nombre','tipo','categoria','precio'];
    foreach ($fields as $f) {
        if (empty($_POST[$f])) {
            throw new Exception("El campo $f es requerido");
        }
    }
    
    $id = (int)$_POST['prenda_id'];
    $nombre = trim($_POST['nombre']);
    $tipo = trim($_POST['tipo']);
    $categoria = trim($_POST['categoria']);
    $precio = (float)$_POST['precio'];
    $precio_compra = !empty($_POST['precio_compra']) ? (float)$_POST['precio_compra'] : null;
    $descripcion = trim($_POST['descripcion'] ?? '');
    $imagen = null;
    $updateImage = false;

    // Procesar imagen si existe
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir el archivo. Código: ' . $_FILES['imagen']['error']);
        }
        
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

    // Guardar en BD
    if ($mode === 'add') {
        if ($productModel->productExists($id)) {
            throw new Exception("Ya existe un producto con este código");
        }
        
        $result = $productModel->add($id, $nombre, $tipo, $categoria, $precio, $imagen, $descripcion, $precio_compra);
        $msg = 'Producto agregado correctamente';
    } else {
        if (!$productModel->productExists($id)) {
            throw new Exception("No existe el producto");
        }
        
        $result = $productModel->update($id, $nombre, $tipo, $categoria, $precio, $imagen, $descripcion, $updateImage, $precio_compra);
        $msg = 'Producto actualizado correctamente';
    }
    
    $product = $productModel->getById($id);
    $response = ['success'=>true, 'message'=>$msg, 'product'=>$product];
    
    echo json_encode($response);
    exit();
}


function handleDeleteAjax($productModel) {
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


function getProductsAjax($productModel) {
    if (isset($_GET['prenda_id']) && is_numeric($_GET['prenda_id'])) {
        $product = $productModel->getById((int)$_GET['prenda_id']);
        if (!$product) throw new Exception("No existe el producto");
        echo json_encode(['success'=>true, 'products'=>[$product]]); 
        exit();
    }
    
    $products = $productModel->getAll();
    echo json_encode(['success'=>true, 'products'=>$products, 'count'=>count($products)]); 
    exit();
}


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