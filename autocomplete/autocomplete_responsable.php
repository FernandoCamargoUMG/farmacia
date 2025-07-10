<?php
require_once __DIR__ . '/../config/conexion.php';

$term = $_GET['term'] ?? '';
$conn = Conexion::conectar();

$stmt = $conn->prepare("SELECT id, nombre, apellido FROM responsable WHERE nombre LIKE ? OR apellido LIKE ?");
$stmt->execute(["%$term%", "%$term%"]);

$resultados = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $resultados[] = [
        'id' => $row['id'],
        'label' => $row['nombre'] . ' ' . $row['apellido'],
        'value' => $row['nombre'] . ' ' . $row['apellido']
    ];
}

header('Content-Type: application/json');
echo json_encode($resultados);
