document.addEventListener('DOMContentLoaded', function() {
            function loadactivo() {
                const content = document.getElementById('dynamic-content');
                content.innerHTML = `
        <div class="activo-view">
            <h2 class="mb-4"><i class="bi bi-layers"></i> Tipo de activos</h2>
            <div class="card shadow" style="max-width: 1000px; margin: 0 auto;">
                <div class="card-body">
                    <h5 class="card-title">Listado</h5>
                    <button id="btnMostraractivo" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-eye"></i> Mostrar activos
                    </button>
                    <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevoactivo">
                        <i class="bi bi-plus-circle"></i> Nuevo Registro
                    </button>

                    <table id="tablaactivo" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Categoria</th>
                                <th>% de depreciación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyactivo"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalNuevoactivo" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-white text-dark">
                    <form id="formNuevoactivo">
                        <input type="hidden" name="id" id="activoId">
                        <div class="modal-header">
                            <h5 class="modal-title">Nuevo activo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Categoría</label>
                                <select name="categoria_depreciacion" id="categoria_depreciacion" class="form-control" required>
                                        <option value="">Seleccione una categoría</option>
                                        <option value="Edificios">Edificios</option>
                                        <option value="Mobiliario y Equipo">Mobiliario y Equipo</option>
                                        <option value="Vehiculos">Vehiculos</option>
                                        <option value="Tecnologia">Tecnologia</option>
                                </select>
                            </div>
                            <div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                            <div class="mb-3"><label>% de depreciacion</label><input type="text" name="porcentaje_depreciacion" class="form-control"></div>
                            <!--<div class="mb-3"><label>precio</label><input type="text" name="porcentaje_depreciacion" class="form-control"></div>-->
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;

                // Abrir modal
                document.querySelector('[data-bs-target="#modalNuevoactivo"]').addEventListener('click', function() {
                    const form = document.getElementById('formNuevoactivo');
                    form.reset();
                    form.activoId.value = '';
                    //cargarSucursales();
                });


                // Mostrar listado de activos
                function mostraractivos() {
                    fetch('/controllers/activocatController.php?action=listar')
                        .then(res => res.json())
                        .then(data => {
                            if ($.fn.DataTable.isDataTable('#tablaactivo')) {
                                $('#tablaactivo').DataTable().clear().destroy();
                            }
                            const tbody = document.getElementById('tbodyactivo');
                            tbody.innerHTML = data.map(activo => `
                        <tr>
                            <td>${activo.nombre}</td>
                            <td>${activo.categoria_depreciacion || ''}</td>
                            <td>${activo.porcentaje_depreciacion || ''}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btnEditar" data-id="${activo.id}">✏️</button>
                                <button class="btn btn-sm btn-danger btnEliminar" data-id="${activo.id}">🗑️</button>
                            </td>
                        </tr>
                    `).join('');

                            $('#tablaactivo').DataTable({
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
                                    fetch(`/controllers/activocatController.php?action=ver&id=${id}`)
                                        .then(res => res.json())
                                        .then(activo => {
                                            const form = document.getElementById('formNuevoactivo');
                                            form.activoId.value = activo.id;
                                            form.nombre.value = activo.nombre;
                                            form.categoria_depreciacion.value = activo.categoria_depreciacion;
                                            form.porcentaje_depreciacion.value = activo.porcentaje_depreciacion;
                                            setTimeout(() => {}, 300);
                                            new bootstrap.Modal(document.getElementById('modalNuevoactivo')).show();
                                        });
                                });
                            });
                            //para eliminar
                            document.querySelectorAll('.btnEliminar').forEach(btn => {
                                btn.addEventListener('click', function() {
                                    const id = this.dataset.id;
                                    Swal.fire({
                                        title: '¿Eliminar registro?',
                                        text: 'Esta acción no se puede deshacer.',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Sí, eliminar',
                                        cancelButtonText: 'Cancelar'
                                    }).then(result => {
                                        if (result.isConfirmed) {
                                            fetch('/controllers/activocatController.php?action=eliminar', {
                                                    method: 'POST',
                                                    headers: {
                                                        'Content-Type': 'application/x-www-form-urlencoded'
                                                    },
                                                    body: `id=${id}`
                                                })
                                                .then(res => res.json())
                                                .then(data => {
                                                    if (data.success) {
                                                        Swal.fire('Eliminado', 'Registro eliminado correctamente.', 'success');
                                                        mostraractivos();
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

                document.getElementById('btnMostraractivo').addEventListener('click', mostraractivos);

                // Guardar datos
                const form = document.getElementById('formNuevoactivo');
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(form);
                    const id = formData.get("id");
                    const action = id ? 'actualizar' : 'guardar';

                    fetch(`/controllers/activocatController.php?action=${action}`, {
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: '¡Registro guardado!',
                                    text: '¿Qué deseas hacer ahora?',
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonText: 'Agregar otro',
                                    cancelButtonText: 'Ver listado'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        form.reset();
                                        form.activoId.value = '';
                                        cargarCategorias();
                                    } else {
                                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoactivo'));
                                        modal.hide();
                                        setTimeout(() => {
                                            document.body.classList.remove('modal-open');
                                            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                        }, 300);
                                        mostraractivos();
                                    }
                                });
                            } else {
                                Swal.fire('Error', 'Ocurrió un error al guardar.', 'error');
                            }
                        });
                });
            }

            const activoLink = document.querySelector('a[href="#activoc"]');
            if (activoLink) {
                activoLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadactivo();
                });
            }
        });