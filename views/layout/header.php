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
    <title>Ferreteria</title>
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
    <link rel="stylesheet" href="public/css/style.css?v=4">
    <link rel="stylesheet" href="public/css/ingreso.css">

</head>
<style>
    /* Estilos del sistema ERP */
    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f1f3f4;
    }

    /* Navbar */
    .navbar {
        background: linear-gradient(135deg, #2c7a7b, #319795);
        padding: 0.5rem 1rem;
        color: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .navbar-brand {
        color: white !important;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .menu-btn {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        padding: 0.5rem;
        border-radius: 4px;
        transition: background-color 0.3s;
    }

    .menu-btn:hover {
        background-color: rgba(240,244,248,0.08);
    }

    /* Sidebar principal */
    #sidebar {
        position: fixed;
        top: 0;
        left: -280px;
        width: 280px;
        height: 100vh;
        background: linear-gradient(180deg, #2c7a7b 0%, #285e61 100%);
        z-index: 1050;
        transition: left 0.3s ease;
        overflow-y: auto;
        padding-top: 60px;
        box-shadow: 2px 0 10px rgba(0,0,0,0.15);
    }

    #sidebar.active {
        left: 0;
    }

    /* Título del sistema */
    .sidebar-header {
        background-color: rgba(0,0,0,0.2);
        border-bottom: 1px solid rgba(240,244,248,0.08);
    }

    .sidebar-header h4 {
        font-size: 1.1rem;
        font-weight: 600;
        color: #e2e8f0 !important;
        margin: 0;
        padding: 1rem 0;
    }

    /* Enlaces del menú principal */
    #sidebar .nav-link {
        color: #e2e8f0 !important;
        padding: 0.8rem 1.2rem;
        margin: 0.2rem 0.5rem;
        border-radius: 8px;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        position: relative;
    }

    #sidebar .nav-link:hover {
        background-color: rgba(240,244,248,0.08);
        color: white !important;
        transform: translateX(3px);
    }

    #sidebar .nav-link i {
        margin-right: 0.8rem;
        font-size: 1.1rem;
        min-width: 20px;
    }

    /* Enlaces con submenú */
    .activos-toggle {
        cursor: pointer;
    }

    .toggle-icon {
        transition: transform 0.3s ease;
        font-size: 0.8rem;
        margin-left: auto;
    }

    .activos-toggle[aria-expanded="true"] .toggle-icon,
    .activos-toggle.active .toggle-icon {
        transform: rotate(180deg);
    }

    /* Submenús */
    .submenu {
        display: none;
        background-color: rgba(0,0,0,0.15);
        margin: 0.3rem 0.5rem;
        border-radius: 6px;
        padding: 0.5rem 0;
        border-left: 3px solid #4fd1c7;
    }

    .submenu.show {
        display: block;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            max-height: 0;
        }
        to {
            opacity: 1;
            max-height: 200px;
        }
    }

    .submenu .nav-link {
        color: #cbd5e0 !important;
        padding: 0.6rem 1rem 0.6rem 2.5rem;
        margin: 0.1rem 0;
        font-size: 0.9rem;
        font-weight: 400;
    }

    .submenu .nav-link:hover {
        background-color: rgba(240,244,248,0.08);
        color: #4fd1c7 !important;
        transform: translateX(5px);
    }

    .submenu .nav-link i {
        margin-right: 0.6rem;
        font-size: 0.9rem;
        color: #4fd1c7;
    }

    /* Overlay */
    #overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 1040;
        display: none;
    }

    /* Contenido principal */
    .main-content {
        margin-top: 60px;
        padding: 1.5rem;
        transition: margin-left 0.3s ease;
        min-height: calc(100vh - 60px);
        background-color: #f1f4f6;
    }

    .main-content.shifted {
        margin-left: 280px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        #sidebar {
            width: 100%;
            left: -100%;
        }
        
        .main-content.shifted {
            margin-left: 0;
        }
    }

    /* Scroll personalizado */
    #sidebar::-webkit-scrollbar {
        width: 6px;
    }

    #sidebar::-webkit-scrollbar-track {
        background: rgba(240,244,248,0.08);
    }

    #sidebar::-webkit-scrollbar-thumb {
        background: rgba(245,248,250,0.25);
        border-radius: 3px;
    }

    #sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(240,244,248,0.4);
    }

    /* Botón cerrar sesión */
    .btn-outline-danger {
        border-color: rgba(240,244,248,0.25);
        color: white;
    }

    .btn-outline-danger:hover {
        background-color: #e53e3e;
        border-color: #e53e3e;
        color: white;
    }

    /* ESTILOS DEL DASHBOARD */
    .dashboard-container {
        padding: 2rem;
        background: linear-gradient(135deg, #f1f3f4 0%, #e8f4f2 20%, #e0f2e8 40%, #c8e6d0 70%, #4fd1c7 100%);
        min-height: 100vh;
        margin-top: -1.5rem;
        margin-left: -1.5rem;
        margin-right: -1.5rem;
        margin-bottom: -1.5rem;
        position: relative;
    }
    
    .dashboard-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(79,209,199,0.05)"/><circle cx="75" cy="75" r="1" fill="rgba(79,209,199,0.03)"/><circle cx="50" cy="10" r="0.5" fill="rgba(240,244,248,0.08)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        pointer-events: none;
    }

    .dashboard-header {
        background: rgba(248, 250, 252, 0.85);
        backdrop-filter: blur(15px);
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid rgba(79, 209, 199, 0.15);
        box-shadow: 0 8px 25px rgba(79, 209, 199, 0.08);
    }

    .dashboard-title {
        color: #0d9488;
        font-weight: 700;
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(79, 209, 199, 0.1);
    }

    .dashboard-subtitle {
        color: #14b8a6;
        font-size: 1.1rem;
        margin: 0;
    }

    .user-info {
        display: flex;
        align-items: center;
        background: rgba(248, 250, 252, 0.8);
        padding: 1rem 1.5rem;
        border-radius: 50px;
        border: 1.5px solid rgba(79, 209, 199, 0.3);
        box-shadow: 0 4px 15px rgba(79, 209, 199, 0.12);
    }

    .user-avatar {
        font-size: 2.5rem;
        color: #4fd1c7;
        margin-right: 1rem;
    }

    .user-details {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        color: #0d9488;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .user-role {
        color: #14b8a6;
        font-size: 0.9rem;
    }

    /* Dropdown del usuario */
    .user-info.dropdown-toggle::after {
        margin-left: 1rem;
        color: #4fd1c7;
    }

    .user-info:hover {
        background: rgba(79, 209, 199, 0.1);
        border-color: #4fd1c7;
        transform: translateY(-1px);
        transition: all 0.3s ease;
    }

    .dropdown-menu {
        border-radius: 15px;
        border: 1px solid rgba(79, 209, 199, 0.2);
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        backdrop-filter: blur(10px);
        background: rgba(255,255,255,0.95);
    }

    .dropdown-item {
        padding: 0.75rem 1.25rem;
        border-radius: 10px;
        margin: 0.25rem;
    }

    .dropdown-item:hover {
        background: rgba(79, 209, 199, 0.1);
        color: #0d9488;
    }

    /* Tarjetas de estadísticas */
    .stats-card {
        background: rgba(240, 244, 248, 0.65);
        backdrop-filter: blur(20px);
        border-radius: 24px;
        padding: 2rem;
        border: 1px solid rgba(79, 209, 199, 0.15);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        position: relative;
        overflow: hidden;
        box-shadow: 0 6px 25px rgba(79, 209, 199, 0.08);
    }

    .stats-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #4fd1c7, #2dd4bf);
    }

    .stats-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(79, 209, 199, 0.18);
        background: rgba(250, 252, 254, 0.9);
        border-color: rgba(79, 209, 199, 0.3);
    }
    
    .stats-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 50% 50%, rgba(240,244,248,0.08) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }
    
    .stats-card:hover::after {
        opacity: 1;
    }

    .stats-products::before { background: linear-gradient(90deg, #4fd1c7, #0d9488); }
    .stats-sales::before { background: linear-gradient(90deg, #14b8a6, #0f766e); }
    .stats-clients::before { background: linear-gradient(90deg, #06b6d4, #0891b2); }
    .stats-inventory::before { background: linear-gradient(90deg, #f59e0b, #d97706); }

    .stats-icon {
        font-size: 3rem;
        color: #4fd1c7;
        margin-bottom: 1rem;
        opacity: 0.8;
        transition: all 0.3s ease;
    }

    .stats-card:hover .stats-icon {
        opacity: 1;
        transform: scale(1.1);
    }

    .stats-content h3 {
        color: #0d9488;
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .stats-content p {
        color: #14b8a6;
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .stats-trend {
        font-size: 0.9rem;
        padding: 0.25rem 0.5rem;
        border-radius: 10px;
    }

    .stats-trend.positive {
        background: rgba(79, 209, 199, 0.2);
        color: #4fd1c7;
        border: 1px solid rgba(79, 209, 199, 0.3);
    }

    .stats-trend.negative {
        background: rgba(239, 68, 68, 0.2);
        color: #ef4444;
        border: 1px solid rgba(239, 68, 68, 0.3);
    }

    /* Tarjetas de gráficos */
    .chart-card, .activity-card, .table-card {
        background: rgba(235, 240, 245, 0.70);
        border-radius: 20px;
        border: 1px solid rgba(79, 209, 199, 0.12);
        box-shadow: 0 8px 25px rgba(79, 209, 199, 0.1);
        overflow: hidden;
    }

    .chart-header, .activity-header, .table-header {
        background: linear-gradient(135deg, #4fd1c7, #0d9488);
        color: white;
        padding: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        overflow: hidden;
    }
    
    .chart-header::before, .activity-header::before, .table-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(240,244,248,0.08) 0%, transparent 100%);
        pointer-events: none;
    }

    .chart-header h5, .activity-header h5, .table-header h5 {
        margin: 0;
        font-weight: 600;
    }

    .chart-body, .activity-body, .table-body {
        padding: 1.5rem;
        background: rgba(250, 252, 254, 0.6);
    }

    /* Actividad reciente */
    .activity-item {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #e2e8f0;
    }

    .activity-item:last-child {
        border-bottom: none;
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin-right: 1rem;
        font-size: 1.2rem;
    }

    .activity-content p {
        margin: 0;
        font-weight: 500;
        color: #2d3748;
    }

    .activity-content small {
        color: #718096;
    }

    /* Tabla de stock bajo */
    #low-stock-table {
        margin: 0;
    }

    #low-stock-table th {
        background: linear-gradient(135deg, #f1f4f6, #e8f4f2);
        color: #0d9488;
        font-weight: 600;
        border: none;
        padding: 1rem;
    }

    #low-stock-table td {
        padding: 1rem;
        vertical-align: middle;
        border-color: rgba(79, 209, 199, 0.1);
        background: rgba(248, 250, 252, 0.75);
    }

    .badge-danger {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        border: none;
    }

    .badge-warning {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
        border: none;
    }
    
    .badge-success {
        background: linear-gradient(135deg, #10b981, #047857);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
        border: none;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
            background: linear-gradient(135deg, #d1d4d6 0%, #e8f4f2 50%, #4fd1c7 100%);
        }
        
        .dashboard-header {
            padding: 1.5rem;
        }
        
        .dashboard-title {
            font-size: 2rem;
        }
        
        .user-info {
            padding: 0.8rem 1rem;
        }
        
        .stats-card {
            margin-bottom: 1rem;
        }
    }
    
    /* Indicador en vivo */
    .live-indicator {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .live-dot {
        width: 8px;
        height: 8px;
        background: #4fd1c7;
        border-radius: 50%;
        animation: pulse 2s infinite;
        box-shadow: 0 0 10px rgba(79, 209, 199, 0.5);
    }
    
    @keyframes pulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(79, 209, 199, 0.7);
        }
        
        70% {
            transform: scale(1);
            box-shadow: 0 0 0 10px rgba(79, 209, 199, 0);
        }
        
        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(79, 209, 199, 0);
        }
    }
    
    /* Mejoras en botones */
    .btn-outline-light {
        border-color: rgba(240,244,248,0.7);
        color: white;
        background: rgba(240,244,248,0.08);
        transition: all 0.3s ease;
    }
    
    .btn-outline-light:hover {
        background-color: rgba(245,248,250,0.85);
        border-color: rgba(240,244,248,1);
        color: #4fd1c7;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(240,244,248,0.3);
    }
    
    .btn-outline-primary {
        border-color: #4fd1c7;
        color: #4fd1c7;
        transition: all 0.3s ease;
    }
    
    .btn-outline-primary:hover {
        background-color: #4fd1c7;
        border-color: #4fd1c7;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 209, 199, 0.3);
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
                <a href="index.php?logout=1" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Cerrar sesión
                </a>
            </div>
        </div>
    </nav>
    <!-- Menú lateral -->
<div id="sidebar">
    <div class="sidebar-header">
        <h4 class="text-center text-white py-3 mb-0 border-bottom border-secondary">Sistema ERP</h4>
    </div>
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    require_once __DIR__ . '/../../config/conexion.php';

    // Roles
    define('ADMIN', 1);
    define('Bodeguero', 2);
    define('CAJERO', 3);

    // arreglos para roles
    $menu = [
        'inicio' => ['label' => 'Inicio', 'icon' => 'house-door', 'link' => '/?route=dashboard.php', 'roles' => [ADMIN, Bodeguero, CAJERO]],
        'usuarios' => ['label' => 'Mantenimiento de Usuarios', 'icon' => 'people', 'link' => '#Usuarios', 'roles' => [ADMIN]],
        'clientes' => ['label' => 'Clientes', 'icon' => 'people', 'link' => '#clientes', 'roles' => [ADMIN, Bodeguero]],
        'proveedores' => ['label' => 'Proveedores', 'icon' => 'truck', 'link' => '#proveedores', 'roles' => [ADMIN, Bodeguero]],
        'productos' => ['label' => 'Productos', 'icon' => 'capsule-pill', 'link' => '#producto', 'roles' => [ADMIN, Bodeguero]],
        'bodegas' => ['label' => 'Bodegas', 'icon' => 'box-seam', 'link' => '#bodega', 'roles' => [ADMIN, Bodeguero]],
        'planilla' => ['label' => 'Planilla', 'icon' => 'file-earmark-text', 'link' => '#planilla', 'roles' => [ADMIN]],
        'caja' => ['label' => 'Movimiento Caja', 'icon' => 'file-earmark-text', 'link' => '#caja', 'roles' => [ADMIN, Bodeguero, CAJERO]],
        'inventario' => [
            'label' => 'Inventario', 
            'icon' => 'arrow-down-square', 
            'link' => '#', 
            'roles' => [ADMIN, Bodeguero],
            'submenu' => [
                ['label' => 'Ingreso a Inventario', 'link' => '#ingreso', 'icon' => 'arrow-down-square'],
                ['label' => 'Ventas', 'link' => '#venta', 'icon' => 'currency-dollar'],
                ['label' => 'Movimientos', 'link' => '#stock', 'icon' => 'arrow-left-right'],
                ['label' => 'Reportes PDF', 'link' => '#reportes', 'icon' => 'file-earmark-pdf'],
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
                    <?php $submenuId = 'submenu' . str_replace(' ', '_', $item['label']); ?>
                    <li class="nav-item">
                        <a class="nav-link d-flex justify-content-between align-items-center activos-toggle" href="#" data-target="#<?= $submenuId ?>">
                            <span><i class="bi bi-<?= $item['icon'] ?>"></i> <?= $item['label'] ?></span>
                            <i class="bi bi-chevron-down toggle-icon"></i>
                        </a>
                        <ul class="submenu list-unstyled ps-3" id="<?= $submenuId ?>">
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
        <div id="dynamic-content" class="dashboard-container">
            <!-- Dashboard Principal -->
            <div class="dashboard-header mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="dashboard-title">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </h1>
                        <p class="dashboard-subtitle">Panel de control</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="d-flex align-items-center justify-content-end gap-3">
                            <div class="dropdown">
                                <div class="user-info dropdown-toggle" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                                    <div class="user-avatar">
                                        <i class="bi bi-person-circle"></i>
                                    </div>
                                    <div class="user-details">
                                        <span class="user-name">
                                            <i class="bi bi-person-check me-1"></i>
                                            <?php echo isset($_SESSION['usuario_nombre']) ? htmlspecialchars($_SESSION['usuario_nombre']) : 'Usuario'; ?>
                                        </span>
                                        <small class="user-role">
                                            <i class="bi bi-shield-check me-1"></i>
                                            <?php echo isset($_SESSION['rol_nombre']) ? htmlspecialchars($_SESSION['rol_nombre']) : 'Rol'; ?>
                                            • 
                                            <i class="bi bi-building me-1"></i>
                                            <?php echo htmlspecialchars($sucursalNombre); ?>
                                        </small>
                                    </div>
                                </div>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="updateDashboard()">
                                            <i class="bi bi-arrow-clockwise me-2"></i>Actualizar Dashboard
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="index.php?logout=1">
                                            <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stats-card stats-products">
                        <div class="stats-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="stats-content">
                            <h3 id="total-productos">-</h3>
                            <p>Total Productos</p>
                            <small class="stats-trend positive">
                                <i class="bi bi-arrow-up"></i> +5% este mes
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stats-card stats-sales">
                        <div class="stats-icon">
                            <i class="bi bi-currency-dollar"></i>
                        </div>
                        <div class="stats-content">
                            <h3 id="total-ventas">-</h3>
                            <p>Ventas Hoy</p>
                            <small class="stats-trend positive">
                                <i class="bi bi-arrow-up"></i> +12% vs ayer
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stats-card stats-clients">
                        <div class="stats-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <div class="stats-content">
                            <h3 id="total-clientes">-</h3>
                            <p>Clientes Activos</p>
                            <small class="stats-trend positive">
                                <i class="bi bi-arrow-up"></i> +3 nuevos
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="stats-card stats-inventory">
                        <div class="stats-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <div class="stats-content">
                            <h3 id="stock-bajo">-</h3>
                            <p>Stock Bajo</p>
                            <small class="stats-trend negative">
                                <i class="bi bi-exclamation-triangle"></i> Revisar
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos y actividad reciente -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h5><i class="bi bi-bar-chart"></i> Ventas de los últimos 7 días</h5>
                            <div class="chart-actions">
                                <small class="text-white-50 me-3" id="last-refresh">Actualizando...</small>
                                <button class="btn btn-sm btn-outline-light" onclick="refreshChart()">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="salesChart" width="400" height="150"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 mb-4">
                    <div class="activity-card">
                        <div class="activity-header">
                            <h5><i class="bi bi-clock"></i> Actividad Reciente</h5>
                        </div>
                        <div class="activity-body" id="recent-activity">
                            <div class="activity-item">
                                <div class="activity-icon bg-success">
                                    <i class="bi bi-plus-circle"></i>
                                </div>
                                <div class="activity-content">
                                    <p>Nueva venta registrada</p>
                                    <small>Hace 5 minutos</small>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon bg-info">
                                    <i class="bi bi-box"></i>
                                </div>
                                <div class="activity-content">
                                    <p>Producto actualizado</p>
                                    <small>Hace 15 minutos</small>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon bg-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <div class="activity-content">
                                    <p>Stock bajo detectado</p>
                                    <small>Hace 1 hora</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos con stock bajo -->
            <div class="row">
                <div class="col-12">
                    <div class="table-card">
                        <div class="table-header">
                            <h5><i class="bi bi-exclamation-triangle-fill text-warning"></i> Productos con Stock Bajo</h5>
                            <div class="d-flex align-items-center">
                                <div class="live-indicator me-3">
                                    <span class="live-dot"></span>
                                    <small class="text-muted">En vivo</small>
                                </div>
                                <button class="btn btn-sm btn-outline-primary" onclick="refreshLowStock()">
                                    <i class="bi bi-arrow-clockwise"></i> Actualizar
                                </button>
                            </div>
                        </div>
                        <div class="table-body">
                            <table class="table table-hover" id="low-stock-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Stock Actual</th>
                                    </tr>
                                </thead>
                                <tbody id="low-stock-tbody">
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            <div class="spinner-border spinner-border-sm" role="status">
                                                <span class="visually-hidden">Cargando...</span>
                                            </div>
                                            Cargando datos...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- JS -->
    <script src="public/js/menu.js"></script>
    <script src="public/js/clientes.js"></script>
    <script src="public/js/proveedor.js"></script>
    <script src="public/js/producto.js"></script>
    <script src="public/js/bodega.js"></script>
    <script src="public/js/ingreso.js"></script>
    <script src="public/js/venta.js"></script>
    <script src="public/js/planilla.js"></script>
    <script src="public/js/CategoriaActivo.js"></script>
    <script src="public/js/CategoriaProducto.js"></script>
    <script src="public/js/CategoriaProveedor.js"></script>
    <script src="public/js/formaPago.js"></script>
    <script src="public/js/activoFijo.js"></script>
    <script src="public/js/depreciacion.js"></script>
    <script src="public/js/inventario.js"></script>
    <script src="public/js/reportes.js"></script>
    <script src="public/js/caja.js"></script>
    <script src="public/js/usuario.js"></script>
    <script src="public/js/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Configurar ruta base para todos los fetch
        window.BASE_URL = '';
        
        // Intercepatar todas las peticiones fetch para corregir rutas
        const originalFetch = window.fetch;
        window.fetch = function(url, options) {
            // Si la URL empieza con /controllers/, quitarle la barra inicial
            if (typeof url === 'string' && url.startsWith('/controllers/')) {
                url = url.substring(1); // Quitar la barra inicial
            }
            // Si la URL empieza con /autocomplete/, quitarle la barra inicial
            if (typeof url === 'string' && url.startsWith('/autocomplete/')) {
                url = url.substring(1); // Quitar la barra inicial
            }
            return originalFetch.call(this, url, options);
        };

        // Funciones globales para el dashboard
        function refreshChart() {
            if (window.dashboardManager) {
                window.dashboardManager.initSalesChart();
            }
        }

        function refreshLowStock() {
            if (window.dashboardManager) {
                window.dashboardManager.loadLowStockProducts();
            }
        }

        // Inicializar dashboard automáticamente después de cargar todos los scripts
        setTimeout(() => {
            if (typeof initDashboard === 'function') {
                initDashboard();
            }
        }, 500);
    </script>
</body>

</html>