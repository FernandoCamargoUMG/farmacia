<?php
class Conexion {
    // conexion a bd
    public static function conectar() {
        $host = "127.0.0.1";
        $dbname = "farmacia";
        $user = "root";
        $pass = "";

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("SET NAMES utf8mb4");
            //echo "Conexión establecida correctamente con la base de datos: $dbname";
            return $pdo;
        } catch (PDOException $e) {
            //echo "Error al conectar con la base de datos $dbname: " . $e->getMessage();
            die();
        }
    }
}