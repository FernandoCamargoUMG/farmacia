<?php

require_once __DIR__ . '/../controllers/AuthController.php';

$auth = new AuthController();

$route = $_GET['route'] ?? null;

if ($route === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->login($correo, $password);
} elseif ($route === 'logout') {
    $auth->logout();
} else {
    header("HTTP/1.0 404 Not Found");
    echo "Ruta no encontrada";
}
