<?php
require_once __DIR__ . '/../models/usuario.php';

class AuthController {
    private function asegurarSesion() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function login() {
        $this->asegurarSesion();

        $correo = $_POST['correo'];
        $password = $_POST['password'];
        $sucursal_id = $_POST['sucursal_id'];
        $usuario = Usuario::login($correo, $password);

        if ($usuario) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre']; // Agregar nombre del usuario
            $_SESSION['sucursal_id'] = $sucursal_id;
            $_SESSION['rol_id'] = $usuario['rol_id'];
            $_SESSION['rol_nombre'] = $usuario['rol_nombre']; // Agregar nombre del rol
            $_SESSION['correo'] = $usuario['correo'];

            header("Location: /?route=dashboard");
            exit;
        } else {
            header("Location: /?error=1");
            exit;
        }
    }

    public function logout() {
        $this->asegurarSesion();
        session_destroy();
        header('Location: /');
        exit;
    }
}
