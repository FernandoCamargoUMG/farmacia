<?php
require_once 'config/conexion.php';

try {
    $conn = Conexion::conectar();
    echo "Probando stored procedure sp_inventario(1)...\n";
    
    $stmt = $conn->prepare('CALL sp_inventario(?)');
    $stmt->execute([1]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Registros encontrados: " . count($result) . "\n";
    
    if (count($result) > 0) {
        echo "\nPrimer registro:\n";
        print_r($result[0]);
        
        echo "\nTodos los registros:\n";
        foreach ($result as $index => $row) {
            echo "Registro " . ($index + 1) . ":\n";
            echo "  Producto: " . $row['producto'] . "\n";
            echo "  Bodega: " . $row['bodega'] . "\n";
            echo "  Movimiento: " . $row['movimiento'] . "\n";
            echo "  Cantidad: " . $row['cantidad'] . "\n";
            echo "  Stock Actual: " . $row['stock_actual'] . "\n";
            echo "  Fecha: " . $row['fecha'] . "\n";
            echo "  Origen: " . $row['origen'] . "\n";
            echo "  ----------------\n";
        }
    } else {
        echo "No se encontraron registros.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
?>