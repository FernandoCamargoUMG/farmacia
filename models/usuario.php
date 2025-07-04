<?php

require_once __DIR__ . '/../config/conexion.php';

class Usuario {
    public static function login($correo, $password) {
        $pdo = Conexion::conectar();
        $stmt = $pdo->prepare("SELECT * FROM usuario WHERE correo = ? AND password = ?");
        $stmt->execute([$correo, md5($password)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
