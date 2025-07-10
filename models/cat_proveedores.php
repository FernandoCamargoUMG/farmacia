<?php
require_once __DIR__ . '/../config/conexion.php';

class catProducto
{

    public static function obtenerPorId($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM categoria WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function obtenerTodos()
    {
        $conn = Conexion::conectar();
        $stmt = $conn->query("SELECT * FROM categoria");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function guardar($descripcion)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO categoria (descripcion) VALUES (?)");
        return $stmt->execute([$descripcion]);
    }

    public static function actualizar($id, $datos)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("UPDATE categoria SET descripcion = ? WHERE id = ?");
        return $stmt->execute([
            $datos['descripcion'],
            $id
        ]);
    }
    public static function eliminar($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM categoria WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
