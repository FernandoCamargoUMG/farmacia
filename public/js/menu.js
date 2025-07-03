document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const mainContent = document.getElementById('main-content');

    // Abrir/cerrar menú
    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
        overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
        mainContent.classList.toggle('shifted');
    });

    // Cerrar al hacer clic fuera
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.style.display = 'none';
        mainContent.classList.remove('shifted');
    });

    // Función para cargar el módulo de clientes
    function loadClientes() {
        const content = document.getElementById('dynamic-content');
        content.innerHTML = `
            <div class="clientes-view">
                <h2 class="mb-4"><i class="bi bi-people-fill"></i> Módulo de Clientes</h2>
                <div class="card shadow" style="max-width: 800px; margin: 0 auto;">
                    <div class="card-body">
                        <h5 class="card-title">Listado de Clientes</h5>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Teléfono</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>Fernando Camargo</td>
                                    <td>555-1234</td>
                                </tr>
                                <!-- Más filas de datos... -->
                            </tbody>
                        </table>
                        <button class="btn btn-primary mt-3">
                            <i class="bi bi-plus-circle"></i> Nuevo Cliente
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    // Asignar la función al enlace de Clientes
    document.querySelector('a[href="#clientes"]').addEventListener('click', function(e) {
        e.preventDefault();
        loadClientes();
        // Cerrar el menú si es móvil
        sidebar.classList.remove('active');
        overlay.style.display = 'none';
        mainContent.classList.remove('shifted');
    });
});