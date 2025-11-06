<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/ingreso.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Clase personalizada TCPDF para comprobantes de compra
class PDF_Comprobante extends TCPDF {
    private $numeroComprobante = '';
    private $fechaComprobante = '';
    private $proveedor = '';
    
    public function setDatosComprobante($numero, $fecha, $proveedor) {
        $this->numeroComprobante = $numero;
        $this->fechaComprobante = $fecha;
        $this->proveedor = $proveedor;
    }
    
    public function Header() {
        // HEADER PROFESIONAL PARA COMPROBANTE DE COMPRA
        
        // Fondo del header
        $this->SetFillColor(248, 250, 252); 
        $this->Rect(15, 10, $this->getPageWidth() - 30, 35, 'F');
        
        // Borde del header
        $this->SetDrawColor(34, 197, 94); // Verde para compras
        $this->SetLineWidth(0.5);
        $this->Rect(15, 10, $this->getPageWidth() - 30, 35);
        
        // Logo de Ferretería Costa Sur (usando la misma ruta que reporteController)
        $logoPath = __DIR__ . '/../public/img/logo.jpg';
        
        if (file_exists($logoPath)) {
            try {
                // Insertar logo real con el formato correcto
                $this->Image($logoPath, 20, 15, 25, 20, 'JPG');
            } catch (Exception $e) {
                // Si hay error con el logo, usar texto como fallback
                $this->SetFont('helvetica', 'B', 24);
                $this->SetTextColor(34, 197, 94);
                $this->SetXY(22, 18);
                $this->Cell(12, 12, '🏗️', 0, 0, 'C'); // Ícono de ferretería
            }
        } else {
            // Logo de texto como fallback
            $this->SetFont('helvetica', 'B', 24);
            $this->SetTextColor(34, 197, 94);
            $this->SetXY(22, 18);
            $this->Cell(12, 12, '🏗️', 0, 0, 'C'); // Ícono de ferretería
        }
        
        // Nombre de la ferretería
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(15, 23, 42);
        $this->SetXY(50, 16);
        $this->Cell(80, 8, 'FERRETERÍA COSTA SUR', 0, 0, 'L');
        
        // Subtítulo
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 116, 139);
        $this->SetXY(50, 26);
        $this->Cell(80, 6, 'Comprobante de Ingreso de Inventario', 0, 0, 'L');
        
        // Información del comprobante (lado derecho)
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(220, 38, 38); // Rojo para destacar
        $this->SetXY(130, 16);
        $this->Cell(60, 8, 'COMPROBANTE N° ' . $this->numeroComprobante, 0, 1, 'R');
        
        // Fecha
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(71, 85, 105);
        $this->SetXY(130, 26);
        $this->Cell(60, 6, 'Fecha: ' . $this->fechaComprobante, 0, 1, 'R');
        
        // Proveedor
        $this->SetFont('helvetica', 'B', 10);
        $this->SetTextColor(15, 23, 42);
        $this->SetXY(130, 34);
        $this->Cell(60, 6, 'Proveedor: ' . $this->proveedor, 0, 1, 'R');
        
        // Línea decorativa
        $this->SetDrawColor(34, 197, 94);
        $this->SetLineWidth(1);
        $this->Line(15, 48, $this->getPageWidth() - 15, 48);
        
        $this->Ln(15);
    }
    
    public function Footer() {
        $this->SetY(-20);
        
        // Línea superior
        $this->SetDrawColor(203, 213, 225);
        $this->Line(15, $this->GetY(), $this->getPageWidth() - 15, $this->GetY());
        
        $this->Ln(3);
        
        // Información del pie
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 5, 'Sistema de Inventario - Ferretería Costa Sur', 0, 1, 'C');
        $this->Cell(0, 5, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages() . ' - Generado: ' . date('d/m/Y H:i:s'), 0, 0, 'C');
    }
}

class ComprobanteController {
    private $conn;
    
    public function __construct() {
        $this->conn = Conexion::conectar();
    }
    
