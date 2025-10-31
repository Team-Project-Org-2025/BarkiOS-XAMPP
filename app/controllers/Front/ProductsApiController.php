<?php
use Barkios\models\Product;

header('Content-Type: application/json; charset=utf-8');

try {
    $productModel = new Product();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al conectar con la base de datos.']);
    exit;
}


$categoria = $_POST['categoria'] ?? null;
$limit = isset($_POST['limit']) ? (int)$_POST['limit'] : null;

try {
    if ($categoria) {
        $products = $productModel->getByCategoria($categoria, $limit ?? 12);
    } elseif ($limit) {
        $products = $productModel->getLatest($limit);
    } else {
        $products = $productModel->getDisponibles();
    }

    foreach ($products as &$p) {
        $p['id'] = (int)$p['prenda_id'];
        unset($p['prenda_id']);

        if (empty($p['imagen'])) {
            $p['imagen'] = "public/assets/img/no-image.png";
        }
    }

    echo json_encode([
        'success' => true,
        'count' => count($products),
        'products' => $products
    ]);
    exit;
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error interno: ' . $e->getMessage()
    ]);
    exit;
}
