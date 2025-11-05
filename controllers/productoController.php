<?php
require_once __DIR__ . '/../models/producto.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');
    $producto = Producto::obtenerTodos();
    echo json_encode($producto);
    exit;
}

if ($action === 'guardar') {
    if (!isset($_SESSION['sucursal_id'])){
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }
    
    $exito = Producto::guardar(
        $_POST['categoria_id'],
        $_POST['codigo'] ?? '',
        $_POST['nombre'] ?? '',
        $_POST['descripcion']?? '',
        $_POST['precio']?? ''
        );
        
    
    echo json_encode(['success' => true]);
}

if ($action === 'ver') {
    $id = $_GET['id'] ?? 0;
    echo json_encode(Producto::obtenerPorId($id));
}


if ($action === 'actualizar') {
    $id = $_POST['id'] ?? 0;
    $exito = Producto::actualizar($id, $_POST);
    echo json_encode(['success' => $exito]);
}

if ($action === 'eliminar') {
    $id = $_POST['id'] ?? 0;
    $exito = Producto::eliminar($id);
    echo json_encode(['success' => $exito]);
}

if ($action === 'count') {
    header('Content-Type: application/json');
    $total = Producto::contarTotal();
    echo json_encode(['total' => $total]);
}
