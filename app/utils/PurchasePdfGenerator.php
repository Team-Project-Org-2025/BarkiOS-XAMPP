<?php
namespace Barkios\utils;

require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

use TCPDF;

/**
 * Generador de PDF para Compras
 * Genera informes de compras con detalle de prendas
 */
class PurchasePdfGenerator
{
    private $pdf;
    
    public function __construct()
    {
        $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $this->configurePdf();
    }
    
    private function configurePdf()
    {
        // Información del documento
        $this->pdf->SetCreator('Garage Barki - BarkiOS');
        $this->pdf->SetAuthor('Garage Barki');
        $this->pdf->SetTitle('Factura de Compra');
        
        // Quitar header y footer por defecto
        $this->pdf->setPrintHeader(false);
        $this->pdf->setPrintFooter(false);
        
        // Márgenes
        $this->pdf->SetMargins(15, 15, 15);
        $this->pdf->SetAutoPageBreak(TRUE, 15);
        
        // Fuente por defecto
        $this->pdf->SetFont('helvetica', '', 10);
    }
    
    /**
     * Generar PDF de compra
     * 
     * @param array $compra Datos de la compra
     * @return string Ruta del archivo PDF generado
     */
    public function generate($compra)
    {
        // Agregar página
        $this->pdf->AddPage();
        
        // Header personalizado
        $this->addHeader($compra);
        
        // Información de la compra
        $this->addPurchaseInfo($compra);
        
        // Tabla de prendas
        $this->addItemsTable($compra['prendas']);
        
        // Resumen total
        $this->addSummary($compra['monto_total']);
        
        // Generar nombre del archivo
        $fecha = date('Y-m-d', strtotime($compra['fecha_compra']));
        $filename = "Factura_{$compra['factura_numero']}_{$fecha}.pdf";
        $filepath = __DIR__ . '/../../public/temp/' . $filename;
        
        // Crear directorio temp si no existe
        if (!file_exists(dirname($filepath))) {
            mkdir(dirname($filepath), 0777, true);
        }
        
        // Guardar PDF
        $this->pdf->Output($filepath, 'F');
        
        return $filepath;
    }
    
    /**
     * Generar PDF y retornar como string para descarga directa
     */
    public function generateForDownload($compra)
    {
        // Agregar página
        $this->pdf->AddPage();
        
        // Header personalizado
        $this->addHeader($compra);
        
        // Información de la compra
        $this->addPurchaseInfo($compra);
        
        // Tabla de prendas
        $this->addItemsTable($compra['prendas']);
        
        // Resumen total
        $this->addSummary($compra['monto_total']);
        
        // Generar nombre del archivo
        $fecha = date('Y-m-d', strtotime($compra['fecha_compra']));
        $filename = "Factura_{$compra['factura_numero']}_{$fecha}.pdf";
        
        // Retornar PDF como string
        return [
            'content' => $this->pdf->Output('', 'S'),
            'filename' => $filename
        ];
    }
    
    private function addHeader($compra)
    {
        // Logo y título
        $this->pdf->SetFont('helvetica', 'B', 20);
        $this->pdf->SetTextColor(102, 126, 234); // Color púrpura
        $this->pdf->Cell(0, 10, 'GARAGE BARKI', 0, 1, 'C');
        
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->SetTextColor(100, 100, 100);
        $this->pdf->Cell(0, 5, 'Compra de Mercancía Internacional', 0, 1, 'C');
        
        $this->pdf->Ln(5);
        
        // Línea separadora
        $this->pdf->SetDrawColor(102, 126, 234);
        $this->pdf->SetLineWidth(0.5);
        $this->pdf->Line(15, $this->pdf->GetY(), 195, $this->pdf->GetY());
        
        $this->pdf->Ln(8);
    }
    
