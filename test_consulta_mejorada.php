<?php
require_once 'config/conexion.php';

try {
    $conn = Conexion::conectar();
    echo "=== PRUEBA DE CONSULTA MEJORADA ===\n\n";
    
    $sucursal_id = 1;
    
    // Consulta mejorada que incluye código, precio y stock real
    $query = "
        SELECT DISTINCT
            p.codigo,
            p.nombre AS producto,
            p.descripcion,
            p.precio as precio_venta,
            b.nombre AS bodega,
            s.nombre_sucursal AS sucursal,
            COALESCE(
                (SELECT SUM(
                    CASE 
                        WHEN inv.movimiento = 'ingreso' THEN inv.cantidad
                        WHEN inv.movimiento = 'egreso' THEN -inv.cantidad
                        ELSE 0
                    END
                )
                FROM inventario inv
                WHERE inv.producto_id = p.id 
                  AND inv.bodega_id = b.id 
                  AND inv.sucursal_id = s.id), 0
            ) AS stock_actual
        FROM producto p
        CROSS JOIN bodega b
        CROSS JOIN sucursal s
        WHERE s.id = ?
          AND EXISTS (
              SELECT 1 FROM inventario inv2 
              WHERE inv2.producto_id = p.id 
                AND inv2.bodega_id = b.id 
                AND inv2.sucursal_id = s.id
          )
        ORDER BY p.nombre, b.nombre
    ";
    
    echo "Ejecutando consulta mejorada...\n";
    $stmt = $conn->prepare($query);
    $stmt->execute([$sucursal_id]);
    $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Registros encontrados: " . count($datos) . "\n\n";
    
    if (count($datos) > 0) {
        echo "Datos obtenidos:\n";
        foreach ($datos as $index => $row) {
            echo "Registro " . ($index + 1) . ":\n";
            echo "  Código: " . ($row['codigo'] ?? 'N/A') . "\n";
            echo "  Producto: " . ($row['producto'] ?? 'N/A') . "\n";
            echo "  Precio: Q" . number_format($row['precio_venta'] ?? 0, 2) . "\n";
            echo "  Bodega: " . ($row['bodega'] ?? 'N/A') . "\n";
            echo "  Stock: " . number_format($row['stock_actual'] ?? 0) . "\n";
            echo "  ----------------\n";
        }
        
        // Calcular totales
        $totalValor = 0;
        $totalProductos = 0;
        
        foreach ($datos as $fila) {
            $totalValor += (($fila['stock_actual'] ?? 0) * ($fila['precio_venta'] ?? 0));
            $totalProductos += ($fila['stock_actual'] ?? 0);
        }
        
        echo "\n=== TOTALES ===\n";
        echo "Total Productos: " . number_format($totalProductos) . " unidades\n";
        echo "Valor Total: Q" . number_format($totalValor, 2) . "\n";
        
    } else {
        echo "No se encontraron datos. Probando consulta fallback...\n\n";
        
        $query_fallback = "
            SELECT 
                p.codigo,
                p.nombre AS producto,
                p.descripcion,
                p.precio as precio_venta,
                'ALMACEN 2' AS bodega,
                s.nombre_sucursal AS sucursal,
                0 as stock_actual
            FROM producto p
            CROSS JOIN sucursal s
            WHERE s.id = ?
            ORDER BY p.nombre
        ";
        
        $stmt = $conn->prepare($query_fallback);
        $stmt->execute([$sucursal_id]);
        $datos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Registros fallback: " . count($datos) . "\n";
        
        if (count($datos) > 0) {
            foreach ($datos as $index => $row) {
                echo "Producto " . ($index + 1) . ": " . $row['producto'] . " - Código: " . $row['codigo'] . " - Precio: Q" . number_format($row['precio_venta'], 2) . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>