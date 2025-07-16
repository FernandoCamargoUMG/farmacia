document.addEventListener('DOMContentLoaded', function() {
        // Variables globales para productos y bodegas (se cargarán al iniciar)
        window.productos = [];
        window.bodegas = [];

        function loadIngresos() {
            const content = document.getElementById('dynamic-content');
            content.innerHTML = `
        <div class="ingresos-view">
            <h2 class="mb-4"><i class="bi bi-journal-plus"></i> Ingresos a Inventario</h2>
            <div class="card shadow" style="max-width: 1100px; margin: 0 auto;">
                <div class="card-body">
                    <h5 class="card-title">Listado de Ingresos</h5>
                    <button id="btnMostrarIngresos" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-eye"></i> Mostrar Ingresos
                    </button>
                    <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevoIngreso">
                        <i class="bi bi-plus-circle"></i> Nuevo Ingreso
                    </button>

                    <table id="tablaIngresos" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyIngresos"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modalNuevoIngreso" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content bg-white text-dark">
                    <form id="formNuevoIngreso">
                        
                        <div class="modal-header">
                            <h5 class="modal-title">Nuevo Ingreso</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label>Fecha</label>
                                    <input type="date" name="fecha" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label>Número</label>
                                    <input type="text" name="numero" class="form-control" required>
                                </div>
                                <div class="col-md-4 mb-2 position-relative">
                                    <label>Proveedor</label>
                                    <input type="text" id="inputProveedor" class="form-control" autocomplete="off" required>
                                    <input type="hidden" name="proveedor_id" id="proveedor_id" required>
                                    <div id="autocompleteProveedorList" class="autocomplete-items"></div>
                                </div>
                            </div>

                            <hr>

                            <table class="table table-bordered" id="tablaDetalles">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Bodega</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Importe</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="detalleBody"></tbody>
                            </table>
                            <button type="button" class="btn btn-secondary btn-sm" id="btnAgregarDetalle">+ Agregar</button>

                            <hr>

                            <div class="row">
                                <div class="col-md-3"><label>Subtotal</label><input type="text" name="subtotal" class="form-control" readonly></div>
                                <div class="col-md-3"><label>Gravada</label><input type="text" name="gravada" class="form-control" readonly></div>
                                <div class="col-md-3"><label>IVA (12%)</label><input type="text" name="iva" class="form-control" readonly></div>
                                <div class="col-md-3"><label>Total</label><input type="text" name="total" class="form-control" readonly></div>
                            </div>

                            <div class="mt-3">
                                <label>Observaciones</label>
                                <textarea name="observaciones" class="form-control"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        `;

            document.querySelector('[data-bs-target="#modalNuevoIngreso"]').addEventListener('click', function() {
                const form = document.getElementById('formNuevoIngreso');
                form.reset();
                form.removeAttribute('data-editing-id'); // quitar ID si venía de una edición
                document.getElementById('detalleBody').innerHTML = ''; // limpiar detalles
                document.getElementById('proveedor_id').value = '';
                document.getElementById('inputProveedor').value = '';
            });


            // Función autocompletar genérica
            function autocomplete(input, hiddenInput, listContainer, endpoint, extraCallback = null) {
                input.addEventListener('input', function() {
                    const val = this.value.trim();
                    if (val.length < 1) {
                        hiddenInput.value = '';
                        listContainer.innerHTML = '';
                        return;
                    }

                    fetch(`${endpoint}?q=${encodeURIComponent(val)}`)
                        .then(res => res.json())
                        .then(data => {
                            listContainer.innerHTML = '';
                            data.forEach(item => {
                                const div = document.createElement('div');
                                div.textContent = item.nombre || item.label || item.nombre_proveedor || item.nombre_producto || item.nombre_bodega || item.nombre || '';
                                div.classList.add('autocomplete-item');
                                div.style.cursor = 'pointer';
                                div.style.padding = '5px';
                                div.addEventListener('click', () => {
                                    input.value = div.textContent;
                                    hiddenInput.value = item.id;
                                    listContainer.innerHTML = '';
                                    if (extraCallback) extraCallback(item);
                                });
                                listContainer.appendChild(div);
                            });
                        });
                });

                document.addEventListener('click', (e) => {
                    if (e.target !== input) {
                        listContainer.innerHTML = '';
                    }
                });
            }

            // Inicializar autocomplete para proveedor
            const inputProveedor = document.getElementById('inputProveedor');
            const proveedorId = document.getElementById('proveedor_id');
            const listaProveedor = document.getElementById('autocompleteProveedorList');
            autocomplete(inputProveedor, proveedorId, listaProveedor, '/autocomplete/autocomplete_proveedores.php');

            // Cargar productos y bodegas para uso en filas
            fetch('/controllers/productoController.php?action=listar')
                .then(res => res.json())
                .then(data => {
                    window.productos = data;
                });

            fetch('/controllers/sucursalBodegaController.php?action=listar')
                .then(res => res.json())
                .then(data => {
                    window.bodegas = data;
                });

            // Función para agregar fila con autocompletados para producto y bodega
            function agregarFilaDetalle() {
                const tbody = document.getElementById('detalleBody');
                const idUnico = Date.now();

                const tr = document.createElement('tr');
                tr.dataset.id = idUnico;

                tr.innerHTML = `
            <td class="position-relative">
                <input type="text" class="form-control inputProducto" id="inputProducto${idUnico}" autocomplete="off" required>
                <input type="hidden" name="producto_id[]" id="producto_id${idUnico}" required>
                <div id="autocompleteProductoList${idUnico}" class="autocomplete-items"></div>
            </td>
            <td class="position-relative">
                <input type="text" class="form-control inputBodega" id="inputBodega${idUnico}" autocomplete="off" required>
                <input type="hidden" name="bodega_id[]" id="bodega_id${idUnico}" required>
                <div id="autocompleteBodegaList${idUnico}" class="autocomplete-items"></div>
            </td>
            <td><input type="number" class="form-control cantidad" name="cantidad[]" min="1" value="1" required></td>
            <td><input type="text" class="form-control precio" name="precio[]" min="0" step="0.01" value="0" required></td>
            <td><input type="text" class="form-control total" readonly></td>
            <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">🗑</button></td>
            `;

                tbody.appendChild(tr);

                // Autocomplete producto
                autocomplete(
                    document.getElementById(`inputProducto${idUnico}`),
                    document.getElementById(`producto_id${idUnico}`),
                    document.getElementById(`autocompleteProductoList${idUnico}`),
                    './autocomplete/autocomplete_productos.php',
                    (item) => {
                        const precioInput = tr.querySelector('.precio');
                        precioInput.value = item.precio || 0;
                        calcularFila(tr);
                    }
                );

                // Autocomplete bodega
                autocomplete(
                    document.getElementById(`inputBodega${idUnico}`),
                    document.getElementById(`bodega_id${idUnico}`),
                    document.getElementById(`autocompleteBodegaList${idUnico}`),
                    '/autocomplete/autocomplete_bodegas.php'
                );

                // Eventos para recalcular totales cuando cambien cantidad o precio
                tr.querySelectorAll('.cantidad, .precio').forEach(input => {
                    input.addEventListener('input', () => calcularFila(tr));
                });

                // Botón eliminar fila
                tr.querySelector('.eliminar-fila').addEventListener('click', () => {
                    tr.remove();
                    calcularTotales();
                });
            }

            // Calcular total de fila
            function calcularFila(tr) {
                const cantidad = parseFloat(tr.querySelector('.cantidad').value) || 0;
                const precioConIVA = parseFloat(tr.querySelector('.precio').value) || 0;

                // Convertimos el precio con IVA a precio sin IVA
                const precioSinIVA = precioConIVA / 1.12;

                const total = cantidad * precioSinIVA;

                tr.querySelector('.total').value = total.toFixed(2);
                calcularTotales();
            }

            // Calcular totales generales
            function calcularTotales() {
                const filas = document.querySelectorAll('#detalleBody tr');
                let subtotal = 0;

                filas.forEach(tr => {
                    subtotal += parseFloat(tr.querySelector('.total').value) || 0; 
                });

                const iva = subtotal * 0.12;
                const total = subtotal + iva;

                const form = document.getElementById('formNuevoIngreso');
                form.subtotal.value = subtotal.toFixed(2);
                form.gravada.value = subtotal.toFixed(2); 
                form.iva.value = iva.toFixed(2);
                form.total.value = total.toFixed(2);
            }

            // Cargar ingreso para editar
            function cargarIngresoParaEditar(id) {
                fetch(`/controllers/ingresoController.php?action=obtener&id=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        console.log(data);
                        if (!data) {
                            Swal.fire('Error', 'No se encontró el ingreso.', 'error');
                            return;
                        }

                        const form = document.getElementById('formNuevoIngreso');

                        form.fecha.value = data.fecha.split(' ')[0]; // Separa la fecha de la hora
                        form.numero.value = data.numero;
                        document.getElementById('proveedor_id').value = data.proveedor_id;
                        document.getElementById('inputProveedor').value = data.proveedor;

                        const tbody = document.getElementById('detalleBody');
                        tbody.innerHTML = '';

                        data.detalles.forEach(det => {
                            agregarFilaDetalle(); // Crea la fila
                            const tr = tbody.lastElementChild;

                            // Rellenar directamente desde el tr, sin usar IDs individuales
                            tr.querySelector('.inputProducto').value = det.producto_nombre;
                            tr.querySelector('[name="producto_id[]"]').value = det.producto_id;

                            tr.querySelector('.inputBodega').value = det.bodega_nombre;
                            tr.querySelector('[name="bodega_id[]"]').value = det.bodega_id;

                            tr.querySelector('.cantidad').value = det.cantidad;
                            tr.querySelector('.precio').value = det.precio;

                            calcularFila(tr); // Calcula total de la fila
                        });


                        form.subtotal.value = parseFloat(data.subtotal).toFixed(2);
                        form.gravada.value = parseFloat(data.gravada).toFixed(2);
                        form.iva.value = parseFloat(data.iva).toFixed(2);
                        form.total.value = parseFloat(data.total).toFixed(2);

                        form.observaciones.value = data.observaciones || '';

                        form.dataset.editingId = id;

                        const modal = new bootstrap.Modal(document.getElementById('modalNuevoIngreso'));
                        modal.show();
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Error al cargar el ingreso.', 'error');
                    });
            }

            // Mostrar ingresos
            function mostrarIngresos() {
                fetch('/controllers/ingresoController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        console.log(data);
                        if ($.fn.DataTable.isDataTable('#tablaIngresos')) {
                            $('#tablaIngresos').DataTable().clear().destroy();
                        }
                        const tbody = document.getElementById('tbodyIngresos');
                        tbody.innerHTML = data.map(row => `
                    <tr>
                        <td>${row.numero}</td>
                        <td>${row.fecha}</td>
                        <td>${row.proveedor}</td>
                        <td>Q ${row.total}</td>
                        <td>
                            <button class="btn btn-sm btn-warning btnEditar" data-id="${row.id}">✏️</button>
                            <button class="btn btn-sm btn-danger btnEliminar" data-id="${row.id}">🗑</button>
                        </td>
                    </tr>
                `).join('');

                        $('#tablaIngresos').DataTable({
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                            },
                            pageLength: 5,
                            lengthMenu: [5, 10, 25, 50, 100]
                        });

                        // Evento eliminar ingreso
                        document.querySelectorAll('.btnEliminar').forEach(btn => {
                            btn.addEventListener('click', () => {
                                const id = btn.dataset.id;
                                Swal.fire({
                                    title: '¿Eliminar ingreso?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Sí, eliminar',
                                    cancelButtonText: 'Cancelar'
                                }).then(result => {
                                    if (result.isConfirmed) {
                                        fetch('/controllers/ingresoController.php?action=eliminar', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/x-www-form-urlencoded'
                                                },
                                                body: `id=${id}`
                                            })
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.success) {
                                                    Swal.fire('Eliminado', '', 'success');
                                                    mostrarIngresos();
                                                } else {
                                                    Swal.fire('Error', 'No se pudo eliminar.', 'error');
                                                }
                                            });
                                    }
                                });
                            });
                        });

                        // Evento editar ingreso
                        document.querySelectorAll('.btnEditar').forEach(btn => {
                            btn.addEventListener('click', () => {
                                const id = btn.dataset.id;
                                cargarIngresoParaEditar(id);
                            });
                        });
                    });
            }

            // Eventos e inicializaciones
            document.getElementById('btnAgregarDetalle').addEventListener('click', agregarFilaDetalle);
            document.getElementById('btnMostrarIngresos').addEventListener('click', mostrarIngresos);

            // Guardar o editar ingreso
            document.getElementById('formNuevoIngreso').addEventListener('submit', function(e) {
                e.preventDefault();

                const form = this;

                if (!proveedorId.value) {
                    Swal.fire('Error', 'Debe seleccionar un proveedor válido.', 'error');
                    return;
                }

                const filas = document.querySelectorAll('#detalleBody tr');
                if (filas.length === 0) {
                    Swal.fire('Error', 'Debe agregar al menos un detalle.', 'error');
                    return;
                }

                const detalles = [];
                for (const tr of filas) {
                    const producto_id = tr.querySelector('input[name="producto_id[]"]').value;
                    const bodega_id = tr.querySelector('input[name="bodega_id[]"]').value;
                    const cantidad = tr.querySelector('.cantidad').value;
                    const precio = tr.querySelector('.precio').value;

                    if (!producto_id || !bodega_id) {
                        Swal.fire('Error', 'Debe seleccionar producto y bodega válidos en todas las filas.', 'error');
                        return;
                    }

                    detalles.push({
                        producto_id,
                        bodega_id,
                        cantidad,
                        precio
                    });
                }

                ['subtotal', 'gravada', 'iva', 'total'].forEach(campo => {
                    const val = form[campo].value.replace(/,/g, '');
                    form[campo].value = parseFloat(val).toFixed(2);
                });

                const action = form.dataset.editingId ? 'editar' : 'guardar';
                let formData = new FormData(form);
                formData.append('detalles', JSON.stringify(detalles));
                if (form.dataset.editingId) {
                    formData.append('id', form.dataset.editingId);
                }

                fetch(`/controllers/ingresoController.php?action=${action}`, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '¡Ingreso guardado!',
                                text: '¿Qué deseas hacer ahora?',
                                icon: 'success',
                                showCancelButton: true,
                                confirmButtonText: 'Agregar otro',
                                cancelButtonText: 'Ver listado',
                                reverseButtons: true
                            }).then((result) => {
                                const modalEl = document.getElementById('modalNuevoIngreso');
                                const modal = bootstrap.Modal.getInstance(modalEl);

                                if (result.isConfirmed) {
                                    // Reiniciar formulario y volver a abrir modal para agregar otro ingreso
                                    form.reset();
                                    document.getElementById('detalleBody').innerHTML = '';
                                    proveedorId.value = '';
                                    inputProveedor.value = '';
                                    if (!modal._isShown) {
                                        modal.show();
                                    }
                                } else {
                                    // Ocultar modal y mostrar listado
                                    modal.hide();
                                    setTimeout(() => {
                                        document.body.classList.remove('modal-open');
                                        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
                                    }, 300);
                                    mostrarIngresos();
                                }
                            });
                        } else {
                            Swal.fire('Error', `No se pudo ${action === 'editar' ? 'actualizar' : 'guardar'}.`, 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Error de comunicación con el servidor.', 'error');
                    });
            });

        }

        const ingresosLink = document.querySelector('a[href="#ingreso"]');
        if (ingresosLink) {
            ingresosLink.addEventListener('click', function(e) {
                e.preventDefault();
                loadIngresos();
            });
        }
    });