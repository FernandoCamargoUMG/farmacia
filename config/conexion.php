<?php
class Conexion {
    // conexion a bd
    public static function conectar() {
        // Obtener datos desde variables de entorno
        $host = getenv('DB_HOST') ?: '82.197.82.175';
        $dbname = getenv('DB_NAME') ?: 'u834187355_farmacia';
        $user = getenv('DB_USER') ?: 'u834187355_fcamargo';
        $pass = getenv('DB_PASS') ?: 'Pldb2610200';

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