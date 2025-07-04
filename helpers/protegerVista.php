<?php
require_once __DIR__ . '/../models/Auth.php';

if (!Auth::estaAutenticado()) {
    header('Location: /farmacia/views/auth/login.php');
    exit;
}
