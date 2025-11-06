<?php
require_once __DIR__ . '/../config/conexion.php';

class Ingreso
{
    // Obtener todos los ingresos por sucursal
    public static function obtenerPorSucursal($sucursalId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT ic.id, ic.sucursal_id, 
            ic.proveedor_id, 
            ic.numero, ic.total, 
            DATE_FORMAT(ic.fecha, '%d-%m-%Y') AS fecha,
            p.nombre AS proveedor
            FROM ingreso_cab ic
            INNER JOIN proveedor p ON ic.proveedor_id = p.id
            WHERE ic.sucursal_id = ?
            ORDER BY ic.fecha DESC
        ");
        $stmt->execute([$sucursalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener ingreso por ID (solo cabecera)
    public static function obtenerPorId($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM ingreso_cab WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtener ingreso con detalles completos (producto y bodega con nombre)
    public static function obtenerPorIdConDetalles($id)
    {
        $conn = Conexion::conectar();

        try {
            // Cabecera
            $stmt = $conn->prepare("SELECT ic.*, p.nombre AS proveedor, p.codigo AS proveedor_codigo
                                FROM ingreso_cab ic
                                INNER JOIN proveedor p ON ic.proveedor_id = p.id
                                WHERE ic.id = ?");
            $stmt->execute([$id]);
            $cabecera = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$cabecera) {
                return null;
            }

            // Detalles
            $stmt = $conn->prepare("SELECT ingreso_det.*, p.nombre AS producto_nombre, b.nombre AS bodega_nombre
            FROM ingreso_det
            INNER JOIN producto p ON ingreso_det.producto_id = p.id
            INNER JOIN bodega b ON ingreso_det.bodega_id = b.id
            WHERE ingreso_det.ingreso_cab_id = ?");
            $stmt->execute([$id]);
            $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Validar formato
            foreach ($detalles as &$d) {
                // Convierte valores numéricos correctamente
                $d['cantidad'] = floatval($d['cantidad']);
                $d['precio'] = floatval($d['precio']);
            }

            $cabecera['detalles'] = $detalles;

            return $cabecera;
        } catch (Exception $e) {
            error_log("Error en obtenerPorIdConDetalles: " . $e->getMessage());
            return null;
        }
    }


    // Guardar nuevo ingreso (cabecera)
    public static function guardarCabecera($sucursal_id, $proveedor_id, $fecha, $numero, $subtotal, $gravada, $iva, $total, $observaciones)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO ingreso_cab (sucursal_id, proveedor_id, fecha, numero, subtotal, gravada, iva, total, observaciones, sta)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([$sucursal_id, $proveedor_id, $fecha, $numero, $subtotal, $gravada, $iva, $total, $observaciones]);
        return $conn->lastInsertId();
    }

    // Actualizar cabecera existente
    public static function actualizarCabecera($id, $sucursal_id, $proveedor_id, $fecha, $numero, $subtotal, $gravada, $iva, $total, $observaciones)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("UPDATE ingreso_cab
            SET sucursal_id = ?, proveedor_id = ?, fecha = ?, numero = ?, subtotal = ?, gravada = ?, iva = ?, total = ?, observaciones = ?
            WHERE id = ?
        ");
        return $stmt->execute([$sucursal_id, $proveedor_id, $fecha, $numero, $subtotal, $gravada, $iva, $total, $observaciones, $id]);
    }

    // Guardar detalle de ingreso
    public static function guardarDetalle($sucursal_id, $ingreso_cab_id, $bodega_id, $producto_id, $cantidad, $precio)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO ingreso_det (sucursal_id, ingreso_cab_id, bodega_id, producto_id, cantidad, precio)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$sucursal_id, $ingreso_cab_id, $bodega_id, $producto_id, $cantidad, $precio]);
    }

    // Eliminar todos los detalles de un ingreso
    public static function eliminarDetalles($ingreso_cab_id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM ingreso_det WHERE ingreso_cab_id = ?");
        return $stmt->execute([$ingreso_cab_id]);
    }

    // Eliminar ingreso cabecera
    public static function eliminarCabecera($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM ingreso_cab WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
