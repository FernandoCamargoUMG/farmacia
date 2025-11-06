<?php
// Archivo temporal para probar la API
session_start();

// Simular una sesión válida para la prueba
$_SESSION['usuario_id'] = 1;
$_SESSION['sucursal_id'] = 1;

require_once __DIR__ . '/config/conexion.php';

echo "<h2>Test de Dashboard API</h2>";

// Probar conexión a BD
try {
    $conn = Conexion::conectar();
    echo "<p>✅ Conexión a BD: OK</p>";
} catch (Exception $e) {
    echo "<p>❌ Error de conexión: " . $e->getMessage() . "</p>";
    exit;
}

// Probar cada endpoint
$endpoints = [
    'productos_count',
    'ventas_hoy', 
    'clientes_count',
    'stock_bajo',
    'ventas_semanales',
    'actividad_reciente'
];

foreach ($endpoints as $endpoint) {
    echo "<h3>Testing: $endpoint</h3>";
    
    // Simular la petición
    $_GET['action'] = $endpoint;
    
    ob_start();
    include 'dashboard_api.php';
    $output = ob_get_clean();
    
    echo "<pre>$output</pre>";
    echo "<hr>";
}
?>