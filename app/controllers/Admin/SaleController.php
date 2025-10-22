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
============================================================ */

function getSales($model)
{
    try {
        $sales = $model->getAll();
        echo json_encode([
            'success' => true, 
            'sales' => $sales,
            'count' => count($sales)
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getSaleById($model)
{
    try {
        $id = intval($_GET['id'] ?? 0);
        if ($id <= 0) {
            throw new Exception("ID de venta inválido");
        }

        $venta = $model->getSaleWithDetails($id);
        echo json_encode([
            'success' => !!$venta, 
            'venta' => $venta,
            'message' => $venta ? 'Venta encontrada' : 'Venta no encontrada'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getClients($model)
{
    try {
        $clients = $model->getClients();
        echo json_encode([
            'success' => true, 
            'clients' => $clients
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getEmployees($model)
{
    try {
        $employees = $model->getEmployees();
        echo json_encode([
            'success' => true, 
            'employees' => $employees
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getProducts($model)
{
    try {
        $products = $model->getProducts();
        echo json_encode([
            'success' => true, 
            'products' => $products,
            'count' => count($products)
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getProductByCode($model)
{
    try {
        $codigo = trim($_GET['codigo'] ?? '');
        if (empty($codigo)) {
            throw new Exception("Código de prenda requerido");
        }

        $product = $model->getProductByCode($codigo);
        echo json_encode([
            'success' => !!$product,
            'product' => $product,
            'message' => $product ? 'Producto encontrado' : 'Producto no encontrado'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

/* ============================================================
   ENDPOINTS POST
============================================================ */

function addSale($model)
{
    try {
        // Validar campos requeridos
        $required = ['cliente_ced', 'empleado_ced', 'tipo_venta', 'productos'];
        foreach ($required as $f) {
            if (empty($_POST[$f])) {
                throw new Exception("Campo requerido: $f");
            }
        }

        $cliente = trim($_POST['cliente_ced']);
        $empleado = trim($_POST['empleado_ced']);
        $tipo = strtolower($_POST['tipo_venta']);

        // Decodificar productos
        $productos = json_decode($_POST['productos'], true);
        if (!$productos || !is_array($productos) || count($productos) === 0) {
            throw new Exception("Debe agregar al menos un producto válido");
        }

        // Validar que cada producto tenga código_prenda
        foreach ($productos as $p) {
            if (empty($p['codigo_prenda']) || empty($p['precio_unitario'])) {
                throw new Exception("Productos inválidos: falta código o precio");
            }
        }

        // Preparar datos de venta
        $ventaData = [
            'cliente_ced' => $cliente,
            'empleado_ced' => $empleado,
            'tipo_venta' => $tipo,
            'productos' => $productos,
            'observaciones' => $_POST['observaciones'] ?? null,
            'iva_porcentaje' => floatval($_POST['iva_porcentaje'] ?? 16.00),
            'referencia' => $_POST['referencia'] ?? null
        ];

        // Registrar venta
        $ventaId = $model->addSale($ventaData);

        if ($ventaId) {
            // Obtener referencia generada
            $venta = $model->getById($ventaId);
            echo json_encode([
                'success' => true, 
                'message' => 'Venta registrada correctamente', 
                'venta_id' => $ventaId,
                'referencia' => $venta['referencia'] ?? null
            ]);
        } else {
            throw new Exception("Error al registrar la venta");
        }

    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
}

function addPayment($model)
{
    try {
        $ventaId = intval($_POST['venta_id'] ?? 0);
        $monto = floatval($_POST['monto'] ?? 0);

        if ($ventaId <= 0) {
            throw new Exception("ID de venta inválido");
        }

        if ($monto <= 0) {
            throw new Exception("Monto de pago inválido");
        }

        $success = $model->addPayment([
            'venta_id' => $ventaId,
            'monto' => $monto,
            'observaciones' => $_POST['observaciones'] ?? null
        ]);

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Pago registrado correctamente' : 'Error al registrar el pago'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
}

function cancelSale($model)
{
    try {
        $id = intval($_POST['venta_id'] ?? 0);
        if ($id <= 0) {
            throw new Exception("ID de venta inválido");
        }

        $success = $model->cancelSale($id);
        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Venta anulada correctamente' : 'Error al anular venta'
        ]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
}

/* ============================================================
   MOSTRAR VISTA
============================================================ */

function index()
{
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


