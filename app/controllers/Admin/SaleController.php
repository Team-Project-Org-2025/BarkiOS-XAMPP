<?php
use Barkios\models\Sale;

// Proteger el módulo (requiere autenticación)
require_once __DIR__ . '/LoginController.php';
checkAuth();

if (session_status() === PHP_SESSION_NONE) session_start();

$saleModel = new Sale();
handleRequest($saleModel);

/**
 * Función principal de enrutamiento
 */

function handleRequest($model)
{
    $basePath = '/BarkiOS';

    // Validación de sesión
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
            handleAjax($model, $action);
        } else {
            if (empty($action)) {
               return null;
            } else {
                throw new Exception("Acción no válida");
            }
        }
    } catch (Exception $e) {
        error_log("SaleController Error: " . $e->getMessage());
        if ($isAjax) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        } else {
            echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
        exit();
    }
}

/**
 * Manejador de peticiones AJAX
 */
function handleAjax($model, $action)
{
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ("{$method}_{$action}") {

        case 'GET_get_sales':
            getSales($model);
            break;

        case 'GET_get_by_id':
            getSaleById($model);
            break;

        case 'GET_get_clients':
            getClients($model);
            break;

        case 'GET_get_employees':
            getEmployees($model);
            break;

        case 'GET_get_products':
            getProducts($model);
            break;

        case 'GET_get_product_by_code':
            getProductByCode($model);
            break;

        case 'POST_add_sale':
            addSale($model);
            break;

        case 'POST_add_payment':
            addPayment($model);
            break;

        case 'POST_cancel_sale':
            cancelSale($model);
            break;

        default:
            throw new Exception("Petición no válida: {$method} {$action}");
    }

    exit();
}

/* ============================================================
   ENDPOINTS GET
    $paths = [
        __DIR__ . '/../../views/admin/sale-admin.php',
        dirname(__DIR__, 2) . '/views/admin/sale-admin.php'
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require $path;
            return;
        }
    }

    throw new Exception("Vista no encontrada");
}


