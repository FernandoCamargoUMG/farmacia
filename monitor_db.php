<?php
// Script de monitoreo de conexiones DB
require_once __DIR__ . '/config/conexion.php';

header('Content-Type: application/json');

// Verificar conexiones activas
$conexiones_activas = Conexion::getConexionesActivas();

// Información del estado
$estado = [
    'timestamp' => date('Y-m-d H:i:s'),
    'conexiones_activas' => $conexiones_activas,
    'memoria_uso' => memory_get_usage(true),
    'memoria_pico' => memory_get_peak_usage(true),
    'estado' => $conexiones_activas > 10 ? 'warning' : 'ok'
];

// Si hay demasiadas conexiones, forzar limpieza
if ($conexiones_activas > 15) {
    Conexion::cerrarTodas();
    $estado['accion'] = 'Conexiones forzadas a cerrar';
    $estado['conexiones_activas'] = 0;
}

echo json_encode($estado, JSON_PRETTY_PRINT);
?>