<?php
require_once __DIR__ . '/../models/auth.php';

if (!Auth::Autenticado()) {
    header('Location: /'); // Redirige a index.php sin rutas rotas
    exit;
}
