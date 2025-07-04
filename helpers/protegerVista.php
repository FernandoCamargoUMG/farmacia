<?php
require_once __DIR__ . '/../models/Auth.php';

if (!Auth::Autenticado()) {
    header('Location: /farmacia/views/auth/login.php');
    exit;
}
