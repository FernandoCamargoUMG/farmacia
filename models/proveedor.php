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
        $stmt = $conn->query("SELECT p.*, c.descripcion AS categoria 
                                FROM proveedor p
                                LEFT JOIN categoria c ON p.categoria_id = c.id 
                            ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function guardar($categoria_id, $codigo, $nombre, $nit, $direccion, $telefono, $email)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO proveedor (categoria_id, codigo, nombre, nit, direccion, telefono, email) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$categoria_id, $codigo, $nombre, $nit, $direccion, $telefono, $email]);
    }

    public static function actualizar($id, $datos)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("UPDATE proveedor SET categoria_id = ?, codigo = ?, nombre = ?, nit = ? , direccion = ?, telefono = ?, email = ? WHERE id = ?");
        return $stmt->execute([
            $datos['categoria_id'],
            $datos['codigo'],
            $datos['nombre'],
            $datos['nit'],
            $datos['direccion'],
            $datos['telefono'],
            $datos['email'],
            //$datos['nit'],
            $id
        ]);
    }
    public static function eliminar($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM proveedor WHERE id = ?");
        return $stmt->execute([$id]);
    }
}




