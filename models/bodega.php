<?php
require_once __DIR__ . '/../config/conexion.php';

class Bodega
{
    public static function obtenerPorSucursal($sucursalId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT b.id, b.nombre, b.ubicacion, s.nombre_sucursal AS sucursal
                                FROM bodega b
                                LEFT JOIN sucursal s ON b.sucursal_id = s.id
                                WHERE b.sucursal_id = ?");
        $stmt->execute([$sucursalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public static function obtenerPorId($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM bodega WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function obtenerTodos()
    {
        $conn = Conexion::conectar();
        $stmt = $conn->query("SELECT b.*, suc.nombre_sucursal AS nombre_sucursal
                            FROM bodega b
                            LEFT JOIN sucursal suc ON b.sucursal_id = suc.id
                            ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function guardar($sucursal_id, $nombre, $ubicacion)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO bodega (sucursal_id, nombre, ubicacion) VALUES (?, ?, ?)");
        return $stmt->execute([$sucursal_id, $nombre, $ubicacion]);
    }

    public static function actualizar($id, $datos)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("UPDATE bodega SET nombre = ?, ubicacion = ? WHERE id = ?");
        return $stmt->execute([
            $datos['nombre'],
            $datos['ubicacion'],
            $id
        ]);
    }
    public static function eliminar($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM bodega WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
