<?php
require_once __DIR__ . '/../config/conexion.php';

class catActivo
{

    public static function obtenerPorId($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM tipo_activo WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function obtenerTodos()
    {
        $conn = Conexion::conectar();
        $stmt = $conn->query("SELECT * FROM tipo_activo");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function guardar($nombre, $categoria_depreciacion, $porcentaje_depreciacion)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO tipo_activo (nombre, categoria_depreciacion, porcentaje_depreciacion) VALUES (?, ?, ?)");
        return $stmt->execute([$nombre, $categoria_depreciacion, $porcentaje_depreciacion]);
    }

    public static function actualizar($id, $datos)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("UPDATE tipo_activo SET nombre = ?, categoria_depreciacion = ? , porcentaje_depreciacion = ? WHERE id = ?");
        return $stmt->execute([
            $datos['nombre'],
            $datos['categoria_depreciacion'],
            $datos['porcentaje_depreciacion'],
            $id
        ]);
    }
    public static function eliminar($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM tipo_activo WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
