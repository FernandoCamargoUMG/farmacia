<?php
require_once __DIR__ . '/../models/ingreso.php';
session_start();

$action = $_GET['action'] ?? '';

if (!isset($_SESSION['sucursal_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($action === 'listar') {
    header('Content-Type: application/json');
    $ingresos = Ingreso::obtenerPorSucursal($_SESSION['sucursal_id']);
    echo json_encode($ingresos);
    exit;
}

if ($action === 'obtener') {
    header('Content-Type: application/json; charset=utf-8');
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        die(json_encode(['error' => 'ID inválido']));
    }

    $ingreso = Ingreso::obtenerPorIdConDetalles($id);

    if ($ingreso) {
        // Evita errores de codificación
        echo json_encode($ingreso, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Ingreso no encontrado']);
    }
    exit;
}

if ($action === 'guardar') {
    $datos = $_POST;

    // Guardar cabecera
    $cabId = Ingreso::guardarCabecera(
        $_SESSION['sucursal_id'],
        $datos['proveedor_id'],
        $datos['fecha'],
        $datos['numero'],
        $datos['subtotal'],
        $datos['gravada'],
        $datos['iva'],
        $datos['total'],
        $datos['observaciones']
    );

    // Guardar detalles
    $detalles = json_decode($datos['detalles'], true);
    foreach ($detalles as $detalle) {
        Ingreso::guardarDetalle(
            $_SESSION['sucursal_id'],
            $cabId,
            $detalle['bodega_id'],
            $detalle['producto_id'],
            $detalle['cantidad'],
            $detalle['precio']
        );
    }

    echo json_encode(['success' => true, 'id' => $cabId]);
    exit;
}

if ($action === 'editar') {
    $datos = $_POST;
    $id = intval($datos['id']);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }

    // Actualizar cabecera
    $exito = Ingreso::actualizarCabecera(
        $id,
        $_SESSION['sucursal_id'],
        $datos['proveedor_id'],
        $datos['fecha'],
        $datos['numero'],
        $datos['subtotal'],
        $datos['gravada'],
        $datos['iva'],
        $datos['total'],
        $datos['observaciones']
    );

    if (!$exito) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar cabecera']);
        exit;
    }

    // Eliminar detalles antiguos
    Ingreso::eliminarDetalles($id);

    // Guardar nuevos detalles
    $detalles = json_decode($datos['detalles'], true);
    foreach ($detalles as $detalle) {
        Ingreso::guardarDetalle(
            $_SESSION['sucursal_id'],
            $id,
            $detalle['bodega_id'],
            $detalle['producto_id'],
            $detalle['cantidad'],
            $detalle['precio']
        );
    }

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'eliminar') {
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false]);
        exit;
    }

    Ingreso::eliminarDetalles($id);
    $exito = Ingreso::eliminarCabecera($id);
    echo json_encode(['success' => $exito]);
    exit;
}
