<?php
require_once __DIR__ . '/../config/conexion.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');
    $conn = Conexion::conectar();

    // Parámetro sucursal_id: usar el GET si viene, sino la sesión
    $sucursal_id = $_GET['sucursal_id'] ?? $_SESSION['sucursal_id'] ?? null;

    if (!$sucursal_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Sucursal no especificada']);
        exit;
    }

    $sql = "SELECT 
            DATE(fecha) AS fecha,
            CASE 
                WHEN tipo = 'ingreso' AND egreso_id IS NOT NULL THEN 'Venta'
                WHEN tipo = 'egreso' AND planilla_id IS NOT NULL THEN 'Gasto'
                ELSE 'Otro Movimiento'
            END AS tipo_movimiento,
            descripcion,
            monto,
            CASE metodo_pago
                WHEN 1 THEN 'Efectivo'
                WHEN 2 THEN 'Cheque'
                WHEN 3 THEN 'Depósito'
                WHEN 4 THEN 'Tarjeta de Crédito'
                WHEN 5 THEN 'Tarjeta de Débito'
                WHEN 6 THEN 'Transferencia Bancaria'
                ELSE 'Otro'
            END AS metodo_pago,
            SUM(CASE WHEN tipo = 'ingreso' THEN monto ELSE -monto END) 
                OVER (ORDER BY id ASC) AS saldo_acumulado,
            id
        FROM movimiento_caja
        WHERE sucursal_id = ?
        ORDER BY id ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$sucursal_id]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Opcional: si no quieres enviar el id al frontend, puedes quitarlo:
    // foreach ($result as &$row) {
    //     unset($row['id']);
    // }

    echo json_encode($result);
    exit;
}

if ($action === 'filtros') {
    header('Content-Type: application/json');
    $conn = Conexion::conectar();

    $sucursales = $conn->query("SELECT id, nombre_sucursal FROM sucursal")->fetchAll(PDO::FETCH_ASSOC);

    // Si no usas bodegas aquí, quita esa parte para evitar error:
    echo json_encode(['sucursales' => $sucursales]);
    exit;
}

// Si ninguna acción coincide:
http_response_code(400);
echo json_encode(['error' => 'Acción inválida']);
exit;
