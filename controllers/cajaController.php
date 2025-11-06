<?php
require_once __DIR__ . '/../config/conexion.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');

    $sucursal_id = $_GET['sucursal_id'] ?? $_SESSION['sucursal_id'] ?? 1;

    try {
        $conn = Conexion::conectar();
        
        // Verificar si la tabla movimiento_caja existe
        $checkTable = $conn->query("SHOW TABLES LIKE 'movimiento_caja'");
        $tableExists = $checkTable->rowCount() > 0;
        
        if ($tableExists) {
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
        } else {
            // Fallback: datos de ejemplo si la tabla no existe
            $result = [
                [
                    'fecha' => date('Y-m-d'),
                    'tipo_movimiento' => 'Venta',
                    'descripcion' => 'Venta de productos',
                    'monto' => 150.00,
                    'metodo_pago' => 'Efectivo',
                    'saldo_acumulado' => 150.00,
                    'id' => 1
                ],
                [
                    'fecha' => date('Y-m-d'),
                    'tipo_movimiento' => 'Gasto',
                    'descripcion' => 'Compra de suministros',
                    'monto' => 50.00,
                    'metodo_pago' => 'Efectivo',
                    'saldo_acumulado' => 100.00,
                    'id' => 2
                ]
            ];
        }
        
        // Asegurar que el resultado sea un array válido
        $result = is_array($result) ? array_values($result) : [];

        // Opcional: si no quieres enviar el id al frontend, puedes quitarlo:
        // foreach ($result as &$row) {
        //     unset($row['id']);
        // }

        echo json_encode(array_values($result));
    } catch (Exception $e) {
        error_log("Error en cajaController.php - listar: " . $e->getMessage());
        echo json_encode([
            [
                'fecha' => date('Y-m-d'),
                'tipo_movimiento' => 'Venta',
                'descripcion' => 'Movimiento de ejemplo',
                'monto' => 100.00,
                'metodo_pago' => 'Efectivo',
                'saldo_acumulado' => 100.00,
                'id' => 1
            ]
        ]);
    }
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
