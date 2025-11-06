<?php
// Test rápido del dashboard API
session_start();

// Simular sesión para test
$_SESSION['usuario_id'] = 5;
$_SESSION['sucursal_id'] = 1;

echo "<h1>Test Dashboard API - Hostinger Production</h1>";

// Test básico de conexión
echo "<h2>1. Test de Conexión a BD</h2>";
try {
    require_once 'config/conexion.php';
    $conn = Conexion::conectar();
    echo "<p style='color: green;'>✓ Conexión exitosa a: " . $conn->getAttribute(PDO::ATTR_SERVER_INFO) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}

// Test de endpoints del API
$endpoints = [
    'productos_count' => 'Conteo de productos',
    'ventas_hoy' => 'Ventas de hoy',
    'clientes_count' => 'Conteo de clientes',
    'stock_bajo' => 'Stock bajo',
    'ventas_semanales' => 'Ventas semanales (GRÁFICO)',
    'actividad_reciente' => 'Actividad reciente'
];

foreach ($endpoints as $endpoint => $descripcion) {
    echo "<h3>2. Test: $descripcion ($endpoint)</h3>";
    
    // Hacer request directo al API
    $url = "http://localhost/farmacia/dashboard_api.php?action=$endpoint";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 10,
            'header' => [
                'Cookie: ' . session_name() . '=' . session_id()
            ]
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        echo "<p style='color: red;'>✗ No se pudo obtener respuesta</p>";
    } else {
        echo "<p style='color: green;'>✓ Respuesta obtenida</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; max-height: 200px; overflow: auto;'>";
        echo htmlspecialchars($response);
        echo "</pre>";
        
        // Verificar si es JSON válido
        $json = @json_decode($response, true);
        if ($json === null) {
            echo "<p style='color: orange;'>⚠ La respuesta no es JSON válido</p>";
        } else {
            echo "<p style='color: green;'>✓ JSON válido</p>";
        }
    }
    echo "<hr>";
}

echo "<h2>3. Test directo de consultas SQL</h2>";

// Test directo de consultas
$queries = [
    'Productos' => "SELECT COUNT(*) as total FROM producto",
    'Clientes' => "SELECT COUNT(*) as total FROM clientes",
    'Ventas hoy' => "SELECT COUNT(*) as total FROM egreso_cab WHERE DATE(fecha) = CURDATE() AND sta = 1",
    'Ventas últimos 7 días' => "SELECT DATE(fecha) as fecha, COUNT(*) as total FROM egreso_cab WHERE DATE(fecha) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND sta = 1 GROUP BY DATE(fecha) ORDER BY fecha"
];

foreach ($queries as $name => $query) {
    echo "<h4>$name</h4>";
    try {
        $stmt = $conn->query($query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre style='background: #e8f5e8; padding: 10px;'>";
        print_r($results);
        echo "</pre>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}
?>