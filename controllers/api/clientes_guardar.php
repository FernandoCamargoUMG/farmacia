<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

// Validar sesión
if (!isset($_SESSION['sucursal_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Conexión
$conn = Conexion::conectar();

// Preparar consulta con todos los campos
$stmt = $conn->prepare("
    INSERT INTO clientes 
    (sucursal_id, nombre, apellido, dpi, email, direccion, telefono, nit) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$exito = $stmt->execute([
    $_SESSION['sucursal_id'],
    $_POST['nombre'] ?? '',
    $_POST['apellido'] ?? '',
    $_POST['dpi'] ?? '',
    $_POST['email'] ?? '',
    $_POST['direccion'] ?? '',
    $_POST['telefono'] ?? '',
    $_POST['nit'] ?? ''
]);

echo json_encode(['success' => $exito]);
