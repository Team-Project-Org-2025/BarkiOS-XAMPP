<?php
// /app/controllers/Admin/SaleController.php
use Barkios\models\Sale;

/** 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
*/
if (session_status() === PHP_SESSION_NONE) session_start();

function index() {
    $SaleModel = new Sale();
    $basePath = '/BarkiOS';

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
            handleAjax($SaleModel, $action);
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

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    error_log("DEBUG: Acción -> $action");
    handleAjax($SaleModel, $action);
}



function handleAjax($model, $action) {
    switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
        case 'GET_get_sales':
            echo json_encode(['success' => true, 'sales' => $model->getAll()]);
            break;
        case 'GET_get_clients':
            echo json_encode(['success' => true, 'clients' => $model->getAllClients()]);
            break;
        case 'GET_get_employees':
            echo json_encode(['success' => true, 'employees' => $model->getAllEmployees()]);
            break;
        case 'GET_get_products':
            echo json_encode(['success' => true, 'products' => $model->getAvailableProducts()]);
            break;
        case 'GET_get_by_id':
            $sale = $model->getSaleWithDetails($_GET['id'] ?? 0);
            echo json_encode(['success' => !!$sale, 'sale' => $sale]);
            break;
        case 'POST_add_ajax':
            addSale($model);
            break;
        case 'POST_delete_ajax':
            $success = $model->cancelSale($_POST['venta_id'] ?? 0);
            echo json_encode(['success' => $success, 'message' => $success ? 'Venta cancelada' : 'Error']);
            break;
        case 'POST_add_payment':
            addPayment($model);
            break;
        case 'GET_get_payments':
            $payments = $model->getPaymentsBySale($_GET['venta_id'] ?? 0);
            echo json_encode(['success' => true, 'payments' => $payments]);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Acción inválida']);
    }
    exit();
}

function addSale($model) {
    $required = ['cliente_ced', 'empleado_ced', 'tipo_venta', 'metodo_pago_principal'];
    foreach ($required as $f) {
        if (empty($_POST[$f])) throw new Exception("Campo $f requerido");
    }

    if (empty($_POST['productos'])) throw new Exception("Agregue al menos un producto");

    $cliente = trim($_POST['cliente_ced']);
    $empleado = trim($_POST['empleado_ced']);
    
    if (!preg_match('/^\d{7,10}$/', $cliente)) throw new Exception("Cédula de cliente inválida");
    if (!preg_match('/^\d{7,10}$/', $empleado)) throw new Exception("Cédula de empleado inválida");
    if (!$model->clientExists($cliente)) throw new Exception("Cliente no existe");
    if (!$model->employeeExists($empleado)) throw new Exception("Empleado no existe");

    $subtotal = 0;
    $productosValidados = [];

    foreach ($_POST['productos'] as $p) {
        $cantidad = intval($p['cantidad'] ?? 0);
        $precio = floatval($p['precio_unitario'] ?? 0);
        
        if ($cantidad <= 0 || $precio <= 0) throw new Exception("Datos de producto inválidos");
        
        $stock = $model->getProductStock($p['prenda_id']);
        if ($stock < $cantidad) throw new Exception("Stock insuficiente (ID: {$p['prenda_id']})");
        
        $sub = $cantidad * $precio;
        $subtotal += $sub;
        $productosValidados[] = [
            'prenda_id' => $p['prenda_id'],
            'cantidad' => $cantidad,
            'precio_unitario' => $precio,
            'subtotal' => $sub
        ];
    }

    $descuento = ($subtotal * floatval($_POST['descuento'] ?? 0)) / 100;
    $total = $subtotal - $descuento;

    if ($total <= 0) throw new Exception("Monto total debe ser mayor a cero");

    $ventaId = $model->addSale([
        'cliente_ced' => $cliente,
        'empleado_ced' => $empleado,
        'tipo_venta' => $_POST['tipo_venta'],
        'metodo_pago' => $_POST['metodo_pago_principal'],
        'subtotal' => $subtotal,
        'descuento' => $descuento,
        'monto_total' => $total,
        'observaciones' => $_POST['observaciones'] ?? null,
        'productos' => $productosValidados
    ]);

    if ($ventaId) {
        echo json_encode(['success' => true, 'message' => 'Venta registrada', 'venta_id' => $ventaId]);
    } else {
        throw new Exception("Error al procesar la venta");
    }
}

function addPayment($model) {
    $required = ['venta_id', 'monto', 'metodo_pago'];
    foreach ($required as $f) {
        if (empty($_POST[$f])) throw new Exception("Campo $f requerido");
    }

    $monto = floatval($_POST['monto']);
    if ($monto <= 0) throw new Exception("Monto inválido");

    $metodo = $_POST['metodo_pago'];
    if (in_array($metodo, ['transferencia', 'pago_movil', 'cheque']) && empty($_POST['referencia'])) {
        throw new Exception("Referencia requerida para este método");
    }

    $pagoId = $model->addPayment([
        'venta_id' => $_POST['venta_id'],
        'monto' => $monto,
        'metodo_pago' => $metodo,
        'referencia' => $_POST['referencia'] ?? null,
        'banco' => $_POST['banco'] ?? null,
        'observaciones' => $_POST['observaciones'] ?? null
    ]);

    if ($pagoId) {
        echo json_encode(['success' => true, 'message' => 'Pago registrado', 'pago_id' => $pagoId]);
    } else {
        throw new Exception("Error al registrar pago");
    }
}

function showView() {
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