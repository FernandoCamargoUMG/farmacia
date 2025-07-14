<?php
require_once __DIR__ . '/../config/conexion.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'filtros') {
    header('Content-Type: application/json');
    $conn = Conexion::conectar();

    $sucursales = $conn->query("SELECT id, nombre_sucursal FROM sucursal")->fetchAll(PDO::FETCH_ASSOC);
    $bodegas = $conn->query("SELECT id, nombre FROM bodega")->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['sucursales' => $sucursales, 'bodegas' => $bodegas]);
    exit;
}

if ($action === 'listar') {
    header('Content-Type: application/json');

    $sucursal_id = $_GET['sucursal_id'] ?? $_SESSION['sucursal_id'];
    $bodega_id = $_GET['bodega_id'] ?? null;

    try {
        $conn = Conexion::conectar();
    //$stmt = $conn->prepare("CALL obtener_movimientos_y_stock(?)");
        $stmt = $conn->prepare("CALL sp_inventario(?)");
        $stmt->execute([$sucursal_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($bodega_id) {
            $result = array_filter($result, fn($r) => $r['bodega'] == getBodegaNombre($conn, $bodega_id));
        }

        echo json_encode(array_values($result));
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function getBodegaNombre($conn, $id) {
    $stmt = $conn->prepare("SELECT nombre FROM bodega WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetchColumn();
}
