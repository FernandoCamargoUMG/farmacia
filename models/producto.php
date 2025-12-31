<?php
require_once __DIR__ . '/../config/conexion.php';

class Producto
{
    public static function obtenerCategoriaPorId($categoriaId, $sucursal_id)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT * FROM producto WHERE categoria_id = ? AND sucursal_id = ?");
        $stmt->execute([$categoriaId, $sucursal_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function obtenerPorId($id, $sucursal_id = null)
    {
        $conn = Conexion::conectar();
        if ($sucursal_id) {
            $stmt = $conn->prepare("SELECT * FROM producto WHERE id = ? AND sucursal_id = ?");
            $stmt->execute([$id, $sucursal_id]);
        } else {
            $stmt = $conn->prepare("SELECT * FROM producto WHERE id = ?");
            $stmt->execute([$id]);
        }
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $conn = null; // Cerrar conexión PDO
        return $result;
    }
    public static function obtenerTodos($sucursal_id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT pr.*, cp.descripcion AS categoria, s.nombre_sucursal as sucursal
                                FROM producto pr
                                LEFT JOIN categoria_producto cp ON pr.categoria_id = cp.id
                                LEFT JOIN sucursal s ON pr.sucursal_id = s.id
                                WHERE pr.sucursal_id = ?");
        $stmt->execute([$sucursal_id]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $conn = null; // Cerrar conexión PDO
        return $result;
    }

    public static function generarCodigoSKU()
    {
        $conn = Conexion::conectar();
        // Obtener el último código SKU
        $stmt = $conn->query("SELECT codigo FROM producto WHERE codigo LIKE 'SKU-%' ORDER BY id DESC LIMIT 1");
        $ultimoCodigo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($ultimoCodigo && preg_match('/^SKU-(\d+)$/', $ultimoCodigo['codigo'], $matches)) {
            // Si existe un código previo, incrementar el número
            $numero = intval($matches[1]) + 1;
        } else {
            // Si no existe, empezar desde 1
            $numero = 1;
        }
        
        // Formatear con ceros a la izquierda (4 dígitos)
        return 'SKU-' . str_pad($numero, 4, '0', STR_PAD_LEFT);
    }

    public static function guardar($categoria_id, $sucursal_id, $codigo, $nombre, $descripcion, $precio)
    {
        $conn = Conexion::conectar();
        
        // Si no se proporciona código o está vacío, generar uno automáticamente
        if (empty($codigo)) {
            $codigo = self::generarCodigoSKU();
        }
        
        $stmt = $conn->prepare("INSERT INTO producto (categoria_id, sucursal_id, codigo ,nombre, descripcion, precio) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$categoria_id, $sucursal_id, $codigo, $nombre, $descripcion, $precio]);
    }

    public static function actualizar($id, $datos, $sucursal_id = null)
    {
        $conn = Conexion::conectar();
        if ($sucursal_id) {
            // Actualizar solo si el producto pertenece a la sucursal
            $stmt = $conn->prepare("UPDATE producto SET categoria_id = ?, codigo = ?, nombre = ?, descripcion = ?, precio = ? WHERE id = ? AND sucursal_id = ?");
            return $stmt->execute([
                $datos['categoria_id'],
                $datos['codigo'],
                $datos['nombre'],
                $datos['descripcion'],
                $datos['precio'],
                $id,
                $sucursal_id
            ]);
        } else {
            $stmt = $conn->prepare("UPDATE producto SET categoria_id = ?, codigo = ?, nombre = ?, descripcion = ?, precio = ? WHERE id = ?");
            return $stmt->execute([
                $datos['categoria_id'],
                $datos['codigo'],
                $datos['nombre'],
                $datos['descripcion'],
                $datos['precio'],
                $id
            ]);
        }
    }
    public static function eliminar($id, $sucursal_id = null)
    {
        $conn = Conexion::conectar();
        if ($sucursal_id) {
            // Eliminar solo si el producto pertenece a la sucursal
            $stmt = $conn->prepare("DELETE FROM producto WHERE id = ? AND sucursal_id = ?");
            return $stmt->execute([$id, $sucursal_id]);
        } else {
            $stmt = $conn->prepare("DELETE FROM producto WHERE id = ?");
            return $stmt->execute([$id]);
        }
    }

    public static function contarTotal($sucursal_id = null)
    {
        $conn = Conexion::conectar();
        if ($sucursal_id) {
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM producto WHERE sucursal_id = ?");
            $stmt->execute([$sucursal_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->query("SELECT COUNT(*) as total FROM producto");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        $conn = null; // Cerrar conexión PDO
        return $result['total'];
    }

    public static function obtenerStockBajo($sucursal_id = null)
    {
        $conn = Conexion::conectar();
        if ($sucursal_id) {
            $stmt = $conn->prepare("SELECT *, 
                                CASE 
                                    WHEN stock_actual IS NULL THEN 10
                                    ELSE stock_actual 
                                END as stock_actual,
                                CASE 
                                    WHEN stock_minimo IS NULL THEN 5
                                    ELSE stock_minimo 
                                END as stock_minimo
                                FROM producto 
                                WHERE sucursal_id = ? AND (stock_actual IS NULL OR stock_actual <= COALESCE(stock_minimo, 5))
                                ORDER BY stock_actual ASC");
            $stmt->execute([$sucursal_id]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->query("SELECT *, 
                                CASE 
                                    WHEN stock_actual IS NULL THEN 10
                                    ELSE stock_actual 
                                END as stock_actual,
                                CASE 
                                    WHEN stock_minimo IS NULL THEN 5
                                    ELSE stock_minimo 
                                END as stock_minimo
                                FROM producto 
                                WHERE (stock_actual IS NULL OR stock_actual <= COALESCE(stock_minimo, 5))
                                ORDER BY stock_actual ASC");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $conn = null; // Cerrar conexión PDO
        return $result;
    }
}




