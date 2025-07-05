<?php

require_once __DIR__ . '/../models/usuario.php';

class AuthController {
    public function login() {
        session_start();

        $correo = $_POST['correo'];
        $password = $_POST['password'];
        $sucursal_id = $_POST['sucursal_id'];
        $usuario = Usuario::login($correo, $password);

        if ($usuario) {
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['sucursal_id'] = $sucursal_id;
            $_SESSION['rol_id'] = $usuario['rol_id'];
            $_SESSION['correo'] = $usuario['correo'];
            header("Location: /farmacia/views/dashboard.php");
        } else {
            header('Location: /views/auth/login.php?error=Credenciales inválidas');
        }
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /views/auth/login.php');
    }
}
