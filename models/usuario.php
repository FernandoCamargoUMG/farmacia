<?php

require_once __DIR__ . '/../config/conexion.php';

class Usuario
{
    public static function login($correo, $password)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = ? AND password = ?");
        $stmt->execute([$correo, md5($password)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function obtenerPorSucursal($sucursalId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT id, nombre, correo, sucursal_id, rol_id
                                FROM usuario
                                WHERE sucursal_id = ?
                                ORDER BY nombre ASC
    ");
        $stmt->execute([$sucursalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public static function guardar($nombre, $correo, $password, $sucursal_id, $rol_id)
{
    $conn = Conexion::conectar();
    $passwordMd5 = md5($password); // encriptar desde PHP
    $stmt = $conn->prepare("INSERT INTO usuario (nombre, correo, password, rol_id, sucursal_id)
                       VALUES (?, ?, ?, ?, ?)
                       ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), password = VALUES(password)");
    return $stmt->execute([$nombre, $correo, $passwordMd5, $sucursal_id, $rol_id]);

}



    public static function obtenerPorId($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM usuario WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function actualizar($id, $datos)
    {
        $conn = Conexion::conectar();

        if (!empty($datos['password'])) {
            // Si envía nueva contraseña
            $stmt = $conn->prepare("
            UPDATE usuario
            SET nombre = ?, correo = ?, password = MD5(?), sucursal_id = ?, rol_id = ?
            WHERE id = ?
        ");
            return $stmt->execute([
                $datos['nombre'],
                $datos['correo'],
                $datos['password'],
                $datos['sucursal_id'],
                $datos['rol_id'],
                $id
            ]);
        } else {
            // Si no cambia la contraseña
            $stmt = $conn->prepare("
            UPDATE usuario
            SET nombre = ?, correo = ?, sucursal_id = ?, rol_id = ?
            WHERE id = ?
        ");
            return $stmt->execute([
                $datos['nombre'],
                $datos['correo'],
                $datos['sucursal_id'],
                $datos['rol_id'],
                $id
            ]);
        }
    }

    public static function eliminar($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM usuario WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
