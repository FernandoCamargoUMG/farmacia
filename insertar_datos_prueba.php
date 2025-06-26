<?php
require_once __DIR__ . '/config/conexion.php';

function nombreAleatorio() {
    $nombres = ['Luis', 'Carlos', 'Ana', 'María', 'Pedro', 'Laura', 'Jorge', 'Lucía', 'Daniel', 'Sofía'];
    return $nombres[array_rand($nombres)];
}

function apellidoAleatorio() {
    $apellidos = ['González', 'Rodríguez', 'Martínez', 'López', 'Hernández', 'Pérez', 'Gómez', 'Ramírez'];
    return $apellidos[array_rand($apellidos)];
}

function nombreProductoAleatorio() {
    $productos = ['Paracetamol', 'Ibuprofeno', 'Vitamina C', 'Amoxicilina', 'Loratadina', 'Aspirina', 'Omeprazol', 'Metformina'];
    return $productos[array_rand($productos)];
}

try {
    $conn = Conexion::conectar();

    // Insertar sucursales
    for ($i = 1; $i <= 5; $i++) {
        $stmt = $conn->prepare("INSERT INTO sucursal (nombre_sucursal, direccion_sucursal, departamento, telefono, porc_iva) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            "Sucursal " . chr(64 + $i), // A, B, C...
            "Avenida $i Zona $i",
            "Departamento $i",
            "5566$i$i$i",
            12
        ]);
    }

    // Insertar productos
    for ($i = 1; $i <= 10; $i++) {
        $nombreProducto = nombreProductoAleatorio() . " " . rand(100, 500) . "mg";
        $descripcion = "Medicamento genérico para uso común.";
        $precio = rand(5, 50) + (rand(0, 99) / 100);
        $stmt = $conn->prepare("INSERT INTO producto (nombre, descripcion, precio) VALUES (?, ?, ?)");
        $stmt->execute([$nombreProducto, $descripcion, $precio]);
    }

    // Insertar clientes
    for ($i = 1; $i <= 20; $i++) {
        $nombre = nombreAleatorio();
        $apellido = apellidoAleatorio();
        $dpi = strval(rand(1000000000000, 9999999999999));
        $email = strtolower($nombre . "." . $apellido . rand(1, 100) . "@correo.com");
        $telefono = "502" . rand(10000000, 99999999);
        $direccion = "Colonia Los Álamos #$i";
        $sucursal_id = rand(1, 5);
        $stmt = $conn->prepare("INSERT INTO clientes (sucursal_id, nombre, apellido, dpi, email, direccion, telefono, nit) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$sucursal_id, $nombre, $apellido, $dpi, $email, $direccion, $telefono, "CF"]);
    }

    echo "Datos aleatorios insertados correctamente.\n";

} catch (PDOException $e) {
    echo "Error al insertar datos: " . $e->getMessage() . "\n";
}
