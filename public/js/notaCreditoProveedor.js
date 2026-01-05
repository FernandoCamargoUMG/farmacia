document.addEventListener('DOMContentLoaded', function () {
    // Variable global para compras pendientes
    window.comprasPendientes = [];

    function loadNotasCreditoProveedor() {
        const content = document.getElementById('dynamic-content');
        content.innerHTML = `
        <div class="notas-credito-proveedor-view">
            <h2 class="mb-4"><i class="bi bi-file-earmark-text"></i> Notas de Crédito - Proveedores</h2>
            <div class="card shadow" style="max-width: 1400px; margin: 0 auto;">
                <div class="card-body">
                    <h5 class="card-title">Listado de Notas de Crédito</h5>
                    <button id="btnMostrarNotasProveedor" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-eye"></i> Mostrar Notas
                    </button>
                    <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevaNotaProveedor">
                        <i class="bi bi-plus-circle"></i> Nueva Nota de Crédito
                    </button>

                    <table id="tablaNotasCreditoProveedor" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th>Proveedor</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyNotasProveedor"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Nueva Nota Proveedor -->
        <div class="modal fade" id="modalNuevaNotaProveedor" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content bg-white text-dark">
                    <form id="formNuevaNotaProveedor">
                        <div class="modal-header">
                            <h5 class="modal-title">Nueva Nota de Crédito - Proveedor</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label>Número</label>
                                    <input type="text" name="numero" class="form-control" readonly>
                                    <small class="text-muted">Se genera automáticamente</small>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label>Fecha</label>
                                    <input type="date" name="fecha" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-2 position-relative">
                                    <label>Proveedor</label>
                                    <input type="text" id="inputProveedor" class="form-control" autocomplete="off" required>
                                    <input type="hidden" name="proveedor_id" id="proveedor_id" required>
                                    <div id="autocompleteProveedorList" class="autocomplete-items"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label>Tipo IVA</label><br>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_iva" value="gravada" checked>
                                        <label class="form-check-label">No Exenta (12% IVA)</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_iva" value="exenta">
                                        <label class="form-check-label">Exenta (0% IVA)</label>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <label>Forma de Pago</label>
                                    <select name="forma_pago" class="form-select" required>
                                        <option value="">Seleccione...</option>
                                        <option value="1">Efectivo</option>
                                        <option value="2">Cheque</option>
                                        <option value="3">Depósito</option>
                                        <option value="6">Transferencia Bancaria</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label>Referencia (Núm. Cheque/Transferencia/Depósito)</label>
                                    <input type="text" name="referencia" class="form-control">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label>Banco</label>
                                    <input type="text" name="banco" class="form-control">
                                </div>
                            </div>

                            <hr>

                            <!-- Sección de compras pendientes -->
                            <h6 class="mb-3">Compras Pendientes de Pago</h6>
                            <button type="button" class="btn btn-sm btn-info mb-2" id="btnCargarCompras">
                                <i class="bi bi-arrow-repeat"></i> Cargar Compras del Proveedor
                            </button>
                            <table class="table table-bordered table-sm" id="tablaComprasPendientes">
                                <thead>
                                    <tr>
                                        <th>Seleccionar</th>
                                        <th>Compra</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Abonos</th>
                                        <th>Saldo</th>
                                        <th>Monto Abono</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyComprasPendientes"></tbody>
                            </table>

                            <hr>

                            <div class="row">
                                <div class="col-md-3"><label>Subtotal</label><input type="text" name="subtotal" class="form-control" readonly></div>
                                <div class="col-md-3"><label>Gravada</label><input type="text" name="gravada" class="form-control" readonly></div>
                                <div class="col-md-3"><label>IVA</label><input type="text" name="iva" class="form-control" readonly></div>
                                <div class="col-md-3"><label>Total</label><input type="text" name="total" class="form-control" readonly></div>
                            </div>

                            <div class="mt-3">
                                <label>Observaciones</label>
                                <textarea name="observaciones" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" id="btnGuardarBorradorProveedor">Guardar como Borrador</button>
                            <button type="button" class="btn btn-success" id="btnEmitirNotaProveedor">Emitir Nota de Crédito</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        `;

        // Inicializar fecha actual
        document.querySelector('[name="fecha"]').valueAsDate = new Date();

        // Generar número automático
        generarNumeroProveedor();

        // Event listeners
        document.getElementById('btnMostrarNotasProveedor').addEventListener('click', mostrarNotasProveedor);
        document.querySelector('[data-bs-target="#modalNuevaNotaProveedor"]').addEventListener('click', limpiarFormularioProveedor);
        document.getElementById('btnGuardarBorradorProveedor').addEventListener('click', () => guardarNotaProveedor(0));
        document.getElementById('btnEmitirNotaProveedor').addEventListener('click', () => guardarNotaProveedor(1));
        document.getElementById('btnCargarCompras').addEventListener('click', cargarComprasPendientes);

        // Cambio de tipo IVA
        document.querySelectorAll('[name="tipo_iva"]').forEach(radio => {
            radio.addEventListener('change', calcularTotalesProveedor);
        });

        // Autocomplete de proveedores
        setupAutocompleteProveedor();
    }

    function setupAutocompleteProveedor() {
        const input = document.getElementById('inputProveedor');
        const lista = document.getElementById('autocompleteProveedorList');
        const hiddenInput = document.getElementById('proveedor_id');

        input.addEventListener('input', async function() {
            const val = this.value.trim();
            lista.innerHTML = '';

            if (val.length < 2) return;

            try {
                const response = await fetch(`../autocomplete/autocomplete_proveedores.php?q=${encodeURIComponent(val)}`);
                const proveedores = await response.json();

                proveedores.forEach(proveedor => {
                    const div = document.createElement('div');
                    div.textContent = proveedor.label;
                    div.addEventListener('click', function() {
                        input.value = proveedor.label;
                        hiddenInput.value = proveedor.id;
                        lista.innerHTML = '';
                        cargarComprasPendientes();
                    });
                    lista.appendChild(div);
                });
            } catch (error) {
                console.error('Error en autocomplete:', error);
            }
        });
    }

    async function cargarComprasPendientes() {
        const proveedorId = document.getElementById('proveedor_id').value;

        if (!proveedorId) {
            Swal.fire('Error', 'Seleccione un proveedor primero', 'error');
            return;
        }

        try {
            const response = await fetch(`../controllers/notaCreditoProveedorController.php?action=compras_pendientes&proveedor_id=${proveedorId}`);
            const compras = await response.json();

            window.comprasPendientes = compras;

            const tbody = document.getElementById('tbodyComprasPendientes');
            tbody.innerHTML = '';

            if (compras.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay compras pendientes</td></tr>';
                return;
            }

            compras.forEach(c => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input check-compra" data-compra-id="${c.compra_id}">
                    </td>
                    <td>${c.compra_numero}</td>
                    <td>${c.compra_fecha}</td>
                    <td class="text-end">Q ${parseFloat(c.compra_total).toFixed(2)}</td>
                    <td class="text-end">Q ${parseFloat(c.total_abonos).toFixed(2)}</td>
                    <td class="text-end">Q ${parseFloat(c.saldo_pendiente).toFixed(2)}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm input-monto-abono" 
                               data-compra-id="${c.compra_id}" 
                               step="0.01" min="0" max="${c.saldo_pendiente}" 
                               placeholder="0.00">
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Event listeners para checkboxes y montos
            document.querySelectorAll('.check-compra').forEach(check => {
                check.addEventListener('change', function() {
                    const compraId = this.dataset.compraId;
                    const inputMonto = document.querySelector(`.input-monto-abono[data-compra-id="${compraId}"]`);
                    
                    if (this.checked) {
                        const compra = window.comprasPendientes.find(c => c.compra_id == compraId);
                        inputMonto.value = parseFloat(compra.saldo_pendiente).toFixed(2);
                    } else {
                        inputMonto.value = '';
                    }
                    calcularTotalesProveedor();
                });
            });

            document.querySelectorAll('.input-monto-abono').forEach(input => {
                input.addEventListener('input', calcularTotalesProveedor);
            });

        } catch (error) {
            console.error('Error cargando compras:', error);
            Swal.fire('Error', 'No se pudieron cargar las compras pendientes', 'error');
        }
    }

    function calcularTotalesProveedor() {
        const tipoIva = document.querySelector('[name="tipo_iva"]:checked').value;
        let totalConIva = 0;

        // Sumar los montos de abono seleccionados (estos vienen con IVA incluido del total de la compra)
        document.querySelectorAll('.check-compra:checked').forEach(check => {
            const compraId = check.dataset.compraId;
            const inputMonto = document.querySelector(`.input-monto-abono[data-compra-id="${compraId}"]`);
            const monto = parseFloat(inputMonto.value) || 0;
            totalConIva += monto;
        });

        const form = document.getElementById('formNuevaNotaProveedor');
        
        if (tipoIva === 'exenta') {
            // Si es exenta, el total es el subtotal (sin IVA)
            form.subtotal.value = totalConIva.toFixed(2);
            form.gravada.value = '0.00';
            form.iva.value = '0.00';
            form.total.value = totalConIva.toFixed(2);
        } else {
            // Si no es exenta, el monto ingresado YA incluye IVA (viene del total de la compra)
            // Hay que separar: si total = subtotal + (subtotal * 0.12) = subtotal * 1.12
            // Entonces: subtotal = total / 1.12
            const subtotal = totalConIva / 1.12;
            const iva = totalConIva - subtotal;
            
            form.subtotal.value = subtotal.toFixed(2);
            form.gravada.value = subtotal.toFixed(2);
            form.iva.value = iva.toFixed(2);
            form.total.value = totalConIva.toFixed(2);
        }
    }

    async function guardarNotaProveedor(sta) {
        const form = document.getElementById('formNuevaNotaProveedor');
        const proveedorId = form.proveedor_id.value;
        const editandoId = form.dataset.editandoId;

        if (!proveedorId) {
            Swal.fire('Error', 'Seleccione un proveedor', 'error');
            return;
        }

        // Validar detalles
        const detalles = [];
        document.querySelectorAll('.check-compra:checked').forEach(check => {
            const compraId = check.dataset.compraId;
            const inputMonto = document.querySelector(`.input-monto-abono[data-compra-id="${compraId}"]`);
            const monto = parseFloat(inputMonto.value) || 0;

            if (monto > 0) {
                detalles.push({
                    ingreso_cab_id: compraId,
                    monto_abono: monto,
                    observaciones: ''
                });
            }
        });

        if (detalles.length === 0) {
            Swal.fire('Error', 'Debe seleccionar al menos una compra y especificar el monto de abono', 'error');
            return;
        }

        const formData = new FormData();
        if (editandoId) {
            formData.append('id', editandoId);
        }
        formData.append('proveedor_id', proveedorId);
        formData.append('numero', form.numero.value);
        formData.append('fecha_local', form.fecha.value + ' ' + new Date().toTimeString().split(' ')[0]);
        formData.append('subtotal', form.subtotal.value);
        formData.append('gravada', form.gravada.value);
        formData.append('iva', form.iva.value);
        formData.append('total', form.total.value);
        formData.append('forma_pago', form.forma_pago.value || '');
        formData.append('referencia', form.referencia.value || '');
        formData.append('banco', form.banco.value || '');
        formData.append('sta', sta);
        formData.append('observaciones', form.observaciones.value || '');
        formData.append('detalles', JSON.stringify(detalles));

        try {
            const response = await fetch('../controllers/notaCreditoProveedorController.php?action=guardar', {
                method: 'POST',
                body: formData
            });

            const resultado = await response.json();

            if (resultado.success) {
                const mensaje = editandoId ? 'actualizada' : 'guardada';
                Swal.fire('Éxito', `Nota de crédito ${mensaje} correctamente`, 'success');
                const modalElement = document.getElementById('modalNuevaNotaProveedor');
                bootstrap.Modal.getInstance(modalElement).hide();
                limpiarFormularioProveedor(); // Limpiar después de guardar
                mostrarNotasProveedor();
            } else {
                Swal.fire('Error', resultado.error || 'No se pudo guardar la nota', 'error');
            }
        } catch (error) {
            console.error('Error guardando nota:', error);
            Swal.fire('Error', 'Error al guardar la nota de crédito', 'error');
        }
    }

    async function mostrarNotasProveedor() {
        try {
            const response = await fetch('../controllers/notaCreditoProveedorController.php?action=listar');
            const notas = await response.json();

            const tbody = document.getElementById('tbodyNotasProveedor');
            tbody.innerHTML = '';

            if (notas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center">No hay notas de crédito registradas</td></tr>';
                return;
            }

            notas.forEach(nota => {
                const estadoBadge = nota.sta == 1 
                    ? '<span class="badge bg-success">Emitido</span>' 
                    : '<span class="badge bg-secondary">Borrador</span>';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${nota.numero}</td>
                    <td>${nota.fecha}</td>
                    <td>${nota.proveedor}</td>
                    <td class="text-end">Q ${parseFloat(nota.total).toFixed(2)}</td>
                    <td>${estadoBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="verDetalleNotaProveedor(${nota.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editarNotaProveedor(${nota.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        ${nota.sta == 0 ? `
                            <button class="btn btn-sm btn-danger" onclick="eliminarNotaProveedor(${nota.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        ` : ''}
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } catch (error) {
            console.error('Error cargando notas:', error);
            Swal.fire('Error', 'No se pudieron cargar las notas de crédito', 'error');
        }
    }

    window.verDetalleNotaProveedor = async function(id) {
        try {
            const response = await fetch(`../controllers/notaCreditoProveedorController.php?action=obtener&id=${id}`);
            const nota = await response.json();

            let html = `
                <div class="card">
                    <div class="card-body">
                        <h5>Nota de Crédito: ${nota.numero}</h5>
                        <p><strong>Proveedor:</strong> ${nota.proveedor}</p>
                        <p><strong>Fecha:</strong> ${nota.fecha}</p>
                        <p><strong>Total:</strong> Q ${parseFloat(nota.total).toFixed(2)}</p>
                        
                        ${nota.detalles && nota.detalles.length > 0 ? `
                            <h6 class="mt-3">Compras Aplicadas:</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Compra</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Abono</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${nota.detalles.map(d => `
                                        <tr>
                                            <td>${d.compra_numero}</td>
                                            <td>${d.compra_fecha}</td>
                                            <td class="text-end">Q ${parseFloat(d.compra_total).toFixed(2)}</td>
                                            <td class="text-end">Q ${parseFloat(d.monto_abono).toFixed(2)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        ` : ''}
                    </div>
                </div>
            `;

            Swal.fire({
                title: 'Detalle de Nota de Crédito',
                html: html,
                width: '800px',
                showCloseButton: true
            });
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudo cargar el detalle', 'error');
        }
    };

    window.editarNotaProveedor = async function(id) {
        try {
            const response = await fetch(`../controllers/notaCreditoProveedorController.php?action=obtener&id=${id}`);
            const nota = await response.json();

            // Llenar el formulario
            const form = document.getElementById('formNuevaNotaProveedor');
            form.numero.value = nota.numero;
            form.fecha.value = nota.fecha.split(' ')[0];
            form.proveedor_id.value = nota.proveedor_id;
            document.getElementById('inputProveedor').value = nota.proveedor;
            
            // Tipo IVA
            const tipoIva = parseFloat(nota.iva) > 0 ? 'gravada' : 'exenta';
            document.querySelector(`[name="tipo_iva"][value="${tipoIva}"]`).checked = true;
            
            form.forma_pago.value = nota.forma_pago || '';
            form.referencia.value = nota.referencia || '';
            form.banco.value = nota.banco || '';
            form.observaciones.value = nota.observaciones || '';
            
            // Cargar compras con los detalles
            if (nota.detalles && nota.detalles.length > 0) {
                // Construir la tabla manualmente con las compras de la nota
                const tbody = document.getElementById('tbodyComprasPendientes');
                tbody.innerHTML = '';
                
                nota.detalles.forEach(detalle => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input check-compra" 
                                   data-compra-id="${detalle.ingreso_cab_id}" checked>
                        </td>
                        <td>${detalle.compra_numero || 'N/A'}</td>
                        <td>${detalle.compra_fecha || 'N/A'}</td>
                        <td class="text-end">Q ${parseFloat(detalle.compra_total || 0).toFixed(2)}</td>
                        <td class="text-end">-</td>
                        <td class="text-end">-</td>
                        <td>
                            <input type="number" class="form-control form-control-sm input-monto-abono" 
                                   data-compra-id="${detalle.ingreso_cab_id}" 
                                   step="0.01" min="0"
                                   value="${detalle.monto_abono}"
                                   placeholder="0.00">
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                
                // Event listeners
                document.querySelectorAll('.check-compra').forEach(check => {
                    check.addEventListener('change', function() {
                        const compraId = this.dataset.compraId;
                        const inputMonto = document.querySelector(`.input-monto-abono[data-compra-id="${compraId}"]`);
                        if (!this.checked) {
                            inputMonto.value = '';
                        }
                        calcularTotalesProveedor();
                    });
                });
                
                document.querySelectorAll('.input-monto-abono').forEach(input => {
                    input.addEventListener('input', calcularTotalesProveedor);
                });
                
                calcularTotalesProveedor();
            } else {
                calcularTotalesProveedor();
            }
            
            // Guardar el ID para actualizar
            form.dataset.editandoId = id;
            
            // Abrir el modal
            const modal = new bootstrap.Modal(document.getElementById('modalNuevaNotaProveedor'));
            modal.show();
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudo cargar la nota para editar', 'error');
        }
    };

    window.eliminarNotaProveedor = async function(id) {
        const result = await Swal.fire({
            title: '¿Eliminar nota de crédito?',
            text: 'Solo se pueden eliminar notas en borrador',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('id', id);

                const response = await fetch('../controllers/notaCreditoProveedorController.php?action=eliminar', {
                    method: 'POST',
                    body: formData
                });

                const resultado = await response.json();

                if (resultado.success) {
                    Swal.fire('Eliminado', 'Nota de crédito eliminada correctamente', 'success');
                    mostrarNotasProveedor();
                } else {
                    Swal.fire('Error', resultado.error || 'No se pudo eliminar la nota', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al eliminar la nota', 'error');
            }
        }
    };

    function limpiarFormularioProveedor() {
        const form = document.getElementById('formNuevaNotaProveedor');
        form.reset();
        form.querySelector('[name="fecha"]').valueAsDate = new Date();
        document.getElementById('proveedor_id').value = '';
        document.getElementById('tbodyComprasPendientes').innerHTML = '';
        delete form.dataset.editandoId;
        generarNumeroProveedor();
        calcularTotalesProveedor();
    }

    async function generarNumeroProveedor() {
        try {
            const response = await fetch('../controllers/notaCreditoProveedorController.php?action=generar_numero');
            const data = await response.json();
            document.querySelector('[name="numero"]').value = data.numero;
        } catch (error) {
            console.error('Error generando número:', error);
        }
    }

    // Exponer función globalmente
    window.loadNotasCreditoProveedor = loadNotasCreditoProveedor;
});