    private function addPurchaseInfo($compra)
    {
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Cell(0, 6, 'INFORMACIÓN DE LA COMPRA', 0, 1, 'L');
        
        $this->pdf->Ln(3);
        
        // Información en dos columnas
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->SetTextColor(60, 60, 60);
        
        $y = $this->pdf->GetY();
        
        // Columna izquierda
        $this->pdf->SetXY(15, $y);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(40, 6, 'N° Factura:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(60, 6, $compra['factura_numero'], 0, 1, 'L');
        
        $this->pdf->SetX(15);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(40, 6, 'Proveedor:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(60, 6, $compra['nombre_empresa'], 0, 1, 'L');
        
        $this->pdf->SetX(15);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(40, 6, 'Contacto:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(60, 6, $compra['nombre_contacto'], 0, 1, 'L');
        
        $this->pdf->SetX(15);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(40, 6, 'Teléfono:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(60, 6, $compra['telefono'] ?? 'N/A', 0, 1, 'L');
        
        // Columna derecha
        $this->pdf->SetXY(115, $y);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(35, 6, 'Fecha Compra:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(45, 6, date('d/m/Y', strtotime($compra['fecha_compra'])), 0, 1, 'L');
        
        $this->pdf->SetX(115);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(35, 6, 'Tracking:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(45, 6, $compra['tracking'] ?? 'N/A', 0, 1, 'L');
        
        $this->pdf->SetX(115);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(35, 6, 'Referencia:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(45, 6, $compra['referencia'] ?? 'N/A', 0, 1, 'L');
        
        $this->pdf->SetX(115);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->pdf->Cell(35, 6, 'Método de Pago:', 0, 0, 'L');
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(45, 6, strtoupper($compra['metodo_pago'] ?? 'N/A'), 0, 1, 'L');
        
        // Dirección completa (ocupa todo el ancho)
        if (!empty($compra['direccion'])) {
            $this->pdf->Ln(2);
            $this->pdf->SetX(15);
            $this->pdf->SetFont('helvetica', 'B', 10);
            $this->pdf->Cell(40, 6, 'Dirección:', 0, 0, 'L');
            $this->pdf->SetFont('helvetica', '', 9);
            $this->pdf->MultiCell(145, 5, $compra['direccion'], 0, 'L');
        }
        
        $this->pdf->Ln(5);
    }
    
    private function addItemsTable($prendas)
    {
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->Cell(0, 6, 'DETALLE DE PRENDAS COMPRADAS', 0, 1, 'L');
        
        $this->pdf->Ln(2);
        
        // Header de la tabla
        $this->pdf->SetFillColor(102, 126, 234); // Púrpura
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->SetFont('helvetica', 'B', 9);
        
        $this->pdf->Cell(10, 8, '#', 1, 0, 'C', true);
        $this->pdf->Cell(95, 8, 'Prenda', 1, 0, 'C', true);
        $this->pdf->Cell(45, 8, 'Categoría', 1, 0, 'C', true);
        $this->pdf->Cell(30, 8, 'Precio Costo', 1, 1, 'C', true);
        
        // Contenido de la tabla
        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('helvetica', '', 9);
        
        $fill = false;
        $contador = 1;
        
        foreach ($prendas as $prenda) {
            $this->pdf->Cell(10, 7, $contador, 1, 0, 'C', $fill);
            $this->pdf->Cell(95, 7, $this->truncateText($prenda['producto_nombre'], 60), 1, 0, 'L', $fill);
            $this->pdf->Cell(45, 7, $prenda['categoria'], 1, 0, 'C', $fill);
            $this->pdf->Cell(30, 7, '$' . number_format($prenda['precio_costo'], 2), 1, 1, 'R', $fill);
            
            $fill = !$fill;
            $contador++;
        }
        
        $this->pdf->Ln(3);
    }
    
    private function addSummary($montoTotal)
    {
        // Cuadro de resumen
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->SetFillColor(240, 240, 240);
        
        $x = 120;
        $this->pdf->SetXY($x, $this->pdf->GetY());
        
        $this->pdf->Cell(45, 10, 'MONTO TOTAL:', 1, 0, 'L', true);
        $this->pdf->SetTextColor(0, 128, 0); // Verde
        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->Cell(30, 10, '$' . number_format($montoTotal, 2), 1, 1, 'R', true);
        
        $this->pdf->SetTextColor(0, 0, 0);
        
        // Footer informativo
        $this->pdf->Ln(10);
        $this->pdf->SetFont('helvetica', 'I', 8);
        $this->pdf->SetTextColor(120, 120, 120);
        $this->pdf->MultiCell(0, 4, 
            "Nota: Cada prenda listada es única y exclusiva. El precio de costo mostrado corresponde al valor individual de cada artículo.\n" .
            "Documento generado automáticamente por BarkiOS - Sistema de Gestión Garage Barki.",
            0, 'L');
    }
    
    private function truncateText($text, $maxLength)
    {
        if (strlen($text) > $maxLength) {
            return substr($text, 0, $maxLength - 3) . '...';
        }
        return $text;
    }
}
