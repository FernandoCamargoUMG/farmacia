<?php
require_once __DIR__ . '/../models/nota_credito_cliente.php';

// Establecer zona horaria para Guatemala
date_default_timezone_set('America/Guatemala');

session_start();

$action = $_GET['action'] ?? '';

if (!isset($_SESSION['sucursal_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Listar notas de crédito de cliente
if ($action === 'listar') {
    header('Content-Type: application/json');
    $notas = NotaCreditoCliente::obtenerPorSucursal($_SESSION['sucursal_id']);
    echo json_encode($notas);
    exit;
}

// Obtener nota de crédito con detalles
if ($action === 'obtener') {
    header('Content-Type: application/json; charset=utf-8');
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        die(json_encode(['error' => 'ID inválido']));
    }

    $nota = NotaCreditoCliente::obtenerPorIdConDetalles($id);

    if ($nota) {
        echo json_encode($nota, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Nota de crédito no encontrada']);
    }
    exit;
}

// Guardar nota de crédito
if ($action === 'guardar') {
    header('Content-Type: application/json');
    $datos = $_POST;

    // Fecha
    $fecha = !empty($datos['fecha_local']) ? $datos['fecha_local'] : date('Y-m-d H:i:s');

    // Preparar detalles
    $detalles = [];
    if (!empty($datos['detalles'])) {
        $detallesRaw = json_decode($datos['detalles'], true);
        foreach ($detallesRaw as $det) {
            $detalles[] = [
                'egreso_cab_id' => intval($det['egreso_cab_id']),
                'monto_abono' => floatval($det['monto_abono']),
                'observaciones' => $det['observaciones'] ?? ''
            ];
        }
    }

    $datosNota = [
        'sucursal_id' => $_SESSION['sucursal_id'],
        'cliente_id' => intval($datos['cliente_id']),
        'numero' => $datos['numero'] ?? NotaCreditoCliente::generarNumero($_SESSION['sucursal_id']),
        'fecha' => $fecha,
        'tipo' => $datos['tipo'] ?? 'abono',
        'subtotal' => floatval($datos['subtotal']),
        'gravada' => floatval($datos['gravada']),
        'iva' => floatval($datos['iva']),
        'total' => floatval($datos['total']),
        'forma_pago' => !empty($datos['forma_pago']) ? intval($datos['forma_pago']) : null,
        'referencia' => $datos['referencia'] ?? null,
        'banco' => $datos['banco'] ?? null,
        'sta' => intval($datos['sta'] ?? 0),
        'observaciones' => $datos['observaciones'] ?? null,
        'detalles' => $detalles
    ];
    
    // Si viene un ID, es una actualización
    if (!empty($datos['id'])) {
        $datosNota['id'] = intval($datos['id']);
    }

    $resultado = NotaCreditoCliente::guardar($datosNota);
    echo json_encode($resultado);
    exit;
}

// Emitir nota de crédito
if ($action === 'emitir') {
    header('Content-Type: application/json');
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID inválido']);
        exit;
    }

    $resultado = NotaCreditoCliente::emitir($id);
    echo json_encode($resultado);
    exit;
}

// Eliminar nota de crédito
if ($action === 'eliminar') {
    header('Content-Type: application/json');
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'ID inválido']);
        exit;
    }

    $resultado = NotaCreditoCliente::eliminar($id);
    echo json_encode($resultado);
    exit;
}

// Obtener facturas pendientes de un cliente
if ($action === 'facturas_pendientes') {
    header('Content-Type: application/json');
    $clienteId = intval($_GET['cliente_id'] ?? 0);

    if ($clienteId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de cliente inválido']);
        exit;
    }

    $facturas = NotaCreditoCliente::obtenerFacturasPendientes($clienteId, $_SESSION['sucursal_id']);
    echo json_encode($facturas);
    exit;
}

// Obtener estado de cuenta de un cliente
if ($action === 'estado_cuenta') {
    header('Content-Type: application/json');
    $clienteId = intval($_GET['cliente_id'] ?? 0);

    if ($clienteId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de cliente inválido']);
        exit;
    }

    $estadoCuenta = NotaCreditoCliente::obtenerEstadoCuenta($clienteId);
    echo json_encode($estadoCuenta);
    exit;
}

// Obtener anticipos disponibles de un cliente
if ($action === 'anticipos_disponibles') {
    header('Content-Type: application/json');
    $clienteId = intval($_GET['cliente_id'] ?? 0);

    if ($clienteId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de cliente inválido']);
        exit;
    }

    $anticipos = NotaCreditoCliente::obtenerAnticiposDisponibles($clienteId);
    echo json_encode($anticipos);
    exit;
}

// Generar número de nota de crédito
if ($action === 'generar_numero') {
    header('Content-Type: application/json');
    $numero = NotaCreditoCliente::generarNumero($_SESSION['sucursal_id']);
    echo json_encode(['numero' => $numero]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Acción no válida']);
