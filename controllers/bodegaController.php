<?php
require_once __DIR__ . '/../models/bodega.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');
    $bodega = Bodega::obtenerPorSucursal($_SESSION['sucursal_id']);
    echo json_encode($bodega);
    exit;
}

if ($action === 'guardar') {
    if (!isset($_SESSION['sucursal_id'])){
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }
    
    $exito = Bodega::guardar(
        $_SESSION['sucursal_id'],
        $_POST['nombre'] ?? '',
        $_POST['ubicacion']?? ''
        //$_POST['precio']
    );
        
    
    echo json_encode(['success' => true]);
}

if ($action === 'ver') {
    $id = $_GET['id'] ?? 0;
    echo json_encode(Bodega::obtenerPorId($id));
}


if ($action === 'actualizar') {
    $id = $_POST['id'] ?? 0;
    $exito = Bodega::actualizar($id, $_POST);
    echo json_encode(['success' => $exito]);
}

if ($action === 'eliminar') {
    $id = $_POST['id'] ?? 0;
    $exito = Bodega::eliminar($id);
    echo json_encode(['success' => $exito]);
}
