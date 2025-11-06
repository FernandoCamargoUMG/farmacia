<?php
class Conexion {
    private static $conexiones_activas = [];
    
    // conexion a bd
    public static function conectar() {
        
        //Obtener datos desde variables de entorno
        $host = "localhost"; 
        $dbname = "u834187355_ferreteria";
        $user = "u834187355_katerin";
        $pass = "4l]JgZCMFzU";

        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_TIMEOUT, 10); // Timeout de 10 segundos
            $pdo->exec("SET NAMES utf8mb4");
            
            // Registrar conexión activa
            $connection_id = spl_object_hash($pdo);
            self::$conexiones_activas[$connection_id] = $pdo;
            
            //echo "Conexión establecida correctamente con la base de datos: $dbname";
            return $pdo;
        } catch (PDOException $e) {
            error_log("Error de conexión DB: " . $e->getMessage());
            //echo "Error al conectar con la base de datos $dbname: " . $e->getMessage();
            die();
        }
    }
    
    // Método para cerrar una conexión específica
    public static function cerrar(&$pdo) {
        if ($pdo !== null) {
            $connection_id = spl_object_hash($pdo);
            unset(self::$conexiones_activas[$connection_id]);
            $pdo = null;
        }
    }
    
    // Método para cerrar todas las conexiones activas
    public static function cerrarTodas() {
        foreach (self::$conexiones_activas as $id => $pdo) {
            $pdo = null;
            unset(self::$conexiones_activas[$id]);
        }
    }
    
    // Obtener número de conexiones activas
    public static function getConexionesActivas() {
        return count(self::$conexiones_activas);
    }
}