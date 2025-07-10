<?php
require_once __DIR__ . '/../config/conexion.php';
session_start();

$action = $_GET['action'] ?? '';

if ($action === 'listar') {
    header('Content-Type: application/json');
    $conn = Conexion::conectar();
    $sql = "SELECT 
            a.id as activo_id, a.nombre, a.estado,
            a.costo, a.valor_residual,
            t.porcentaje_depreciacion,
            ROUND(a.costo * (t.porcentaje_depreciacion/100), 2) AS dep_anual,
            d.monto_depreciado AS dep_real,
            CASE 
                WHEN a.estado = 'Dado de baja' THEN a.valor_residual
                ELSE GREATEST(
                    ROUND(a.costo - (ROUND(a.costo * (t.porcentaje_depreciacion/100), 2) * 
                        FLOOR(TIMESTAMPDIFF(MONTH, a.fecha_adquisicion, CURDATE())/12)), 2),
                    a.valor_residual
                )
            END AS valor_esperado,
            d.valor_actual
        FROM activo_fijo a
        JOIN tipo_activo t ON a.tipo_activo_id = t.id
        LEFT JOIN depreciacion_activo d ON a.id = d.activo_id AND d.periodo_anio = YEAR(CURDATE())
        WHERE a.sucursal_id = ?
        ORDER BY a.nombre";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$_SESSION['sucursal_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

if ($action === 'calcular') {
    header('Content-Type: application/json');
    try {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT id FROM activo_fijo WHERE sucursal_id = ?");
        $stmt->execute([$_SESSION['sucursal_id']]);
        $activos = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($activos as $id) {
            $conn->query("CALL calcular_depreciacion($id)");
        }

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
