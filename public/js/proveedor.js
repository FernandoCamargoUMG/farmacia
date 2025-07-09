document.addEventListener('DOMContentLoaded', function() {

        function loadProveedores() {
            const content = document.getElementById('dynamic-content');
            content.innerHTML = `
        <div class="proveedores-view">
            <h2 class="mb-4"><i class="bi bi-truck"></i> Módulo de Proveedores</h2>
            <div class="card shadow" style="max-width: 1000px; margin: 0 auto;">
                <div class="card-body">
                    <h5 class="card-title">Listado de Proveedores</h5>

                    <button id="btnMostrarProveedores" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-eye"></i> Mostrar Proveedores
                    </button>
                    <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevoProveedor">
                        <i class="bi bi-plus-circle"></i> Nuevo Proveedor
                    </button>

                    <table id="tablaProveedores" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>NIT</th>
                                <th>Dirección</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyProveedores"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalNuevoProveedor" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-white text-dark">
                    <form id="formNuevoProveedor">
                        <input type="hidden" name="id" id="proveedorId">
                        <div class="modal-header">
                            <h5 class="modal-title">Nuevo Proveedor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Categoría</label>
                                <select name="categoria_id" id="selectCategoria" class="form-control" required>
                                    <option value="">Seleccione una categoría</option>
                                </select>
                            </div>
                            <div class="mb-3"><label>Código</label><input type="text" name="codigo" class="form-control"></div>
                            <div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                            <div class="mb-3"><label>NIT</label><input type="text" name="nit" class="form-control"></div>
                            <div class="mb-3"><label>Dirección</label><input type="text" name="direccion" class="form-control"></div>
                            <div class="mb-3"><label>Teléfono</label><input type="text" name="telefono" class="form-control"></div>
                            <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control"></div>
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
                fetch('/controllers/categoriaProveedorController.php?action=listar')
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
            document.querySelector('[data-bs-target="#modalNuevoProveedor"]').addEventListener('click', function() {
                const form = document.getElementById('formNuevoProveedor');
                form.reset();
                form.proveedorId.value = '';
                cargarCategorias();
            });

            // Mostrar listado de proveedores
            function mostrarProveedores() {
                fetch('/controllers/proveedorController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        if ($.fn.DataTable.isDataTable('#tablaProveedores')) {
                            $('#tablaProveedores').DataTable().clear().destroy();
                        }

                        const tbody = document.getElementById('tbodyProveedores');
                        tbody.innerHTML = data.map(proveedor => `
                        <tr>
                            <td>${proveedor.codigo || ''}</td>
                            <td>${proveedor.nombre}</td>
                            <td>${proveedor.categoria || ''}</td>
                            <td>${proveedor.nit || ''}</td>
                            <td>${proveedor.direccion || ''}</td>
                            <td>${proveedor.telefono || ''}</td>
                            <td>${proveedor.email || ''}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btnEditar" data-id="${proveedor.id}">✏️</button>
                                <button class="btn btn-sm btn-danger btnEliminar" data-id="${proveedor.id}">🗑️</button>
                            </td>
                        </tr>
                    `).join('');

                        $('#tablaProveedores').DataTable({
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
                                fetch(`/controllers/proveedorController.php?action=ver&id=${id}`)
                                    .then(res => res.json())
                                    .then(proveedor => {
                                        const form = document.getElementById('formNuevoProveedor');
                                        form.proveedorId.value = proveedor.id;
                                        form.codigo.value = proveedor.codigo;
                                        form.nombre.value = proveedor.nombre;
                                        form.nit.value = proveedor.nit;
                                        form.direccion.value = proveedor.direccion;
                                        form.telefono.value = proveedor.telefono;
                                        form.email.value = proveedor.email;
                                        cargarCategorias();
                                        setTimeout(() => {
                                            form.categoria_id.value = proveedor.categoria_id;
                                        }, 300);
                                        new bootstrap.Modal(document.getElementById('modalNuevoProveedor')).show();
                                    });
                            });
                        });
                        //para eliminar
                        document.querySelectorAll('.btnEliminar').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const id = this.dataset.id;
                                Swal.fire({
                                    title: '¿Eliminar proveedor?',
                                    text: 'Esta acción no se puede deshacer.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Sí, eliminar',
                                    cancelButtonText: 'Cancelar'
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        fetch('/controllers/proveedorController.php?action=eliminar', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/x-www-form-urlencoded'
                                                },
                                                body: `id=${id}`
                                            })
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.success) {
                                                    Swal.fire('Eliminado', 'Proveedor eliminado correctamente.', 'success');
                                                    mostrarProveedores();
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

            document.getElementById('btnMostrarProveedores').addEventListener('click', mostrarProveedores);

            // Guardar proveedor
            const form = document.getElementById('formNuevoProveedor');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const id = formData.get("id");
                const action = id ? 'actualizar' : 'guardar';

                fetch(`/controllers/proveedorController.php?action=${action}`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Proveedor guardado!',
                                text: '¿Qué deseas hacer ahora?',
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Agregar otro',
                                cancelButtonText: 'Ver listado'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    form.reset();
                                    form.proveedorId.value = '';
                                    cargarCategorias();
                                } else {
                                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoProveedor'));
                                    modal.hide();
                                    setTimeout(() => {
                                        document.body.classList.remove('modal-open');
                                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                    }, 300);
                                    mostrarProveedores();
                                }
                            });
                        } else {
                            Swal.fire('Error', 'Ocurrió un error al guardar.', 'error');
                        }
                    });
            });
        }


        const proveedoresLink = document.querySelector('a[href="#proveedores"]');
        if (proveedoresLink) {
            proveedoresLink.addEventListener('click', function(e) {
                e.preventDefault();
                loadProveedores();
            });
        }
    });