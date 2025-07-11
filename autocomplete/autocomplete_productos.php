<?php
require_once __DIR__ . '/../config/conexion.php';

$q = $_GET['q'] ?? '';
$conn = Conexion::conectar();

$stmt = $conn->prepare("SELECT id, codigo, nombre, precio FROM producto WHERE nombre LIKE ? OR codigo LIKE ?");
$stmt->execute(["%$q%", "%$q%"]);

$resultados = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $resultados[] = [
        'id' => $row['id'],
        'label' => $row['codigo'] .' - '. $row['nombre'] . ' (Q' . $row['precio'] . ')',
        'precio' => $row['precio']
    ];
}

header('Content-Type: application/json');
echo json_encode($resultados);
