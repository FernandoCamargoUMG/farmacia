<?php
require_once __DIR__ . '/../models/activo_fijo.php';

// Establecer zona horaria para Guatemala
date_default_timezone_set('America/Guatemala');

session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');
    $ActivoFijos = ActivoFijo::obtenerPorSucursal($_SESSION['sucursal_id']);
    echo json_encode($ActivoFijos);
    exit;
}

if ($action === 'guardar') {
    if (!isset($_SESSION['sucursal_id'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit;
    }

    // Si no se especifica fecha de adquisición, usar la actual
    $fechaAdquisicion = !empty($_POST['fecha_adquisicion']) ? $_POST['fecha_adquisicion'] : date('Y-m-d');

    $datos = [
        'sucursal_id' => $_SESSION['sucursal_id'],
        'codigo' => $_POST['codigo'] ?? '',
        'nombre' => $_POST['nombre'] ?? '',
        'descripcion' => $_POST['descripcion'] ?? '',
        'fecha_adquisicion' => $fechaAdquisicion, // Usar fecha validada
        'tipo_activo_id' => $_POST['tipo_activo_id'] ?? null,
        'responsable' => $_POST['responsable'] ?? null,
        'costo' => $_POST['costo'] ?? 0,
        'valor_residual' => $_POST['valor_residual'] ?? 0,
        'estado' => $_POST['estado'] ?? 'Activo',
        'ubicacion' => $_POST['ubicacion'] ?? ''
    ];

    $exito = ActivoFijo::guardar($datos);

    echo json_encode(['success' => $exito]);
}


if ($action === 'ver') {
    $id = $_GET['id'] ?? 0;
    echo json_encode(ActivoFijo::obtenerPorId($id));
}

if ($action === 'actualizar') {
    $id = $_POST['id'] ?? 0;
    $exito = ActivoFijo::actualizar($id, $_POST);
    echo json_encode(['success' => $exito]);
}

if ($action === 'eliminar') {
    $id = $_POST['id'] ?? 0;
    $exito = ActivoFijo::eliminar($id);
    echo json_encode(['success' => $exito]);
}
