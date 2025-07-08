document.addEventListener('DOMContentLoaded', function() {
        function loadBodega() {
            const content = document.getElementById('dynamic-content');
            content.innerHTML = `
        <div class="Bodega-view">
            <h2 class="mb-4"><i class="bi bi-box-seam"></i> Módulo de Bodegas</h2>
            <div class="card shadow" style="max-width: 1000px; margin: 0 auto;">
                <div class="card-body">
                    <h5 class="card-title">Listado de Bodegas</h5>
                    <button id="btnMostrarBodega" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-eye"></i> Mostrar Bodegas
                    </button>
                    <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevoBodega">
                        <i class="bi bi-plus-circle"></i> Nuevo Bodega
                    </button>

                    <table id="tablaBodega" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Sucursal</th>
                                <th>Nombre</th>
                                <th>Ubicación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyBodega"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalNuevoBodega" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-white text-dark">
                    <form id="formNuevoBodega">
                        <input type="hidden" name="id" id="bodegaId">
                        <div class="modal-header">
                            <h5 class="modal-title">Nuevo Bodega</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!--<div class="mb-3">
                                <label>Bodega</label>
                                <select name="sucursal_id" id="selectBodega" class="form-control" required>
                                    <option value="">Seleccione una sucursal</option>
                                </select>
                            </div>-->
                            <div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                            <div class="mb-3"><label>ubicacion</label><input type="text" name="ubicacion" class="form-control"></div>
                            <!--<div class="mb-3"><label>precio</label><input type="text" name="precio" class="form-control"></div>-->
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;

            //Función para cargar sucursales
            /*function cargarSucursales() {
                fetch('/farmacia/controllers/sucursalBodegaController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        const select = document.getElementById('selectBodega');
                        select.innerHTML = '<option value="">Seleccione una bodega</option>';
                        data.forEach(cat => {
                            const option = document.createElement('option');
                            option.value = cat.id;
                            option.textContent = cat.nombre_sucursal;
                            select.appendChild(option);
                        });
                    });
            }*/

            // Abrir modal
            document.querySelector('[data-bs-target="#modalNuevoBodega"]').addEventListener('click', function() {
                const form = document.getElementById('formNuevoBodega');
                form.reset();
                form.bodegaId.value = '';
                //cargarSucursales();
            });

            // Mostrar listado de bodegas
            function mostrarBodegas() {
                fetch('/farmacia/controllers/bodegaController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        if ($.fn.DataTable.isDataTable('#tablaBodega')) {
                            $('#tablaBodega').DataTable().clear().destroy();
                        }

                        const tbody = document.getElementById('tbodyBodega');
                        tbody.innerHTML = data.map(bodega => `
                        <tr>
                            <td>${bodega.sucursal}</td>
                            <td>${bodega.nombre || ''}</td>
                            <td>${bodega.ubicacion || ''}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btnEditar" data-id="${bodega.id}">✏️</button>
                                <button class="btn btn-sm btn-danger btnEliminar" data-id="${bodega.id}">🗑️</button>
                            </td>
                        </tr>
                    `).join('');

                        $('#tablaBodega').DataTable({
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
                                fetch(`/farmacia/controllers/bodegaController.php?action=ver&id=${id}`)
                                    .then(res => res.json())
                                    .then(bodega => {
                                        const form = document.getElementById('formNuevoBodega');
                                        form.bodegaId.value = bodega.id;
                                        form.nombre.value = bodega.nombre;
                                        form.ubicacion.value = bodega.ubicacion;
                                        //form.precio.value = bodega.precio;
                                        //cargarCategorias();
                                        setTimeout(() => {
                                            //form.sucursal_id.value = bodega.sucursal_id;
                                        }, 300);
                                        new bootstrap.Modal(document.getElementById('modalNuevoBodega')).show();
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
                                        fetch('/farmacia/controllers/bodegaController.php?action=eliminar', {
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
                                                    mostrarBodegas();
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

            document.getElementById('btnMostrarBodega').addEventListener('click', mostrarBodegas);

            // Guardar datos
            const form = document.getElementById('formNuevoBodega');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const id = formData.get("id");
                const action = id ? 'actualizar' : 'guardar';

                fetch(`/farmacia/controllers/bodegaController.php?action=${action}`, {
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
                                    form.bodegaId.value = '';
                                    cargarCategorias();
                                } else {
                                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoBodega'));
                                    modal.hide();
                                    setTimeout(() => {
                                        document.body.classList.remove('modal-open');
                                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                    }, 300);
                                    mostrarBodegas();
                                }
                            });
                        } else {
                            Swal.fire('Error', 'Ocurrió un error al guardar.', 'error');
                        }
                    });
            });
        }

        const bodegaLink = document.querySelector('a[href="#bodega"]');
        if (bodegaLink) {
            bodegaLink.addEventListener('click', function(e) {
                e.preventDefault();
                loadBodega();
            });
        }
    });