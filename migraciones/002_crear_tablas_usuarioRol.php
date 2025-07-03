<?php
// MIGRACIÓN DE USUARIOS Y ROLES

try {
    $conn = Conexion::conectar();

    // Tabla de roles
    $sql_rol = "
        CREATE TABLE IF NOT EXISTS rol (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL
        ) ENGINE=InnoDB;
    ";
    $conn->exec($sql_rol);

    // Tabla de usuarios
    $sql_usuario = "
        CREATE TABLE IF NOT EXISTS usuario (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            correo VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            sucursal_id INT NOT NULL,
            rol_id INT NOT NULL,
            FOREIGN KEY (sucursal_id) REFERENCES sucursal(id),
            FOREIGN KEY (rol_id) REFERENCES rol(id)
        ) ENGINE=InnoDB;
    ";
    $conn->exec($sql_usuario);

    echo "\n✅ Tablas 'rol' y 'usuario' creadas correctamente.\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
