<?php
require_once __DIR__ . '/../config/conexion.php';

class ActivoFijo
{
    public static function obtenerPorSucursal($sucursalId)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT af.*, ta.nombre AS tipo, CONCAT(r.nombre, ' ', r.apellido) AS responsable_activo
            FROM activo_fijo af
            LEFT JOIN tipo_activo ta ON af.tipo_activo_id = ta.id
            LEFT JOIN responsable r ON af.responsable = r.id
            WHERE af.sucursal_id = ?
        ");
        $stmt->execute([$sucursalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function obtenerPorId($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM activo_fijo WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function guardar($datos)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO activo_fijo 
            (sucursal_id, tipo_activo_id, codigo, nombre, descripcion, fecha_adquisicion, costo, valor_residual, estado, ubicacion, responsable)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $datos['sucursal_id'],
            $datos['tipo_activo_id'],
            $datos['codigo'],
            $datos['nombre'],
            $datos['descripcion'],
            $datos['fecha_adquisicion'],
            $datos['costo'],
            $datos['valor_residual'],
            $datos['estado'],
            $datos['ubicacion'],
            $datos['responsable']
        ]);
    }

    

    public static function actualizar($id, $datos)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("UPDATE activo_fijo SET 
                                    tipo_activo_id = ?, 
                                    codigo = ?, 
                                    nombre = ?, 
                                    descripcion = ?, 
                                    fecha_adquisicion = ?, 
                                    costo = ?, 
                                    valor_residual = ?, 
                                    estado = ?, 
                                    ubicacion = ?, 
                                    responsable = ?
                                WHERE id = ?
                            ");
        return $stmt->execute([
            $datos['tipo_activo_id'],
            $datos['codigo'],
            $datos['nombre'],
            $datos['descripcion'],
            $datos['fecha_adquisicion'],
            $datos['costo'],
            $datos['valor_residual'],
            $datos['estado'],
            $datos['ubicacion'],
            $datos['responsable'],
            $id
        ]);
    }

    public static function eliminar($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM activo_fijo WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
