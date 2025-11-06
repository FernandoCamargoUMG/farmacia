<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Clase personalizada TCPDF para headers y footers
class PDF_Ferreteria extends TCPDF {
    private $reportTitle = '';
    
    public function setReportTitle($title) {
        $this->reportTitle = $title;
    }
    
    // Header personalizado
    public function Header() {
        // Logo o título principal
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(26, 43, 76);
        $this->Cell(0, 10, 'SISTEMA DE INVENTARIO - FERRETERIA', 0, 1, 'C');
        
        // Subtítulo del reporte
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 6, $this->reportTitle, 0, 1, 'C');
        
        // Línea separadora
        $this->Line(15, 30, $this->getPageWidth() - 15, 30);
        $this->Ln(5);
    }
    
    // Footer personalizado
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

class ReporteController {
    private $conn;
    
    public function __construct() {
        $this->conn = Conexion::conectar();
    }
    
    public function generarReporteInventario() {
        try {
            // Usar consulta mejorada que incluya código, precio y stock
            $sucursal_id = $_GET['sucursal_id'] ?? 1;
            
            // Consulta mejorada que incluye código, precio y stock real
            $query = "
                SELECT DISTINCT
                    p.codigo,
                    p.nombre AS producto,
                    p.descripcion,
                    p.precio as precio_venta,
                    b.nombre AS bodega,
                    s.nombre_sucursal AS sucursal,
                    COALESCE(
                        (SELECT SUM(
                            CASE 
                                WHEN inv.movimiento = 'ingreso' THEN inv.cantidad
                                WHEN inv.movimiento = 'egreso' THEN -inv.cantidad
                                ELSE 0
                            END
                        )
                        FROM inventario inv
                        WHERE inv.producto_id = p.id 
                          AND inv.bodega_id = b.id 
                          AND inv.sucursal_id = s.id), 0
                    ) AS stock_actual
                FROM producto p
                CROSS JOIN bodega b
                CROSS JOIN sucursal s
                WHERE s.id = ?
                  AND EXISTS (
                      SELECT 1 FROM inventario inv2 
                      WHERE inv2.producto_id = p.id 
                        AND inv2.bodega_id = b.id 
                        AND inv2.sucursal_id = s.id
                  )
                ORDER BY p.nombre, b.nombre
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$sucursal_id]);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si no hay datos con inventario, mostrar todos los productos
            if (empty($datos)) {
                $query_fallback = "
                    SELECT 
                        p.codigo,
                        p.nombre AS producto,
                        p.descripcion,
                        p.precio as precio_venta,
                        'ALMACEN 2' AS bodega,
                        s.nombre_sucursal AS sucursal,
                        0 as stock_actual
                    FROM producto p
                    CROSS JOIN sucursal s
                    WHERE s.id = ?
                    ORDER BY p.nombre
                ";
                
                $stmt = $this->conn->prepare($query_fallback);
                $stmt->execute([$sucursal_id]);
                $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            $this->generarPDFInventario($datos);
            
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function generarReporteBajoStock() {
        try {
            $sucursal_id = $_GET['sucursal_id'] ?? 1;
            
            // Consulta para productos con stock bajo (≤ 5 unidades) agrupado por producto
            $query = "
                SELECT 
                    p.codigo,
                    p.nombre AS producto,
                    p.descripcion,
                    p.precio as precio_venta,
                    'TODAS LAS BODEGAS' AS bodega,
                    s.nombre_sucursal AS sucursal,
                    5 as stock_minimo,
                    COALESCE(
                        (SELECT SUM(
                            CASE 
                                WHEN inv.movimiento = 'ingreso' THEN inv.cantidad
                                WHEN inv.movimiento = 'egreso' THEN -inv.cantidad
                                ELSE 0
                            END
                        )
                        FROM inventario inv
                        WHERE inv.producto_id = p.id 
                          AND inv.sucursal_id = s.id), 0
                    ) AS stock_actual
                FROM producto p
                CROSS JOIN sucursal s
                WHERE s.id = ?
                HAVING stock_actual <= 5
                ORDER BY stock_actual ASC, p.nombre
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$sucursal_id]);
            $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Si no hay productos con stock bajo, crear mensaje informativo
            if (empty($datos)) {
                $datos = [
                    [
                        'codigo' => 'N/A',
                        'producto' => 'NO HAY PRODUCTOS CON STOCK BAJO',
                        'descripcion' => 'Todos los productos tienen stock suficiente',
                        'precio_venta' => 0,
                        'bodega' => 'N/A',
                        'sucursal' => 'N/A',
                        'stock_minimo' => 5,
                        'stock_actual' => 0
                    ]
                ];
            }
            
            $this->generarPDFBajoStock($datos);
            
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    public function generarReporteMovimientos() {
        try {
            $fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
            $fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');
            $sucursal_id = $_GET['sucursal_id'] ?? 1;
            
            // Usar stored procedure para obtener movimientos
            $stmt = $this->conn->prepare("CALL sp_inventario(?)");
            $stmt->execute([$sucursal_id]);
            $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Filtrar por fechas si es necesario
            $datos = [];
            foreach ($movimientos as $mov) {
                $fecha_mov = date('Y-m-d', strtotime($mov['fecha']));
                if ($fecha_mov >= $fechaInicio && $fecha_mov <= $fechaFin) {
                    $datos[] = [
                        'fecha' => $mov['fecha'],
                        'codigo' => substr($mov['producto'], 0, 15), // Usar parte del nombre como código
                        'producto' => $mov['producto'],
                        'movimiento' => $mov['movimiento'],
                        'cantidad' => $mov['cantidad'],
                        'origen' => $mov['origen'],
                        'bodega' => $mov['bodega'],
                        'sucursal' => $mov['sucursal']
                    ];
                }
            }
            
            $this->generarPDFMovimientos($datos, $fechaInicio, $fechaFin);
            
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    private function generarPDFInventario($datos) {
        // Crear instancia de TCPDF personalizada
        $pdf = new PDF_Ferreteria('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setReportTitle('REPORTE DE INVENTARIO GENERAL');
        
        // Configurar documento
        $pdf->SetCreator('Sistema Ferreteria');
        $pdf->SetAuthor('Sistema de Inventario');
        $pdf->SetTitle('Reporte de Inventario General');
        $pdf->SetSubject('Inventario');
        
        // Configurar márgenes
        $pdf->SetMargins(15, 35, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        
        // Agregar página
        $pdf->AddPage();
        
        // Título principal
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(26, 43, 76); // Color azul oscuro
        $pdf->Cell(0, 10, 'INVENTARIO GENERAL', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Fecha del reporte
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 6, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
        $pdf->Ln(8);
        
        // Headers de tabla
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(26, 43, 76);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(26, 43, 76);
        
        $pdf->Cell(25, 8, 'Código', 1, 0, 'C', 1);
        $pdf->Cell(40, 8, 'Producto', 1, 0, 'C', 1);
        $pdf->Cell(35, 8, 'Sucursal', 1, 0, 'C', 1);
        $pdf->Cell(30, 8, 'Bodega', 1, 0, 'C', 1);
        $pdf->Cell(20, 8, 'Stock', 1, 0, 'C', 1);
        $pdf->Cell(25, 8, 'Precio Q', 1, 1, 'C', 1);
        
        // Datos
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        $totalValor = 0;
        $totalProductos = 0;
        
        if (empty($datos)) {
            // Mensaje cuando no hay datos
            $pdf->SetFont('helvetica', 'I', 12);
            $pdf->SetTextColor(150, 150, 150);
            $pdf->Cell(0, 20, 'No hay productos en el inventario para mostrar', 0, 1, 'C');
        } else {
            foreach ($datos as $fila) {
                $pdf->SetFillColor(248, 249, 250);
                
                $pdf->Cell(25, 6, $fila['codigo'] ?? 'N/A', 1, 0, 'C', 1);
                $pdf->Cell(40, 6, mb_substr($fila['producto'] ?? 'Sin nombre', 0, 25), 1, 0, 'L', 1);
                $pdf->Cell(35, 6, mb_substr($fila['sucursal'] ?? 'N/A', 0, 20), 1, 0, 'C', 1);
                $pdf->Cell(30, 6, mb_substr($fila['bodega'] ?? 'N/A', 0, 18), 1, 0, 'C', 1);
                $pdf->Cell(20, 6, number_format($fila['stock_actual'] ?? 0), 1, 0, 'C', 1);
                $pdf->Cell(25, 6, 'Q' . number_format($fila['precio_venta'] ?? 0, 2), 1, 1, 'R', 1);
                
                $totalValor += (($fila['stock_actual'] ?? 0) * ($fila['precio_venta'] ?? 0));
                $totalProductos += ($fila['stock_actual'] ?? 0);
            }
        }
        
        // Totales
        $pdf->Ln(5);
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(79, 209, 199);
        $pdf->SetTextColor(255, 255, 255);
        
        $pdf->Cell(130, 8, 'TOTALES:', 1, 0, 'R', 1);
        $pdf->Cell(20, 8, number_format($totalProductos), 1, 0, 'C', 1);
        $pdf->Cell(25, 8, 'Q' . number_format($totalValor, 2), 1, 1, 'R', 1);
        
        // Salida del PDF
        $pdf->Output('Reporte_Inventario_' . date('Y-m-d') . '.pdf', 'I');
    }
    
    private function generarPDFBajoStock($datos) {
        $pdf = new PDF_Ferreteria('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->setReportTitle('REPORTE DE STOCK BAJO');

        $pdf->SetCreator('Sistema Ferreteria');
        $pdf->SetAuthor('Sistema de Inventario');
        $pdf->SetTitle('Reporte de Stock Bajo');
        $pdf->SetSubject('Stock Bajo');
        
        $pdf->SetMargins(15, 35, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        
        $pdf->AddPage();
        
        // Título con alerta
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(220, 53, 69); // Color rojo
        $pdf->Cell(0, 10, '⚠️ PRODUCTOS CON STOCK BAJO', 0, 1, 'C');
        $pdf->Ln(5);
        
        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 6, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
        $pdf->Ln(8);
        
        // Headers
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(220, 53, 69);
        $pdf->SetTextColor(255, 255, 255);
        
        $pdf->Cell(25, 8, 'Código', 1, 0, 'C', 1);
        $pdf->Cell(50, 8, 'Producto', 1, 0, 'C', 1);
        $pdf->Cell(30, 8, 'Sucursal', 1, 0, 'C', 1);
        $pdf->Cell(25, 8, 'Stock', 1, 0, 'C', 1);
        $pdf->Cell(25, 8, 'Mínimo', 1, 0, 'C', 1);
        $pdf->Cell(20, 8, 'Estado', 1, 1, 'C', 1);
        
        // Datos
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        
        foreach ($datos as $fila) {
            $estado = $fila['stock_actual'] == 0 ? 'AGOTADO' : 'BAJO';
            $colorFondo = $fila['stock_actual'] == 0 ? [255, 235, 235] : [255, 248, 235];
            
            $pdf->SetFillColor($colorFondo[0], $colorFondo[1], $colorFondo[2]);
            
            $pdf->Cell(25, 6, $fila['codigo'], 1, 0, 'C', 1);
            $pdf->Cell(50, 6, mb_substr($fila['producto'], 0, 30), 1, 0, 'L', 1);
            $pdf->Cell(30, 6, mb_substr($fila['sucursal'] ?? 'N/A', 0, 18), 1, 0, 'C', 1);
            $pdf->Cell(25, 6, number_format($fila['stock_actual']), 1, 0, 'C', 1);
            $pdf->Cell(25, 6, number_format($fila['stock_minimo']), 1, 0, 'C', 1);
            $pdf->Cell(20, 6, $estado, 1, 1, 'C', 1);
        }
        
        $pdf->Output('Reporte_Stock_Bajo_' . date('Y-m-d') . '.pdf', 'I');
    }
    
    private function generarPDFMovimientos($datos, $fechaInicio, $fechaFin) {
        $pdf = new PDF_Ferreteria('L', 'mm', 'A4', true, 'UTF-8', false); // Horizontal
        $pdf->setReportTitle('REPORTE DE MOVIMIENTOS DE INVENTARIO');
        
        $pdf->SetCreator('Sistema Ferreteria');
        $pdf->SetAuthor('Sistema de Inventario');
        $pdf->SetTitle('Reporte de Movimientos');
        $pdf->SetSubject('Movimientos');
        
        $pdf->SetMargins(15, 35, 15);
        $pdf->SetAutoPageBreak(TRUE, 25);
        
        $pdf->AddPage();
        
        // Título y período
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(26, 43, 76);
        $pdf->Cell(0, 10, 'MOVIMIENTOS DE INVENTARIO', 0, 1, 'C');
        
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetTextColor(79, 209, 199);
        $pdf->Cell(0, 8, 'Período: ' . date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)), 0, 1, 'C');
        $pdf->Ln(8);
        
        // Headers
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(26, 43, 76);
        $pdf->SetTextColor(255, 255, 255);
        
        $pdf->Cell(25, 8, 'Fecha', 1, 0, 'C', 1);
        $pdf->Cell(25, 8, 'Código', 1, 0, 'C', 1);
        $pdf->Cell(60, 8, 'Producto', 1, 0, 'C', 1);
        $pdf->Cell(30, 8, 'Movimiento', 1, 0, 'C', 1);
        $pdf->Cell(20, 8, 'Cantidad', 1, 0, 'C', 1);
        $pdf->Cell(40, 8, 'Sucursal', 1, 0, 'C', 1);
        $pdf->Cell(35, 8, 'Bodega', 1, 0, 'C', 1);
        $pdf->Cell(35, 8, 'Origen', 1, 1, 'C', 1);
        
        // Datos
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(0, 0, 0);
        
        foreach ($datos as $fila) {
            $colorFondo = $fila['movimiento'] == 'entrada' ? [230, 255, 230] : [255, 230, 230];
            $pdf->SetFillColor($colorFondo[0], $colorFondo[1], $colorFondo[2]);
            
            $pdf->Cell(25, 6, date('d/m/Y', strtotime($fila['fecha'])), 1, 0, 'C', 1);
            $pdf->Cell(25, 6, $fila['codigo'], 1, 0, 'C', 1);
            $pdf->Cell(60, 6, mb_substr($fila['producto'], 0, 35), 1, 0, 'L', 1);
            $pdf->Cell(30, 6, strtoupper($fila['movimiento']), 1, 0, 'C', 1);
            $pdf->Cell(20, 6, number_format($fila['cantidad']), 1, 0, 'C', 1);
            $pdf->Cell(40, 6, mb_substr($fila['sucursal'], 0, 22), 1, 0, 'C', 1);
            $pdf->Cell(35, 6, mb_substr($fila['bodega'], 0, 20), 1, 0, 'C', 1);
            $pdf->Cell(35, 6, mb_substr($fila['origen'], 0, 20), 1, 1, 'C', 1);
        }
        
        $pdf->Output('Reporte_Movimientos_' . date('Y-m-d') . '.pdf', 'I');
    }
    
    public function testDatos() {
        try {
            // Contar productos
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM producto");
            $totalProductos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Contar movimientos de inventario
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM inventario");
            $totalMovimientos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Obtener algunos productos
            $stmt = $this->conn->query("SELECT codigo, nombre, precio FROM producto LIMIT 5");
            $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'total_productos' => $totalProductos,
                'total_movimientos' => $totalMovimientos,
                'productos_muestra' => $productos
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}

// Procesar solicitud
if (isset($_GET['action'])) {
    $controller = new ReporteController();
    
    switch ($_GET['action']) {
        case 'inventario':
            $controller->generarReporteInventario();
            break;
        case 'bajo_stock':
            $controller->generarReporteBajoStock();
            break;
        case 'movimientos':
            $controller->generarReporteMovimientos();
            break;
        case 'test':
            $controller->testDatos();
            break;
        default:
            echo json_encode(['error' => 'Acción no válida']);
    }
}
?>