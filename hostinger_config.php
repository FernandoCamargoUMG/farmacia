<?php
// hostinger_config.php - Configuraciones específicas para Hostinger

// Configuración de errores para producción en Hostinger
if (strpos($_SERVER['HTTP_HOST'], 'hostinger') !== false || 
    strpos($_SERVER['HTTP_HOST'], '.com') !== false) {
    
    // Configuración para producción
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    ini_set('log_errors', 1);
    
    // Configuración específica de sesiones para Hostinger
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS
    ini_set('session.gc_maxlifetime', 3600);
    ini_set('session.cookie_path', '/');
    
    // Configurar zona horaria
    date_default_timezone_set('America/Guatemala');
    
} else {
    // Configuración para desarrollo local
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Función para obtener la URL base correcta en Hostinger
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Para Hostinger, usar el directorio actual
    $path = dirname($_SERVER['SCRIPT_NAME']);
    if ($path === '/') {
        $path = '';
    }
    
    return $protocol . '://' . $host . $path;
}

// Función para redirección segura compatible con Hostinger
function safeRedirect($url, $fallback_message = '') {
    // Limpiar cualquier output previo
    if (ob_get_level()) {
        ob_clean();
    }
    
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    } else {
        // Si los headers ya fueron enviados, usar JavaScript
        echo "<script type='text/javascript'>";
        echo "window.location.href = '$url';";
        echo "</script>";
        echo "<noscript>";
        echo "<meta http-equiv='refresh' content='0;url=$url'>";
        echo "</noscript>";
        
        if ($fallback_message) {
            echo "<p>$fallback_message <a href='$url'>Haz clic aquí si no eres redirigido automáticamente</a></p>";
        }
        exit;
    }
}

// Función para verificar si estamos en Hostinger
function isHostinger() {
    return strpos($_SERVER['HTTP_HOST'], 'hostinger') !== false || 
           strpos($_SERVER['HTTP_HOST'], '.com') !== false ||
           isset($_SERVER['HOSTINGER']);
}

// Configuración de base de datos para Hostinger
function getDbConfig() {
    if (isHostinger()) {
        // Configuración típica de Hostinger
        return [
            'host' => 'localhost', // O el host que te proporcione Hostinger
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]
        ];
    } else {
        // Configuración local
        return [
            'host' => 'localhost',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        ];
    }
}

// Incluir este archivo al inicio de tu aplicación principal
if (!defined('HOSTINGER_CONFIG_LOADED')) {
    define('HOSTINGER_CONFIG_LOADED', true);
}

?>