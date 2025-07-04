<?php
class Auth
{
    public static function iniciarSesionSiNoExiste()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function usuarioId()
    {
        self::iniciarSesionSiNoExiste();
        return $_SESSION['usuario_id'] ?? null;
    }

    public static function nombreUsuario()
    {
        self::iniciarSesionSiNoExiste();
        return $_SESSION['nombre'] ?? 'Invitado';
    }

    public static function rolId()
    {
        self::iniciarSesionSiNoExiste();
        return $_SESSION['rol_id'] ?? null;
    }

    public static function sucursalId()
    {
        self::iniciarSesionSiNoExiste();
        return $_SESSION['sucursal_id'] ?? null;
    }

    public static function estaAutenticado()
    {
        self::iniciarSesionSiNoExiste();
        return isset($_SESSION['usuario_id']);
    }

    public static function cerrarSesion()
    {
        self::iniciarSesionSiNoExiste();
        session_destroy();
    }
}
