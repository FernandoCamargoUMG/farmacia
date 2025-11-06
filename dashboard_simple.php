<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config/conexion.php';

// Obtener estadísticas básicas
$totalProductos = 0;
$totalVentas = 0;
$totalClientes = 0;
$productosStockBajo = [];

try {
    $conn = Conexion::conectar();
    
    // Contar productos
    $stmt = $conn->query("SELECT COUNT(*) as total FROM producto");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalProductos = $result['total'] ?? 0;
    
    // Contar ventas de hoy
    $stmt = $conn->query("SELECT COUNT(*) as total FROM egreso_cab WHERE DATE(fecha) = CURDATE()");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalVentas = $result['total'] ?? 0;
    
    // Contar clientes
    $stmt = $conn->query("SELECT COUNT(*) as total FROM clientes");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalClientes = $result['total'] ?? 0;
    
    // Productos con stock bajo (simulado)
    $stmt = $conn->query("SELECT nombre, codigo FROM producto LIMIT 3");
    $productosStockBajo = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    // Si hay error, usar valores por defecto
    $totalProductos = 0;
    $totalVentas = 0;
    $totalClientes = 0;
    $productosStockBajo = [];
}

// Incluir el header
include 'views/layout/header.php';
?>

<script>
// Llenar los datos cuando la página carga
document.addEventListener('DOMContentLoaded', function() {
    // Llenar estadísticas
    const totalProductosEl = document.getElementById('total-productos');
    const totalVentasEl = document.getElementById('total-ventas');
    const totalClientesEl = document.getElementById('total-clientes');
    const stockBajoEl = document.getElementById('stock-bajo');
    
    if (totalProductosEl) totalProductosEl.textContent = '<?php echo $totalProductos; ?>';
    if (totalVentasEl) totalVentasEl.textContent = '<?php echo $totalVentas; ?>';
    if (totalClientesEl) totalClientesEl.textContent = '<?php echo $totalClientes; ?>';
    if (stockBajoEl) stockBajoEl.textContent = '<?php echo count($productosStockBajo); ?>';
    
    // Llenar tabla de stock bajo
    const tbody = document.getElementById('low-stock-tbody');
    if (tbody) {
        let html = '';
        <?php if (!empty($productosStockBajo)): ?>
            <?php foreach ($productosStockBajo as $producto): ?>
                html += `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="product-avatar me-3">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($producto['nombre'] ?? 'Producto'); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($producto['codigo'] ?? 'Sin código'); ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-danger">5</span>
                        </td>
                    </tr>
                `;
            <?php endforeach; ?>
        <?php else: ?>
            html = `
                <tr>
                    <td colspan="2" class="text-center py-4">
                        <div class="text-success">
                            <i class="bi bi-check-circle-fill fs-2"></i>
                            <p class="mt-2 mb-0">Todos los productos tienen stock suficiente</p>
                        </div>
                    </td>
                </tr>
            `;
        <?php endif; ?>
        tbody.innerHTML = html;
    }
    
    // Inicializar gráfico simple
    const ctx = document.getElementById('salesChart');
    if (ctx && typeof Chart !== 'undefined') {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'],
                datasets: [{
                    label: 'Ventas',
                    data: [2, 5, 3, 8, 6, 4, 1],
                    borderColor: '#4fd1c7',
                    backgroundColor: 'rgba(79, 209, 199, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});

// Función para actualizar dashboard
function updateDashboard() {
    location.reload();
}

function refreshChart() {
    location.reload();
}

function refreshLowStock() {
    location.reload();
}
</script>