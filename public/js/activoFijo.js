document.addEventListener('DOMContentLoaded', function () {
    function loadActivos() {
        const content = document.getElementById('dynamic-content');
        content.innerHTML = `
        <div class="activos-view">
            <h2 class="mb-4"><i class="bi bi-building"></i> Módulo de Activos Fijos</h2>
            <div class="card shadow" style="max-width: 2000px; margin: 0 auto;">
                <div class="card-body">
                    <h5 class="card-title">Listado de Activos</h5>

                    <button id="btnMostrarActivos" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-eye"></i> Mostrar Activos
                    </button>
                    <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevoActivo">
                        <i class="bi bi-plus-circle"></i> Nuevo Activo
                    </button>

                    <table id="tablaActivos" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Fecha de Adquisición</th>
                                <th>Tipo</th>
                                <th>Responsable</th>
                                <th>Estado</th>
                                <th>Ubicación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyActivos"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modalNuevoActivo" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-white text-dark">
                    <form id="formNuevoActivo">
                        <input type="hidden" name="id" id="activoId">
                        <div class="modal-header">
                            <h5 class="modal-title">Nuevo Activo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3"><label>Código</label><input type="text" name="codigo" class="form-control" required></div>
                            <div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                            <div class="mb-3"><label>Tipo de Activo</label>
                                <select name="tipo_activo_id" id="tipo_activo_id" class="form-select" required></select>
                            </div>
                            <div class="mb-3 position-relative"><label>Responsable</label>
                                <input type="text" name="responsable_autocomplete" id="responsable_autocomplete" class="form-control" autocomplete="off">
                                <input type="hidden" name="responsable" id="responsable">
                                <div id="autocompleteResponsableList" class="autocomplete-items" style="position:absolute; z-index:1000; background:white; width:100%; max-height:150px; overflow-y:auto; border:1px solid #ddd;"></div>
                            </div>
                            <div class="mb-3"><label>Fecha Adquisición</label><input type="date" name="fecha_adquisicion" class="form-control"></div>
                            <div class="mb-3"><label>Costo</label><input type="text" step="0.01" name="costo" class="form-control"></div>
                            <div class="mb-3"><label>Valor Residual</label><input type="text" step="0.01" name="valor_residual" class="form-control"></div>
                            <div class="mb-3"><label>Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="Activo">Activo</option>
                                    <option value="En mantenimiento">En mantenimiento</option>
                                    <option value="Dado de baja">Dado de baja</option>
                                    <option value="Vendido">Vendido</option>
                                </select>
                            </div>
                            <div class="mb-3"><label>Ubicación</label><input type="text" name="ubicacion" class="form-control"></div>
                            <div class="mb-3"><label>Descripción</label><textarea name="descripcion" class="form-control"></textarea></div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;

        // Función autocomplete sin jQuery para Responsable
        function autocomplete(input, hidden, list, url) {
            input.oninput = () => {
                list.innerHTML = '';
                hidden.value = '';
                if (input.value.length < 2) return;
                fetch(url + '?term=' + encodeURIComponent(input.value))
                .then(r => r.json())
                .then(data => {
                    data.forEach(i => {
                    let div = document.createElement('div');
                    div.textContent = i.label;
                    div.style.cursor = 'pointer';
                    div.onclick = () => {
                        input.value = i.label;
                        hidden.value = i.id;
                        list.innerHTML = '';
                    };
                    list.appendChild(div);
                    });
                });
            };
            input.onkeydown = e => {
                if (e.key === 'Enter') {
                e.preventDefault();
                if (list.firstChild) list.firstChild.click();
                }
            };
            document.addEventListener('click', e => {
                if (e.target !== input) list.innerHTML = '';
            });
            }


        // Inicializar autocomplete responsable
        const inputResp = document.getElementById('responsable_autocomplete');
        const hiddenResp = document.getElementById('responsable');
        const listaResp = document.getElementById('autocompleteResponsableList');
        autocomplete(inputResp, hiddenResp, listaResp, '/autocomplete/autocomplete_responsable.php');

        function tipoActivo() {
            fetch('/controllers/activoTipoController.php?action=listar')
                .then(res => res.json())
                .then(data => {
                    const select = document.getElementById('tipo_activo_id');
                    select.innerHTML = '<option value="">Seleccione una categoría</option>';
                    data.forEach(cat => {
                        const option = document.createElement('option');
                        option.value = cat.id;
                        option.textContent = cat.nombre;
                        select.appendChild(option);
                    });
                });
        }

        // Mostrar listado
        function mostrarActivos() {
            fetch('/controllers/activoController.php?action=listar')
                .then(res => res.json())
                .then(data => {
                    if ($.fn.DataTable.isDataTable('#tablaActivos')) {
                        $('#tablaActivos').DataTable().clear().destroy();
                    }

                    const tbody = document.getElementById('tbodyActivos');
                    tbody.innerHTML = data.map(activo => `
                        <tr>
                            <td>${activo.codigo}</td>
                            <td>${activo.nombre}</td>
                            <td>${activo.descripcion || ''}</td>
                            <td>${activo.fecha_adquisicion || ''}</td>
                            <td>${activo.tipo || ''}</td>
                            <td>${activo.responsable_activo || ''}</td>
                            <td>${activo.estado || ''}</td>
                            <td>${activo.ubicacion || ''}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btnEditar" data-id="${activo.id}">✏️</button>
                                <button class="btn btn-sm btn-danger btnEliminar" data-id="${activo.id}">🗑️</button>
                            </td>
                        </tr>
                    `).join('');

                    $('#tablaActivos').DataTable({
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                        },
                        pageLength: 5,
                        lengthMenu: [5, 10, 25, 50, 100]
                    });

                    // Editar activo
                    document.querySelectorAll('.btnEditar').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const id = this.dataset.id;
                            fetch(`/controllers/activoController.php?action=ver&id=${id}`)
                                .then(res => res.json())
                                .then(activo => {
                                    const form = document.getElementById('formNuevoActivo');
                                    form.activoId.value = activo.id;
                                    form.codigo.value = activo.codigo;
                                    form.nombre.value = activo.nombre;
                                    form.descripcion.value = activo.descripcion;
                                    form.fecha_adquisicion.value = activo.fecha_adquisicion;
                                    form.costo.value = activo.costo;
                                    form.valor_residual.value = activo.valor_residual;
                                    form.estado.value = activo.estado;
                                    form.ubicacion.value = activo.ubicacion;
                                    document.getElementById('responsable_autocomplete').value = activo.responsable_nombre || '';
                                    document.getElementById('responsable').value = activo.responsable;
                                    tipoActivo();
                                    setTimeout(() => {
                                        form.tipo_activo_id.value = activo.tipo_activo_id;
                                    }, 300);

                                    new bootstrap.Modal(document.getElementById('modalNuevoActivo')).show();
                                });
                        });
                    });

                    // Eliminar activo
                    document.querySelectorAll('.btnEliminar').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const id = this.dataset.id;
                            Swal.fire({
                                title: '¿Eliminar activo?',
                                text: 'Esta acción no se puede deshacer.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Sí, eliminar',
                                cancelButtonText: 'Cancelar'
                            }).then(result => {
                                if (result.isConfirmed) {
                                    fetch('/controllers/activoController.php?action=eliminar', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/x-www-form-urlencoded'
                                        },
                                        body: `id=${id}`
                                    })
                                        .then(res => res.json())
                                        .then(data => {
                                            if (data.success) {
                                                Swal.fire('Eliminado', 'Activo eliminado correctamente.', 'success');
                                                mostrarActivos();
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

        document.getElementById('btnMostrarActivos').addEventListener('click', mostrarActivos);

        // Guardar o actualizar activo
        const form = document.getElementById('formNuevoActivo');
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(form);
            const id = formData.get("id");
            const action = id ? 'actualizar' : 'guardar';

            fetch(`/controllers/activoController.php?action=${action}`, {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: '¡Activo guardado!',
                            text: '¿Qué deseas hacer ahora?',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Agregar otro',
                            cancelButtonText: 'Ver listado'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                form.reset();
                                form.activoId.value = '';
                                tipoActivo();
                            } else {
                                const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevoActivo'));
                                modal.hide();
                                setTimeout(() => {
                                    document.body.classList.remove('modal-open');
                                    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                }, 300);
                                mostrarActivos();
                            }
                        });
                    } else {
                        Swal.fire('Error', 'Ocurrió un error al guardar.', 'error');
                    }
                });
        });

        // Modal: limpiar al abrir
        document.querySelector('[data-bs-target="#modalNuevoActivo"]').addEventListener('click', function () {
            const form = document.getElementById('formNuevoActivo');
            form.reset();
            form.activoId.value = '';
            tipoActivo();
        });
    }

    // Asignar evento al menú
    const activosLink = document.querySelector('a[href="#activos"]');
    if (activosLink) {
        activosLink.addEventListener('click', function (e) {
            e.preventDefault();
            loadActivos();
        });
    }
});
