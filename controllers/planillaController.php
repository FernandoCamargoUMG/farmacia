<?php
require_once __DIR__ . '/../models/planilla.php';

// Establecer zona horaria para Guatemala
date_default_timezone_set('America/Guatemala');

session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');
    $clientes = Planilla::obtenerPorSucursal($_SESSION['sucursal_id']);
    echo json_encode($clientes);
    exit;
}

if ($action === 'guardar') {
    if (!isset($_SESSION['sucursal_id'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    // Si viene fecha_local del cliente (JavaScript), usarla; si no, usar fecha del servidor
    $fecha = !empty($_POST['fecha_local']) ? $_POST['fecha_local'] : date('Y-m-d H:i:s');

    $exito = Planilla::guardar(
        $_SESSION['sucursal_id'],
        $fecha, // Usar fecha del cliente o servidor
        $_POST['descripcion'] ?? '',
        $_POST['monto'] ?? '',
        $_POST['metodopago'] ?? '',
        $_POST['observaciones'] ?? ''
        //$_POST['telefono'] ?? '',
        //$_POST['nit'] ?? ''
    );

    echo json_encode(['success' => $exito]);
}

if ($action === 'ver') {
    $id = $_GET['id'] ?? 0;
    echo json_encode(Planilla::obtenerPorId($id));
}

if ($action === 'actualizar') {
    $id = $_POST['id'] ?? 0;
    $exito = Planilla::actualizar($id, $_POST);
    echo json_encode(['success' => $exito]);
}

if ($action === 'eliminar') {
    $id = $_POST['id'] ?? 0;
    $exito = Planilla::eliminar($id);
    echo json_encode(['success' => $exito]);
}
