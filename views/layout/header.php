<?php
session_start();
require_once __DIR__ . '/../../config/conexion.php';

$sucursalNombre = 'No definido';

if (isset($_SESSION['sucursal_id'])) {
    $conn = Conexion::conectar();
    $stmt = $conn->prepare("SELECT nombre_sucursal FROM sucursal WHERE id = ?");
    $stmt->execute([$_SESSION['sucursal_id']]);
    $sucursal = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sucursal) {
        $sucursalNombre = $sucursal['nombre_sucursal'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Farmacia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 + Íconos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="../public/css/style.css">
</head>

<body>
    <!-- Navbar minimalista -->
    <nav class="navbar">
        <div class="container-fluid">
            <button id="menuToggle" class="menu-btn">
                <i class="bi bi-list"></i>
            </button>
            <span class="navbar-brand">
                Sucursal: <?php echo htmlspecialchars($sucursalNombre); ?>
            </span>

        </div>
        <div class="ms-auto">
            <a href="/farmacia/controllers/logout.php" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-box-arrow-right"></i> Cerrar sesión
            </a>
        </div>

    </nav>

    <!-- Menú lateral -->
    <div id="sidebar">
        <ul>
            <li><a href="./dashboard.php"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li><a class="nav-link" href="#clientes"><i class="bi bi-people"></i> Clientes</a></li>
            <li><a href="/productos"><i class="bi bi-capsule-pill"></i> Productos</a></li>
            <li><a href="/ventas"><i class="bi bi-currency-dollar"></i> Ventas</a></li>
            <li><a href="/planilla"><i class="bi bi-file-earmark-text"></i> Planilla</a></li>
            <li><a href="/activos"><i class="bi bi-building-gear"></i> Activos Fijos</a></li>
        </ul>
    </div>

    <!-- Overlay -->
    <div id="overlay"></div>

    <!-- Contenido principal -->
    <div class="main-content" id="main-content">
        <!-- Contenido dinámico se cargará aquí -->
        <div id="dynamic-content" class="container text-center py-5">

            <h1>Bienvenido</h1>
            <p>Usuario</p>
            <!--<h3>Inicio</h3>-->
            <!-- <footer>© 2025 Sistema de Farmacia</footer>-->

        </div>
    </div>



    <!-- JS -->
    <script src="../public/js/menu.js"></script>
</body>

</html>