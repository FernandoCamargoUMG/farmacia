document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const mainContent = document.getElementById('main-content');

    menuToggle.addEventListener('click', function () {
        sidebar.classList.toggle('active');
        overlay.style.display = sidebar.classList.contains('active') ? 'block' : 'none';
        mainContent.classList.toggle('shifted');
    });

    overlay.addEventListener('click', function () {
        sidebar.classList.remove('active');
        overlay.style.display = 'none';
        mainContent.classList.remove('shifted');
    });

    document.querySelectorAll('.activos-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const targetSelector = this.getAttribute('data-target');
            const submenu = document.querySelector(targetSelector);
            const icon = this.querySelector('.toggle-icon');

            if (submenu.classList.contains('show')) {
                submenu.classList.remove('show');
                if (icon) icon.style.transform = 'rotate(0deg)';
            } else {
                submenu.classList.add('show');
                if (icon) icon.style.transform = 'rotate(180deg)';
            }
        });
    });
});