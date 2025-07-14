<?php
require_once __DIR__ . '/../models/cat_activo.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');
    $cat_activo = catActivo::obtenerTodos();
    echo json_encode($cat_activo);
    exit;
}

if ($action === 'guardar') {
    if (!isset($_SESSION['sucursal_id'])){
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }
    
    $exito = catActivo::guardar(
        $_POST['nombre'],
        $_POST['categoria_depreciacion'] ?? '',
        $_POST['porcentaje_depreciacion']?? ''
        //$_POST['precio']
    );
        
    
    echo json_encode(['success' => true]);
}

if ($action === 'ver') {
    $id = $_GET['id'] ?? 0;
    echo json_encode(catActivo::obtenerPorId($id));
}


if ($action === 'actualizar') {
    $id = $_POST['id'] ?? 0;
    $exito = catActivo::actualizar($id, $_POST);
    echo json_encode(['success' => $exito]);
}

if ($action === 'eliminar') {
    $id = $_POST['id'] ?? 0;
    $exito = catActivo::eliminar($id);
    echo json_encode(['success' => $exito]);
}
