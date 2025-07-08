<?php
// Mostrar errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Autoload o requires
require_once __DIR__ . '/../controllers/AuthController.php';

// Obtener la ruta
$route = $_GET['route'] ?? null;

// Routing
if ($route === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new AuthController();
    $auth->login();
} elseif ($route === 'logout') {
    $auth = new AuthController();
    $auth->logout();
} elseif ($route === 'dashboard') {
    require_once __DIR__ . '/../views/dashboard.php';
} else {
    // Cargar login por defecto
    require_once __DIR__ . '/../views/auth/login.php';
}
