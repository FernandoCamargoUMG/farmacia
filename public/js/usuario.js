document.addEventListener('DOMContentLoaded', function() {

        function loadUsuarios() {
            const content = document.getElementById('dynamic-content');
            content.innerHTML = `
        <div class="Usuarios-view">
            <h2 class="mb-4"><i class="bi bi-truck"></i> Módulo de Usuarios</h2>
            <div class="card shadow" style="max-width: 1000px; margin: 0 auto;">
                <div class="card-body">
                    <h5 class="card-title">Listado de Usuarios</h5>

                    <button id="btnMostrarUsuarios" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-eye"></i> Mostrar Usuarios
                    </button>
                    <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevoUsuarios">
                        <i class="bi bi-plus-circle"></i> Nuevo Usuarios
                    </button>

                    <table id="tablaUsuarios" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyUsuarios"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalNuevoUsuarios" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-white text-dark">
                    <form id="formNuevoUsuarios">
                        <input type="hidden" name="id" id="UsuariosId">
                        <div class="modal-header">
                            <h5 class="modal-title">Nuevo Usuarios</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Categoría</label>
                                <select name="rol_id" id="selectRol" class="form-control" required>
                                    <option value="">Seleccione una categoría</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label>Categoría</label>
                                <select name="sucursal_id" id="selectSucursal" class="form-control" required>
                                    <option value="">Seleccione una categoría</option>
                                </select>
                            </div>

                            <div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control"></div>
                            <div class="mb-3"><label>Correo</label><input type="text" name="correo" class="form-control" required></div>
                            <div class="mb-3"><label>Password</label><input type="text" name="password" class="form-control"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;

            // Función para cargar categorías
            function cargarRoles() {
                fetch('/controllers/categoriaUsuariosController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        const select = document.getElementById('selectRol');
                        select.innerHTML = '<option value="">Seleccione una categoría</option>';
                        data.forEach(cat => {
                            const option = document.createElement('option');
                            option.value = cat.id;
                            option.textContent = cat.nombre;
                            select.appendChild(option);
                        });
                    });
            }

            function cargarSucursales() {
                fetch('/controllers/SucUsuariosController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        const select = document.getElementById('selectSucursal');
                        select.innerHTML = '<option value="">Seleccione una categoría</option>';
                        data.forEach(cat => {
                            const option = document.createElement('option');
                            option.value = cat.id;
                            option.textContent = cat.nombre_sucursal;
                            select.appendChild(option);
                        });
                    });
            }

            // Abrir modal
            document.querySelector('[data-bs-target="#modalNuevoUsuarios"]').addEventListener('click', function() {
                const form = document.getElementById('formNuevoUsuarios');
                form.reset();
                form.UsuariosId.value = '';
                cargarRoles();
                cargarSucursales();
            });

            // Mostrar listado de Usuarios
            function mostrarUsuarios() {
                fetch('/controllers/UsuariosController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        if ($.fn.DataTable.isDataTable('#tablaUsuarios')) {
                            $('#tablaUsuarios').DataTable().clear().destroy();
                        }

            const tbody = document.getElementById('tbodyUsuarios');
            tbody.innerHTML = data.map(usuario => `
                <tr>
                    <td>${usuario.nombre || ''}</td>
                    <td>${usuario.correo || ''}</td>
                    <td>
                        <button class="btn btn-sm btn-warning btnEditar" data-id="${usuario.id}">✏️</button>
                        <button class="btn btn-sm btn-danger btnEliminar" data-id="${usuario.id}">🗑️</button>
                    </td>
                </tr>
            `).join('');

                        $('#tablaUsuarios').DataTable({
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
                                fetch(`/controllers/UsuariosController.php?action=ver&id=${id}`)
                                    .then(res => res.json())
                                    .then(Usuarios => {
                                        const form = document.getElementById('formNuevoUsuarios');
                                        form.UsuariosId.value = Usuarios.id;
                                        form.nombre.value = Usuarios.nombre;
                                        form.correo.value = Usuarios.correo;
                                        cargarRoles();
                                        cargarSucursales();
                                        setTimeout(() => {
                                            form.sucursal_id.value = Usuarios.sucursal_id;
                                            form.rol_id.value = Usuarios.rol_id;
                                        }, 300);
                                        new bootstrap.Modal(document.getElementById('modalNuevoUsuarios')).show();
                                    });
                            });
                        });
                        //para eliminar
                        document.querySelectorAll('.btnEliminar').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const id = this.dataset.id;
                                Swal.fire({
                                    title: '¿Eliminar Usuarios?',
                                    text: 'Esta acción no se puede deshacer.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Sí, eliminar',
                                    cancelButtonText: 'Cancelar'
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        fetch('/controllers/UsuariosController.php?action=eliminar', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/x-www-form-urlencoded'
                                                },
                                                body: `id=${id}`
                                            })
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.success) {
                                                    Swal.fire('Eliminado', 'Usuarios eliminado correctamente.', 'success');
                                                    mostrarUsuarios();
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

            document.getElementById('btnMostrarUsuarios').addEventListener('click', mostrarUsuarios);

            // Guardar Usuarios
            const form = document.getElementById('formNuevoUsuarios');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const id = formData.get("id");
                const action = id ? 'actualizar' : 'guardar';

                fetch(`/controllers/UsuariosController.php?action=${action}`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Usuarios guardado!',
                                text: '¿Qué deseas hacer ahora?',
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Agregar otro',
                                cancelButtonText: 'Ver listado'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    form.reset();
                                    form.UsuariosId.value = '';
                                    cargarRoles();
                                    cargarSucursales();
                                } else {
                                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoUsuarios'));
                                    modal.hide();
                                    setTimeout(() => {
                                        document.body.classList.remove('modal-open');
                                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                    }, 300);
                                    mostrarUsuarios();
                                }
                            });
                        } else {
                            Swal.fire('Error', 'Ocurrió un error al guardar.', 'error');
                        }
                    });
            });
        }


        const UsuariosLink = document.querySelector('a[href="#Usuarios"]');
        if (UsuariosLink) {
            UsuariosLink.addEventListener('click', function(e) {
                e.preventDefault();
                loadUsuarios();
            });
        }
    });