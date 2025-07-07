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
/*
if ($_GET['action'] === 'guardar') {
    //$stmt = $conn->prepare("INSERT INTO proveedor (categoria_id, codigo, nombre, nit, direccion, telefono, email) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if(!isset($_SESSION['sucursal_id'])){
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }
    
    $exito = Proveedor::guardar(
        $_POST['categoria_id'],
        $_POST['codigo'],
        $_POST['nombre'],
        $_POST['nit'],
        $_POST['direccion'],
        $_POST['telefono'],
        $_POST['email']);
        
    
    echo json_encode(['success' => true]);
}

if ($action === 'ver') {
    $id = $_GET['id'] ?? 0;
    echo json_encode(Proveedor::obtenerPorId($id));
}


if ($action === 'actualizar') {
    $id = $_POST['id'] ?? 0;
    $exito = Proveedor::actualizar($id, $_POST);
    echo json_encode(['success' => $exito]);
}

if ($action === 'eliminar') {
    $id = $_POST['id'] ?? 0;
    $exito = Proveedor::eliminar($id);
    echo json_encode(['success' => $exito]);
}*/
