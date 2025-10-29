<?php
// test_products.php - Coloca este archivo en la raíz de BarkiOS

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔍 Diagnóstico del Módulo de Productos</h2>";
echo "<pre>";

// 1. Verificar estructura de directorios
echo "1. VERIFICANDO ESTRUCTURA DE ARCHIVOS:\n";
echo str_repeat("-", 50) . "\n";

$files = [
    'app/models/Product.php',
    'app/helpers/ImageUploader.php',
    'app/controllers/admin/ProductsController.php',
    'app/views/admin/products-admin.php',
    'public/assets/js/products-admin.js',
    'app/core/Database.php'
];

foreach ($files as $file) {
    $exists = file_exists(__DIR__ . '/' . $file);
    echo ($exists ? "✅" : "❌") . " $file\n";
}

// 2. Verificar directorio de uploads
echo "\n2. VERIFICANDO DIRECTORIO DE UPLOADS:\n";
echo str_repeat("-", 50) . "\n";

$uploadDir = __DIR__ . '/public/uploads/products/';
if (is_dir($uploadDir)) {
    echo "✅ Directorio existe: $uploadDir\n";
    echo "   Permisos: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "\n";
    echo "   ¿Es escribible?: " . (is_writable($uploadDir) ? "Sí" : "No") . "\n";
} else {
    echo "❌ Directorio NO existe: $uploadDir\n";
    echo "   Creando directorio...\n";
    if (mkdir($uploadDir, 0755, true)) {
        echo "   ✅ Directorio creado exitosamente\n";
    } else {
        echo "   ❌ Error al crear directorio\n";
    }
}

// 3. Verificar conexión a base de datos
echo "\n3. VERIFICANDO CONEXIÓN A BASE DE DATOS:\n";
echo str_repeat("-", 50) . "\n";

try {
    require_once __DIR__ . '/app/core/Database.php';
    
    $testConnection = new class extends Barkios\core\Database {
        public function testConnection() {
            return $this->db !== null;
        }
        
        public function testQuery() {
            try {
                $stmt = $this->db->query("SELECT COUNT(*) as total FROM prendas");
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                return ['error' => $e->getMessage()];
            }
        }
    };
    
    if ($testConnection->testConnection()) {
        echo "✅ Conexión a BD exitosa\n";
        
        $result = $testConnection->testQuery();
        if (isset($result['error'])) {
            echo "❌ Error en consulta: " . $result['error'] . "\n";
        } else {
            echo "✅ Tabla 'prendas' accesible\n";
            echo "   Total de productos: " . $result['total'] . "\n";
        }
    } else {
        echo "❌ Error en conexión a BD\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// 4. Verificar tabla prendas
echo "\n4. VERIFICANDO ESTRUCTURA DE TABLA 'prendas':\n";
echo str_repeat("-", 50) . "\n";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=barkios_db;charset=utf8', 'root', '');
    $stmt = $pdo->query("DESCRIBE prendas");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $requiredColumns = ['prenda_id', 'nombre', 'tipo', 'categoria', 'precio', 'imagen', 'descripcion'];
    
    foreach ($requiredColumns as $col) {
        $exists = false;
        foreach ($columns as $column) {
            if ($column['Field'] === $col) {
                $exists = true;
                break;
            }
        }
        echo ($exists ? "✅" : "❌") . " Columna '$col'\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

// 5. Verificar clases PHP
echo "\n5. VERIFICANDO CLASES PHP:\n";
echo str_repeat("-", 50) . "\n";

try {
    require_once __DIR__ . '/app/models/Product.php';
    echo "✅ Clase Product cargada\n";
    
    if (class_exists('Barkios\models\Product')) {
        echo "✅ Namespace correcto\n";
        
        $product = new Barkios\models\Product();
        echo "✅ Instancia creada\n";
        
        if (method_exists($product, 'getAll')) {
            echo "✅ Método getAll() existe\n";
            
            try {
                $products = $product->getAll();
                echo "✅ getAll() ejecutado: " . count($products) . " productos\n";
            } catch (Exception $e) {
                echo "❌ Error al ejecutar getAll(): " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ Método getAll() NO existe\n";
        }
    } else {
        echo "❌ Clase no encontrada en namespace\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error al cargar Product: " . $e->getMessage() . "\n";
}

// 6. Verificar extensión GD
echo "\n6. VERIFICANDO EXTENSIÓN GD (para imágenes):\n";
echo str_repeat("-", 50) . "\n";

if (extension_loaded('gd')) {
    echo "✅ Extensión GD instalada\n";
    $gdInfo = gd_info();
    echo "   Versión: " . $gdInfo['GD Version'] . "\n";
    echo "   JPEG: " . ($gdInfo['JPEG Support'] ? "Sí" : "No") . "\n";
    echo "   PNG: " . ($gdInfo['PNG Support'] ? "Sí" : "No") . "\n";
    echo "   GIF: " . ($gdInfo['GIF Read Support'] ? "Sí" : "No") . "\n";
    echo "   WebP: " . (isset($gdInfo['WebP Support']) && $gdInfo['WebP Support'] ? "Sí" : "No") . "\n";
} else {
    echo "❌ Extensión GD NO instalada\n";
    echo "   Instalar con: sudo apt-get install php-gd\n";
}

// 7. Verificar límites de PHP
echo "\n7. CONFIGURACIÓN PHP PARA UPLOADS:\n";
echo str_repeat("-", 50) . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";

echo "\n";
echo str_repeat("=", 50) . "\n";
echo "DIAGNÓSTICO COMPLETO\n";
echo str_repeat("=", 50) . "\n";
echo "</pre>";

echo "<p><strong>Siguiente paso:</strong> Accede a <a href='/BarkiOS/admin/products'>Módulo de Productos</a></p>";
?>