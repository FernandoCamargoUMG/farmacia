<?php
require_once __DIR__ . '/../models/usuario.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');
    $clientes = Usuario::obtenerPorSucursal($_SESSION['sucursal_id']);
    echo json_encode($clientes);
    exit;
}


if ($action === 'guardar') {

    $sucursal_id = isset($_POST['sucursal_id']) ? $_POST['sucursal_id'] : 1;
    $rol_id      = isset($_POST['rol_id']) ? $_POST['rol_id'] : 1;
    $exito = Usuario::guardar(
        $_POST['nombre'],
        $_POST['correo'],
        $_POST['password'], // se encripta en el modelo con MD5
        $sucursal_id,
        $rol_id
    );

    echo json_encode(['success' => $exito]);
    exit;
}


if ($action === 'ver') {
    $id = $_GET['id'] ?? 0;
    echo json_encode(Usuario::obtenerPorId($id));
    exit;
}

if ($action === 'actualizar') {
    $id = $_POST['id'] ?? 0;
    $exito = Usuario::actualizar($id, $_POST);
    echo json_encode(['success' => $exito]);
    exit;
}

if ($action === 'eliminar') {
    $id = $_POST['id'] ?? 0;
    $exito = Usuario::eliminar($id);
    echo json_encode(['success' => $exito]);
    exit;
}
