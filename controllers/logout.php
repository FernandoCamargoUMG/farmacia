<?php
require_once __DIR__ . '/../models/auth.php';

Auth::cerrarSesion();

// Redirige al login
header("Location: /views/auth/login.php");
exit;
