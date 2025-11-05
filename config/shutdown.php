<?php
// Script de cierre automático de conexiones
// Se ejecuta al finalizar cada request

require_once __DIR__ . '/conexion.php';

// Registrar función de cierre automático
register_shutdown_function(function() {
    // Cerrar todas las conexiones abiertas
    Conexion::cerrarTodas();
    
    // Log de conexiones cerradas (solo en desarrollo)
    if (defined('DEBUG') && constant('DEBUG')) {
        error_log("Conexiones DB cerradas automáticamente al finalizar script");
    }
});

// Función para manejar errores fatales y cerrar conexiones
function manejar_error_fatal() {
    $error = error_get_last();
    
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR])) {
        // Cerrar conexiones en caso de error fatal
        Conexion::cerrarTodas();
        error_log("Error fatal detectado. Conexiones DB cerradas: " . $error['message']);
    }
}

// Registrar manejador de errores fatales
register_shutdown_function('manejar_error_fatal');
?>