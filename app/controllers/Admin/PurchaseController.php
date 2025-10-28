<?php
// filepath: app/controllers/Admin/PurchaseController.php

use Barkios\models\Purchase;
use Barkios\models\Supplier;
use Dompdf\Dompdf;
use Dompdf\Options;

require_once __DIR__ . '/LoginController.php';

checkAuth();

$purchaseModel = new Purchase();
$supplierModel = new Supplier();

function index() {
    require __DIR__ . '/../../views/admin/purchase-admin.php';
}

handleRequest($purchaseModel, $supplierModel);

function handleRequest($purchaseModel, $supplierModel) {
    $action = $_GET['action'] ?? '';
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');
            switch ("{$_SERVER['REQUEST_METHOD']}_$action") {
                case 'POST_add_ajax':    handleAddAjax($purchaseModel); break;
                case 'POST_edit_ajax':   handleEditAjax($purchaseModel); break;
                case 'POST_delete_ajax': handleDeleteAjax($purchaseModel); break;
                case 'GET_get_purchases': getPurchasesAjax($purchaseModel); break;
                case 'GET_get_purchase_detail': getPurchaseDetailAjax($purchaseModel); break;
                case 'GET_search_supplier': searchSupplierAjax($supplierModel); break;
                case 'GET_get_stats': getStatsAjax($purchaseModel); break;
                default: echo json_encode(['success'=>false,'message'=>'Acción inválida']); exit();
            }
        } elseif ($action === 'generate_pdf') {
            generatePdf($purchaseModel);
        }
    } catch (Exception $e) {
        if ($isAjax) {
            http_response_code(500);
            echo json_encode(['success'=>false, 'message'=>$e->getMessage()]);
        } else {
            die("Error: " . $e->getMessage());
        }
        exit();
    }
}

/**
 * Validaciones de formato en el controlador
 */
function validarDatosCompra($datos) {
    $errores = [];

    // Validar campos requeridos
    if (empty($datos['proveedor_rif'])) {
        $errores[] = 'El proveedor es requerido';
    }

    if (empty($datos['factura_numero'])) {
        $errores[] = 'El número de factura es requerido';
    } elseif (!preg_match('/^\d{8}$/', $datos['factura_numero'])) {
        $errores[] = 'El número de factura debe tener 8 dígitos';
    }

    if (empty($datos['fecha_compra'])) {
        $errores[] = 'La fecha de compra es requerida';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $datos['fecha_compra'])) {
        $errores[] = 'Formato de fecha inválido';
    }

    if (!empty($datos['tracking']) && !preg_match('/^\d{8}$/', $datos['tracking'])) {
        $errores[] = 'El número de tracking debe tener 8 dígitos';
    }

    return $errores;
}

function validarDatosPrenda($prenda) {
    $errores = [];

    if (empty($prenda['codigo_prenda'])) {
        $errores[] = 'El código de prenda es requerido';
    } elseif (!preg_match('/^[A-Z0-9\-]+$/i', $prenda['codigo_prenda'])) {
        $errores[] = "El código '{$prenda['codigo_prenda']}' contiene caracteres inválidos (solo letras, números y guiones)";
    }

    if (empty($prenda['nombre'])) {
        $errores[] = 'El nombre es requerido';
    } elseif (strlen(trim($prenda['nombre'])) < 3) {
        $errores[] = 'El nombre debe tener al menos 3 caracteres';
    } elseif (strlen(trim($prenda['nombre'])) > 150) {
        $errores[] = 'El nombre no puede exceder 150 caracteres';
    }

    if (empty($prenda['categoria'])) {
        $errores[] = 'La categoría es requerida';
    }

    if (empty($prenda['tipo'])) {
        $errores[] = 'El tipo es requerido';
    }

    if (!isset($prenda['precio_costo']) || floatval($prenda['precio_costo']) <= 0) {
        $errores[] = 'El precio de costo debe ser mayor a 0';
    }

    return $errores;
}

/**
 * Maneja la creación vía AJAX
 */
