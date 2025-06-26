<?php

require_once __DIR__ . '/config/conexion.php';

// Lista de migraciones específicas a ejecutar
$migraciones = [
    __DIR__ . '/migraciones/001_crear_tablas_basicas.php',
    /*
    */
];

sort($migraciones);

foreach ($migraciones as $archivo) {
    echo "Ejecutando: " . basename($archivo) . "\n";
    include $archivo;
}
