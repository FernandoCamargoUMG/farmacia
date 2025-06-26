<?php
class Conexion {
    // conexion a bd
    public static function conectar() {
        $host = "localhost";
        $dbname = "farmacia";
        $user = "root";
        $pass = "";

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("SET NAMES utf8mb4");
            return $pdo;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}
