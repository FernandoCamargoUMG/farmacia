<?php
require_once __DIR__ . '/../config/conexion.php';

class Proveedor
{
    public static function obtenerCategoriaPorId($categoriaId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT * FROM proveedor WHERE categoria_id = ?");
        $stmt->execute([$categoriaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerPorId($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM proveedor WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function obtenerTodos()
    {
        $conn = Conexion::conectar();
        $stmt = $conn->query("SELECT * FROM proveedor");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
