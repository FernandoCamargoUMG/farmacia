<?php
require_once __DIR__ . '/../models/categoria_precio.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');
    $categorias = CategoriaPrecio::obtenerTodas();
    echo json_encode($categorias);
    exit;
}

if ($action === 'guardar') {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio_base = $_POST['precio_base'] ?? '';
    
    if (empty($nombre)) {
        echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
        exit;
    }
    
    $exito = CategoriaPrecio::guardar($nombre, $descripcion, $precio_base);
    echo json_encode(['success' => $exito]);
    exit;
}

if ($action === 'ver') {
    $id = $_GET['id'] ?? 0;
    $categoria = CategoriaPrecio::obtenerPorId($id);
    echo json_encode($categoria);
    exit;
}

if ($action === 'actualizar') {
    $id = $_POST['id'] ?? 0;
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio_base = $_POST['precio_base'] ?? '';
    
    if (empty($nombre)) {
        echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
        exit;
    }
    
    $exito = CategoriaPrecio::actualizar($id, $nombre, $descripcion, $precio_base);
    echo json_encode(['success' => $exito]);
    exit;
}

if ($action === 'eliminar') {
    $id = $_POST['id'] ?? 0;
    $exito = CategoriaPrecio::eliminar($id);
    
    if ($exito) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar. Hay productos asociados a esta categoría.']);
    }
    exit;
}

if ($action === 'obtener_precio') {
    header('Content-Type: application/json');
    $categoria_id = $_GET['categoria_id'] ?? 0;
    $precio = CategoriaPrecio::obtenerPrecioBase($categoria_id);
    echo json_encode(['precio_base' => $precio]);
    exit;
}
