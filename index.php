<?php
// Mostrar errores para desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión solo si no está activa
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/controllers/AuthController.php';

$route = $_GET['route'] ?? null;
$auth = new AuthController();

// Rutas
if ($route === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->login();
    exit;
} elseif ($route === 'logout') {
    $auth->logout();
    exit;
} elseif ($route === 'dashboard') {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: /?route=login');
        exit;
    }
    require_once __DIR__ . '/views/dashboard.php';
    exit;
}

// Ruta por defecto
if (isset($_SESSION['usuario_id'])) {
    header('Location: /?route=dashboard');
    exit;
}

require_once __DIR__ . '/views/auth/login.php';
