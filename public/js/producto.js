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
                                <label>Categoría de Producto</label>
                                <select name="categoria_id" id="selectCategoria" class="form-control" required>
                                    <option value="">Seleccione una categoría</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label>Categoría de Precio</label>
                                <select name="categoria_precio_id" id="selectCategoriaPrecio" class="form-control">
                                    <option value="">Seleccione una categoría de precio (opcional)</option>
                                </select>
                                <small class="text-muted">Si la categoría tiene precio base, se autocompletará el campo precio</small>
                            </div>
                            <div class="mb-3">
                                <label>Código de Barras</label>
                                <input type="text" name="codigo" id="codigoBarras" class="form-control" placeholder="Escanee el código de barras...">
                                <small class="text-muted">Use el lector de código de barras o déjelo vacío para generar automáticamente</small>
                            </div>
                            <div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" required></div>
                            <div class="mb-3"><label>descripción</label><input type="text" name="descripcion" class="form-control"></div>
                            <div class="mb-3">
                                <label>Precio</label>
                                <input type="number" name="precio" id="precioProducto" step="0.01" min="0" class="form-control" required>
                                <small class="text-muted" id="mensajePrecioCategoria" style="display: none; color: #28a745;">
                                    <i class="bi bi-info-circle"></i> Precio autocompletado desde la categoría
                                </small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;

            // Función para cargar categorías de producto
            function cargarCategorias() {
                return fetch('controllers/categoriaProductoController.php?action=listar')
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
                        return data;
                    });
            }
            
            // Función para cargar categorías de precio
            function cargarCategoriasPrecio() {
                return fetch('controllers/categoriaPrecioController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        const select = document.getElementById('selectCategoriaPrecio');
                        select.innerHTML = '<option value="">Seleccione una categoría de precio (opcional)</option>';
                        data.forEach(cat => {
                            const option = document.createElement('option');
                            option.value = cat.id;
                            option.textContent = cat.nombre + (cat.precio_base ? ` (Q${parseFloat(cat.precio_base).toFixed(2)})` : ' (Personalizado)');
                            option.dataset.precioBase = cat.precio_base || '';
                            select.appendChild(option);
                        });
                        return data;
                    });
            }
            
            // Listener para cambio de categoría de precio
            document.addEventListener('change', function(e) {
                if (e.target.id === 'selectCategoriaPrecio') {
                    const selectedOption = e.target.options[e.target.selectedIndex];
                    const precioBase = selectedOption.dataset.precioBase;
                    const precioInput = document.getElementById('precioProducto');
                    const mensajePrecio = document.getElementById('mensajePrecioCategoria');
                    
                    if (precioBase) {
                        // Autocompletar precio
                        precioInput.value = parseFloat(precioBase).toFixed(2);
                        mensajePrecio.style.display = 'block';
                        precioInput.readOnly = false; // Permitir editar aunque venga de categoría
                    } else {
                        // Limpiar precio si no hay precio base
                        mensajePrecio.style.display = 'none';
                        precioInput.readOnly = false;
                    }
                }
            });

            // Abrir modal
            document.querySelector('[data-bs-target="#modalNuevoProducto"]').addEventListener('click', function() {
                const form = document.getElementById('formNuevoProducto');
                form.reset();
                form.productoId.value = '';
                document.getElementById('mensajePrecioCategoria').style.display = 'none';
                cargarCategorias();
                cargarCategoriasPrecio();
                
                // Focus en el campo de código para lector de barras
                setTimeout(() => {
                    document.getElementById('codigoBarras').focus();
                }, 500);
            });

            // Listener para código de barras - el lector envía Enter automáticamente
            document.getElementById('codigoBarras').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const codigo = this.value.trim();
                    
                    if (codigo) {
                        // Verificar si el código ya existe
                        fetch(`controllers/productoController.php?action=buscar_codigo&codigo=${encodeURIComponent(codigo)}`)
                            .then(res => res.json())
                            .then(data => {
                                if (data.existe) {
                                    Swal.fire({
                                        title: 'Código ya existe',
                                        text: `El código "${codigo}" ya está registrado para: ${data.producto.nombre}`,
                                        icon: 'warning',
                                        confirmButtonText: 'Entendido'
                                    });
                                    this.value = '';
                                    this.focus();
                                } else {
                                    // Código disponible, pasar al siguiente campo
                                    document.querySelector('[name="nombre"]').focus();
                                }
                            })
                            .catch(err => {
                                console.error('Error verificando código:', err);
                                document.querySelector('[name="nombre"]').focus();
                            });
                    } else {
                        // Si está vacío, pasar al siguiente campo
                        document.querySelector('[name="nombre"]').focus();
                    }
                }
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
                                        document.getElementById('codigoBarras').value = producto.codigo;
                                        form.nombre.value = producto.nombre;
                                        form.descripcion.value = producto.descripcion;
                                        document.getElementById('precioProducto').value = producto.precio;
                                        
                                        // Cargar categorías y luego seleccionar la correcta
                                        cargarCategorias().then(() => {
                                            document.getElementById('selectCategoria').value = producto.categoria_id;
                                        });
                                        
                                        // Cargar categorías de precio
                                        cargarCategoriasPrecio().then(() => {
                                            if (producto.categoria_precio_id) {
                                                document.getElementById('selectCategoriaPrecio').value = producto.categoria_precio_id;
                                            }
                                        });
                                        
                                        document.getElementById('mensajePrecioCategoria').style.display = 'none';
                                        
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