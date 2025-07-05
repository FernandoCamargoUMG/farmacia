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
});