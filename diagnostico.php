<?php
// diagnóstico.php - Archivo para diagnosticar problemas en Hostinger
session_start();

echo "<h1>🔧 Diagnóstico del Sistema - Ferretería Costa Sur</h1>";
echo "<p><strong>Fecha:</strong> " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";

echo "<h2>📊 Información del Servidor</h2>";
echo "<ul>";
echo "<li><strong>PHP Version:</strong> " . phpversion() . "</li>";
echo "<li><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'No disponible') . "</li>";
echo "<li><strong>Document Root:</strong> " . ($_SERVER['DOCUMENT_ROOT'] ?? 'No disponible') . "</li>";
echo "<li><strong>Script Name:</strong> " . ($_SERVER['SCRIPT_NAME'] ?? 'No disponible') . "</li>";
echo "<li><strong>HTTP Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'No disponible') . "</li>";
echo "<li><strong>Request URI:</strong> " . ($_SERVER['REQUEST_URI'] ?? 'No disponible') . "</li>";
echo "</ul>";

echo "<h2>🔐 Estado de la Sesión</h2>";
echo "<ul>";
echo "<li><strong>Session Status:</strong> " . session_status() . " (1=Disabled, 2=Active, 3=None)</li>";
echo "<li><strong>Session ID:</strong> " . (session_id() ?: 'No session') . "</li>";
echo "<li><strong>Session Name:</strong> " . session_name() . "</li>";
echo "</ul>";

if (!empty($_SESSION)) {
    echo "<h3>Variables de Sesión:</h3>";
    echo "<pre>";
    print_r($_SESSION);
    echo "</pre>";
} else {
    echo "<p>❌ No hay variables de sesión activas</p>";
}

echo "<h2>📁 Verificación de Archivos</h2>";
$archivos_criticos = [
    'config/conexion.php',
    'controllers/AuthController.php',
    'models/usuario.php',
    'views/dashboard.php',
    'views/auth/login.php'
];

echo "<ul>";
foreach ($archivos_criticos as $archivo) {
    $existe = file_exists($archivo);
    $icono = $existe ? "✅" : "❌";
    $permisos = $existe ? substr(sprintf('%o', fileperms($archivo)), -4) : 'N/A';
    echo "<li>$icono <strong>$archivo</strong> - Permisos: $permisos</li>";
}
echo "</ul>";

echo "<h2>🌐 URLs de Prueba</h2>";
$base_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
echo "<ul>";
echo "<li><a href='$base_url/index.php'>Página Principal</a></li>";
echo "<li><a href='$base_url/index.php?route=dashboard'>Dashboard</a></li>";
echo "<li><a href='$base_url/index.php?route=logout'>Logout</a></li>";
echo "</ul>";

echo "<h2>🔄 Prueba de Conexión a Base de Datos</h2>";
try {
    require_once 'config/conexion.php';
    $conn = Conexion::conectar();
    if ($conn) {
        echo "<p>✅ <strong>Conexión exitosa a la base de datos</strong></p>";
        
        // Probar consulta simple
        $stmt = $conn->query("SELECT COUNT(*) as total FROM usuario");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>✅ <strong>Usuarios en BD:</strong> " . $result['total'] . "</p>";
    } else {
        echo "<p>❌ <strong>Error: No se pudo conectar a la base de datos</strong></p>";
    }
} catch (Exception $e) {
    echo "<p>❌ <strong>Error de BD:</strong> " . $e->getMessage() . "</p>";
}

echo "<h2>🔧 Configuración PHP</h2>";
$php_configs = [
    'session.save_path',
    'session.cookie_path',
    'session.cookie_domain',
    'session.use_cookies',
    'session.use_only_cookies',
    'display_errors',
    'error_reporting'
];

echo "<ul>";
foreach ($php_configs as $config) {
    $value = ini_get($config);
    echo "<li><strong>$config:</strong> " . ($value ?: 'No definido') . "</li>";
}
echo "</ul>";

echo "<h2>📝 Logs de Error Recientes</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $lines = file($error_log);
    $recent_lines = array_slice($lines, -10);
    echo "<pre style='background: #f0f0f0; padding: 10px; font-size: 12px;'>";
    foreach ($recent_lines as $line) {
        echo htmlspecialchars($line);
    }
    echo "</pre>";
} else {
    echo "<p>📝 No se encontró archivo de log de errores</p>";
}

echo "<hr>";
echo "<p><small>Para resolver el error 403, revisa especialmente los permisos de archivos y la configuración de sesiones.</small></p>";
?>