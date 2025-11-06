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
        
        // Obtener nombre de bodega ANTES de ejecutar el stored procedure
        $bodegaNombre = null;
        if ($bodega_id) {
            $stmtBodega = $conn->prepare("SELECT nombre FROM bodega WHERE id = ?");
            $stmtBodega->execute([$bodega_id]);
            $bodegaNombre = $stmtBodega->fetchColumn();
            $stmtBodega->closeCursor(); // Cerrar cursor
        }
        
        // Ejecutar stored procedure
        $stmt = $conn->prepare("CALL sp_inventario(?)");
        $stmt->execute([$sucursal_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor(); // Cerrar cursor del stored procedure

        // Filtrar por bodega si se especificó
        if ($bodega_id && $bodegaNombre) {
            $result = array_filter($result, fn($r) => $r['bodega'] == $bodegaNombre);
        }

        echo json_encode(array_values($result));
    } catch (Exception $e) {
        error_log("Error en inventarioController.php - listar: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error al cargar los datos de inventario']);
    }
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

if ($action === 'stats' || $action === 'estadisticas') {
    header('Content-Type: application/json');
    
    try {
        $conn = Conexion::conectar();
        $sucursal_id = $_SESSION['sucursal_id'] ?? 1;
        
        // Total de productos
        $stmt = $conn->query("SELECT COUNT(*) as total FROM producto");
        $totalProductos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Productos con stock bajo usando el cálculo correcto
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total
            FROM (
                SELECT 
                    p.id,
                    COALESCE(SUM(CASE 
                        WHEN i.movimiento = 'ingreso' THEN i.cantidad 
                        WHEN i.movimiento = 'egreso' THEN -i.cantidad 
                        ELSE 0 
                    END), 0) as stock_actual
                FROM producto p
                LEFT JOIN inventario i ON p.id = i.producto_id AND i.sucursal_id = ?
                GROUP BY p.id
                HAVING stock_actual <= 5
            ) as productos_stock_bajo
        ");
        $stmt->execute([$sucursal_id]);
        $stockBajo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Movimientos de hoy
        $stmt = $conn->prepare("
            SELECT COUNT(*) as total 
            FROM inventario 
            WHERE DATE(fecha) = CURDATE() AND sucursal_id = ?
        ");
        $stmt->execute([$sucursal_id]);
        $movimientosHoy = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Valor total del inventario
        $stmt = $conn->prepare("
            SELECT SUM(stock_actual * precio) as valor_total
            FROM (
                SELECT 
                    p.precio,
                    COALESCE(SUM(CASE 
                        WHEN i.movimiento = 'ingreso' THEN i.cantidad 
                        WHEN i.movimiento = 'egreso' THEN -i.cantidad 
                        ELSE 0 
                    END), 0) as stock_actual
                FROM producto p
                LEFT JOIN inventario i ON p.id = i.producto_id AND i.sucursal_id = ?
                GROUP BY p.id, p.precio
                HAVING stock_actual > 0
            ) as stock_productos
        ");
        $stmt->execute([$sucursal_id]);
        $valorTotal = $stmt->fetch(PDO::FETCH_ASSOC)['valor_total'] ?? 0;
        
        echo json_encode([
            'totalProductos' => (int)$totalProductos,
            'productosStockBajo' => (int)$stockBajo,
            'movimientosHoy' => (int)$movimientosHoy,
            'valorInventario' => number_format((float)$valorTotal, 2)
        ]);
        
    } catch (Exception $e) {
        error_log("Error en estadísticas: " . $e->getMessage());
        echo json_encode([
            'totalProductos' => 0,
            'productosStockBajo' => 0,
            'movimientosHoy' => 0,
            'valorInventario' => '0.00'
        ]);
    }
    exit;
}
