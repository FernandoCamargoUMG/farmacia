document.addEventListener('DOMContentLoaded', function() {
        function loadClientes() {
            const content = document.getElementById('dynamic-content');
            content.innerHTML = `
        <div class="clientes-view">
            <h2 class="mb-4"><i class="bi bi-people-fill"></i> Módulo de Clientes</h2>
            <div class="card shadow" style="max-width: 1000px; margin: 0 auto;">
                <div class="card-body">
                    <h5 class="card-title">Listado de Clientes</h5>

                    <button id="btnMostrarClientes" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-eye"></i> Mostrar Clientes
                    </button>
                    <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevoCliente">
                        <i class="bi bi-plus-circle"></i> Nuevo Cliente
                    </button>

                    <table id="tablaClientes" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>DPI</th>
                                <th>Correo Electronico</th>
                                <th>Dirección</th>
                                <th>Teléfono</th>
                                <th>NIT</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyClientes"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalNuevoCliente" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-white text-dark">
                    <form id="formNuevoCliente">
                        <input type="hidden" name="id" id="clienteId">
                        <div class="modal-header">
                            <h5 class="modal-title">Nuevo Cliente</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                            <div class="mb-3"><label>Apellido</label><input type="text" name="apellido" class="form-control" required></div>
                            <div class="mb-3"><label>DPI</label><input type="text" name="dpi" class="form-control"></div>
                            <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control"></div>
                            <div class="mb-3"><label>Dirección</label><input type="text" name="direccion" class="form-control"></div>
                            <div class="mb-3"><label>Teléfono</label><input type="text" name="telefono" class="form-control"></div>
                            <div class="mb-3"><label>NIT</label><input type="text" name="nit" class="form-control"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;

            document.querySelector('[data-bs-target="#modalNuevoCliente"]').addEventListener('click', function() {
                const form = document.getElementById('formNuevoCliente');
                form.reset();
                form.clienteId.value = ''; // Esto evita actualizar accidentalmente
            });

            function mostrarClientes() {
                fetch('/controllers/clienteController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        if ($.fn.DataTable.isDataTable('#tablaClientes')) {
                            $('#tablaClientes').DataTable().clear().destroy();
                        }

                        const tbody = document.getElementById('tbodyClientes');
                        tbody.innerHTML = data.map(cliente => `
                        <tr>
                            <td>${cliente.nombre}</td>
                            <td>${cliente.apellido}</td>
                            <td>${cliente.dpi || ''}</td>
                            <td>${cliente.email || ''}</td>
                            <td>${cliente.direccion || ''}</td>
                            <td>${cliente.telefono || ''}</td>
                            <td>${cliente.nit || ''}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btnEditar" data-id="${cliente.id}">✏️</button>
                                <button class="btn btn-sm btn-danger btnEliminar" data-id="${cliente.id}">🗑️</button>
                            </td>
                        </tr>
                    `).join('');

                        $('#tablaClientes').DataTable({
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                            },
                            pageLength: 5,
                            lengthMenu: [5, 10, 25, 50, 100]
                        });

                        //document.querySelector('.card-body').insertAdjacentHTML('beforeend', tableHtml);

                        //const tabla = $('#tablaClientes').DataTable();

                        // Botón editar
                        document.querySelectorAll('.btnEditar').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const id = this.dataset.id;

                                fetch(`/controllers/clienteController.php?action=ver&id=${id}`)
                                    .then(res => res.json())
                                    .then(cliente => {
                                        const form = document.getElementById('formNuevoCliente');
                                        form.clienteId.value = cliente.id;
                                        form.nombre.value = cliente.nombre;
                                        form.apellido.value = cliente.apellido;
                                        form.dpi.value = cliente.dpi;
                                        form.email.value = cliente.email;
                                        form.direccion.value = cliente.direccion;
                                        form.telefono.value = cliente.telefono;
                                        form.nit.value = cliente.nit;

                                        new bootstrap.Modal(document.getElementById('modalNuevoCliente')).show();
                                    });
                            });
                        });

                        // Botón eliminar
                        document.querySelectorAll('.btnEliminar').forEach(btn => {
                            btn.addEventListener('click', function() {
                                const id = this.dataset.id;

                                Swal.fire({
                                    title: '¿Eliminar cliente?',
                                    text: 'Esta acción no se puede deshacer.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Sí, eliminar',
                                    cancelButtonText: 'Cancelar'
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        fetch('/controllers/clienteController.php?action=eliminar', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/x-www-form-urlencoded'
                                                },
                                                body: `id=${id}`
                                            })
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.success) {
                                                    Swal.fire('Eliminado', 'Cliente eliminado correctamente.', 'success');
                                                    mostrarClientes();
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
            //mostrarClientes(); // Llamar al final después del modal

            // Mostrar listado
            document.getElementById('btnMostrarClientes').addEventListener('click', mostrarClientes);

            // Guardar o actualizar cliente
            const form = document.getElementById('formNuevoCliente');
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);
                const id = formData.get("id");
                const action = id ? 'actualizar' : 'guardar';

                fetch(`/controllers/clienteController.php?action=${action}`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Cliente guardado!',
                                text: '¿Qué deseas hacer ahora?',
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Agregar otro',
                                cancelButtonText: 'Ver listado',
                                reverseButtons: true
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    form.reset();
                                    form.clienteId.value = '';
                                } else {
                                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoCliente'));
                                    modal.hide();
                                    setTimeout(() => {
                                        document.body.classList.remove('modal-open');
                                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                    }, 300);
                                    mostrarClientes();
                                }
                            });
                        } else {
                            Swal.fire('Error', 'Ocurrió un error al guardar.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error("Error en el guardado:", error);
                    });
            });
        }

        // Asignar evento al menú
        const clientesLink = document.querySelector('a[href="#clientes"]');
        if (clientesLink) {
            clientesLink.addEventListener('click', function(e) {
                e.preventDefault();
                loadClientes();
            });
        }
    });