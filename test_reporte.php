<?php
// Página de prueba para los reportes
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Reportes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Prueba del Sistema de Reportes</h2>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Inventario General</h5>
                        <p class="card-text">Reporte completo de todos los productos en inventario</p>
                        <button onclick="generarReporte('inventario')" class="btn btn-primary">Generar PDF</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Stock Bajo</h5>
                        <p class="card-text">Productos con stock mínimo</p>
                        <button onclick="generarReporte('bajo_stock')" class="btn btn-warning">Generar PDF</button>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Movimientos</h5>
                        <p class="card-text">Movimientos de inventario por fechas</p>
                        <button onclick="abrirModalFechas()" class="btn btn-info">Generar PDF</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para fechas -->
    <div class="modal fade" id="modalFechas" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar Fechas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fechaInicio">
                    </div>
                    <div class="mb-3">
                        <label for="fechaFin" class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" id="fechaFin">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="generarReporteMovimientos()">Generar Reporte</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function generarReporte(tipo, params = {}) {
            // Mostrar loading
            Swal.fire({
                title: 'Generando Reporte...',
                text: 'Por favor espera mientras se genera el PDF',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Construir URL
            const url = new URL('/controllers/reporteController.php', window.location.origin);
            url.searchParams.set('action', tipo);
            
            // Agregar sucursal_id por defecto
            url.searchParams.set('sucursal_id', '1');
            
            // Agregar parámetros adicionales
            Object.keys(params).forEach(key => {
                url.searchParams.set(key, params[key]);
            });

            // Abrir en nueva ventana/pestaña
            window.open(url.toString(), '_blank');
            
            // Cerrar loading después de un momento
            setTimeout(() => {
                Swal.close();
            }, 1000);
        }

        function abrirModalFechas() {
            const modal = new bootstrap.Modal(document.getElementById('modalFechas'));
            modal.show();
        }

        function generarReporteMovimientos() {
            const fechaInicio = document.getElementById('fechaInicio').value;
            const fechaFin = document.getElementById('fechaFin').value;
            
            if (!fechaInicio || !fechaFin) {
                Swal.fire('Error', 'Por favor selecciona ambas fechas', 'error');
                return;
            }
            
            if (new Date(fechaInicio) > new Date(fechaFin)) {
                Swal.fire('Error', 'La fecha de inicio no puede ser posterior a la fecha fin', 'error');
                return;
            }
            
            // Cerrar modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalFechas'));
            modal.hide();
            
            // Generar reporte
            generarReporte('movimientos', { fecha_inicio: fechaInicio, fecha_fin: fechaFin });
        }
    </script>
</body>
</html>