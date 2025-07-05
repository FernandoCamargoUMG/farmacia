<?php
require_once __DIR__ . '/../models/clientes.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    $clientes = Cliente::obtenerPorSucursal($_SESSION['sucursal_id']);
    echo json_encode($clientes);
}

/*if ($action === 'guardar') {
    // para guardar datos
    if (!isset($_SESSION['sucursal_id'], $POST['nombre'], $POST['apellido'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos Incompletos.']);
        exit;
    }*/

if ($action === 'guardar') {
    if (!isset($_SESSION['sucursal_id'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    $exito = Cliente::guardar(
        $_SESSION['sucursal_id'],
        $_POST['nombre'] ?? '',
        $_POST['apellido'] ?? '',
        $_POST['dpi'] ?? '',
        $_POST['email'] ?? '',
        $_POST['direccion'] ?? '',
        $_POST['telefono'] ?? '',
        $_POST['nit'] ?? ''
    );

    echo json_encode(['success' => $exito]);
}

if ($action === 'ver') {
    $id = $_GET['id'] ?? 0;
    echo json_encode(Cliente::obtenerPorId($id));
}

if ($action === 'actualizar') {
    $id = $_POST['id'] ?? 0;
    $exito = Cliente::actualizar($id, $_POST);
    echo json_encode(['success' => $exito]);
}

if ($action === 'eliminar') {
    $id = $_POST['id'] ?? 0;
    $exito = Cliente::eliminar($id);
    echo json_encode(['success' => $exito]);
}
