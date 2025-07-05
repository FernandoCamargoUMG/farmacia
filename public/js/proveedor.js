document.addEventListener('DOMContentLoaded', function() {

    function loadProveedores() {
            const content = document.getElementById('dynamic-content');
            content.innerHTML = `
        <div class="proveedores-view">
            <h2 class="mb-4"><i class="bi bi-people-fill"></i> Módulo de Proveedores</h2>
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
                                <th>NIT</th>
                                <th>Dirección</th>
                                <th>Teléfono</th>
                                <th>Correo Electrónico</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyProveedor"></tbody>
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
                            <div class="mb-3"><label>codigo</label><input type="text" name="nombre" class="form-control" required></div>
                            <div class="mb-3"><label>nombre</label><input type="text" name="apellido" class="form-control" required></div>
                            <div class="mb-3"><label>nit</label><input type="text" name="dpi" class="form-control"></div>
                            <div class="mb-3"><label>direccion</label><input type="email" name="email" class="form-control"></div>
                            <div class="mb-3"><label>telefono</label><input type="text" name="direccion" class="form-control"></div>
                            <div class="mb-3"><label>email</label><input type="text" name="telefono" class="form-control"></div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;

        document.querySelector('[data-bs-target="#modalNuevoProveedor"]').addEventListener('click', function() {
                const form = document.getElementById('formNuevoProveedor');
                form.reset();
                form.proveedorId.value = ''; // Esto evita actualizar accidentalmente
            });
            function mostrarProveedores() {
                fetch('/farmacia/controllers/proveedorController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        if ($.fn.DataTable.isDataTable('#tablaProveedores')) {
                            $('#tablaProveedores').DataTable().clear().destroy();
                        }

                        const tbody = document.getElementById('tbodyProveedor');
                        tbody.innerHTML = data.map(proveedor => `
                        <tr>
                            <td>${proveedor.codigo}</td>
                            <td>${proveedor.nombre}</td>
                            <td>${proveedor.nit || ''}</td>
                            <td>${proveedor.direccion || ''}</td>
                            <td>${proveedor.telefono || ''}</td>
                            <td>${proveedor.email || ''}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btnEditar" data-id="${proveedor.id}">✏️</button>
                                <button class="btn btn-sm btn-danger btnEliminar" data-id="${proveedor.id}">🗑️</button>
                        </tr>
                    `).join('');

                        
                    });
            }
            //mostrarClientes(); // Llamar al final después del modal

            // Mostrar listado
            document.getElementById('btnMostrarProveedores').addEventListener('click', mostrarProveedores);
        }

    const proveedoresLink = document.querySelector('a[href="#proveedores"]');
        if (proveedoresLink) {
            proveedoresLink.addEventListener('click', function(e) {
                e.preventDefault();
                loadProveedores();
            });
        }
    });