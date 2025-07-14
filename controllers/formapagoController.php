<?php
require_once __DIR__ . '/../models/forma_pago.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');
    $cat_activo = formapago::obtenerTodos();
    echo json_encode($cat_activo);
    exit;
}

if ($action === 'guardar') {
    if (!isset($_SESSION['sucursal_id'])){
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }
    
    $exito = formapago::guardar(
        $_POST['descripcion']
        //$_POST['precio']
    );
        
    
    echo json_encode(['success' => true]);
}

if ($action === 'ver') {
    $id = $_GET['id'] ?? 0;
    echo json_encode(formapago::obtenerPorId($id));
}


if ($action === 'actualizar') {
    $id = $_POST['id'] ?? 0;
    $exito = formapago::actualizar($id, $_POST);
    echo json_encode(['success' => $exito]);
}

if ($action === 'eliminar') {
    $id = $_POST['id'] ?? 0;
    $exito = formapago::eliminar($id);
    echo json_encode(['success' => $exito]);
}
