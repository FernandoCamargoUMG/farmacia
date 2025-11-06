<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../models/egreso.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Clase personalizada TCPDF para recibos de caja (egresos/ventas)
class PDF_ReciboCaja extends TCPDF {
    private $numeroRecibo = '';
    private $fechaRecibo = '';
    private $cliente = '';
    
    public function setDatosRecibo($numero, $fecha, $cliente) {
        $this->numeroRecibo = $numero;
        $this->fechaRecibo = $fecha;
        $this->cliente = $cliente;
    }
    
    public function Header() {
        // HEADER PROFESIONAL PARA RECIBO DE CAJA
        
        // Fondo del header
        $this->SetFillColor(240, 248, 255); 
        $this->Rect(15, 10, $this->getPageWidth() - 30, 35, 'F');
        
        // Borde del header
        $this->SetDrawColor(59, 130, 246); // Azul para ventas
        $this->SetLineWidth(0.5);
        $this->Rect(15, 10, $this->getPageWidth() - 30, 35);
        
        // Logo de Ferretería Costa Sur (misma ruta que reporteController)
        $logoPath = __DIR__ . '/../public/img/logo.jpg';
        
        if (file_exists($logoPath)) {
            try {
                // Insertar logo real con el formato correcto
                $this->Image($logoPath, 20, 15, 25, 20, 'JPG');
            } catch (Exception $e) {
                // Si hay error con el logo, usar texto como fallback
                $this->SetFont('helvetica', 'B', 24);
                $this->SetTextColor(59, 130, 246);
                $this->SetXY(22, 18);
                $this->Cell(12, 12, '💰', 0, 0, 'C'); // Ícono de caja/venta
            }
        } else {
            // Logo de texto como fallback
            $this->SetFont('helvetica', 'B', 24);
            $this->SetTextColor(59, 130, 246);
            $this->SetXY(22, 18);
            $this->Cell(12, 12, '💰', 0, 0, 'C'); // Ícono de caja/venta
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
        $this->Cell(80, 6, 'Recibo de Caja - Venta de Productos', 0, 0, 'L');
        
        // Información del recibo (lado derecho)
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(220, 38, 38); // Rojo para destacar
        $this->SetXY(130, 16);
        $this->Cell(60, 8, 'RECIBO N° ' . $this->numeroRecibo, 0, 1, 'R');
        
        // Fecha
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(71, 85, 105);
        $this->SetXY(130, 26);
        $this->Cell(60, 6, 'Fecha: ' . $this->fechaRecibo, 0, 1, 'R');
        
        // Cliente
        $this->SetFont('helvetica', 'B', 10);
        $this->SetTextColor(15, 23, 42);
        $this->SetXY(130, 34);
        $this->Cell(60, 6, 'Cliente: ' . $this->cliente, 0, 1, 'R');
        
        // Línea decorativa
        $this->SetDrawColor(59, 130, 246);
        $this->SetLineWidth(1);
        $this->Line(15, 48, $this->getPageWidth() - 15, 48);
        
        $this->Ln(15);
    }
    
    public function Footer() {
        $this->SetY(-25);
        
        // Línea superior
        $this->SetDrawColor(203, 213, 225);
        $this->Line(15, $this->GetY(), $this->getPageWidth() - 15, $this->GetY());
        
        $this->Ln(3);
        
        // Información del pie
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 5, 'Sistema de Inventario - Ferretería Costa Sur', 0, 1, 'C');
        $this->Cell(0, 5, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages() . ' - Generado: ' . date('d/m/Y H:i:s'), 0, 1, 'C');
        
        // Nota legal
        $this->SetFont('helvetica', 'I', 7);
        $this->SetTextColor(107, 114, 128);
        $this->Cell(0, 4, 'DOCUMENTO NO FISCAL - Para uso interno y control de inventario', 0, 0, 'C');
    }
}

class ReciboController {
    private $conn;
    
    public function __construct() {
        $this->conn = Conexion::conectar();
    }
    
