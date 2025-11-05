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

if ($action === 'low_stock') {
    header('Content-Type: application/json');
    
    try {
        $conn = Conexion::conectar();
        
        // Calcular stock actual por producto usando la tabla inventario
        $stmt = $conn->prepare("
            SELECT 
                p.id,
                p.nombre,
                p.codigo,
                p.precio,
                cp.descripcion as categoria,
                COALESCE(stock_calc.stock_actual, 0) as stock_actual,
                5 as stock_minimo
            FROM producto p
            LEFT JOIN categoria_producto cp ON p.categoria_id = cp.id
            LEFT JOIN (
                SELECT 
                    producto_id,
                    SUM(CASE 
                        WHEN movimiento = 'ingreso' THEN cantidad 
                        WHEN movimiento = 'egreso' THEN -cantidad 
                        ELSE 0 
                    END) as stock_actual
                FROM inventario 
                WHERE sucursal_id = ?
                GROUP BY producto_id
            ) stock_calc ON p.id = stock_calc.producto_id
            HAVING stock_actual <= stock_minimo
            ORDER BY stock_actual ASC
        ");
        
        $sucursal_id = $_SESSION['sucursal_id'] ?? 1;
        $stmt->execute([$sucursal_id]);
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Formatear los datos
        $productosFormateados = [];
        foreach ($productos as $producto) {
            $productosFormateados[] = [
                'id' => $producto['id'],
                'nombre' => $producto['nombre'],
                'codigo' => $producto['codigo'] ?? 'N/A',
                'stock_actual' => (int)$producto['stock_actual'],
                'stock_minimo' => 5, // Stock mínimo fijo por ahora
                'categoria' => $producto['categoria'] ?? 'Sin categoría',
                'precio' => (float)$producto['precio']
            ];
        }
        
        echo json_encode($productosFormateados);
        
    } catch (Exception $e) {
        error_log("Error en low_stock: " . $e->getMessage());
        echo json_encode(['error' => 'Error al obtener productos con stock bajo: ' . $e->getMessage()]);
    }
    exit;
}
