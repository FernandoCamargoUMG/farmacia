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

        $correo = $_POST['correo'] ?? '';
        $password = $_POST['password'] ?? '';
        $sucursal_id = $_POST['sucursal_id'] ?? 1;

        if (empty($correo) || empty($password)) {
            $this->redirectToLogin("Correo y contraseña son requeridos");
            return;
        }

        try {
            $usuario = Usuario::login($correo, $password);

            if ($usuario) {
                // Configurar variables de sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['sucursal_id'] = $sucursal_id;
                $_SESSION['rol_id'] = $usuario['rol_id'];
                $_SESSION['rol_nombre'] = $usuario['rol_nombre'];
                $_SESSION['correo'] = $usuario['correo'];
                $_SESSION['logged_in'] = true;

                // Redirección compatible con hosting compartido
                $this->redirectToDashboard();
            } else {
                $this->redirectToLogin("Credenciales inválidas");
            }
        } catch (Exception $e) {
            error_log("Error en login: " . $e->getMessage());
            $this->redirectToLogin("Error del sistema. Intente nuevamente.");
        }
    }

    private function redirectToDashboard() {
        // Redirección directa al dashboard sin rutas para Hostinger
        if (headers_sent()) {
            echo '<script>window.location.href = "views/dashboard.php";</script>';
            echo '<meta http-equiv="refresh" content="0;url=views/dashboard.php">';
        } else {
            header("Location: views/dashboard.php");
            exit;
        }
    }

    private function redirectToLogin($message = "") {
        if (headers_sent()) {
            $errorParam = !empty($message) ? "&error=" . urlencode($message) : "&error=1";
            echo '<script>window.location.href = "index.php' . $errorParam . '";</script>';
            echo '<meta http-equiv="refresh" content="0;url=index.php' . $errorParam . '">';
        } else {
            $errorParam = !empty($message) ? "?error=" . urlencode($message) : "?error=1";
            header("Location: index.php" . $errorParam);
            exit;
        }
    }

    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $script = dirname($_SERVER['SCRIPT_NAME']);
        return $protocol . '://' . $host . rtrim($script, '/') . '/';
    }

    public function logout() {
        $this->asegurarSesion();
        
        // Limpiar todas las variables de sesión
        $_SESSION = array();
        
        // Destruir la cookie de sesión si existe
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        
        // Destruir la sesión
        session_destroy();
        
        // Redirección compatible con hosting compartido
        if (headers_sent()) {
            echo '<script>window.location.href = "index.php";</script>';
            echo '<meta http-equiv="refresh" content="0;url=index.php">';
        } else {
            header('Location: index.php');
            exit;
        }
    }
}
