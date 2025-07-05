<?php
// helpers/AuthHelper.php

class AuthHelper {
    public static function getSucursalId() {
        session_start();
        return isset($_SESSION['sucursal_id']) ? $_SESSION['sucursal_id'] : null;
    }

    public static function getUsuarioId() {
        session_start();
        return isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
    }

    public static function getRolId() {
        session_start();
        return isset($_SESSION['rol_id']) ? $_SESSION['rol_id'] : null;
    }
}
