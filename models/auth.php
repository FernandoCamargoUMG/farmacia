<?php
class Auth
{
    public static function sessionNoExist()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function usuarioId()
    {
        self::sessionNoExist();
        return $_SESSION['usuario_id'] ?? null;
    }

    public static function nombreUsuario()
    {
        self::sessionNoExist();
        return $_SESSION['nombre'] ?? 'Invitado';
    }

    public static function rolId()
    {
        self::sessionNoExist();
        return $_SESSION['rol_id'] ?? null;
    }

    public static function sucursalId()
    {
        self::sessionNoExist();
        return $_SESSION['sucursal_id'] ?? null;
    }

    public static function Autenticado()
    {
        self::sessionNoExist();
        return isset($_SESSION['usuario_id']);
    }

    public static function cerrarSesion()
    {
        self::sessionNoExist();
        session_destroy();
    }
}
