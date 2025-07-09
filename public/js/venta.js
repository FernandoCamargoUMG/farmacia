document.addEventListener('DOMContentLoaded', function() {
        function loadEgresos() {
            const content = document.getElementById('dynamic-content');
            content.innerHTML = `

        <!-- Vista egresos -->
        <div class="egresos-view">
            <h2 class="mb-4"><i class="bi bi-cash-coin"></i> Ventas</h2>
            <div class="card shadow" style="max-width: 1100px; margin: 0 auto;">
                <div class="card-body">
                    <h5 class="card-title">Listado de Ventas</h5>
                    <button id="btnMostrarEgresos" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-eye"></i> Mostrar Ventas
                    </button>
                    <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevoEgreso">
                        <i class="bi bi-plus-circle"></i> Nueva Venta
                    </button>

                    <table id="tablaEgresos" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                
                                <th>Número</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyEgresos"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="modalNuevoEgreso" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content bg-white text-dark">
                    <form id="formNuevoEgreso">
                        <input type="hidden" name="sta" id="sta" value="0">
                        <input type="hidden" name="egreso_id" id="egreso_id" value="">
                        <div class="modal-header">
                            <h5 class="modal-title">Nueva Venta</h5>
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
                                    <label>Cliente</label>
                                    <input type="text" id="inputCliente" class="form-control" autocomplete="off" required>
                                    <input type="hidden" name="cliente_id" id="cliente_id" required>
                                    <div id="autocompleteClienteList" class="autocomplete-items"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <label>Forma de Pago</label>
                                    <select name="forma_pago" class="form-select" required>
                                        <option value="1">Efectivo</option>
                                        <option value="2">Cheque</option>
                                        <option value="3">Depósito</option>
                                        <option value="4">Tarjeta de Crédito</option>
                                        <option value="5">Tarjeta de Débito</option>
                                        <option value="6">Transferencia Bancaria</option>
                                    </select>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label>Tipo de Venta</label><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="opcionpago" value="0" checked>
                                        <label class="form-check-label">Contado</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="opcionpago" value="1">
                                        <label class="form-check-label">Crédito</label>
                                    </div>
                                </div>

                                <div class="col-md-4 mb-2">
                                    <label>Tipo</label><br>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="tipo_iva" value="gravada" checked>
                                        <label class="form-check-label">No Exenta</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="tipo_iva" value="exenta">
                                        <label class="form-check-label">Exenta</label>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <table class="table table-bordered" id="tablaDetallesEgreso">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Bodega</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Descuento</th>
                                        <th>Importe</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody id="detalleBodyEgreso"></tbody>
                            </table>
                            <button type="button" class="btn btn-secondary btn-sm" id="btnAgregarDetalleEgreso">+ Agregar</button>

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
                            <button type="submit" class="btn btn-secondary" data-estado="0">Guardar como Borrador</button>
                            <button type="submit" class="btn btn-success" data-estado="1">Emitir Venta</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>`;

            document.querySelector('[data-bs-target="#modalNuevoEgreso"]').addEventListener('click', function() {
                const form = document.getElementById('formNuevoEgreso');
                form.reset();
                form.removeAttribute('data-editing-id'); // quitar ID si venía de una edición
                document.getElementById('detalleBody').innerHTML = ''; // limpiar detalles
                document.getElementById('cliente_id').value = '';
                document.getElementById('inputCliente').value = '';
            });

            // --- AUTOCOMPLETE CLIENTE ---
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
                                div.textContent = item.label || item.nombre || '';
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
            autocomplete(
                document.getElementById('inputCliente'),
                document.getElementById('cliente_id'),
                document.getElementById('autocompleteClienteList'),
                '/autocomplete/autocomplete_clientes.php'
            );

            // --- DATOS GLOBALES ---
            window.productos = [];
            window.bodegas = [];

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

            // --- AGREGAR FILA DETALLE ---
            function agregarFilaDetalleEgreso() {
                const tbody = document.getElementById('detalleBodyEgreso');
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
                <td><input type="number" class="form-control precio" name="precio[]" min="0" step="0.01" value="0" required></td>
                <td><input type="number" class="form-control descuento" name="descuento[]" min="0" step="0.01" value="0" required></td>
                <td><input type="text" class="form-control total" readonly></td>
                <td><button type="button" class="btn btn-danger btn-sm eliminar-fila">🗑</button></td>`;

                tbody.appendChild(tr);

                autocomplete(
                    document.getElementById(`inputProducto${idUnico}`),
                    document.getElementById(`producto_id${idUnico}`),
                    document.getElementById(`autocompleteProductoList${idUnico}`),
                    '/autocomplete/autocomplete_productos.php',
                    (item) => {
                        tr.querySelector('.precio').value = item.precio || 0;
                        calcularFilaEgreso(tr);
                    }
                );

                autocomplete(
                    document.getElementById(`inputBodega${idUnico}`),
                    document.getElementById(`bodega_id${idUnico}`),
                    document.getElementById(`autocompleteBodegaList${idUnico}`),
                    '/autocomplete/autocomplete_bodegas.php'
                );

                tr.querySelectorAll('.cantidad, .precio, .descuento').forEach(input => {
                    input.addEventListener('input', () => calcularFilaEgreso(tr));
                });

                tr.querySelector('.eliminar-fila').addEventListener('click', () => {
                    tr.remove();
                    calcularTotalesEgreso();
                });
            }

            // --- CÁLCULO POR FILA ---
            function calcularFilaEgreso(tr) {
                const cantidad = parseFloat(tr.querySelector('.cantidad').value) || 0;
                const precioConIVA = parseFloat(tr.querySelector('.precio').value) || 0;
                const descuento = parseFloat(tr.querySelector('.descuento').value) || 0; // descuento en valor fijo sobre precio con IVA

                const tipoIva = document.querySelector('input[name="tipo_iva"]:checked')?.value || 'gravada';

                let importeConDescuento = (cantidad * precioConIVA) - descuento; // precio total con IVA ya descontado

                if (tipoIva === 'gravada') {
                    // Calculamos importe sin IVA restando el IVA proporcionalmente
                    var importeSinIVA = importeConDescuento / 1.12;
                } else {
                    var importeSinIVA = importeConDescuento; // exenta, no hay IVA
                }

                tr.querySelector('.total').value = importeSinIVA.toFixed(2);

                calcularTotalesEgreso();
            }

            // --- CÁLCULO TOTALES ---
            function calcularTotalesEgreso() {
                const filas = document.querySelectorAll('#detalleBodyEgreso tr');
                let subtotal = 0;

                filas.forEach(tr => {
                    subtotal += parseFloat(tr.querySelector('.total').value) || 0;
                });

                const tipoIva = document.querySelector('input[name="tipo_iva"]:checked')?.value || 'gravada';

                const gravada = tipoIva === 'gravada' ? subtotal : subtotal;
                const iva = tipoIva === 'gravada' ? gravada * 0.12 : 0;
                const total = gravada + iva;

                const form = document.getElementById('formNuevoEgreso');
                form.subtotal.value = gravada.toFixed(2);
                form.gravada.value = gravada.toFixed(2);
                form.iva.value = iva.toFixed(2);
                form.total.value = total.toFixed(2);
            }

            // --- MOSTRAR EGRESOS (LISTADO) ---
            function mostrarEgresos() {
                fetch('/controllers/egresoController.php?action=listar')
                    .then(res => res.json())
                    .then(data => {
                        console.log(data);
                        if ($.fn.DataTable.isDataTable('#tablaEgresos')) {
                            $('#tablaEgresos').DataTable().clear().destroy();
                        }
                        const tbody = document.getElementById('tbodyEgresos');
                        tbody.innerHTML = '';
                        data.forEach(egreso => {
                            const tr = document.createElement('tr');
                            const textoEstado = parseInt(egreso.sta) === 0 ? 'Borrador' : 'Emitido';

                            tr.innerHTML = `
                            
                            <td>${egreso.numero}</td>
                            <td>${egreso.fecha}</td>
                            <td>${egreso.cliente_nombre}</td>
                            <td>${parseFloat(egreso.total).toFixed(2)}</td>
                            <td>${textoEstado}</td>
                            <td>
                                <button class="btn btn-primary btn-sm btn-editar" data-id="${egreso.id}"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-danger btn-sm btn-eliminar" data-id="${egreso.id}"><i class="bi bi-trash"></i></button>
                            </td>`;

                            tbody.appendChild(tr);
                        });

                        $('#tablaEgresos').DataTable({
                            language: {
                                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                            },
                            pageLength: 5,
                            lengthMenu: [5, 10, 25, 50, 100]
                        });

                        // Eventos editar
                        tbody.querySelectorAll('.btn-editar').forEach(btn => {
                            btn.addEventListener('click', () => {
                                const id = btn.getAttribute('data-id');
                                editarEgreso(id);
                            });
                        });

                        // Eventos eliminar
                        tbody.querySelectorAll('.btn-eliminar').forEach(btn => {
                            btn.addEventListener('click', () => {
                                const id = btn.getAttribute('data-id');
                                eliminarEgreso(id);
                            });
                        });
                    });
            }

            // --- EDITAR EGRESO ---
            function editarEgreso(id) {
                fetch(`/controllers/egresoController.php?action=obtener&id=${id}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.id) { // cambiar esta línea para detectar éxito
                            const form = document.getElementById('formNuevoEgreso');
                            const egreso = data; // aquí usas data directamente

                            form.reset();

                            form.egreso_id.value = egreso.id;
                            form.fecha.value = egreso.fecha.split(' ')[0]; // si quieres solo la fecha sin hora
                            form.numero.value = egreso.numero;
                            document.getElementById('inputCliente').value = egreso.cliente_nombre;
                            form.cliente_id.value = egreso.cliente_id;
                            form.forma_pago.value = egreso.forma_pago;

                            // Radios contado/crédito
                            form.querySelectorAll('input[name="opcionpago"]').forEach(radio => {
                                radio.checked = radio.value == egreso.opcionpago;
                            });

                            // Radios tipo IVA
                            form.querySelectorAll('input[name="tipo_iva"]').forEach(radio => {
                                radio.checked = radio.value == egreso.tipo_iva;
                            });

                            // Limpiar y cargar detalles
                            const tbody = document.getElementById('detalleBodyEgreso');
                            tbody.innerHTML = '';
                            egreso.detalles.forEach(item => {
                                agregarFilaDetalleEgreso();
                                const tr = tbody.lastElementChild;
                                tr.querySelector('input.inputProducto').value = item.producto_nombre;
                                tr.querySelector('input[name="producto_id[]"]').value = item.producto_id;
                                tr.querySelector('input.inputBodega').value = item.bodega_nombre;
                                tr.querySelector('input[name="bodega_id[]"]').value = item.bodega_id;
                                tr.querySelector('.cantidad').value = item.cantidad;
                                tr.querySelector('.precio').value = item.precio;
                                tr.querySelector('.descuento').value = item.descuento;
                                tr.querySelector('.total').value = item.importe || (item.precio * item.cantidad).toFixed(2);
                                calcularFilaEgreso(tr);
                            });

                            form.subtotal.value = egreso.subtotal;
                            form.gravada.value = egreso.gravada;
                            form.iva.value = egreso.iva;
                            form.total.value = egreso.total;
                            form.observaciones.value = egreso.observaciones;
                            form.sta.value = egreso.sta;

                            // Dejamos siempre editable independientemente de 'sta'
                            form.querySelectorAll('input, select, textarea, button').forEach(el => {
                                // No deshabilites botones para cerrar modal o guardar
                                if (el.type === 'button' && el.classList.contains('btn-close')) return;
                                el.disabled = false;
                            });

                            document.querySelectorAll('#detalleBodyEgreso .eliminar-fila').forEach(btn => {
                                btn.disabled = false;
                            });

                            const modal = new bootstrap.Modal(document.getElementById('modalNuevoEgreso'));
                            modal.show();

                        } else {
                            Swal.fire('Error', 'No se pudo cargar la venta para editar.', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Error al obtener los datos.', 'error');
                    });
            }


            // --- ELIMINAR EGRESO ---
            function eliminarEgreso(id) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "No podrás revertir esta acción",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('/controllers/egresoController.php?action=eliminar', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `id=${encodeURIComponent(id)}`
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Eliminado', 'La venta ha sido eliminada.', 'success');
                                    mostrarEgresos();
                                } else {
                                    Swal.fire('Error', 'No se pudo eliminar la venta.', 'error');
                                }
                            })
                            .catch(() => Swal.fire('Error', 'Error en el servidor.', 'error'));
                    }
                });
            }


            // --- EVENTOS ---
            document.getElementById('btnMostrarEgresos').addEventListener('click', mostrarEgresos);
            document.getElementById('btnAgregarDetalleEgreso').addEventListener('click', agregarFilaDetalleEgreso);

            // Submit formulario (Guardar o Emitir)
            // SUBMIT FORMULARIO (GUARDAR O EDITAR)
            document.getElementById('formNuevoEgreso').addEventListener('submit', function(e) {
                e.preventDefault();

                const form = e.target;
                const formData = new FormData(form);

                // Capturar el botón que disparó el submit para saber el estado
                const submitter = e.submitter;
                const estado = submitter ? submitter.getAttribute('data-estado') : '0';
                formData.set('sta', estado);

                const egresoId = form.egreso_id.value.trim();

                if (document.querySelectorAll('#detalleBodyEgreso tr').length === 0) {
                    return Swal.fire('Error', 'Agrega al menos un producto.', 'error');
                }

                const filasDetalle = document.querySelectorAll('#detalleBodyEgreso tr');

                // Construir array detalles validando cada fila
                const detallesArray = [];
                for (const tr of filasDetalle) {
                    const producto_id = tr.querySelector('input[name="producto_id[]"]').value.trim();
                    const bodega_id = tr.querySelector('input[name="bodega_id[]"]').value.trim();
                    const cantidad = tr.querySelector('.cantidad').value.trim();
                    const precio = tr.querySelector('.precio').value.trim();
                    const descuento = tr.querySelector('.descuento').value.trim();

                    if (!producto_id || !bodega_id) {
                        return Swal.fire('Error', 'Debe seleccionar producto y bodega válidos en todas las filas.', 'error');
                    }

                    if (parseFloat(cantidad) <= 0 || isNaN(parseFloat(cantidad))) {
                        return Swal.fire('Error', 'La cantidad debe ser mayor que cero en todas las filas.', 'error');
                    }
                    if (parseFloat(precio) < 0 || isNaN(parseFloat(precio))) {
                        return Swal.fire('Error', 'El precio no puede ser negativo en ninguna fila.', 'error');
                    }
                    if (parseFloat(descuento) < 0 || isNaN(parseFloat(descuento))) {
                        return Swal.fire('Error', 'El descuento no puede ser negativo en ninguna fila.', 'error');
                    }

                    detallesArray.push({
                        producto_id,
                        bodega_id,
                        cantidad: parseFloat(cantidad),
                        precio: parseFloat(precio),
                        descuento: parseFloat(descuento)
                    });
                }

                formData.set('detalles', JSON.stringify(detallesArray));
                formData.set('id', egresoId); // ¡AGREGAR ESTA LÍNEA!
                // Cambiar URL según sea nuevo o editar
                const url = egresoId ? '/controllers/egresoController.php?action=editar' : '/controllers/egresoController.php?action=guardar';

                fetch(url, {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('¡Éxito!', data.message || 'Guardado correctamente.', 'success');
                            form.reset();
                            document.getElementById('detalleBodyEgreso').innerHTML = '';
                            mostrarEgresos();
                            bootstrap.Modal.getInstance(document.getElementById('modalNuevoEgreso')).hide();
                        } else {
                            Swal.fire('Error', data.message || 'Error al guardar.', 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error', 'Error en el servidor.', 'error'));
            });

            // Eventos iniciales
            document.getElementById('btnMostrarEgresos').addEventListener('click', mostrarEgresos);
            document.getElementById('btnAgregarDetalleEgreso').addEventListener('click', agregarFilaDetalleEgreso);

            // Mostrar listado inicial al cargar la vista
            //mostrarEgresos();
        }
        const egresosLink = document.querySelector('a[href="#venta"]');
        if (egresosLink) {
            egresosLink.addEventListener('click', function(e) {
                e.preventDefault();
                loadEgresos();
            });
        }
    });