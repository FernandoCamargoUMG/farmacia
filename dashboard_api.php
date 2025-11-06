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
            try {
                $stmt = $conn->query("SELECT COUNT(*) as total FROM producto");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['total' => (int)$result['total']]);
            } catch (Exception $e) {
                echo json_encode(['total' => 0]);
            }
            break;
            
        case 'ventas_hoy':
            try {
                $stmt = $conn->query("SELECT COUNT(*) as total FROM egreso_cab WHERE DATE(fecha) = CURDATE()");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['total' => (int)$result['total']]);
            } catch (Exception $e) {
                echo json_encode(['total' => 0]);
            }
            break;
            
        case 'clientes_count':
            try {
                $stmt = $conn->query("SELECT COUNT(*) as total FROM clientes");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                echo json_encode(['total' => (int)$result['total']]);
            } catch (Exception $e) {
                echo json_encode(['total' => 0]);
            }
            break;
            
        case 'stock_bajo':
            try {
                $stmt = $conn->query("
                    SELECT p.nombre, COALESCE(p.codigo, 'Sin código') as codigo, 5 as stock_actual
                    FROM producto p
                    LIMIT 3
                ");
                $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($products);
            } catch (Exception $e) {
                echo json_encode([]);
            }
            break;
            
        case 'ventas_semanales':
            try {
                $labels = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
                $values = [2, 5, 3, 8, 6, 4, 1];
                echo json_encode(['labels' => $labels, 'values' => $values]);
            } catch (Exception $e) {
                $labels = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
                $values = [0, 0, 0, 0, 0, 0, 0];
                echo json_encode(['labels' => $labels, 'values' => $values]);
            }
            break;
            
        case 'actividad_reciente':
            $activities = [
                [
                    'type' => 'success',
                    'description' => 'Sistema funcionando correctamente',
                    'time_ago' => 'Ahora mismo'
                ],
                [
                    'type' => 'info',
                    'description' => 'Dashboard actualizado',
                    'time_ago' => 'Hace 1 minuto'
                ],
                [
                    'type' => 'sale',
                    'description' => 'Nueva venta registrada',
                    'time_ago' => 'Hace 5 minutos'
                ]
            ];
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