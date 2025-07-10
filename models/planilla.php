<?php
require_once __DIR__ . '/../config/conexion.php';

class Planilla
{
    public static function obtenerPorSucursal($sucursalId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT id,sucursal_id, 
                                DATE_FORMAT(fecha, '%d-%m-%Y') AS fecha, 
                                descripcion, 
                                monto, 
                                    CASE metodopago
                                    WHEN 1 THEN 'Efectivo'
                                    WHEN 2 THEN 'Cheque'
                                    WHEN 3 THEN 'Depósito'
                                    WHEN 4 THEN 'Tarjeta de Crédito'
                                    WHEN 5 THEN 'Tarjeta de Débito'
                                    WHEN 6 THEN 'Transferencia Bancaria'
                                    ELSE 'Desconocido'
                                END AS metodopago,
                                observaciones
                                FROM planilla 
                                WHERE sucursal_id   = ?");
        $stmt->execute([$sucursalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function guardar($sucursal_id, $fecha, $descripcion, $monto, $metodopago, $observaciones)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO planilla (sucursal_id, fecha, descripcion, monto, metodopago, observaciones) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$sucursal_id, $fecha, $descripcion, $monto, $metodopago, $observaciones]);
    }

    public static function obtenerPorId($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM planilla WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function actualizar($id, $datos)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("UPDATE planilla SET fecha = ?, descripcion = ?, monto= ?, metodopago = ?, observaciones = ? WHERE id = ?");
        return $stmt->execute([
            $datos['fecha'],
            $datos['descripcion'],
            $datos['monto'],
            $datos['metodopago'],
            $datos['observaciones'],
            //$datos['direccion'],
            //$datos['nit'],
            $id
        ]);
    }
    public static function eliminar($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM planilla WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
