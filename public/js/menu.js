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

            if (submenu) {
                if (submenu.classList.contains('show')) {
                    submenu.classList.remove('show');
                    if (icon) icon.style.transform = 'rotate(0deg)';
                } else {
                    submenu.classList.add('show');
                    if (icon) icon.style.transform = 'rotate(180deg)';
                }
            }
        });
    });
});

// Función global para obtener fecha y hora local de la PC del cliente
function getFechaHoraLocal() {
    const ahora = new Date();
    const year = ahora.getFullYear();
    const month = String(ahora.getMonth() + 1).padStart(2, '0');
    const day = String(ahora.getDate()).padStart(2, '0');
    const hours = String(ahora.getHours()).padStart(2, '0');
    const minutes = String(ahora.getMinutes()).padStart(2, '0');
    const seconds = String(ahora.getSeconds()).padStart(2, '0');
    
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

// Función para agregar automáticamente la fecha local a los formularios
function agregarFechaLocal(form) {
    // Crear un input hidden con la fecha local
    const inputFechaLocal = document.createElement('input');
    inputFechaLocal.type = 'hidden';
    inputFechaLocal.name = 'fecha_local';
    inputFechaLocal.value = getFechaHoraLocal();
    
    // Agregar al formulario si no existe ya
    if (!form.querySelector('input[name="fecha_local"]')) {
        form.appendChild(inputFechaLocal);
    } else {
        // Actualizar valor si ya existe
        form.querySelector('input[name="fecha_local"]').value = getFechaHoraLocal();
    }
}

// Interceptar todos los envíos de formularios para agregar fecha local automáticamente
document.addEventListener('submit', function(e) {
    const form = e.target;
    if (form.tagName === 'FORM') {
        agregarFechaLocal(form);
    }
});

