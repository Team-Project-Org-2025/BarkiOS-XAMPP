<?php
use Barkios\models\Sale;

if (session_status() === PHP_SESSION_NONE) session_start();

function index()
{
    $SaleModel = new Sale();
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
            handleAjax($SaleModel, $action);
        } else {
            showView();
        }
    } catch (Exception $e) {
        if ($isAjax) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        } else {
            echo "<h1>Error</h1><p>" . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

/* ============================================================
   MANEJADOR AJAX
============================================================ */
function handleAjax($model, $action)
{
    switch ("{$_SERVER['REQUEST_METHOD']}_$action") {

        case 'GET_get_sales':
            echo json_encode(['success' => true, 'sales' => $model->getAll()]);
            break;

        case 'GET_get_by_id':
            $id = intval($_GET['id'] ?? 0);
            $venta = $model->getSaleWithDetails($id);
            echo json_encode(['success' => !!$venta, 'venta' => $venta]);
            break;

        case 'POST_add_sale':
            addSale($model);
            break;

        case 'POST_add_payment':
            addPayment($model);
            break;

        case 'POST_cancel_sale':
            $id = intval($_POST['venta_id'] ?? 0);
            $success = $model->cancelSale($id);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Venta cancelada correctamente' : 'Error al cancelar venta'
            ]);
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Petición no válida']);
    }

    exit();
}

/* ============================================================
   REGISTRAR NUEVA VENTA
============================================================ */
function addSale($model)
{
    $required = ['cliente_ced', 'empleado_ced', 'tipo_venta', 'productos'];

    foreach ($required as $f) {
        if (empty($_POST[$f])) {
            throw new Exception("Campo requerido: $f");
        }
    }

    $cliente = trim($_POST['cliente_ced']);
    $empleado = trim($_POST['empleado_ced']);
    $tipo = strtolower($_POST['tipo_venta']);

    $productos = json_decode($_POST['productos'], true);
    if (!$productos || !is_array($productos)) {
        throw new Exception("Lista de productos inválida");
    }

    $montoTotal = 0;
    foreach ($productos as $p) {
        if (empty($p['prenda_id']) || empty($p['precio_unitario'])) {
            throw new Exception("Producto inválido en la lista");
        }
        $montoTotal += floatval($p['precio_unitario']);
    }

    $ventaData = [
        'cliente_ced' => $cliente,
        'empleado_ced' => $empleado,
        'tipo_venta' => $tipo,
        'monto_total' => $montoTotal,
        'productos' => $productos,
        'observaciones' => $_POST['observaciones'] ?? null
    ];

    $ventaId = $model->addSale($ventaData);

    if ($ventaId) {
        echo json_encode(['success' => true, 'message' => 'Venta registrada', 'venta_id' => $ventaId]);
    } else {
        throw new Exception("Error al registrar la venta");
    }
}

/* ============================================================
   REGISTRAR PAGO
============================================================ */
function addPayment($model)
{
    $ventaId = intval($_POST['venta_id'] ?? 0);
    $monto = floatval($_POST['monto'] ?? 0);

    if ($ventaId <= 0 || $monto <= 0) {
        throw new Exception("Datos de pago inválidos");
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
}

/* ============================================================
   MOSTRAR VISTA
============================================================ */
function showView()
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
