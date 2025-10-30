<?php
class Conexion {
    // conexion a bd
    public static function conectar() {
        // Obtener datos desde variables de entorno
        $host = "srv1928.hstgr.io"; // IP pública del servidor MySQL de Hostinger
        $dbname = "u834187355_ferreteria"; // Nombre de la base de datos
        $user = "u834187355_katerin";  // Usuario de la base de datos
        $pass = "4l]JgZCMFzU";  

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