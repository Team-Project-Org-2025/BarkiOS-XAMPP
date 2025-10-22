<?php
// /app/controllers/Admin/PurchaseController.php
use Barkios\models\Purchase;
use Barkios\utils\PurchasePdfGenerator;

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/LoginController.php';

// ✅ Protege todo el módulo
checkAuth();

function index() {
    $purchaseModel = new Purchase();
    $basePath = '/BarkiOS';

    // Verificar autenticación
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_logged_in'])) {
        header("Location: {$basePath}/login");
        exit();
    }

    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            handleAjax($purchaseModel, $action);
        } else {
            showView();
        }
    } catch (Exception $e) {
        if ($isAjax) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } else {
            echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
        exit();
    }
}

/* ==========================================================
   🔹 MANEJADOR DE PETICIONES AJAX
========================================================== */
function handleAjax($model, $action) {
    switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
        case 'GET_get_purchases':
            $purchases = $model->getAll();
            echo json_encode(['success' => true, 'data' => $purchases]);
            break;

        case 'GET_get_suppliers':
            $suppliers = $model->getAllProveedores();
            echo json_encode(['success' => true, 'data' => $suppliers]);
            break;

        case 'GET_search_supplier':
            $search = $_GET['search'] ?? '';
            if (strlen($search) < 2) {
                echo json_encode(['success' => false, 'message' => 'Mínimo 2 caracteres']);
                exit();
            }
            $suppliers = $model->searchProveedores($search);
            echo json_encode(['success' => true, 'data' => $suppliers]);
            break;

        case 'GET_get_by_id':
            $id = intval($_GET['id'] ?? 0);
            $purchase = $model->getById($id);
            echo json_encode(['success' => !!$purchase, 'data' => $purchase]);
            break;

        case 'GET_get_estadisticas':
            $stats = $model->getEstadisticas();
            echo json_encode(['success' => true, 'data' => $stats]);
            break;

        case 'GET_get_categorias':
            $categorias = $model->getCategorias();
            echo json_encode(['success' => true, 'data' => $categorias]);
            break;

        case 'GET_download_pdf':
            downloadPdf($model);
            break;

        case 'POST_add_ajax':
            addPurchase($model);
            break;

        case 'POST_delete_ajax':
            $id = intval($_POST['compra_id'] ?? 0);
            $success = $model->delete($id);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Compra eliminada correctamente' : 'Error al eliminar la compra'
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
    exit();
}

/* ==========================================================
   🔹 AGREGAR COMPRA
========================================================== */
function addPurchase($model) {
    // Validar campos requeridos
    $required = ['proveedor_id', 'factura_numero'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

    // Validar prendas
    if (empty($_POST['prendas']) || !is_array($_POST['prendas'])) {
        throw new Exception("Debe agregar al menos una prenda");
    }

    // Validar y calcular total
    $total = 0;
    $prendasValidadas = [];

    foreach ($_POST['prendas'] as $prenda) {
        $precio = floatval($prenda['precio_costo'] ?? 0);
        $nombre = trim($prenda['producto_nombre'] ?? '');
        $categoria = trim($prenda['categoria'] ?? '');

        if ($precio <= 0) {
            throw new Exception("El precio de costo debe ser mayor a cero");
        }

        if (empty($nombre)) {
            throw new Exception("El nombre de la prenda es requerido");
        }

        if (empty($categoria)) {
            throw new Exception("La categoría es requerida");
        }

        $total += $precio;

        $prendasValidadas[] = [
            'producto_nombre' => $nombre,
            'categoria' => $categoria,
            'precio_costo' => $precio
        ];
    }

    if ($total <= 0) {
        throw new Exception("El monto total debe ser mayor a cero");
    }

    // Preparar datos
    $data = [
        'proveedor_id' => trim($_POST['proveedor_id']),
        'factura_numero' => trim($_POST['factura_numero']),
        'tracking' => !empty($_POST['tracking']) ? trim($_POST['tracking']) : null,
        'monto_total' => $total,
        'prendas' => $prendasValidadas
    ];

    // Guardar
    $compraId = $model->add($data);

    if ($compraId) {
        echo json_encode([
            'success' => true,
            'message' => 'Compra registrada correctamente',
            'compra_id' => $compraId
        ]);
    } else {
        throw new Exception("Error al registrar la compra");
    }
}

/* ==========================================================
   🔹 DESCARGAR PDF DE COMPRA
========================================================== */
function downloadPdf($model) {
    $id = intval($_GET['id'] ?? 0);
    
    if ($id <= 0) {
        http_response_code(400);
        die('ID no válido');
    }

    // Obtener datos de la compra
    $compra = $model->getById($id);
    
    if (!$compra) {
        http_response_code(404);
        die('Compra no encontrada');
    }

    try {
        // Generar PDF
        $pdfGenerator = new PurchasePdfGenerator();
        $result = $pdfGenerator->generateForDownload($compra);
        
        // Marcar como PDF generado
        $model->markPdfGenerated($id);
        
        // Enviar headers para descarga
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
        header('Content-Length: ' . strlen($result['content']));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        
        echo $result['content'];
        exit();
        
    } catch (Exception $e) {
        error_log('Error generando PDF: ' . $e->getMessage());
        http_response_code(500);
        die('Error al generar el PDF');
    }
}

/* ==========================================================
   🔹 MOSTRAR VISTA
========================================================== */
function showView() {
    $paths = [
        __DIR__ . '/../../views/admin/purchase-admin.php',
        dirname(__DIR__, 2) . '/views/admin/purchase-admin.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require $path;
            return;
        }
    }
    
    throw new Exception("Vista de compras no encontrada");
}

// Ejecutar controlador
index();
