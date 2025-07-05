<?php
require_once __DIR__ . '/../models/proveedor.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');
    
    // Suponiendo que estás usando categoria_id como filtro:
    $categoria_id = $_SESSION['categoria_id'] ?? 1; // puedes ajustar este valor según tu lógica
    $proveedores = Proveedor::obtenerTodos();
    
    echo json_encode($proveedores);
    exit;
}
