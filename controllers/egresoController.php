<?php
require_once __DIR__ . '/../models/egreso.php';
session_start();

$action = $_GET['action'] ?? '';

if (!isset($_SESSION['sucursal_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($action === 'listar') {
    header('Content-Type: application/json');
    $egresos = Egreso::obtenerPorSucursal($_SESSION['sucursal_id']);
    echo json_encode($egresos);
    exit;
}

if ($action === 'obtener') {
    header('Content-Type: application/json; charset=utf-8');
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID inválido']);
        exit;
    }

    $egreso = Egreso::obtenerPorIdConDetalles($id);

    if ($egreso) {
        echo json_encode($egreso, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Egreso no encontrado']);
    }
    exit;
}

if ($action === 'guardar') {
    $datos = $_POST;

    // Para debug: ver qué llega en detalles
    error_log('Detalles recibidos en guardar: ' . ($datos['detalles'] ?? 'No vienen detalles'));

    if (empty($datos['detalles'])) {
        echo json_encode(['success' => false, 'message' => 'No llegan detalles al servidor']);
        exit;
    }

    $detalles = json_decode($datos['detalles'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Error en JSON detalles: ' . json_last_error_msg()]);
        exit;
    }
    if (!is_array($detalles)) {
        echo json_encode(['success' => false, 'message' => 'Detalles no es un array válido']);
        exit;
    }

    $cabId = Egreso::guardarCabecera(
        $_SESSION['sucursal_id'],
        $datos['cliente_id'],
        $datos['forma_pago'],
        $datos['fecha'],
        $datos['numero'],
        $datos['subtotal'],
        $datos['gravada'],
        $datos['iva'],
        $datos['total'],
        $datos['observaciones'],
        $datos['opcionpago'],
        $datos['sta']
    );

    $erroresDetalle = [];
    foreach ($detalles as $i => $detalle) {
        $ok = Egreso::guardarDetalle(
            $_SESSION['sucursal_id'],
            $cabId,
            $detalle['producto_id'],
            $detalle['bodega_id'],
            $detalle['cantidad'],
            $detalle['precio'],
            $detalle['descuento']
        );
        if (!$ok) {
            $erroresDetalle[] = "Error guardando detalle índice $i";
        }
    }

    if (count($erroresDetalle) > 0) {
        echo json_encode(['success' => false, 'message' => 'Error guardando algunos detalles', 'errors' => $erroresDetalle]);
        exit;
    }

    echo json_encode(['success' => true, 'id' => $cabId]);
    exit;
}

if ($action === 'editar') {
    $datos = $_POST;
    $id = intval($datos['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido']);
        exit;
    }

    // Para debug: ver qué llega en detalles
    error_log('Detalles recibidos en editar: ' . ($datos['detalles'] ?? 'No vienen detalles'));

    if (empty($datos['detalles'])) {
        echo json_encode(['success' => false, 'message' => 'No llegan detalles al servidor']);
        exit;
    }

    $detalles = json_decode($datos['detalles'], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(['success' => false, 'message' => 'Error en JSON detalles: ' . json_last_error_msg()]);
        exit;
    }
    if (!is_array($detalles)) {
        echo json_encode(['success' => false, 'message' => 'Detalles no es un array válido']);
        exit;
    }

    $exito = Egreso::actualizarCabecera(
        $id,
        $_SESSION['sucursal_id'],
        $datos['cliente_id'],
        $datos['forma_pago'],
        $datos['fecha'],
        $datos['numero'],
        $datos['subtotal'],
        $datos['gravada'],
        $datos['iva'],
        $datos['total'],
        $datos['observaciones'],
        $datos['opcionpago'],
        $datos['sta']
    );

    if (!$exito) {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar cabecera']);
        exit;
    }

    Egreso::eliminarDetalles($id);

    $erroresDetalle = [];
    foreach ($detalles as $i => $detalle) {
        $ok = Egreso::guardarDetalle(
            $_SESSION['sucursal_id'],
            $id,
            $detalle['producto_id'],
            $detalle['bodega_id'],
            $detalle['cantidad'],
            $detalle['precio'],
            $detalle['descuento']
        );
        if (!$ok) {
            $erroresDetalle[] = "Error guardando detalle índice $i";
        }
    }

    if (count($erroresDetalle) > 0) {
        echo json_encode(['success' => false, 'message' => 'Error guardando algunos detalles', 'errors' => $erroresDetalle]);
        exit;
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

    Egreso::eliminarDetalles($id);
    $exito = Egreso::eliminarCabecera($id);
    echo json_encode(['success' => $exito]);
    exit;
}