    public function generarReciboVenta($egresoId) {
        try {
            // Obtener datos del egreso
            $egreso = Egreso::obtenerPorIdConDetalles($egresoId);
            
            if (!$egreso) {
                throw new Exception('Egreso no encontrado');
            }
            
            // Crear PDF
            $pdf = new PDF_ReciboCaja('P', 'mm', 'A4', true, 'UTF-8', false);
            $pdf->SetCreator('Sistema de Inventario');
            $pdf->SetAuthor('Ferretería Costa Sur');
            $pdf->SetTitle('Recibo de Caja N° ' . $egreso['numero']);
            $pdf->SetSubject('Recibo de Venta - Control de Inventario');
            
            // Configurar datos del recibo
            $fechaFormateada = date('d/m/Y', strtotime($egreso['fecha']));
            $clienteNombre = $egreso['cliente_nombre'] ?? 'CLIENTE GENERAL';
            $pdf->setDatosRecibo($egreso['numero'], $fechaFormateada, $clienteNombre);
            
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            $pdf->SetMargins(15, 55, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(15);
            $pdf->SetAutoPageBreak(TRUE, 30);
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            
            $pdf->AddPage();
            
            // INFORMACIÓN GENERAL DEL RECIBO
            $this->agregarInformacionGeneral($pdf, $egreso);
            
            // TABLA DE PRODUCTOS VENDIDOS
            $this->agregarTablaProductos($pdf, $egreso['detalles']);
            
            // TOTALES
            $this->agregarTotales($pdf, $egreso);
            
            // OBSERVACIONES
            $this->agregarObservaciones($pdf, $egreso);
            
            // SECCIÓN DE PAGO Y FIRMAS
            $this->agregarSeccionPago($pdf);
            
            // Salida del PDF
            $nombreArchivo = 'Recibo_Venta_' . $egreso['numero'] . '_' . date('Y-m-d') . '.pdf';
            $pdf->Output($nombreArchivo, 'I');
            
        } catch (Exception $e) {
            die('Error al generar recibo: ' . $e->getMessage());
        }
    }
    
    private function agregarInformacionGeneral($pdf, $egreso) {
        // Recuadro de información general
        $pdf->SetFillColor(239, 246, 255);
        $pdf->SetDrawColor(59, 130, 246);
        $pdf->Rect(15, $pdf->GetY(), $pdf->getPageWidth() - 30, 25, 'FD');
        
        $yInicial = $pdf->GetY() + 3;
        
        // Columna izquierda
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(30, 64, 175);
        $pdf->SetXY(20, $yInicial);
        $pdf->Cell(40, 6, 'DATOS DE LA VENTA', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->SetX(20);
        $pdf->Cell(35, 5, 'N° Recibo:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(40, 5, $egreso['numero'], 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->SetX(20);
        $pdf->Cell(35, 5, 'Fecha:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(40, 5, date('d/m/Y', strtotime($egreso['fecha'])), 0, 1, 'L');
        
        // Columna derecha
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(30, 64, 175);
        $pdf->SetXY(110, $yInicial);
        $pdf->Cell(40, 6, 'CLIENTE', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->SetX(110);
        $pdf->Cell(25, 5, 'Nombre:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(60, 5, $egreso['cliente_nombre'] ?? 'CLIENTE GENERAL', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(55, 65, 81);
        $pdf->SetX(110);
        $pdf->Cell(25, 5, 'Tipo Venta:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(60, 5, 'CONTADO', 0, 1, 'L');
        
        $pdf->Ln(8);
    }
    
    private function agregarTablaProductos($pdf, $detalles) {
        // Título de la tabla
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(0, 8, 'DETALLE DE PRODUCTOS VENDIDOS', 0, 1, 'C');
        $pdf->Ln(2);
        
        // Encabezados de la tabla
        $pdf->SetFillColor(59, 130, 246); // Azul
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(59, 130, 246);
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
        $pdf->SetFillColor(239, 246, 255);
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
    
    private function agregarTotales($pdf, $egreso) {
        // Recuadro de totales
        $pdf->SetFillColor(240, 253, 244);
        $pdf->SetDrawColor(34, 197, 94);
        $pdf->SetLineWidth(0.3);
        
        $x = $pdf->getPageWidth() - 80;
        $y = $pdf->GetY();
        $pdf->Rect($x, $y, 65, 35, 'FD');
        
        // Totales
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(22, 163, 74);
        $pdf->SetXY($x + 5, $y + 3);
        $pdf->Cell(55, 6, 'RESUMEN DE VENTA', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(55, 65, 81);
        
        // Subtotal
        $pdf->SetX($x + 5);
        $pdf->Cell(35, 5, 'Subtotal:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(20, 5, 'Q' . number_format($egreso['subtotal'] ?? 0, 2), 0, 1, 'R');
        
        // IVA
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetX($x + 5);
        $pdf->Cell(35, 5, 'IVA (12%):', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(20, 5, 'Q' . number_format($egreso['iva'] ?? 0, 2), 0, 1, 'R');
        
        // Total
        $pdf->SetDrawColor(34, 197, 94);
        $pdf->Line($x + 5, $pdf->GetY() + 1, $x + 60, $pdf->GetY() + 1);
        $pdf->Ln(2);
        
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(22, 163, 74);
        $pdf->SetX($x + 5);
        $pdf->Cell(35, 6, 'TOTAL A PAGAR:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(20, 6, 'Q' . number_format($egreso['total'], 2), 0, 1, 'R');
        
        $pdf->Ln(10);
    }
    
    private function agregarObservaciones($pdf, $egreso) {
        if (!empty($egreso['observaciones'])) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->SetTextColor(55, 65, 81);
            $pdf->Cell(0, 6, 'OBSERVACIONES:', 0, 1, 'L');
            
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(75, 85, 99);
            $pdf->MultiCell(0, 5, $egreso['observaciones'], 0, 'L');
            $pdf->Ln(5);
        }
    }
    
    private function agregarSeccionPago($pdf) {
        $pdf->Ln(5);
        
        // Sección de pago
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(30, 64, 175);
        $pdf->Cell(0, 6, 'INFORMACIÓN DE PAGO', 0, 1, 'C');
        $pdf->Ln(3);
        
        // Recuadro para información de pago
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(156, 163, 175);
        $pdf->Rect(15, $pdf->GetY(), $pdf->getPageWidth() - 30, 20, 'FD');
        
        $yPago = $pdf->GetY() + 3;
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(75, 85, 99);
        $pdf->SetXY(20, $yPago);
        $pdf->Cell(40, 5, 'Forma de Pago:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(50, 5, 'EFECTIVO', 0, 0, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(75, 85, 99);
        $pdf->SetX(110);
        $pdf->Cell(30, 5, 'Recibido por:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(50, 5, '______________________', 0, 1, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(75, 85, 99);
        $pdf->SetX(20);
        $pdf->Cell(40, 5, 'Estado:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(22, 163, 74);
        $pdf->Cell(50, 5, 'PAGADO', 0, 0, 'L');
        
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(75, 85, 99);
        $pdf->SetX(110);
        $pdf->Cell(30, 5, 'Fecha:', 0, 0, 'L');
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(15, 23, 42);
        $pdf->Cell(50, 5, date('d/m/Y'), 0, 1, 'L');
        
        $pdf->Ln(10);
        
        // Nota importante
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetTextColor(220, 38, 38);
        $pdf->Cell(0, 5, 'IMPORTANTE: Conserve este recibo como comprobante de su compra', 0, 1, 'C');
    }
}

// Manejar las peticiones
if (isset($_GET['action']) && $_GET['action'] === 'recibo' && isset($_GET['id'])) {
    $controller = new ReciboController();
    $controller->generarReciboVenta(intval($_GET['id']));
} else {
    die('Acción no válida');
}
?>