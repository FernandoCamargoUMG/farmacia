<?php
require_once __DIR__ . '/../config/conexion.php';

class Egreso
{
    // Obtener todos los egresos por sucursal
    public static function obtenerPorSucursal($sucursalId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT ec.id, ec.sta, ec.cliente_id, ec.numero, ec.total, 
            DATE_FORMAT(ec.fecha, '%d-%m-%Y') AS fecha,
            CONCAT(c.nombre, ' ', c.apellido) AS cliente_nombre
            FROM egreso_cab ec
            LEFT JOIN clientes c ON ec.cliente_id = c.id
            WHERE ec.sucursal_id = ?
            ORDER BY ec.fecha DESC
        ");
        $stmt->execute([$sucursalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener egreso por ID con cabecera
    public static function obtenerPorId($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM egreso_cab WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener egreso por ID con detalles
    public static function obtenerPorIdConDetalles($id)
    {
        $conn = Conexion::conectar();

        // Cabecera
        $stmt = $conn->prepare("SELECT ec.*, c.nombre AS cliente_nombre
            FROM egreso_cab ec
            LEFT JOIN clientes c ON ec.cliente_id = c.id
            WHERE ec.id = ?");
        $stmt->execute([$id]);
        $cabecera = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cabecera) return null;

        // Detalles
        $stmt = $conn->prepare("SELECT ed.*, p.nombre AS producto_nombre, b.nombre AS bodega_nombre
            FROM egreso_det ed
            INNER JOIN producto p ON ed.producto_id = p.id
            INNER JOIN bodega b ON ed.bodega_id = b.id
            WHERE ed.egreso_cab_id = ?");
        $stmt->execute([$id]);
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($detalles as &$d) {
            $d['cantidad'] = floatval($d['cantidad']);
            $d['precio'] = floatval($d['precio']);
            $d['descuento'] = floatval($d['descuento']);
        }

        $cabecera['detalles'] = $detalles;
        return $cabecera;
    }

    // Guardar cabecera
    public static function guardarCabecera($sucursal_id, $cliente_id, $forma_pago, $fecha, $numero, $subtotal, $gravada, $iva, $total, $observaciones, $opcionpago, $sta)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO egreso_cab 
            (sucursal_id, cliente_id, forma_pago, fecha, numero, subtotal, gravada, iva, total, observaciones, opcionpago, sta)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$sucursal_id, $cliente_id, $forma_pago, $fecha, $numero, $subtotal, $gravada, $iva, $total, $observaciones, $opcionpago, $sta]);
        return $conn->lastInsertId();
    }

    // Actualizar cabecera
    public static function actualizarCabecera($id, $sucursal_id, $cliente_id, $forma_pago, $fecha, $numero, $subtotal, $gravada, $iva, $total, $observaciones, $opcionpago, $sta)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("UPDATE egreso_cab SET 
            sucursal_id = ?, cliente_id = ?, forma_pago = ?, fecha = ?, numero = ?, 
            subtotal = ?, gravada = ?, iva = ?, total = ?, observaciones = ?, opcionpago = ?, sta = ?
            WHERE id = ?");
        return $stmt->execute([$sucursal_id, $cliente_id, $forma_pago, $fecha, $numero, $subtotal, $gravada, $iva, $total, $observaciones, $opcionpago, $sta, $id]);
    }

    // Guardar detalle
    public static function guardarDetalle($sucursal_id, $egreso_cab_id, $producto_id, $bodega_id, $cantidad, $precio, $descuento)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO egreso_det 
            (sucursal_id, egreso_cab_id, producto_id, bodega_id, cantidad, precio, descuento)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$sucursal_id, $egreso_cab_id, $producto_id, $bodega_id, $cantidad, $precio, $descuento]);
    }

    // Eliminar todos los detalles de un egreso
    public static function eliminarDetalles($egreso_cab_id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM egreso_det WHERE egreso_cab_id = ?");
        return $stmt->execute([$egreso_cab_id]);
    }

    // Eliminar egreso cabecera
    public static function eliminarCabecera($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM egreso_cab WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Contar ventas de hoy
    public static function contarVentasHoy($sucursalId = null)
    {
        $conn = Conexion::conectar();
        
        if ($sucursalId) {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM egreso_cab 
                                   WHERE sucursal_id = ? AND DATE(fecha) = CURDATE()");
            $stmt->execute([$sucursalId]);
        } else {
            // Si no hay sucursal específica, contar todas las ventas de hoy
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM egreso_cab 
                                   WHERE DATE(fecha) = CURDATE()");
            $stmt->execute();
        }
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $conn = null; // Cerrar conexión PDO
        return $result['total'];
    }

    // Obtener ventas de los últimos 7 días
    public static function obtenerVentasSemanales($sucursalId = null)
    {
        $conn = Conexion::conectar();
        
        if ($sucursalId) {
            $stmt = $conn->prepare("
                SELECT 
                    DATE_FORMAT(fecha, '%a') as dia,
                    DATE(fecha) as fecha,
                    COUNT(*) as ventas,
                    ROUND(SUM(total), 2) as monto
                FROM egreso_cab 
                WHERE sucursal_id = ? 
                AND fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(fecha)
                ORDER BY fecha ASC
            ");
            $stmt->execute([$sucursalId]);
        } else {
            $stmt = $conn->prepare("
                SELECT 
                    DATE_FORMAT(fecha, '%a') as dia,
                    DATE(fecha) as fecha,
                    COUNT(*) as ventas,
                    ROUND(SUM(total), 2) as monto
                FROM egreso_cab 
                WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                GROUP BY DATE(fecha)
                ORDER BY fecha ASC
            ");
            $stmt->execute();
        }
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Preparar datos para el gráfico
        $labels = [];
        $values = [];
        
        // Crear array con los últimos 7 días
        for ($i = 6; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-$i days"));
            $dia = date('D', strtotime($fecha));
            
            $labels[] = $dia;
            
            // Buscar si hay datos para este día
            $encontrado = false;
            foreach ($resultados as $resultado) {
                if (date('Y-m-d', strtotime($resultado['fecha'] ?? $fecha)) === $fecha) {
                    $values[] = (int)$resultado['ventas'];
                    $encontrado = true;
                    break;
                }
            }
            
            if (!$encontrado) {
                $values[] = 0;
            }
        }

        $conn = null; // Cerrar conexión PDO
        
        return [
            'labels' => $labels,
            'values' => $values
        ];
    }
}
