<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/shutdown.php';

// Establecer zona horaria para Guatemala
date_default_timezone_set('America/Guatemala');

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
        // Últimas ventas (egresos) - últimos 7 días
        $stmt = $conn->prepare("
            SELECT 'sale' as type, 
                   CONCAT('Nueva venta #', numero, ' por Q', ROUND(total, 2)) as description, 
                   fecha,
                   TIMESTAMPDIFF(MINUTE, fecha, NOW()) as minutes_ago
            FROM egreso_cab 
            WHERE fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND sta = 1
            ORDER BY fecha DESC 
            LIMIT 3
        ");
        $stmt->execute();
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($sales as $sale) {
            // Calcular tiempo transcurrido en PHP para mayor precisión
            $fecha_venta = new DateTime($sale['fecha']);
            $ahora = new DateTime();
            $diff = $ahora->diff($fecha_venta);
            
            // Calcular minutos totales transcurridos
            $minutes_ago = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
            
            // Si es muy reciente (menos de 2 minutos), considerar como "ahora mismo"
            if ($minutes_ago < 2) {
                $time_display = 'Ahora mismo';
            } else {
                $time_display = formatTimeAgo($minutes_ago);
            }
            
            $activities[] = [
                'type' => $sale['type'],
                'description' => $sale['description'],
                'time_ago' => $time_display
            ];
        }
        
        // Últimos ingresos de productos
        $stmt = $conn->prepare("
            SELECT 'success' as type, 
                   CONCAT('Ingreso de productos #', numero) as description, 
                   fecha
            FROM ingreso_cab 
            WHERE fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)
            AND sta = 1
            ORDER BY fecha DESC 
            LIMIT 2
        ");
        $stmt->execute();
        $ingresos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($ingresos as $ingreso) {
            // Calcular tiempo transcurrido en PHP
            $fecha_ingreso = new DateTime($ingreso['fecha']);
            $ahora = new DateTime();
            $diff = $ahora->diff($fecha_ingreso);
            
            $minutes_ago = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
            
            // Si es muy reciente, mostrar como "ahora mismo"
            if ($minutes_ago < 2) {
                $time_display = 'Ahora mismo';
            } else {
                $time_display = formatTimeAgo($minutes_ago);
            }
            
            $activities[] = [
                'type' => $ingreso['type'],
                'description' => $ingreso['description'],
                'time_ago' => $time_display
            ];
        }
        
        // Movimientos de caja recientes
        $stmt = $conn->prepare("
            SELECT 'info' as type, 
                   CONCAT('Movimiento de caja: ', descripcion) as description, 
                   fecha
            FROM movimiento_caja 
            WHERE fecha >= DATE_SUB(NOW(), INTERVAL 3 DAY)
            ORDER BY fecha DESC 
            LIMIT 2
        ");
        $stmt->execute();
        $movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($movimientos as $movimiento) {
            // Calcular tiempo transcurrido en PHP
            $fecha_movimiento = new DateTime($movimiento['fecha']);
            $ahora = new DateTime();
            $diff = $ahora->diff($fecha_movimiento);
            
            $minutes_ago = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
            
            // Si es muy reciente, mostrar como "ahora mismo"
            if ($minutes_ago < 2) {
                $time_display = 'Ahora mismo';
            } else {
                $time_display = formatTimeAgo($minutes_ago);
            }
            
            $activities[] = [
                'type' => $movimiento['type'],
                'description' => $movimiento['description'],
                'time_ago' => $time_display
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
        $stmt = $conn->query("SELECT COUNT(*) as total FROM producto");
        $stats['total_productos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Ventas de hoy (egresos emitidos)
        $stmt = $conn->query("SELECT COUNT(*) as total FROM egreso_cab WHERE DATE(fecha) = CURDATE() AND sta = 1");
        $stats['ventas_hoy'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total clientes
        $stmt = $conn->query("SELECT COUNT(*) as total FROM clientes");
        $stats['total_clientes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Total de productos con movimientos (usando inventario)
        $stmt = $conn->query("
            SELECT COUNT(DISTINCT producto_id) as total 
            FROM inventario 
            WHERE sucursal_id = 1
        ");
        $stats['productos_activos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
    } catch (Exception $e) {
        error_log("Error en getDashboardStats: " . $e->getMessage());
        $stats = [
            'total_productos' => 0,
            'ventas_hoy' => 0,
            'total_clientes' => 0,
            'productos_activos' => 0
        ];
    }
    
    return $stats;
}

function formatTimeAgo($minutes) {
    if ($minutes < 0) {
        return 'Ahora mismo';
    } elseif ($minutes < 1) {
        return 'Hace menos de un minuto';
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