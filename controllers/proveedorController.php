<?php
require_once __DIR__ . '/../models/proveedor.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');
    //$categoria_id = $_SESSION['categoria_id'] ?? 1; // puedes ajustar este valor según tu lógica
    $proveedores = Proveedor::obtenerTodos();
    echo json_encode($proveedores);
    exit;
}

if ($action === 'guardar') {
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
        $_POST['email']
        );
        
    
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
}
