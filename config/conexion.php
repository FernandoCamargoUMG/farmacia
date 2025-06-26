<?php
$servername = "localhost";
$database = "";
$username = "root";
$password = "";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $database);

// Verificar si la conexión fue exitosa
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Configurar el conjunto de caracteres
$conn->set_charset('utf8mb4');
?>
