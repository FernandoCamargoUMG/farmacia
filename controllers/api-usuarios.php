<?php
require_once __DIR__ . '/../config/conexion.php';

// Recibir datos JSON enviados desde el Sistema A
$input = json_decode(file_get_contents("php://input"), true);

if (!empty($input)) {
    try {
        $pdo = Conexion::conectar();

        foreach($input as $usuario) {
            $nombre = $usuario['name'] ?? '';
            $correo = $usuario['email'] ?? '';

            if($nombre && $correo) {
                // Generar password aleatoria
                $password = md5(uniqid(rand(), true));

                // Insertar en la tabla usuario
                $stmt = $pdo->prepare("INSERT INTO usuario (nombre, correo, password) VALUES (?, ?, ?)");
                $stmt->execute([$nombre, $correo, $password]);
            }
        }

        echo json_encode([
            "status" => "success",
            "message" => "Usuarios insertados correctamente"
        ]);

    } catch (Exception $e) {
        echo json_encode([
            "status" => "error",
            "message" => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "No se recibieron datos"
    ]);
}
