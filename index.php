<?php
// Iniciar sesión
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Si hay un POST de login, procesarlo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['correo']) && isset($_POST['password'])) {
    require_once __DIR__ . '/models/usuario.php';
    
    $correo = $_POST['correo'];
    $password = $_POST['password'];
    $sucursal_id = $_POST['sucursal_id'] ?? 1;
    
    try {
        $usuario = Usuario::login($correo, $password);
        
        if ($usuario) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['sucursal_id'] = $sucursal_id;
            $_SESSION['rol_id'] = $usuario['rol_id'];
            $_SESSION['rol_nombre'] = $usuario['rol_nombre'];
            $_SESSION['correo'] = $usuario['correo'];
            $_SESSION['logged_in'] = true;
            
            // Redirección simple sin JavaScript
            header('Location: index.php');
            exit;
        } else {
            $error = "Credenciales inválidas";
        }
    } catch (Exception $e) {
        $error = "Error del sistema: " . $e->getMessage();
    }
}

// Si hay logout
if (isset($_GET['logout'])) {
    $_SESSION = array();
    session_destroy();
    header('Location: index.php');
    exit;
}

// Si está logueado, mostrar dashboard simple que funciona
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    require_once __DIR__ . '/dashboard_simple.php';
    exit;
}

// Si no está logueado, mostrar login
require_once __DIR__ . '/views/auth/login.php';
