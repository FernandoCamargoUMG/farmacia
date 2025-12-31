<?php
require_once __DIR__ . '/../models/producto.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');
    $producto = Producto::obtenerTodos($_SESSION['sucursal_id']);
    echo json_encode($producto);
    exit;
}

if ($action === 'guardar') {
    if (!isset($_SESSION['sucursal_id'])){
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }
    
    // El código se generará automáticamente si no se proporciona
    $exito = Producto::guardar(
        $_POST['categoria_id'],
        $_SESSION['sucursal_id'],
        $_POST['codigo'] ?? '',
        $_POST['nombre'] ?? '',
        $_POST['descripcion']?? '',
        $_POST['precio']?? ''
        );
        
    
    echo json_encode(['success' => true]);
}

if ($action === 'ver') {
    $id = $_GET['id'] ?? 0;
    $sucursal_id = $_SESSION['sucursal_id'] ?? null;
    echo json_encode(Producto::obtenerPorId($id, $sucursal_id));
}


if ($action === 'actualizar') {
    $id = $_POST['id'] ?? 0;
    $sucursal_id = $_SESSION['sucursal_id'] ?? null;
    $exito = Producto::actualizar($id, $_POST, $sucursal_id);
    echo json_encode(['success' => $exito]);
}

if ($action === 'eliminar') {
    $id = $_POST['id'] ?? 0;
    $sucursal_id = $_SESSION['sucursal_id'] ?? null;
    $exito = Producto::eliminar($id, $sucursal_id);
    echo json_encode(['success' => $exito]);
}

if ($action === 'count') {
    header('Content-Type: application/json');
    $sucursal_id = $_SESSION['sucursal_id'] ?? null;
    $total = Producto::contarTotal($sucursal_id);
    echo json_encode(['total' => $total]);
}
