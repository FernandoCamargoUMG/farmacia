<?php
require_once __DIR__ . '/../models/Auth.php';

Auth::cerrarSesion();

// Redirige al login
header("Location: /farmacia/views/auth/login.php");
exit;
