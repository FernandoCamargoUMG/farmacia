<?php
require_once __DIR__ . '/../config/conexion.php';

class Cliente
{
    public static function obtenerPorSucursal($sucursalId)
    {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT id, nombre, apellido, dpi, email, direccion, telefono, nit FROM clientes WHERE sucursal_id = ?");
        $stmt->execute([$sucursalId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function guardar($sucursal_id, $nombre, $apellido, $dpi, $email, $direccion, $telefono, $nit)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("INSERT INTO clientes (sucursal_id, nombre, apellido, dpi, email, direccion, telefono, nit) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$sucursal_id, $nombre, $apellido, $dpi, $email, $direccion, $telefono, $nit]);
    }

    public static function obtenerPorId($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public static function actualizar($id, $datos)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("UPDATE clientes SET nombre = ?, apellido = ?, telefono = ?, dpi = ?, email = ?, direccion = ?, nit = ? WHERE id = ?");
        return $stmt->execute([
            $datos['nombre'],
            $datos['apellido'],
            $datos['telefono'],
            $datos['dpi'],
            $datos['email'],
            $datos['direccion'],
            $datos['nit'],
            $id
        ]);
    }
    public static function eliminar($id)
    {
        $conn = Conexion::conectar();
        $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
