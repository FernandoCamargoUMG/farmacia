<?php
require_once __DIR__ . '/../config/conexion.php';

$q = $_GET['q'] ?? '';
$conn = Conexion::conectar();

$stmt = $conn->prepare("SELECT id, nombre, apellido FROM clientes WHERE nombre LIKE ?");
$stmt->execute(["%$q%"]);

$resultados = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $resultados[] = [
        'id' => $row['id'],
        'label' => $row['nombre'] . ' ' . $row['apellido']
    ];
}

header('Content-Type: application/json');
echo json_encode($resultados);
