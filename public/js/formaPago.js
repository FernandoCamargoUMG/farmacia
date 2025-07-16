document.addEventListener('DOMContentLoaded', function() {
            function loadformapago() {
                const content = document.getElementById('dynamic-content');
                content.innerHTML = `
                <div class="formapago-view">
                    <h2 class="mb-4"><i class="bi bi-layers"></i> Formas de pago</h2>
                    <div class="card shadow" style="max-width: 1000px; margin: 0 auto;">
                        <div class="card-body">
                            <h5 class="card-title">Listado</h5>
                            <button id="btnMostrarformapago" class="btn btn-outline-primary mb-3">
                                <i class="bi bi-eye"></i> Mostrar Registros
                            </button>
                            <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevoformapago">
                                <i class="bi bi-plus-circle"></i> Nuevo Registro
                            </button>

                            <table id="tablaformapago" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Descripcion</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyformapago"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalNuevoformapago" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content bg-white text-dark">
                            <form id="formNuevoformapago">
                                <input type="hidden" name="id" id="formapagoId">
                                <div class="modal-header">
                                    <h5 class="modal-title">Nuevo formapago</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                    <div class="mb-3"><label>Descripcion</label><input type="text" name="descripcion" class="form-control" required></div
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>`;

                // Abrir modal
                document.querySelector('[data-bs-target="#modalNuevoformapago"]').addEventListener('click', function() {
                    const form = document.getElementById('formNuevoformapago');
                    form.reset();
                    form.formapagoId.value = '';
                    //cargarSucursales();
                });


                // Mostrar listado de formapago
                function mostrarformapago() {
                    fetch('/controllers/formapagoController.php?action=listar')
                        .then(res => res.json())
                        .then(data => {
                            if ($.fn.DataTable.isDataTable('#tablaformapago')) {
                                $('#tablaformapago').DataTable().clear().destroy();
                            }
                            const tbody = document.getElementById('tbodyformapago');
                            tbody.innerHTML = data.map(formapago => `
                        <tr>
                            <td>${formapago.descripcion}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btnEditar" data-id="${formapago.id}">✏️</button>
                                <button class="btn btn-sm btn-danger btnEliminar" data-id="${formapago.id}">🗑️</button>
                            </td>
                        </tr>
                    `).join('');

                            $('#tablaformapago').DataTable({
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
                                    fetch(`/controllers/formapagoController.php?action=ver&id=${id}`)
                                        .then(res => res.json())
                                        .then(formapago => {
                                            const form = document.getElementById('formNuevoformapago');
                                            form.formapagoId.value = formapago.id;
                                            form.descripcion.value = formapago.descripcion;
                                            setTimeout(() => {}, 300);
                                            new bootstrap.Modal(document.getElementById('modalNuevoformapago')).show();
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
                                            fetch('/controllers/formapagoController.php?action=eliminar', {
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
                                                        mostrarformapago();
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

                document.getElementById('btnMostrarformapago').addEventListener('click', mostrarformapago);

                // Guardar datos
                const form = document.getElementById('formNuevoformapago');
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(form);
                    const id = formData.get("id");
                    const action = id ? 'actualizar' : 'guardar';

                    fetch(`/controllers/formapagoController.php?action=${action}`, {
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
                                        form.formapagoId.value = '';
                                        cargarCategorias();
                                    } else {
                                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoformapago'));
                                        modal.hide();
                                        setTimeout(() => {
                                            document.body.classList.remove('modal-open');
                                            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                        }, 300);
                                        mostrarformapago();
                                    }
                                });
                            } else {
                                Swal.fire('Error', 'Ocurrió un error al guardar.', 'error');
                            }
                        });
                });
            }

            const formapagoLink = document.querySelector('a[href="#formapago"]');
            if (formapagoLink) {
                formapagoLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadformapago();
                });
            }
        });