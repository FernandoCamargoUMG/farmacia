<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/shutdown.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    $conn = Conexion::conectar();
    
    switch ($action) {
        case 'recent_activity':
            echo json_encode(getRecentActivity($conn));
            break;
            
        case 'stats':
            echo json_encode(getDashboardStats($conn));
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error del servidor: ' . $e->getMessage()]);
}

function getRecentActivity($conn) {
    $activities = [];
    
    try {
        // Últimas ventas (egresos)
        $stmt = $conn->prepare("
            SELECT 'sale' as type, CONCAT('Nueva venta #', numero, ' por $', ROUND(total, 2)) as description, 
                   TIMESTAMPDIFF(MINUTE, fecha, NOW()) as minutes_ago
            FROM egreso_cab 
            WHERE DATE(fecha) = CURDATE() 
            ORDER BY fecha DESC 
            LIMIT 3
        ");
        $stmt->execute();
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($sales as $sale) {
            $activities[] = [
                'type' => $sale['type'],
                'description' => $sale['description'],
                'time_ago' => formatTimeAgo($sale['minutes_ago'])
            ];
        }
        
        // Productos con stock bajo
        $stmt = $conn->prepare("
            SELECT 'warning' as type, CONCAT('Stock bajo: ', nombre) as description, 
                   5 as minutes_ago
            FROM producto 
            WHERE stock_actual <= stock_minimo 
            ORDER BY stock_actual ASC 
            LIMIT 2
        ");
        $stmt->execute();
        $lowStock = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($lowStock as $item) {
            $activities[] = [
                'type' => $item['type'],
                'description' => $item['description'],
                'time_ago' => 'Detectado recientemente'
            ];
        }
        
        // Si no hay actividades, mostrar algunas por defecto
        if (empty($activities)) {
            $activities = [
                [
                    'type' => 'info',
                    'description' => 'Sistema iniciado correctamente',
                    'time_ago' => 'Hace pocos minutos'
                ],
                [
                    'type' => 'success',
                    'description' => 'Dashboard actualizado',
                    'time_ago' => 'Ahora mismo'
                ]
            ];
        }
        
    } catch (Exception $e) {
        error_log("Error en getRecentActivity: " . $e->getMessage());
        return [];
    }
    
    // Ordenar por tiempo y limitar a 5
    usort($activities, function($a, $b) {
        return strcmp($a['time_ago'], $b['time_ago']);
    });
    
    return array_slice($activities, 0, 5);
}

function getDashboardStats($conn) {
    $stats = [];
    
    try {
        // Total productos
        $stmt = $conn->query("SELECT COUNT(*) as total FROM producto WHERE activo = 1");
        $stats['total_productos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Ventas de hoy
        $stmt = $conn->query("SELECT COUNT(*) as total FROM egreso_cab WHERE DATE(fecha) = CURDATE()");
        $stats['ventas_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total clientes
        $stmt = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1");
        $stats['total_clientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Stock bajo
        $stmt = $conn->query("SELECT COUNT(*) as total FROM producto WHERE stock_actual <= stock_minimo");
        $stats['stock_bajo'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
    } catch (Exception $e) {
        error_log("Error en getDashboardStats: " . $e->getMessage());
        $stats = [
            'total_productos' => 0,
            'ventas_hoy' => 0,
            'total_clientes' => 0,
            'stock_bajo' => 0
        ];
    }
    
    return $stats;
}

function formatTimeAgo($minutes) {
    if ($minutes < 1) {
        return 'Ahora mismo';
    } elseif ($minutes < 60) {
        return "Hace $minutes minuto" . ($minutes != 1 ? 's' : '');
    } elseif ($minutes < 1440) {
        $hours = floor($minutes / 60);
        return "Hace $hours hora" . ($hours != 1 ? 's' : '');
    } else {
        $days = floor($minutes / 1440);
        return "Hace $days día" . ($days != 1 ? 's' : '');
    }
}
?>