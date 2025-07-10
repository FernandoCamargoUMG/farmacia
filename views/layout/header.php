<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="/public/css/style.css?v=1">
    <link rel="stylesheet" href="/public/css/ingreso.css">

</head>

<body>
    <!-- Navbar minimalista -->
    <nav class="navbar">
        <div class="container-fluid d-flex justify-content-between">
            <div>
                <button id="menuToggle" class="menu-btn">
                    <i class="bi bi-list"></i>
                </button>
                <span class="navbar-brand">
                    Sucursal: <?php echo htmlspecialchars($sucursalNombre); ?>
                </span>
            </div>
            <div>
                <a href="/controllers/logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </div>
        </div>
    </nav>
    <!-- Menú lateral -->
    <div id="sidebar">
        <ul>
            <li><a href="/?route=dashboard.php"><i class="bi bi-house-door"></i> Inicio</a></li>
            <li><a class="nav-link" href="#clientes"><i class="bi bi-people"></i> Clientes</a></li>
            <li><a class="nav-link" href="#proveedores"><i class="bi bi-truck"></i>Proveedores</a></li>
            <li><a class="nav-link" href="#producto"><i class="bi bi-capsule-pill"></i> Productos</a></li>
            <li><a class="nav-link" href="#bodega"><i class="bi bi-box-seam"></i> Bodegas</a></li>
            <li><a class="nav-link" href="#ingreso"><i class="bi bi-arrow-down-square"></i> Ingreso a Inventario</a></li>
            <li><a class="nav-link" href="#venta"><i class="bi bi-currency-dollar"></i> Ventas</a></li>
            <li><a class="nav-link" href="#planilla"><i class="bi bi-file-earmark-text"></i> Planilla</a></li>
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
    <script src="/public/js/menu.js"></script>
    <script src="/public/js/clientes.js"></script>
    <script src="/public/js/proveedor.js"></script>
    <script src="/public/js/producto.js"></script>
    <script src="/public/js/bodega.js"></script>
    <script src="/public/js/ingreso.js"></script>
    <script src="/public/js/venta.js"></script>
    <script src="/public/js/planilla.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>