document.addEventListener('DOMContentLoaded', function() {
            function loadproducto() {
                const content = document.getElementById('dynamic-content');
                content.innerHTML = `
                <div class="producto-view">
                    <h2 class="mb-4"><i class="bi bi-layers"></i> Tipo de productos</h2>
                    <div class="card shadow" style="max-width: 1000px; margin: 0 auto;">
                        <div class="card-body">
                            <h5 class="card-title">Listado</h5>
                            <button id="btnMostrarproducto" class="btn btn-outline-primary mb-3">
                                <i class="bi bi-eye"></i> Mostrar productos
                            </button>
                            <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevoproducto">
                                <i class="bi bi-plus-circle"></i> Nuevo Registro
                            </button>

                            <table id="tablaproducto" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Descripcion</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyproducto"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalNuevoproducto" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content bg-white text-dark">
                            <form id="formNuevoproducto">
                                <input type="hidden" name="id" id="productoId">
                                <div class="modal-header">
                                    <h5 class="modal-title">Nuevo producto</h5>
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
                document.querySelector('[data-bs-target="#modalNuevoproducto"]').addEventListener('click', function() {
                    const form = document.getElementById('formNuevoproducto');
                    form.reset();
                    form.productoId.value = '';
                    //cargarSucursales();
                });


                // Mostrar listado de productos
                function mostrarproductos() {
                    fetch('/controllers/productocatController.php?action=listar')
                        .then(res => res.json())
                        .then(data => {
                            if ($.fn.DataTable.isDataTable('#tablaproducto')) {
                                $('#tablaproducto').DataTable().clear().destroy();
                            }
                            const tbody = document.getElementById('tbodyproducto');
                            tbody.innerHTML = data.map(producto => `
                        <tr>
                            <td>${producto.descripcion}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btnEditar" data-id="${producto.id}">✏️</button>
                                <button class="btn btn-sm btn-danger btnEliminar" data-id="${producto.id}">🗑️</button>
                            </td>
                        </tr>
                    `).join('');

                            $('#tablaproducto').DataTable({
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
                                    fetch(`/controllers/productocatController.php?action=ver&id=${id}`)
                                        .then(res => res.json())
                                        .then(producto => {
                                            const form = document.getElementById('formNuevoproducto');
                                            form.productoId.value = producto.id;
                                            form.descripcion.value = producto.descripcion;
                                            setTimeout(() => {}, 300);
                                            new bootstrap.Modal(document.getElementById('modalNuevoproducto')).show();
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
                                            fetch('/controllers/productocatController.php?action=eliminar', {
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
                                                        mostrarproductos();
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

                document.getElementById('btnMostrarproducto').addEventListener('click', mostrarproductos);

                // Guardar datos
                const form = document.getElementById('formNuevoproducto');
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(form);
                    const id = formData.get("id");
                    const action = id ? 'actualizar' : 'guardar';

                    fetch(`/controllers/productocatController.php?action=${action}`, {
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
                                        form.productoId.value = '';
                                        cargarCategorias();
                                    } else {
                                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoproducto'));
                                        modal.hide();
                                        setTimeout(() => {
                                            document.body.classList.remove('modal-open');
                                            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                        }, 300);
                                        mostrarproductos();
                                    }
                                });
                            } else {
                                Swal.fire('Error', 'Ocurrió un error al guardar.', 'error');
                            }
                        });
                });
            }

            const productoLink = document.querySelector('a[href="#productocat"]');
            if (productoLink) {
                productoLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadproducto();
                });
            }
        });