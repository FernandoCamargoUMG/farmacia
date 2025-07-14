document.addEventListener('DOMContentLoaded', function() {

        function loadPlanilla() {
            const content = document.getElementById('dynamic-content');
            content.innerHTML = `
        <div class="planilla-view">
            <h2 class="mb-4"><i class="bi bi-file-earmark-text"></i> Módulo de planilla</h2>
            <div class="card shadow" style="max-width: 1000px; margin: 0 auto;">
                <div class="card-body">
                    <h5 class="card-title">Listado de planilla</h5>

                    <button id="btnMostrarplanilla" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-eye"></i> Mostrar Registros
                    </button>
                    <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevoplanilla">
                        <i class="bi bi-plus-circle"></i> Nuevo Registro
                    </button>

                    <table id="tablaplanilla" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Descripción</th>
                                <th>Monto</th>
                                <th>Forma de pago</th>
                                <th>Observaciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyplanilla"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalNuevoplanilla" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-white text-dark">
                    <form id="formNuevoplanilla">
                        <input type="hidden" name="id" id="planillaId">
                        <div class="modal-header">
                            <h5 class="modal-title">Nuevo planilla</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Categoría</label>
                                <select name="metodopago" id="metodopago" class="form-control" required>
                                        <option value="1">Efectivo</option>
                                        <option value="2">Cheque</option>
                                        <option value="3">Depósito</option>
                                        <option value="4">Tarjeta de Crédito</option>
                                        <option value="5">Tarjeta de Débito</option>
                                        <option value="6">Transferencia Bancaria</option>
                                </select>
                            </div>
                            <div class="mb-3"><label>Fecha</label><input type="date" name="fecha" class="form-control" required>
                            <div class="mb-3"><label>Descripción</label><input type="text" name="descripcion" id="descripcion" class="form-control"></div>
                            <div class="mb-3"><label>Monto</label><input type="text" name="monto" id="monto" class="form-control"></div>
                            <div class="mb-3"><label>Observaciones</label><input type="text" name="observaciones" id="observaciones" class="form-control"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;

            /*Función para cargar categorías
            function cargarCategorias() {
                fetch('/controllers/categoriaplanillaController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        const select = document.getElementById('selectCategoria');
                        select.innerHTML = '<option value="">Seleccione una categoría</option>';
                        data.forEach(cat => {
                            const option = document.createElement('option');
                            option.value = cat.id;
                            option.textContent = cat.descripcion;
                            select.appendChild(option);
                        });
                    });
            }*/

            // Abrir modal
            document.querySelector('[data-bs-target="#modalNuevoplanilla"]').addEventListener('click', function() {
                const form = document.getElementById('formNuevoplanilla');
                form.reset();
                form.planillaId.value = '';
                //cargarCategorias();
            });

            // Mostrar listado de planilla
            function mostrarplanilla() {
                fetch('/controllers/planillaController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        if ($.fn.DataTable.isDataTable('#tablaplanilla')) {
                            $('#tablaplanilla').DataTable().clear().destroy();
                        }

                        const tbody = document.getElementById('tbodyplanilla');
                        tbody.innerHTML = data.map(planilla => `
                        <tr>
                            <td>${planilla.fecha}</td>
                            <td>${planilla.descripcion || ''}</td>
                            <td>${planilla.monto || ''}</td>
                            <td>${planilla.metodopago || ''}</td>
                            <td>${planilla.observaciones || ''}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btnEditar" data-id="${planilla.id}">✏️</button>
                                <button class="btn btn-sm btn-danger btnEliminar" data-id="${planilla.id}">🗑️</button>
                            </td>
                        </tr>
                    `).join('');

                        $('#tablaplanilla').DataTable({
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                            },
                            pageLength: 5,
                            lengthMenu: [5, 10, 25, 50, 100]
                        });
                        // para editar
                        document.querySelectorAll('.btnEditar').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const id = this.dataset.id;
                                fetch(`/controllers/planillaController.php?action=ver&id=${id}`)
                                    .then(res => res.json())
                                    .then(planilla => {
                                        console.log(planilla);
                                        const form = document.getElementById('formNuevoplanilla');
                                        form.planillaId.value = planilla.id;
                                        form.fecha.value = planilla.fecha.split(' ')[0];
                                        form.descripcion.value = planilla.descripcion;
                                        form.monto.value = planilla.monto;
                                        form.observaciones.value = planilla.observaciones;
                                        form.metodopago.value = planilla.metodopago;
                                        //form.telefono.value = planilla.telefono;
                                        //form.email.value = planilla.email;
                                        //cargarCategorias();
                                        setTimeout(() => {
                                            //form.metodopago.value = planilla.metodopago;
                                        }, 300);
                                        new bootstrap.Modal(document.getElementById('modalNuevoplanilla')).show();
                                    });
                            });
                        });
                        //para eliminar
                        document.querySelectorAll('.btnEliminar').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const id = this.dataset.id;
                                Swal.fire({
                                    title: '¿Eliminar planilla?',
                                    text: 'Esta acción no se puede deshacer.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Sí, eliminar',
                                    cancelButtonText: 'Cancelar'
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        fetch('/controllers/planillaController.php?action=eliminar', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/x-www-form-urlencoded'
                                                },
                                                body: `id=${id}`
                                            })
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.success) {
                                                    Swal.fire('Eliminado', 'planilla eliminado correctamente.', 'success');
                                                    mostrarplanilla();
                                                } else {
                                                    Swal.fire('Error', 'No se pudo eliminar.', 'error');
                                                }
                                            });
                                    }
                                });
                            });
                        });
                    });
            }

            document.getElementById('btnMostrarplanilla').addEventListener('click', mostrarplanilla);

            // Guardar planilla
            const form = document.getElementById('formNuevoplanilla');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const id = formData.get("id");
                const action = id ? 'actualizar' : 'guardar';

                fetch(`/controllers/planillaController.php?action=${action}`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '¡planilla guardado!',
                                text: '¿Qué deseas hacer ahora?',
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Agregar otro',
                                cancelButtonText: 'Ver listado'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    form.reset();
                                    form.planillaId.value = '';
                                    cargarCategorias();
                                } else {
                                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoplanilla'));
                                    modal.hide();
                                    setTimeout(() => {
                                        document.body.classList.remove('modal-open');
                                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                    }, 300);
                                    mostrarplanilla();
                                }
                            });
                        } else {
                            Swal.fire('Error', 'Ocurrió un error al guardar.', 'error');
                        }
                    });
            });
        }


        const planillaLink = document.querySelector('a[href="#planilla"]');
        if (planillaLink) {
            planillaLink.addEventListener('click', function(e) {
                e.preventDefault();
                loadPlanilla();
            });
        }
    });