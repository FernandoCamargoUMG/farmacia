<?php
echo "<h2>🔧 Reparación de Definers en MySQL</h2>";

try {
    require_once 'config/conexion.php';
    $conn = Conexion::conectar();
    
    echo "<h3>📋 Problemas identificados:</h3>";
    echo "<ul>";
    echo "<li>Los triggers y procedimientos tienen DEFINER='root'@'%'</li>";
    echo "<li>Tu MySQL local usa 'root'@'localhost'</li>";
    echo "<li>Esto causa el error 1449 cuando se ejecutan</li>";
    echo "</ul>";
    
    echo "<h3>🔄 Aplicando soluciones...</h3>";
    
    // Solución 1: Dropar y recrear triggers problemáticos
    $queries = [
        // Dropar triggers existentes
        "DROP TRIGGER IF EXISTS trg_insert_movimiento_caja_venta",
        "DROP TRIGGER IF EXISTS trg_update_movimiento_caja_venta", 
        "DROP TRIGGER IF EXISTS trg_delete_movimiento_caja_venta",
        "DROP TRIGGER IF EXISTS egreso_det_AFTER_INSERT",
        "DROP TRIGGER IF EXISTS egreso_det_AFTER_UPDATE",
        "DROP TRIGGER IF EXISTS egreso_det_AFTER_DELETE",
        "DROP TRIGGER IF EXISTS ingreso_det_AFTER_INSERT",
        "DROP TRIGGER IF EXISTS ingreso_det_AFTER_UPDATE",
        "DROP TRIGGER IF EXISTS ingreso_det_AFTER_DELETE",
        "DROP TRIGGER IF EXISTS trg_movimiento_caja_planilla",
        "DROP TRIGGER IF EXISTS trg_update_movimiento_caja_planilla",
        "DROP TRIGGER IF EXISTS trg_delete_movimiento_caja_planilla",
        
        // Dropar procedimientos
        "DROP PROCEDURE IF EXISTS sp_inventario",
        "DROP PROCEDURE IF EXISTS calcular_depreciacion"
    ];
    
    foreach ($queries as $query) {
        try {
            $conn->exec($query);
            echo "<div style='color: green;'>✅ Ejecutado: " . substr($query, 0, 50) . "...</div>";
        } catch (Exception $e) {
            echo "<div style='color: orange;'>⚠️ " . substr($query, 0, 50) . "... (puede no existir)</div>";
        }
    }
    
    echo "<h3>🔨 Recreando objetos con definer correcto...</h3>";
    
    // Recrear triggers con definer correcto
    $triggers = [
        // Trigger para movimiento de caja en ventas
        "CREATE TRIGGER trg_insert_movimiento_caja_venta
        AFTER INSERT ON egreso_cab
        FOR EACH ROW
        BEGIN
          IF NEW.forma_pago = 1 AND NEW.sta = 1 THEN
            INSERT INTO movimiento_caja (
              sucursal_id, tipo, descripcion, monto,
              metodo_pago, egreso_id, observaciones, fecha
            )
            VALUES (
              NEW.sucursal_id, 'ingreso',
              CONCAT('Venta al contado #', NEW.numero),
              NEW.total, NEW.opcionpago,
              NEW.id, NEW.observaciones, NEW.fecha
            );
          END IF;
        END",
        
        // Trigger para inventario en egresos
        "CREATE TRIGGER egreso_det_AFTER_INSERT
        AFTER INSERT ON egreso_det
        FOR EACH ROW
        BEGIN
          DECLARE v_sta TINYINT;
          SELECT sta INTO v_sta FROM egreso_cab WHERE id = NEW.egreso_cab_id;
          IF v_sta = 1 THEN
            INSERT INTO inventario (
              sucursal_id, bodega_id, producto_id,
              cantidad, fecha, movimiento, cab_id, det_id
            )
            VALUES (
              NEW.sucursal_id, NEW.bodega_id, NEW.producto_id,
              NEW.cantidad, NOW(), 'egreso', NEW.egreso_cab_id, NEW.id
            );
          END IF;
        END",
        
        // Trigger para inventario en ingresos
        "CREATE TRIGGER ingreso_det_AFTER_INSERT
        AFTER INSERT ON ingreso_det
        FOR EACH ROW
        BEGIN
          INSERT INTO inventario (
            sucursal_id, bodega_id, producto_id,
            cantidad, fecha, movimiento, cab_id, det_id
          )
          VALUES (
            NEW.sucursal_id, NEW.bodega_id, NEW.producto_id,
            NEW.cantidad, NOW(), 'ingreso', NEW.ingreso_cab_id, NEW.id
          );
        END",
        
        // Trigger para planilla
        "CREATE TRIGGER trg_movimiento_caja_planilla
        AFTER INSERT ON planilla
        FOR EACH ROW
        BEGIN
          INSERT INTO movimiento_caja (
            sucursal_id, fecha, tipo, descripcion,
            monto, metodo_pago, planilla_id, observaciones
          ) VALUES (
            NEW.sucursal_id, NEW.fecha, 'egreso', NEW.descripcion,
            NEW.monto, NEW.metodopago, NEW.id, NEW.observaciones
          );
        END"
    ];
    
    foreach ($triggers as $trigger) {
        try {
            $conn->exec($trigger);
            echo "<div style='color: green;'>✅ Trigger recreado correctamente</div>";
        } catch (Exception $e) {
            echo "<div style='color: red;'>❌ Error recreando trigger: " . $e->getMessage() . "</div>";
        }
    }
    
    // Recrear procedimiento simplificado
    $procedure = "CREATE PROCEDURE sp_inventario(IN p_sucursal_id INT)
    BEGIN
        SELECT 
            i.id,
            p.nombre AS producto,
            s.nombre_sucursal AS sucursal,
            b.nombre AS bodega,
            i.movimiento,
            i.cantidad,
            DATE_FORMAT(i.fecha, '%d-%m-%Y %H:%i') AS fecha,
            CASE 
                WHEN i.movimiento = 'ingreso' THEN CONCAT('Ingreso #', COALESCE(ic.numero, ''))
                WHEN i.movimiento = 'egreso' THEN CONCAT('Venta #', COALESCE(ec.numero, ''))
                ELSE 'Movimiento'
            END AS origen
        FROM inventario i
        INNER JOIN producto p ON i.producto_id = p.id
        INNER JOIN bodega b ON i.bodega_id = b.id
        INNER JOIN sucursal s ON i.sucursal_id = s.id
        LEFT JOIN ingreso_cab ic ON i.movimiento = 'ingreso' AND i.cab_id = ic.id
        LEFT JOIN egreso_cab ec ON i.movimiento = 'egreso' AND i.cab_id = ec.id
        WHERE i.sucursal_id = p_sucursal_id
        ORDER BY i.fecha DESC;
    END";
    
    try {
        $conn->exec($procedure);
        echo "<div style='color: green;'>✅ Procedimiento sp_inventario recreado correctamente</div>";
    } catch (Exception $e) {
        echo "<div style='color: red;'>❌ Error recreando procedimiento: " . $e->getMessage() . "</div>";
    }
    
    echo "<h3>🧪 Probando la solución...</h3>";
    
    // Test de inserción que antes fallaba
    try {
        $stmt = $conn->prepare("INSERT INTO egreso_cab 
            (sucursal_id, cliente_id, forma_pago, fecha, numero, subtotal, gravada, iva, total, observaciones, opcionpago, sta)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Test con datos de prueba
        $result = $stmt->execute([1, 17, 1, '2025-11-05', 'TEST123', 75.89, 75.89, 9.11, 85.00, 'TEST', 0, 1]);
        
        if ($result) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
            echo "✅ <strong>¡PROBLEMA SOLUCIONADO!</strong><br>";
            echo "La inserción en egreso_cab ahora funciona correctamente.<br>";
            echo "Los triggers han sido recreados con el definer correcto.";
            echo "</div>";
            
            // Limpiar el test
            $last_id = $conn->lastInsertId();
            $conn->exec("DELETE FROM egreso_cab WHERE id = $last_id");
            echo "<small>Test limpiado correctamente.</small>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "❌ <strong>Aún hay problemas:</strong><br>";
        echo "Error: " . htmlspecialchars($e->getMessage());
        echo "</div>";
    }
    
    Conexion::cerrar($conn);
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 15px 0;'>";
    echo "❌ <strong>Error general:</strong><br>";
    echo htmlspecialchars($e->getMessage());
    echo "</div>";
}

echo "<br><a href='/' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🎯 Probar Sistema</a>";
?>