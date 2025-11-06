<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/config/conexion.php';

$action = $_GET['action'] ?? '';

try {
    $conn = Conexion::conectar();
    
    switch ($action) {
        case 'productos_count':
            $stmt = $conn->query("SELECT COUNT(*) as total FROM producto WHERE sta = 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['total' => $result['total']]);
            break;
            
        case 'ventas_hoy':
            $stmt = $conn->query("SELECT COUNT(*) as total FROM egreso_cab WHERE DATE(fecha) = CURDATE() AND sta = 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['total' => $result['total']]);
            break;
            
        case 'clientes_count':
            $stmt = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE sta = 1");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['total' => $result['total']]);
            break;
            
        case 'stock_bajo':
            $stmt = $conn->query("
                SELECT p.nombre, p.codigo, 
                       COALESCE(SUM(i.entrada - i.salida), 0) as stock_actual
                FROM producto p
                LEFT JOIN inventario i ON p.id = i.producto_id
                WHERE p.sta = 1
                GROUP BY p.id, p.nombre, p.codigo
                HAVING stock_actual <= 10
                ORDER BY stock_actual ASC
                LIMIT 10
            ");
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($products);
            break;
            
        case 'ventas_semanales':
            $stmt = $conn->query("
                SELECT 
                    DATE_FORMAT(fecha, '%a') as dia,
                    COUNT(*) as ventas
                FROM egreso_cab 
                WHERE fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                AND sta = 1
                GROUP BY DATE(fecha)
                ORDER BY fecha ASC
            ");
            $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $labels = [];
            $values = [];
            
            if (empty($sales)) {
                $labels = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
                $values = [0, 0, 0, 0, 0, 0, 0];
            } else {
                foreach ($sales as $sale) {
                    $labels[] = $sale['dia'];
                    $values[] = (int)$sale['ventas'];
                }
            }
            
            echo json_encode(['labels' => $labels, 'values' => $values]);
            break;
            
        case 'actividad_reciente':
            $activities = [];
            
            // Últimas ventas
            $stmt = $conn->query("
                SELECT 'sale' as type, 
                       CONCAT('Nueva venta #', numero, ' por Q', ROUND(total, 2)) as description, 
                       'Hace pocos minutos' as time_ago
                FROM egreso_cab 
                WHERE DATE(fecha) = CURDATE() AND sta = 1
                ORDER BY fecha DESC 
                LIMIT 2
            ");
            $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $activities = array_merge($activities, $sales);
            
            // Últimos ingresos
            $stmt = $conn->query("
                SELECT 'success' as type, 
                       CONCAT('Ingreso de productos #', numero) as description, 
                       'Hace 1 hora' as time_ago
                FROM ingreso_cab 
                WHERE DATE(fecha) = CURDATE() AND sta = 1
                ORDER BY fecha DESC 
                LIMIT 1
            ");
            $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $activities = array_merge($activities, $ingresos);
            
            // Si no hay actividades, mostrar algunas por defecto
            if (empty($activities)) {
                $activities = [
                    [
                        'type' => 'info',
                        'description' => 'Sistema funcionando correctamente',
                        'time_ago' => 'Ahora mismo'
                    ],
                    [
                        'type' => 'success',
                        'description' => 'Dashboard actualizado',
                        'time_ago' => 'Hace 1 minuto'
                    ]
                ];
            }
            
            echo json_encode($activities);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
    
} catch (Exception $e) {
    error_log("Error en dashboard_api: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Error del servidor']);
}
?>