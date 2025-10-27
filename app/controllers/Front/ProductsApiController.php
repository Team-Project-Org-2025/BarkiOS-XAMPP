<?php
// app/controllers/front/ProductsApiController.php

use Barkios\models\Product;

header('Content-Type: application/json; charset=utf-8');

// Instanciar modelo
try {
    $productModel = new Product();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al conectar con la BD'
    ]);
    exit();
}

// Obtener productos disponibles al público
$products = $productModel->getDisponibles(); // ✅ Solo los que se pueden mostrar

// Transformar datos para el Front
foreach ($products as &$p) {
    $p['id'] = (int)$p['prenda_id'];
    unset($p['prenda_id']); // No queremos esto en el front

    // Imagen: si viene vacía, mandar un placeholder
    if (empty($p['imagen'])) {
        $p['imagen'] = "/assets/img/no-image.png";
    }
}

echo json_encode([
    'success' => true,
    'products' => $products,
    'count' => count($products)
]);
exit();