function handleAddAjax($purchaseModel) {
    try {
        // Preparar y sanitizar datos básicos
        $datos = [
            'proveedor_rif' => trim($_POST['proveedor_rif'] ?? ''),
            'factura_numero' => trim($_POST['factura_numero'] ?? ''),
            'fecha_compra' => trim($_POST['fecha_compra'] ?? ''),
            'tracking' => trim($_POST['tracking'] ?? ''),
            'observaciones' => trim($_POST['observaciones'] ?? ''),
            'fecha_vencimiento' => trim($_POST['fecha_vencimiento'] ?? '')
        ];

        // Validar formato de datos
        $errores = validarDatosCompra($datos);
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }

        // Preparar y validar prendas
        $rawPrendas = $_POST['prendas'] ?? [];
        if (empty($rawPrendas) || !is_array($rawPrendas)) {
            throw new Exception('Debe agregar al menos una prenda');
        }

        $prendas = [];
        $montoTotal = 0;

        foreach ($rawPrendas as $prenda) {
            // Sanitizar datos de prenda
            $prendaData = [
                'codigo_prenda' => trim($prenda['codigo_prenda'] ?? ''),
                'nombre' => trim($prenda['nombre'] ?? ''),
                'categoria' => $prenda['categoria'] ?? '',
                'tipo' => $prenda['tipo'] ?? '',
                'precio_costo' => floatval($prenda['precio_costo'] ?? 0),
                'descripcion' => trim($prenda['descripcion'] ?? '')
            ];

            // Validar formato de prenda
            $errores = validarDatosPrenda($prendaData);
            if (!empty($errores)) {
                throw new Exception(implode(', ', $errores));
            }

            // Calcular precio de venta (50% margen por defecto)
            $prendaData['precio_venta'] = isset($prenda['precio_venta']) && $prenda['precio_venta'] > 0
                ? floatval($prenda['precio_venta'])
                : round($prendaData['precio_costo'] * 1.5, 2);

            $prendas[] = $prendaData;
            $montoTotal += $prendaData['precio_costo'];
        }

        $datos['prendas'] = $prendas;
        $datos['monto_total'] = $montoTotal;

        // El modelo se encarga de validaciones de negocio e inserción
        $compraId = $purchaseModel->add($datos);

        echo json_encode([
            'success' => true,
            'message' => 'Compra registrada exitosamente',
            'compra_id' => $compraId
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

/**
 * Maneja la edición vía AJAX
 */
/**
 * Maneja la edición vía AJAX
 */
function handleEditAjax($purchaseModel) {
    try {
        $id = isset($_POST['compra_id']) ? intval($_POST['compra_id']) : null;
        if (!$id) {
            throw new Exception('ID de compra inválido');
        }

        // Verificar si puede editarse
        if (!$purchaseModel->canEdit($id)) {
            throw new Exception('No se puede editar: la compra tiene prendas vendidas');
        }

        // Preparar y sanitizar datos
        $datos = [
            'proveedor_rif' => trim($_POST['proveedor_rif'] ?? ''),
            'factura_numero' => trim($_POST['factura_numero'] ?? ''),
            'fecha_compra' => trim($_POST['fecha_compra'] ?? ''),
            'tracking' => trim($_POST['tracking'] ?? ''),
            'observaciones' => trim($_POST['observaciones'] ?? '')
        ];

        // Obtener monto actual desde el modelo
        $montoActual = $purchaseModel->getMontoTotal($id);
        $datos['monto_total'] = $montoActual;

        // Validar formato (pasando monto_total para que pase la validación)
        $errores = validarDatosCompra($datos);
        if (!empty($errores)) {
            throw new Exception(implode(', ', $errores));
        }

        // Actualizar datos generales
        $purchaseModel->update($id, $datos);

        // Procesar nuevas prendas si existen
        $nuevasPrendas = [];
        $rawNuevasPrendas = $_POST['nuevas_prendas'] ?? [];

        foreach ($rawNuevasPrendas as $prenda) {
            // Sanitizar
            $prendaData = [
                'codigo_prenda' => trim($prenda['codigo_prenda'] ?? ''),
                'nombre' => trim($prenda['nombre'] ?? ''),
                'categoria' => $prenda['categoria'] ?? '',
                'tipo' => $prenda['tipo'] ?? '',
                'precio_costo' => floatval($prenda['precio_costo'] ?? 0),
                'descripcion' => trim($prenda['descripcion'] ?? '')
            ];

            // Validar formato
            $errores = validarDatosPrenda($prendaData);
            if (!empty($errores)) {
                throw new Exception(implode(', ', $errores));
            }

            // NO calcular precio de venta automáticamente
            $prendaData['precio_venta'] = isset($prenda['precio_venta']) && $prenda['precio_venta'] > 0
                ? floatval($prenda['precio_venta'])
                : 0;

            $nuevasPrendas[] = $prendaData;
        }

        // Agregar nuevas prendas (el modelo actualiza montos)
        $prendasAgregadas = 0;
        if (!empty($nuevasPrendas)) {
            $prendasAgregadas = $purchaseModel->addPrendasToCompra($id, $nuevasPrendas);
        }

        $mensaje = $prendasAgregadas > 0
            ? "Compra actualizada y {$prendasAgregadas} prenda(s) agregada(s)"
            : 'Compra actualizada exitosamente';

        echo json_encode([
            'success' => true,
            'message' => $mensaje
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

/**
 * Maneja la eliminación vía AJAX
 */
function handleDeleteAjax($purchaseModel) {
    try {
        $id = isset($_POST['compra_id']) ? intval($_POST['compra_id']) : null;
        if (!$id) {
            throw new Exception('ID inválido');
        }

        $purchaseModel->delete($id);

        echo json_encode([
            'success' => true,
            'message' => 'Compra eliminada exitosamente'
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

/**
 * Obtiene todas las compras
 */
function getPurchasesAjax($purchaseModel) {
    try {
        $purchases = $purchaseModel->getAll();

        echo json_encode([
            'success' => true,
            'data' => $purchases
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

/**
 * Obtiene detalle de una compra
 */
function getPurchaseDetailAjax($purchaseModel) {
    try {
        $id = isset($_GET['compra_id']) ? intval($_GET['compra_id']) : (isset($_GET['id']) ? intval($_GET['id']) : null);

        if (!$id) {
            throw new Exception('ID inválido');
        }

        $compra = $purchaseModel->getById($id);
        if (!$compra) {
            throw new Exception('Compra no encontrada');
        }

        $prendas = $purchaseModel->getPrendasByCompraId($id);
        $pagos = $purchaseModel->getPagosByCompraId($id);

        echo json_encode([
            'success' => true,
            'data' => [
                'compra' => $compra,
                'prendas' => $prendas,
                'pagos' => $pagos,
                'totales' => [
                    'total_prendas' => count($prendas),
                    'total_pagado' => $compra['total_pagado'] ?? 0,
                    'saldo_pendiente' => $compra['saldo_pendiente'] ?? 0
                ]
            ]
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

/**
 * Busca proveedores
 */
function searchSupplierAjax($supplierModel) {
    try {
        $query = trim($_GET['search'] ?? $_GET['q'] ?? '');

        if ($query === '') {
            echo json_encode(['success' => true, 'results' => []]);
            exit();
        }

        $results = [];

        if (method_exists($supplierModel, 'search')) {
            $results = $supplierModel->search($query);
        } elseif (method_exists($supplierModel, 'getById')) {
            $byId = $supplierModel->getById($query);
            if ($byId) {
                $results = [$byId];
            }
        }

        echo json_encode([
            'success' => true,
            'results' => $results
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

/**
 * Obtiene estadísticas
 */
function getStatsAjax($purchaseModel) {
    try {
        $stats = $purchaseModel->getEstadisticas();

        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

/**
 * Genera PDF de una compra
 */
/**
 * Genera PDF de una compra
 */
function generatePdf($purchaseModel) {
    try {
        $id = isset($_GET['compra_id']) ? intval($_GET['compra_id']) : (isset($_GET['id']) ? intval($_GET['id']) : null);
        
        if (!$id) {
            die('ID de compra inválido');
        }

        $compra = $purchaseModel->getById($id);
        if (!$compra) {
            die('Compra no encontrada');
        }

        $prendas = $purchaseModel->getPrendasByCompraId($id);

        // Construir HTML para el PDF
        $html = '<!doctype html>
        <html>
        <head>
          <meta charset="utf-8">
          <title>Compra #' . htmlspecialchars($compra['compra_id']) . '</title>
          <style>
            body { font-family: DejaVu Sans, Helvetica, Arial, sans-serif; font-size: 12px; }
            .header { text-align: center; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
            th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
            th { background: #f5f5f5; }
            .right { text-align: right; }
          </style>
        </head>
        <body>
          <div class="header">
            <h2>Detalle de Compra</h2>
            <div>Factura: ' . htmlspecialchars($compra['factura_numero']) .'</div>
            <div>Proveedor: ' . htmlspecialchars($compra['nombre_proveedor'] ?? $compra['proveedor_rif']) . '</div>
            <div>Fecha: ' . date('d/m/Y', strtotime($compra['fecha_compra'])) . '</div>
          </div>

          <h4>Prendas</h4>
          <table>
            <thead>
              <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Tipo</th>
                <th class="right">Precio costo</th>
              </tr>
            </thead>
            <tbody>';

        foreach ($prendas as $p) {
            $html .= '<tr>
                <td>' . htmlspecialchars($p['codigo_prenda']) . '</td>
                <td>' . htmlspecialchars($p['nombre']) . '</td>
                <td>' . htmlspecialchars($p['categoria']) . '</td>
                <td>' . htmlspecialchars($p['tipo']) . '</td>
                <td class="right">$' . number_format($p['precio_costo'], 2, '.', ',') . '</td>
            </tr>';
        }

        $html .= '</tbody></table>';

        $html .= '<div style="margin-top:10px;">
            <strong>Monto total:</strong> $' . number_format($compra['monto_total'], 2, '.', ',') . '
            <br><strong>Observaciones:</strong> ' . htmlspecialchars($compra['observaciones'] ?? '') . '
        </div>';

        $html .= '</body></html>';

        // Configurar Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Marcar PDF como generado
        $purchaseModel->markPdfGenerated($id);

        // Enviar PDF al navegador
        $filename = 'compra_' . $compra['compra_id'] . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        echo $dompdf->output();
        exit();
    } catch (Exception $e) {
        die('Error al generar PDF: ' . $e->getMessage());
    }
}