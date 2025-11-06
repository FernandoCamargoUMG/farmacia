document.addEventListener('DOMContentLoaded', function () {
    function loadMovimientosCaja() {
        const content = document.getElementById('dynamic-content');
        content.innerHTML = `
        <div class="movimientos-caja-view">
            <h2><i class="bi bi-cash-stack"></i> Movimientos de Caja</h2>
            <div class="card shadow p-3">
                <div class="mb-3 d-flex justify-content-between">
                    <div class="d-flex gap-2">
                        <select id="filtroSucursal" class="form-select">
                            <option value="">-- Todas las Sucursales --</option>
                        </select>
                    </div>
                    <button id="btnMostrarMovimientos" class="btn btn-primary">
                        <i class="bi bi-eye"></i> Mostrar Movimientos
                    </button>
                </div>
                <table id="tablaMovimientos" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo de Movimiento</th>
                            <th>Descripción</th>
                            <th>Monto</th>
                            <th>Método de Pago</th>
                            <th>Saldo Acumulado</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyMovimientos"></tbody>
                </table>
            </div>
        </div>`;

            const btnMostrar = document.getElementById('btnMostrarMovimientos');
            const tbody = document.getElementById('tbodyMovimientos');

            btnMostrar.addEventListener('click', () => {
            const sucursalId = document.getElementById('filtroSucursal').value;

            let url = 'controllers/cajaController.php?action=listar';
            if (sucursalId) url += '&sucursal_id=' + sucursalId;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    tbody.innerHTML = data.map(row => `
                        <tr>
                            <td>${row.fecha}</td>
                            <td>${row.tipo_movimiento}</td>
                            <td>${row.descripcion}</td>
                            <td>Q${parseFloat(row.monto).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td>${row.metodo_pago}</td>
                            <td>Q${parseFloat(row.saldo_acumulado).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        </tr>
                    `).join('');
                })
                .catch(error => {
                    console.error(error);
                    Swal.fire('Error', 'No se pudieron cargar los movimientos de caja', 'error');
                });
        });

        function cargarFiltros() {
            fetch('controllers/cajaController.php?action=filtros')
                .then(res => res.json())
                .then(data => {
                    const sucursalSelect = document.getElementById('filtroSucursal');
                    data.sucursales.forEach(s => {
                        sucursalSelect.innerHTML += `<option value="${s.id}">${s.nombre_sucursal}</option>`;
                    });
                });
        }

        cargarFiltros();
    }

    const link = document.querySelector('a[href="#caja"]');
    if (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            loadMovimientosCaja();
        });
    }
});
