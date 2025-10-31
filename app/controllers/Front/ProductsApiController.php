<?php
use Barkios\models\Product;

header('Content-Type: application/json; charset=utf-8');

try {
    $productModel = new Product();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al conectar con la base de datos.']);
    exit;
}

// ================================
// PARÁMETROS (POST/GET)
// ================================
$categoria = $_POST['categoria'] ?? $_GET['categoria'] ?? null;
$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : (isset($_GET['limit']) ? (int)$_GET['limit'] : null);

// ================================
// CONSULTA SEGÚN PARÁMETROS
// ================================
try {
    if ($categoria) {
        $products = $productModel->getByCategoria($categoria, $limit ?? 12);
    } elseif ($limit) {
        $products = $productModel->getLatest($limit);
    } else {
        $products = $productModel->getDisponibles();
    }

    // ================================
    // 🔒 VALIDACIÓN Y FILTRADO DE PRODUCTOS
    // ================================
    $validProducts = [];
    
    foreach ($products as $p) {
        // ✅ Validar que el precio sea mayor a 0
        $precio = isset($p['precio']) ? floatval($p['precio']) : 0;
        
        // ✅ Validar que tenga imagen válida
        $hasValidImage = !empty($p['imagen']) && 
                        $p['imagen'] !== 'public/assets/img/no-image.png' &&
                        $p['imagen'] !== '' &&
                        $p['imagen'] !== null;
        
        // ✅ Solo agregar productos que cumplan AMBAS condiciones
        if ($precio > 0 && $hasValidImage) {
            // Formatear datos del producto
            $p['id'] = (int)$p['prenda_id'];
            $p['precio'] = number_format($precio, 2, '.', '');
            unset($p['prenda_id']);
            
            $validProducts[] = $p;
        }
    }

    // ================================
    // RESPUESTA FINAL
    // ================================
    echo json_encode([
        'success' => true,
        'count' => count($validProducts),
        'products' => $validProducts,
        'total_filtered' => count($products), // Total antes del filtro
        'filtered_out' => count($products) - count($validProducts) // Productos excluidos
    ]);
    exit;
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
    exit;
}