<?php
require_once __DIR__ . '/../config/conexion.php';

class formapago
{

    public static function obtenerPorId($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM forma_pago WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function obtenerTodos()
    {
        $conn = Conexion::conectar();
        $stmt = $conn->query("SELECT * FROM forma_pago");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function guardar($descripcion)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO forma_pago (descripcion) VALUES (?)");
        return $stmt->execute([$descripcion]);
    }

    public static function actualizar($id, $datos)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("UPDATE forma_pago SET descripcion = ? WHERE id = ?");
        return $stmt->execute([
            $datos['descripcion'],
            $id
        ]);
    }
    public static function eliminar($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM forma_pago WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
