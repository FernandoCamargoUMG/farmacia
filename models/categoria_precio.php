<?php
require_once __DIR__ . '/../config/conexion.php';

class CategoriaPrecio
{
    public static function obtenerTodas()
    {
        $conn = Conexion::conectar();
        $stmt = $conn->query("SELECT * FROM categoria_precio WHERE activo = 1 ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerPorId($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM categoria_precio WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function guardar($nombre, $descripcion, $precio_base)
    {
        $conn = Conexion::conectar();
        
        // Convertir precio vacío a NULL
        $precio_base = empty($precio_base) ? null : $precio_base;
        
        $stmt = $conn->prepare("INSERT INTO categoria_precio (nombre, descripcion, precio_base) VALUES (?, ?, ?)");
        return $stmt->execute([$nombre, $descripcion, $precio_base]);
    }

    public static function actualizar($id, $nombre, $descripcion, $precio_base)
    {
        $conn = Conexion::conectar();
        
        // Convertir precio vacío a NULL
        $precio_base = empty($precio_base) ? null : $precio_base;
        
        $stmt = $conn->prepare("UPDATE categoria_precio SET nombre = ?, descripcion = ?, precio_base = ? WHERE id = ?");
        return $stmt->execute([$nombre, $descripcion, $precio_base, $id]);
    }

    public static function eliminar($id)
    {
        $conn = Conexion::conectar();
        
        // Verificar si hay productos usando esta categoría
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE categoria_precio_id = ?");
        $stmt->execute([$id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resultado['total'] > 0) {
            return false; // No se puede eliminar si hay productos asociados
        }
        
        // Desactivar en lugar de eliminar
        $stmt = $conn->prepare("UPDATE categoria_precio SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function obtenerPrecioBase($categoria_precio_id)
    {
        if (empty($categoria_precio_id)) {
            return null;
        }
        
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT precio_base FROM categoria_precio WHERE id = ? AND activo = 1");
        $stmt->execute([$categoria_precio_id]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $resultado ? $resultado['precio_base'] : null;
    }
}
