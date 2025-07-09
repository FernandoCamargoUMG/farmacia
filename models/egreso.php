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
}
