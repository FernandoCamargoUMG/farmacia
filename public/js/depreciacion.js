document.addEventListener('DOMContentLoaded', function() {
            function loadDep() {
                const content = document.getElementById('dynamic-content');
                content.innerHTML = `
                <div class="depreciacion-view">
                    <h2><i class="bi bi-calculator"></i> Cálculo de Depreciación Anual</h2>
                    <div class="card shadow p-3">
                        <div class="d-flex justify-content-between mb-3">
                            <div>
                                <button id="btnCalcularDepreciacion" class="btn btn-primary me-2">
                                    <i class="bi bi-cpu"></i> Calcular Depreciación
                                </button>
                                <button id="btnMostrarDepreciacion" class="btn btn-secondary">
                                    <i class="bi bi-eye"></i> Mostrar Resultados
                                </button>
                            </div>
                            <button id="btnGenerarReporte" class="btn btn-success">
                                <i class="bi bi-file-earmark-excel"></i> Generar Reporte
                            </button>
                        </div>
                        <table id="tablaDepreciacion" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Activo</th>
                                    <th>Estado</th>
                                    <th>Costo</th>
                                    <th>Valor Residual</th>
                                    <!--<th>% Depreciación</th>-->
                                    <th>Dep. Anual</th>
                                    <!--<th>Dep. Registrada</th>-->
                                    <!--<th>Valor Esperado</th>-->
                                    <th>Valor Actual</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDepreciacion"></tbody>
                        </table>
                    </div>
                </div>`;

                const btnCalcular = document.getElementById('btnCalcularDepreciacion');
                const btnMostrar = document.getElementById('btnMostrarDepreciacion');
                const btnReporte = document.getElementById('btnGenerarReporte');
                const tbody = document.getElementById('tbodyDepreciacion');

                function cargarDepreciacion() {
                    fetch('/controllers/depreciacionController.php?action=listar')
                        .then(res => res.json())
                        .then(data => {
                            tbody.innerHTML = data.map(row => `
                        <tr>
                            <td>${row.nombre}</td>
                            <td>${row.estado}</td>
                            <td>${row.costo}</td>
                            <td>${row.valor_residual}</td>
                            <!--<td>${row.porcentaje_depreciacion}%</td>-->
                            <td>${row.dep_anual}</td>
                            <!--<td>${row.dep_real || 0}</td>-->
                            <!--<td>${row.valor_esperado}</td>-->
                            <td>${row.valor_actual || 0}</td>
                        </tr>
                    `).join('');
                        });
                }

                btnCalcular.addEventListener('click', () => {
                    fetch('/controllers/depreciacionController.php?action=calcular', {
                            method: 'POST'
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Éxito', 'Cálculo de depreciación completado', 'success');
                            } else {
                                Swal.fire('Error', data.message || 'No se pudo calcular', 'error');
                            }
                        });
                });

                btnMostrar.addEventListener('click', cargarDepreciacion);

                btnReporte.addEventListener('click', () => {
                    window.open('/reportes/reporte_depreciacion_excel.php', '_blank');
                });
            }

            const bodegaLink = document.querySelector('a[href="#depreciacion"]');
            if (bodegaLink) {
                bodegaLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadDep();
                });
            }
        });