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
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="/public/css/style.css?v=1">
    <link rel="stylesheet" href="/public/css/ingreso.css">

</head>
<style>
    a[aria-expanded="true"] .bi-chevron-down {
        transform: rotate(180deg);
        transition: transform 0.3s ease;
    }

    .bi-chevron-down {
        transition: transform 0.3s ease;
    }
</style>

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
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../../config/conexion.php';

    // Roles
    define('ADMIN', 1);
    define('FARMACEUTICO', 2);
    define('CAJERO', 3);

    // arreglos para roles
    $menu = [
        'inicio' => ['label' => 'Inicio', 'icon' => 'house-door', 'link' => '/?route=dashboard.php', 'roles' => [ADMIN, FARMACEUTICO, CAJERO]],
        'usuarios' => ['label' => 'Mantenimiento de Usuarios', 'icon' => 'people', 'link' => '#Usuarios', 'roles' => [ADMIN]],
        'clientes' => ['label' => 'Clientes', 'icon' => 'people', 'link' => '#clientes', 'roles' => [ADMIN, FARMACEUTICO]],
        'proveedores' => ['label' => 'Proveedores', 'icon' => 'truck', 'link' => '#proveedores', 'roles' => [ADMIN, FARMACEUTICO]],
        'productos' => ['label' => 'Productos', 'icon' => 'capsule-pill', 'link' => '#producto', 'roles' => [ADMIN, FARMACEUTICO]],
        'bodegas' => ['label' => 'Bodegas', 'icon' => 'box-seam', 'link' => '#bodega', 'roles' => [ADMIN, FARMACEUTICO]],
        'planilla' => ['label' => 'Planilla', 'icon' => 'file-earmark-text', 'link' => '#planilla', 'roles' => [ADMIN]],
        'caja' => ['label' => 'Movimiento Caja', 'icon' => 'file-earmark-text', 'link' => '#caja', 'roles' => [ADMIN, FARMACEUTICO, CAJERO]],
        'inventario' => [
            'label' => 'Inventario', 
            'icon' => 'arrow-down-square', 
            'link' => '#', 
            'roles' => [ADMIN, FARMACEUTICO],
            'submenu' => [
                ['label' => 'Ingreso a Inventario', 'link' => '#ingreso', 'icon' => 'arrow-down-square'],
                ['label' => 'Ventas', 'link' => '#venta', 'icon' => 'currency-dollar'],
                ['label' => 'Reporte', 'link' => '#stock', 'icon' => 'file-earmark-text'],
            ]
        ],
        'activos' => [
            'label' => 'Activos Fijos', 
            'icon' => 'building-gear', 
            'link' => '#', 
            'roles' => [ADMIN],
            'submenu' => [
                ['label' => 'Registro de Activos', 'link' => '#activos', 'icon' => 'building-gear'],
                ['label' => 'Depreciaciones', 'link' => '#depreciacion', 'icon' => 'currency-dollar'],
            ]
        ],
        'categorias' => [
            'label' => 'Categorias', 
            'icon' => 'layers', 
            'link' => '#', 
            'roles' => [ADMIN],
            'submenu' => [
                ['label' => 'Tipo de activos', 'link' => '#activoc'],
                ['label' => 'Categoria de productos', 'link' => '#productocat'],
                ['label' => 'Formas de pago', 'link' => '#formapago'],
                ['label' => 'Categoria de proveedores', 'link' => '#proveedorcat'],
            ]
        ],
    ];

    $rol_id = $_SESSION['rol_id'] ?? 0;
    ?>

    <ul class="nav flex-column">
        <?php foreach ($menu as $item): ?>
            <?php if (in_array($rol_id, $item['roles'])): ?>
                <?php if (isset($item['submenu'])): ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex justify-content-between align-items-center activos-toggle" href="#" data-target="#submenu<?= $item['label'] ?>">
                            <span><i class="bi bi-<?= $item['icon'] ?>"></i> <?= $item['label'] ?></span>
                            <i class="bi bi-chevron-down toggle-icon"></i>
                        </a>
                        <ul class="collapse list-unstyled ps-3" id="submenu<?= $item['label'] ?>">
                            <?php foreach ($item['submenu'] as $sub): ?>
                                <li><a class="nav-link" href="<?= $sub['link'] ?>"><i class="bi bi-<?= $sub['icon'] ?? 'dash' ?>"></i> <?= $sub['label'] ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php else: ?>
                    <li><a class="nav-link" href="<?= $item['link'] ?>"><i class="bi bi-<?= $item['icon'] ?>"></i> <?= $item['label'] ?></a></li>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>
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
    <script src="/public/js/CategoriaActivo.js"></script>
    <script src="/public/js/CategoriaProducto.js"></script>
    <script src="/public/js/CategoriaProveedor.js"></script>
    <script src="/public/js/formaPago.js"></script>
    <script src="/public/js/activoFijo.js"></script>
    <script src="/public/js/depreciacion.js"></script>
    <script src="/public/js/inventario.js"></script>
    <script src="/public/js/caja.js"></script>
    <script src="/public/js/usuario.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
    </script>
</body>

</html>