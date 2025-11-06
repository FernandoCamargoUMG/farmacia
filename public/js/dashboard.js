// Dashboard Manager - Sistema ERP
class DashboardManager {
    constructor() {
        this.salesChart = null;
        this.init();
    }

    init() {
        this.loadDashboardData();
        this.initSalesChart();
    }

    async loadDashboardData() {
        try {
            // Cargar estadísticas principales
            await Promise.all([
                this.loadProductStats(),
                this.loadSalesStats(),
                this.loadClientStats(),
                this.loadLowStockProducts(),
                this.loadRecentActivity()
            ]);
        } catch (error) {
            console.error('Error cargando datos del dashboard:', error);
        }
    }

    async loadProductStats() {
        try {
            const response = await fetch('dashboard_api.php?action=productos_count', {
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            const element = document.getElementById('total-productos');
            
            if (element) {
                element.textContent = data.total || 0;
                // Animación de conteo
                this.animateNumber('total-productos', 0, data.total || 0, 1000);
            }
        } catch (error) {
            console.error('Error cargando productos:', error);
            const element = document.getElementById('total-productos');
            if (element) element.textContent = '0';
        }
    }

    async loadSalesStats() {
        try {
            const response = await fetch('dashboard_api.php?action=ventas_hoy', {
                credentials: 'same-origin'
            });
            const data = await response.json();
            const total = data.total || 0;
            document.getElementById('total-ventas').textContent = total;
            
            // Animación de conteo
            this.animateNumber('total-ventas', 0, total, 1000);
        } catch (error) {
            document.getElementById('total-ventas').textContent = '0';
        }
    }

    async loadClientStats() {
        try {
            const response = await fetch('dashboard_api.php?action=clientes_count', {
                credentials: 'same-origin'
            });
            const data = await response.json();
            const total = data.total || 0;
            document.getElementById('total-clientes').textContent = total;
            
            // Animación de conteo
            this.animateNumber('total-clientes', 0, total, 1000);
        } catch (error) {
            document.getElementById('total-clientes').textContent = '0';
        }
    }

    async loadLowStockProducts() {
        try {
            const response = await fetch('dashboard_api.php?action=stock_bajo', {
                credentials: 'same-origin'
            });
            const data = await response.json();
            
            const count = data.length || 0;
            document.getElementById('stock-bajo').textContent = count;
            
            // Animación de conteo
            this.animateNumber('stock-bajo', 0, count, 1000);
            
            const tbody = document.getElementById('low-stock-tbody');
            if (data.length > 0) {
                tbody.innerHTML = data.slice(0, 10).map(product => `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="product-avatar me-3">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div>
                                    <strong>${product.nombre}</strong>
                                    <br><small class="text-muted">${product.codigo || 'Sin código'}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge ${product.stock_actual <= 5 ? 'badge-danger' : 'badge-warning'}">
                                ${product.stock_actual}
                            </span>
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="2" class="text-center py-4">
                            <div class="text-success">
                                <i class="bi bi-check-circle-fill fs-2"></i>
                                <p class="mt-2 mb-0">Todos los productos tienen stock suficiente</p>
                            </div>
                        </td>
                    </tr>
                `;
            }
        } catch (error) {
            document.getElementById('stock-bajo').textContent = '0';
            document.getElementById('low-stock-tbody').innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger py-4">
                        <i class="bi bi-exclamation-triangle"></i> Error cargando datos
                    </td>
                </tr>
            `;
        }
    }

    async loadRecentActivity() {
        try {
            const response = await fetch('dashboard_api.php?action=actividad_reciente', {
                credentials: 'same-origin'
            });
            const data = await response.json();
            
            const activityContainer = document.getElementById('recent-activity');
            if (data.length > 0) {
                activityContainer.innerHTML = data.map(activity => `
                    <div class="activity-item">
                        <div class="activity-icon ${this.getActivityIconClass(activity.type)}">
                            <i class="bi bi-${this.getActivityIcon(activity.type)}"></i>
                        </div>
                        <div class="activity-content">
                            <p>${activity.description}</p>
                            <small>${activity.time_ago}</small>
                        </div>
                    </div>
                `).join('');
            } else {
                activityContainer.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-clock-history fs-2 text-muted"></i>
                        <p class="text-muted mt-2">No hay actividad reciente</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error cargando actividad reciente:', error);
            document.getElementById('recent-activity').innerHTML = `
                <div class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    <p class="mt-2">Error cargando actividad</p>
                </div>
            `;
        }
    }

    async initSalesChart() {
        try {
            const response = await fetch('dashboard_api.php?action=ventas_semanales', {
                credentials: 'same-origin'
            });
            const data = await response.json();
            
            const ctx = document.getElementById('salesChart').getContext('2d');
            
            if (this.salesChart) {
                this.salesChart.destroy();
            }
            
            this.salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels || ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'],
                    datasets: [{
                        label: 'Ventas',
                        data: data.values || [0, 0, 0, 0, 0, 0, 0],
                        borderColor: '#4fd1c7',
                        backgroundColor: 'rgba(79, 209, 199, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#4fd1c7',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: '#0d9488',
                        pointHoverBorderColor: '#ffffff',
                        pointHoverBorderWidth: 3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(79, 209, 199, 0.95)',
                            titleColor: 'white',
                            bodyColor: 'white',
                            borderColor: '#4fd1c7',
                            borderWidth: 2,
                            cornerRadius: 10,
                            displayColors: false,
                            titleFont: {
                                size: 14,
                                weight: 'bold'
                            },
                            bodyFont: {
                                size: 13
                            },
                            padding: 12
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.1)'
                            },
                            ticks: {
                                color: '#666',
                                callback: function(value) {
                                    return value + ' ventas';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#666'
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeInOutQuart'
                    }
                }
            });
        } catch (error) {
            console.error('Error inicializando gráfico:', error);
            document.getElementById('salesChart').getContext('2d').fillText('Error cargando gráfico', 200, 100);
        }
    }

    // Función para animar números
    animateNumber(elementId, start, end, duration) {
        const element = document.getElementById(elementId);
        if (!element) return;

        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const current = Math.floor(progress * (end - start) + start);
            element.textContent = current;
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }

    // Helpers para actividades
    getActivityIconClass(type) {
        const classes = {
            'sale': 'bg-success',
            'product': 'bg-info',
            'warning': 'bg-warning',
            'info': 'bg-primary',
            'success': 'bg-success'
        };
        return classes[type] || 'bg-secondary';
    }

    getActivityIcon(type) {
        const icons = {
            'sale': 'currency-dollar',
            'product': 'box',
            'warning': 'exclamation-triangle',
            'info': 'info-circle',
            'success': 'check-circle'
        };
        return icons[type] || 'dot';
    }

    // Función para actualizar manualmente el dashboard
    refreshDashboard() {
        this.loadDashboardData();
        this.initSalesChart();
        this.showSimpleNotification('Dashboard actualizado');
    }

    // Notificación simple y discreta
    showSimpleNotification(message) {
        const notification = document.createElement('div');
        notification.innerHTML = `<i class="bi bi-check-circle me-2"></i>${message}`;
        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            background: #4fd1c7;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            z-index: 1060;
            font-size: 0.9rem;
            box-shadow: 0 4px 12px rgba(79, 209, 199, 0.3);
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.style.opacity = '1';
            notification.style.transform = 'translateY(0)';
        }, 100);
        
        setTimeout(() => {
            notification.style.opacity = '0';
            notification.style.transform = 'translateY(-20px)';
            setTimeout(() => document.body.removeChild(notification), 300);
        }, 2000);
    }

    refresh() {
        this.refreshDashboard();
        
        // Mostrar feedback visual en botones
        const refreshButtons = document.querySelectorAll('[onclick*="refresh"]');
        refreshButtons.forEach(button => {
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>';
            button.disabled = true;
            
            setTimeout(() => {
                button.innerHTML = originalContent;
                button.disabled = false;
            }, 1000);
        });
    }
}

// Funciones globales
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

// Función global para inicializar el dashboard
function initDashboard() {
    if (!window.dashboardManager) {
        window.dashboardManager = new DashboardManager();
    } else {
        window.dashboardManager.loadDashboardData();
    }
}

// Función global para refrescar el dashboard desde otros módulos
function updateDashboard() {
    if (window.dashboardManager) {
        window.dashboardManager.refreshDashboard();
    }
}

// Función global para actualizar solo las estadísticas (más rápido)
function updateDashboardStats() {
    if (window.dashboardManager) {
        window.dashboardManager.loadProductStats();
        window.dashboardManager.loadSalesStats();
        window.dashboardManager.loadClientStats();
        window.dashboardManager.loadLowStockProducts();
    }
}

// CSS para animación de spin
const style = document.createElement('style');
style.textContent = `
    .spin {
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .product-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #4fd1c7, #0d9488);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
        box-shadow: 0 4px 12px rgba(79, 209, 199, 0.3);
    }
`;
document.head.appendChild(style);

// Inicializar dashboard cuando se carga la página
document.addEventListener('DOMContentLoaded', function() {
    const dynamicContent = document.getElementById('dynamic-content');
    
    if (dynamicContent) {
        // Verificar si ya tiene la clase dashboard-container o si la contiene
        const isDashboard = dynamicContent.classList.contains('dashboard-container') || dynamicContent.querySelector('.dashboard-container');
        
        if (isDashboard) {
            window.dashboardManager = new DashboardManager();
        }
    }
});