document.addEventListener('DOMContentLoaded', function() {
        function loadProducto() {
            const content = document.getElementById('dynamic-content');
            content.innerHTML = `
        <div class="producto-view">
            <h2 class="mb-4"><i class="bi bi-capsule-pill"></i> Módulo de Productos</h2>
            <div class="card shadow" style="max-width: 1000px; margin: 0 auto;">
                <div class="card-body">
                    <h5 class="card-title">Listado de Productos</h5>
                    <button id="btnMostrarProducto" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-eye"></i> Mostrar Productos
                    </button>
                    <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevoProducto">
                        <i class="bi bi-plus-circle"></i> Nuevo Producto
                    </button>

                    <table id="tablaProducto" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Codigo</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Precio</th>
                                <th>Categoria</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyProducto"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalNuevoProducto" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-white text-dark">
                    <form id="formNuevoProducto">
                        <input type="hidden" name="id" id="productoId">
                        <div class="modal-header">
                            <h5 class="modal-title">Nuevo Producto</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Categoría</label>
                                <select name="categoria_id" id="selectCategoria" class="form-control" required>
                                    <option value="">Seleccione una categoría</option>
                                </select>
                            </div>
                            <div class="mb-3"><label>codigo</label><input type="text" name="codigo" class="form-control" required></div>
                            <div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                            <div class="mb-3"><label>descripción</label><input type="text" name="descripcion" class="form-control"></div>
                            <div class="mb-3"><label>precio</label><input type="text" name="precio" class="form-control"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;

            // Función para cargar categorías
            function cargarCategorias() {
                fetch('controllers/categoriaProductoController.php?action=listar')
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
            }

            // Abrir modal
            document.querySelector('[data-bs-target="#modalNuevoProducto"]').addEventListener('click', function() {
                const form = document.getElementById('formNuevoProducto');
                form.reset();
                form.productoId.value = '';
                cargarCategorias();
            });

            // Mostrar listado de productos
            function mostrarProductos() {
                fetch('controllers/productoController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        if ($.fn.DataTable.isDataTable('#tablaProducto')) {
                            $('#tablaProducto').DataTable().clear().destroy();
                        }

                        const tbody = document.getElementById('tbodyProducto');
                        tbody.innerHTML = data.map(producto => `
                        <tr>
                            <td>${producto.codigo}</td>
                            <td>${producto.nombre}</td>
                            <td>${producto.descripcion || ''}</td>
                            <td>${'Q'+producto.precio || ''}</td>
                            <td>${producto.categoria || ''}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btnEditar" data-id="${producto.id}">✏️</button>
                                <button class="btn btn-sm btn-danger btnEliminar" data-id="${producto.id}">🗑️</button>
                            </td>
                        </tr>
                    `).join('');

                        $('#tablaProducto').DataTable({
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
                                fetch(`/controllers/productoController.php?action=ver&id=${id}`)
                                    .then(res => res.json())
                                    .then(producto => {
                                        const form = document.getElementById('formNuevoProducto');
                                        form.productoId.value = producto.id;
                                        form.codigo.value = producto.codigo;
                                        form.nombre.value = producto.nombre;
                                        form.descripcion.value = producto.descripcion;
                                        form.precio.value = producto.precio;
                                        cargarCategorias();
                                        setTimeout(() => {
                                            form.categoria_id.value = producto.categoria_id;
                                        }, 300);
                                        new bootstrap.Modal(document.getElementById('modalNuevoProducto')).show();
                                    });
                            });
                        });
                        //para eliminar
                        document.querySelectorAll('.btnEliminar').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const id = this.dataset.id;
                                Swal.fire({
                                    title: '¿Eliminar producto?',
                                    text: 'Esta acción no se puede deshacer.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Sí, eliminar',
                                    cancelButtonText: 'Cancelar'
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        fetch('/controllers/productoController.php?action=eliminar', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/x-www-form-urlencoded'
                                                },
                                                body: `id=${id}`
                                            })
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.success) {
                                                    Swal.fire('Eliminado', 'Producto eliminado correctamente.', 'success');
                                                    mostrarProductos();
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

            document.getElementById('btnMostrarProducto').addEventListener('click', mostrarProductos);

            // Guardar datos
            const form = document.getElementById('formNuevoProducto');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const id = formData.get("id");
                const action = id ? 'actualizar' : 'guardar';

                fetch(`/controllers/productoController.php?action=${action}`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Producto guardado!',
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
                                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoProducto'));
                                    modal.hide();
                                    setTimeout(() => {
                                        document.body.classList.remove('modal-open');
                                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                    }, 300);
                                    mostrarProductos();
                                }
                            });
                        } else {
                            Swal.fire('Error', 'Ocurrió un error al guardar.', 'error');
                        }
                    });
            });
        }

        const productoLink = document.querySelector('a[href="#producto"]');
        if (productoLink) {
            productoLink.addEventListener('click', function(e) {
                e.preventDefault();
                loadProducto();
            });
        }
    });