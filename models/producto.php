<?php
require_once __DIR__ . '/../config/conexion.php';

class Producto
{
    public static function obtenerCategoriaPorId($categoriaId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT * FROM producto WHERE categoria_id = ?");
        $stmt->execute([$categoriaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerPorId($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM producto WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function obtenerTodos()
    {
        $conn = Conexion::conectar();
        $stmt = $conn->query("SELECT pr.*, cp.descripcion AS categoria 
                            FROM producto pr
                            LEFT JOIN categoria_producto cp ON pr.categoria_id = cp.id
                            ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function guardar($categoria_id, $codigo, $nombre, $descripcion, $precio)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO producto (categoria_id, codigo ,nombre, descripcion, precio) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$categoria_id, $codigo, $nombre, $descripcion, $precio]);
    }

    public static function actualizar($id, $datos)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("UPDATE producto SET categoria_id = ?, codigo = ?, nombre = ?, descripcion = ?, precio = ? WHERE id = ?");
        return $stmt->execute([
            $datos['categoria_id'],
            $datos['codigo'],
            $datos['nombre'],
            $datos['descripcion'],
            $datos['precio'],
            //$datos['direccion'],
            //$datos['telefono'],
            //$datos['email'],
            //$datos['nit'],
            $id
        ]);
    }
    public static function eliminar($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM producto WHERE id = ?");
        return $stmt->execute([$id]);
    }
}




