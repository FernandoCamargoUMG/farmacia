<?php
class Conexion {
    // conexion a bd
    public static function conectar() {
        /*$host = "host.docker.internal";
        $dbname = "farmacia";
        $user = "root";
        $pass = "";

        
        $host = "sql3.freesqldatabase.com";
        $dbname = "sql3789022";
        $user = "sql3789022";
        $pass = "LbJBjUshA8";*/

        // Obtener datos desde variables de entorno
        $host = getenv('DB_HOST') ?: 'sql3.freesqldatabase.com';
        $dbname = getenv('DB_NAME') ?: 'sql3789022';
        $user = getenv('DB_USER') ?: 'sql3789022';
        $pass = getenv('DB_PASS') ?: 'LbJBjUshA8';

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