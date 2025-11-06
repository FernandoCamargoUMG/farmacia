<?php
// Script para crear la base de datos local rápidamente
$host = "localhost";
$user = "root";
$pass = "";

try {
    // Conectar sin especificar base de datos
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS farmacia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Usar la base de datos
    $pdo->exec("USE farmacia");
    
    // Crear tablas básicas si no existen
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS sucursal (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre_sucursal VARCHAR(255) NOT NULL
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuario (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            correo VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            rol_id INT DEFAULT 1,
            sucursal_id INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS producto (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            codigo VARCHAR(100),
            precio DECIMAL(10,2) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sucursal_id INT DEFAULT 1,
            nombre VARCHAR(255) NOT NULL,
            apellido VARCHAR(255),
            dpi VARCHAR(20),
            correo VARCHAR(255),
            direccion TEXT,
            telefono VARCHAR(20),
            nit VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS egreso_cab (
            id INT AUTO_INCREMENT PRIMARY KEY,
            sucursal_id INT DEFAULT 1,
            cliente_id INT,
            usuario_id INT DEFAULT 1,
            fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
            numero_documento VARCHAR(50),
            subtotal DECIMAL(10,2) DEFAULT 0,
            total DECIMAL(10,2) DEFAULT 0,
            total_pagado DECIMAL(10,2) DEFAULT 0,
            impuesto DECIMAL(10,2) DEFAULT 0,
            forma_pago INT DEFAULT 1,
            observaciones TEXT,
            sta TINYINT DEFAULT 1
        )
    ");
    
    // Insertar datos básicos
    $pdo->exec("INSERT IGNORE INTO sucursal (id, nombre_sucursal) VALUES (1, 'FERRETERIA')");
    
    // Insertar usuario admin por defecto (password: admin123)
    $passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT IGNORE INTO usuario (id, nombre, correo, password, rol_id, sucursal_id) 
                VALUES (1, 'Administrador', 'admin@ferreteria.com', '$passwordHash', 1, 1)");
    
    // Insertar algunos productos de ejemplo
    $pdo->exec("INSERT IGNORE INTO producto (id, nombre, codigo, precio) VALUES 
                (1, 'MARTILLO CABEZA PLANA', 'MART001', 85.00),
                (2, 'DESTORNILLADOR', 'DEST001', 25.00),
                (3, 'TORNILLOS', 'TORN001', 15.00)");
    
    // Insertar algunos clientes de ejemplo
    $pdo->exec("INSERT IGNORE INTO clientes (id, sucursal_id, nombre, apellido, dpi, telefono) VALUES 
                (1, 1, 'JUAN', 'PEREZ', '1234567890123', '12345678'),
                (2, 1, 'MARIA', 'GARCIA', '9876543210987', '87654321')");
    
    echo "<h1>✅ Base de datos configurada correctamente</h1>";
    echo "<p>Base de datos: farmacia</p>";
    echo "<p>Usuario admin creado:</p>";
    echo "<p><strong>Email:</strong> admin@ferreteria.com</p>";
    echo "<p><strong>Password:</strong> admin123</p>";
    echo "<br><a href='index.php'>Ir al Sistema</a>";
    
} catch (PDOException $e) {
    echo "<h1>❌ Error configurando base de datos</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Asegúrate de que XAMPP esté ejecutándose y MySQL esté activo.</p>";
}
?>