document.addEventListener('DOMContentLoaded', function() {
    function loadCategoriaPrecio() {
        const content = document.getElementById('dynamic-content');
        content.innerHTML = `
        <div class="categoria-precio-view">
            <h2 class="mb-4"><i class="bi bi-tags"></i> Categorías de Precio</h2>
            <div class="card shadow" style="max-width: 1000px; margin: 0 auto;">
                <div class="card-body">
                    <h5 class="card-title">Listado de Categorías de Precio</h5>
                    <button id="btnMostrarCategoriaPrecio" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-eye"></i> Mostrar Categorías
                    </button>
                    <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevaCategoriaPrecio">
                        <i class="bi bi-plus-circle"></i> Nueva Categoría de Precio
                    </button>

                    <table id="tablaCategoriaPrecio" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Precio Base</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyCategoriaPrecio"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modalNuevaCategoriaPrecio" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content bg-white text-dark">
                    <form id="formNuevaCategoriaPrecio">
                        <input type="hidden" name="id" id="categoriaPrecioId">
                        <div class="modal-header">
                            <h5 class="modal-title">Nueva Categoría de Precio</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text" name="nombre" class="form-control" required placeholder="Ej: Premium, Estándar, Económico">
                            </div>
                            <div class="mb-3">
                                <label>Descripción</label>
                                <textarea name="descripcion" class="form-control" rows="2" placeholder="Descripción de la categoría (opcional)"></textarea>
                            </div>
                            <div class="mb-3">
                                <label>Precio Base <small class="text-muted">(Opcional)</small></label>
                                <input type="number" name="precio_base" step="0.01" min="0" class="form-control" placeholder="Dejar vacío si el precio será definido por producto">
                                <small class="text-muted">Si se define un precio base, todos los productos de esta categoría tendrán este precio por defecto</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;

        // Abrir modal
        document.querySelector('[data-bs-target="#modalNuevaCategoriaPrecio"]').addEventListener('click', function() {
            const form = document.getElementById('formNuevaCategoriaPrecio');
            form.reset();
            document.getElementById('categoriaPrecioId').value = '';
            document.querySelector('#modalNuevaCategoriaPrecio .modal-title').textContent = 'Nueva Categoría de Precio';
        });

        // Mostrar categorías
        function mostrarCategorias() {
            fetch('controllers/categoriaPrecioController.php?action=listar')
                .then(res => res.json())
                .then(data => {
                    if ($.fn.DataTable.isDataTable('#tablaCategoriaPrecio')) {
                        $('#tablaCategoriaPrecio').DataTable().clear().destroy();
                    }

                    const tbody = document.getElementById('tbodyCategoriaPrecio');
                    tbody.innerHTML = data.map(cat => {
                        const precioBase = cat.precio_base ? `Q${parseFloat(cat.precio_base).toFixed(2)}` : '<span class="badge bg-secondary">Personalizado</span>';
                        const estado = cat.activo == 1 ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
                        
                        return `
                        <tr>
                            <td><strong>${cat.nombre}</strong></td>
                            <td>${cat.descripcion || '-'}</td>
                            <td>${precioBase}</td>
                            <td>${estado}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btnEditar" data-id="${cat.id}">
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                                <button class="btn btn-sm btn-danger btnEliminar" data-id="${cat.id}">
                                    <i class="bi bi-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>`;
                    }).join('');

                    $('#tablaCategoriaPrecio').DataTable({
                        language: {
                            url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                        },
                        pageLength: 10,
                        lengthMenu: [5, 10, 25, 50, 100]
                    });

                    // Eventos editar
                    document.querySelectorAll('.btnEditar').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const id = this.getAttribute('data-id');
                            editarCategoria(id);
                        });
                    });

                    // Eventos eliminar
                    document.querySelectorAll('.btnEliminar').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const id = this.getAttribute('data-id');
                            eliminarCategoria(id);
                        });
                    });
                });
        }

        // Editar categoría
        function editarCategoria(id) {
            fetch(`controllers/categoriaPrecioController.php?action=ver&id=${id}`)
                .then(res => res.json())
                .then(data => {
                    if (data) {
                        document.getElementById('categoriaPrecioId').value = data.id;
                        document.querySelector('[name="nombre"]').value = data.nombre;
                        document.querySelector('[name="descripcion"]').value = data.descripcion || '';
                        document.querySelector('[name="precio_base"]').value = data.precio_base || '';
                        
                        document.querySelector('#modalNuevaCategoriaPrecio .modal-title').textContent = 'Editar Categoría de Precio';
                        
                        const modal = new bootstrap.Modal(document.getElementById('modalNuevaCategoriaPrecio'));
                        modal.show();
                    }
                });
        }

        // Eliminar categoría
        function eliminarCategoria(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta categoría será desactivada",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('id', id);

                    fetch('controllers/categoriaPrecioController.php?action=eliminar', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Eliminado', 'Categoría eliminada correctamente', 'success');
                            mostrarCategorias();
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo eliminar la categoría', 'error');
                        }
                    });
                }
            });
        }

        // Guardar categoría
        document.getElementById('formNuevaCategoriaPrecio').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const id = formData.get('id');
            const action = id ? 'actualizar' : 'guardar';

            fetch(`controllers/categoriaPrecioController.php?action=${action}`, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Éxito',
                        text: id ? 'Categoría actualizada correctamente' : 'Categoría creada correctamente',
                        icon: 'success'
                    });
                    
                    const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaCategoriaPrecio'));
                    modal.hide();
                    
                    mostrarCategorias();
                } else {
                    Swal.fire('Error', data.message || 'Error al guardar la categoría', 'error');
                }
            });
        });

        // Evento mostrar
        document.getElementById('btnMostrarCategoriaPrecio').addEventListener('click', mostrarCategorias);
    }

    // Enlace del menú
    const categoriaPrecioLink = document.querySelector('a[href="#categoria-precio"]');
    if (categoriaPrecioLink) {
        categoriaPrecioLink.addEventListener('click', function(e) {
            e.preventDefault();
            loadCategoriaPrecio();
        });
    }
});
