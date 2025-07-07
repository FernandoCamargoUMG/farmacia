<?php
require_once __DIR__ . '/../config/conexion.php';

if ($_GET['action'] === 'listar') {
    $conn = Conexion::conectar();
    $stmt = $conn->query("SELECT * FROM categoria_producto");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($categorias);
}
