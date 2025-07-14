document.addEventListener('DOMContentLoaded', function () {
    function loadStock() {
        const content = document.getElementById('dynamic-content');
        content.innerHTML = `
        <div class="stock-view">
            <h2><i class="bi bi-box-seam"></i> Movimientos de Inventario</h2>
            <div class="card shadow p-3">
                <div class="mb-3 d-flex justify-content-between">
                    <div class="d-flex gap-2">
                        <select id="filtroSucursal" class="form-select">
                            <option value="">-- Todas las Sucursales --</option>
                        </select>
                        <select id="filtroBodega" class="form-select">
                            <option value="">-- Todas las Bodegas --</option>
                        </select>
                    </div>
                    <button id="btnMostrarStock" class="btn btn-primary">
                        <i class="bi bi-eye"></i> Mostrar Movimientos
                    </button>
                </div>
                <table id="tablaStock" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Sucursal</th>
                            <th>Bodega</th>
                            <th>Movimiento</th>
                            <th>Cantidad</th>
                            <th>Fecha</th>
                            <th>Origen</th>
                            <th>Stock Actual</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyStock"></tbody>
                </table>
            </div>
        </div>`;

        const btnMostrar = document.getElementById('btnMostrarStock');
        const tbody = document.getElementById('tbodyStock');

        btnMostrar.addEventListener('click', () => {
            const sucursalId = document.getElementById('filtroSucursal').value;
            const bodegaId = document.getElementById('filtroBodega').value;

            const url = new URL('/controllers/inventarioController.php', window.location.origin);
            url.searchParams.set('action', 'listar');
            if (sucursalId) url.searchParams.set('sucursal_id', sucursalId);
            if (bodegaId) url.searchParams.set('bodega_id', bodegaId);

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = data.map(row => `
                        <tr>
                            <td>${row.producto}</td>
                            <td>${row.sucursal}</td>
                            <td>${row.bodega}</td>
                            <td>${row.movimiento}</td>
                            <td>${row.cantidad}</td>
                            <td>${row.fecha}</td>
                            <td>${row.origen}</td>
                            <td>${row.stock_actual}</td>
                        </tr>
                    `).join('');
                })
                .catch(error => {
                    Swal.fire('Error', 'No se pudieron cargar los datos', 'error');
                });
        });

        function cargarFiltros() {
            fetch('/controllers/inventarioController.php?action=filtros')
                .then(res => res.json())
                .then(data => {
                    const sucursalSelect = document.getElementById('filtroSucursal');
                    const bodegaSelect = document.getElementById('filtroBodega');

                    data.sucursales.forEach(s => {
                        sucursalSelect.innerHTML += `<option value="${s.id}">${s.nombre_sucursal}</option>`;
                    });

                    data.bodegas.forEach(b => {
                        bodegaSelect.innerHTML += `<option value="${b.id}">${b.nombre}</option>`;
                    });
                });
        }

        cargarFiltros();
    }

    const link = document.querySelector('a[href="#stock"]');
    if (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            loadStock();
        });
    }
});