    public function generarComprobanteCompra($ingresoId) {
        try {
            // Obtener datos del ingreso
            $ingreso = Ingreso::obtenerPorIdConDetalles($ingresoId);
            
            if (!$ingreso) {
                throw new Exception('Ingreso no encontrado');
            }
            
            // Crear PDF
            $pdf = new PDF_Comprobante('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('Sistema de Inventario');
            $pdf->SetAuthor('Ferretería Costa Sur');
            $pdf->SetTitle('Comprobante de Compra N° ' . $ingreso['numero']);
            $pdf->SetSubject('Comprobante de Ingreso de Inventario');
            
            // Configurar datos del comprobante
            $fechaFormateada = date('d/m/Y', strtotime($ingreso['fecha']));
            $pdf->setDatosComprobante($ingreso['numero'], $fechaFormateada, $ingreso['proveedor']);
            
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(15, 55, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);
            $pdf->SetAutoPageBreak(TRUE, 25);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            
            $pdf->AddPage();
            
            // INFORMACIÓN GENERAL DEL COMPROBANTE
            $this->agregarInformacionGeneral($pdf, $ingreso);
            
            // TABLA DE PRODUCTOS
            $this->agregarTablaProductos($pdf, $ingreso['detalles']);
            
            // TOTALES
            $this->agregarTotales($pdf, $ingreso);
            
            // OBSERVACIONES
            $this->agregarObservaciones($pdf, $ingreso);
            
            // FIRMAS
            $this->agregarSeccionFirmas($pdf);
            
            // Salida del PDF
            $nombreArchivo = 'Comprobante_Compra_' . $ingreso['numero'] . '_' . date('Y-m-d') . '.pdf';
            $pdf->Output($nombreArchivo, 'I');
            
        } catch (Exception $e) {
            die('Error al generar comprobante: ' . $e->getMessage());
        }
    }
    
    private function agregarInformacionGeneral($pdf, $ingreso) {
        // Recuadro de información general
        $pdf->SetFillColor(249, 250, 251);
        $pdf->SetDrawColor(209, 213, 219);
        $pdf->Rect(15, $pdf->GetY(), $pdf->getPageWidth() - 30, 25, 'FD');
        
        $yInicial = $pdf->GetY() + 3;
        
        // Columna izquierda
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->SetXY(20, $yInicial);
        $pdf->Cell(40, 6, 'DATOS DE LA COMPRA', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(75, 85, 99);
        $pdf->SetX(20);
        $pdf->Cell(35, 5, 'N° Comprobante:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(40, 5, $ingreso['numero'], 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(75, 85, 99);
        $pdf->SetX(20);
        $pdf->Cell(35, 5, 'Fecha:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(40, 5, date('d/m/Y', strtotime($ingreso['fecha'])), 0, 1, 'L');
        
        // Columna derecha
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->SetXY(110, $yInicial);
        $pdf->Cell(40, 6, 'PROVEEDOR', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(75, 85, 99);
        $pdf->SetX(110);
        $pdf->Cell(25, 5, 'Nombre:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(60, 5, $ingreso['proveedor'], 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(75, 85, 99);
        $pdf->SetX(110);
        $pdf->Cell(25, 5, 'Código:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(60, 5, $ingreso['proveedor_codigo'] ?? 'N/A', 0, 1, 'L');
        
        $pdf->Ln(8);
    }
    
    private function agregarTablaProductos($pdf, $detalles) {
        // Título de la tabla
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(0, 8, 'DETALLE DE PRODUCTOS INGRESADOS', 0, 1, 'C');
        $pdf->Ln(2);
        
        // Encabezados de la tabla
        $pdf->SetFillColor(34, 197, 94); // Verde
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(34, 197, 94);
        $pdf->SetLineWidth(0.3);
        $pdf->SetFont('helvetica', 'B', 9);
        
        // Anchos de columnas
        $w = array(15, 60, 25, 20, 25, 25);
        
        $pdf->Cell($w[0], 8, 'N°', 1, 0, 'C', 1);
        $pdf->Cell($w[1], 8, 'PRODUCTO', 1, 0, 'C', 1);
        $pdf->Cell($w[2], 8, 'BODEGA', 1, 0, 'C', 1);
        $pdf->Cell($w[3], 8, 'CANT.', 1, 0, 'C', 1);
        $pdf->Cell($w[4], 8, 'PRECIO UNIT.', 1, 0, 'C', 1);
        $pdf->Cell($w[5], 8, 'SUBTOTAL', 1, 0, 'C', 1);
        $pdf->Ln();
        
        // Contenido de la tabla
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->SetFont('helvetica', '', 8);
        
        $fill = false;
        $contador = 1;
        $totalGeneral = 0;
        
        foreach ($detalles as $detalle) {
            $subtotal = $detalle['cantidad'] * $detalle['precio'];
            $totalGeneral += $subtotal;
            
            $pdf->Cell($w[0], 6, $contador, 1, 0, 'C', $fill);
            $pdf->Cell($w[1], 6, substr($detalle['producto_nombre'], 0, 35), 1, 0, 'L', $fill);
            $pdf->Cell($w[2], 6, substr($detalle['bodega_nombre'], 0, 15), 1, 0, 'C', $fill);
            $pdf->Cell($w[3], 6, number_format($detalle['cantidad'], 0), 1, 0, 'C', $fill);
            $pdf->Cell($w[4], 6, 'Q' . number_format($detalle['precio'], 2), 1, 0, 'R', $fill);
            $pdf->Cell($w[5], 6, 'Q' . number_format($subtotal, 2), 1, 0, 'R', $fill);
            $pdf->Ln();
            
            $fill = !$fill;
            $contador++;
        }
        
        $pdf->Ln(3);
    }
    
    private function agregarTotales($pdf, $ingreso) {
        // Recuadro de totales
        $pdf->SetFillColor(239, 246, 255);
        $pdf->SetDrawColor(59, 130, 246);
        $pdf->SetLineWidth(0.3);
        
        $x = $pdf->getPageWidth() - 80;
        $y = $pdf->GetY();
        $pdf->Rect($x, $y, 65, 35, 'FD');
        
        // Totales
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(30, 64, 175);
        $pdf->SetXY($x + 5, $y + 3);
        $pdf->Cell(55, 6, 'RESUMEN FINANCIERO', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(55, 65, 81);
        
        // Subtotal
        $pdf->SetX($x + 5);
        $pdf->Cell(35, 5, 'Subtotal:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(20, 5, 'Q' . number_format($ingreso['subtotal'] ?? 0, 2), 0, 1, 'R');
        
        // IVA
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetX($x + 5);
        $pdf->Cell(35, 5, 'IVA (12%):', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(20, 5, 'Q' . number_format($ingreso['iva'] ?? 0, 2), 0, 1, 'R');
        
        // Total
        $pdf->SetDrawColor(220, 38, 38);
        $pdf->Line($x + 5, $pdf->GetY() + 1, $x + 60, $pdf->GetY() + 1);
        $pdf->Ln(2);
        
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(220, 38, 38);
        $pdf->SetX($x + 5);
        $pdf->Cell(35, 6, 'TOTAL:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(20, 6, 'Q' . number_format($ingreso['total'], 2), 0, 1, 'R');
        
        $pdf->Ln(10);
    }
    
    private function agregarObservaciones($pdf, $ingreso) {
        if (!empty($ingreso['observaciones'])) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(55, 65, 81);
            $pdf->Cell(0, 6, 'OBSERVACIONES:', 0, 1, 'L');
            
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(75, 85, 99);
            $pdf->MultiCell(0, 5, $ingreso['observaciones'], 0, 'L');
            $pdf->Ln(5);
        }
    }
    
    private function agregarSeccionFirmas($pdf) {
        $pdf->Ln(10);
        
        // Líneas para firmas
        $pdf->SetDrawColor(156, 163, 175);
        $pdf->SetLineWidth(0.3);
        
        // Firma izquierda - Recibido por
        $pdf->Line(20, $pdf->GetY() + 15, 80, $pdf->GetY() + 15);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(107, 114, 128);
        $pdf->SetXY(20, $pdf->GetY() + 17);
        $pdf->Cell(60, 4, 'RECIBIDO POR', 0, 0, 'C');
        $pdf->SetXY(20, $pdf->GetY() + 4);
        $pdf->Cell(60, 4, 'Nombre y Firma', 0, 0, 'C');
        
        // Firma derecha - Autorizado por
        $pdf->Line(120, $pdf->GetY() - 6, 180, $pdf->GetY() - 6);
        $pdf->SetXY(120, $pdf->GetY() + 2);
        $pdf->Cell(60, 4, 'AUTORIZADO POR', 0, 0, 'C');
        $pdf->SetXY(120, $pdf->GetY() + 4);
        $pdf->Cell(60, 4, 'Nombre y Firma', 0, 1, 'C');
        
        $pdf->Ln(15);
        
        // Nota final
        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetTextColor(156, 163, 175);
        $pdf->Cell(0, 4, 'Este comprobante certifica el ingreso de mercadería al inventario según el detalle especificado.', 0, 1, 'C');
    }
}

// Manejar las peticiones
if (isset($_GET['action']) && $_GET['action'] === 'comprobante' && isset($_GET['id'])) {
    $controller = new ComprobanteController();
    $controller->generarComprobanteCompra(intval($_GET['id']));
} else {
    die('Acción no válida');
}
?>