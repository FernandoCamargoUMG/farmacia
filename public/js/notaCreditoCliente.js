document.addEventListener('DOMContentLoaded', function () {
    // Variable global para facturas pendientes
    window.facturasPendientes = [];

    function loadNotasCreditoCliente() {
        const content = document.getElementById('dynamic-content');
        content.innerHTML = `
        <div class="notas-credito-view">
            <h2 class="mb-4"><i class="bi bi-file-earmark-text"></i> Notas de Crédito - Clientes</h2>
            <div class="card shadow" style="max-width: 1400px; margin: 0 auto;">
                <div class="card-body">
                    <h5 class="card-title">Listado de Notas de Crédito</h5>
                    <button id="btnMostrarNotas" class="btn btn-outline-primary mb-3">
                        <i class="bi bi-eye"></i> Mostrar Notas
                    </button>
                    <button class="btn btn-success mb-3 float-end" data-bs-toggle="modal" data-bs-target="#modalNuevaNota">
                        <i class="bi bi-plus-circle"></i> Nueva Nota de Crédito
                    </button>

                    <table id="tablaNotasCredito" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Tipo</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyNotas"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Nueva Nota -->
        <div class="modal fade" id="modalNuevaNota" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content bg-white text-dark">
                    <form id="formNuevaNota">
                        <div class="modal-header">
                            <h5 class="modal-title">Nueva Nota de Crédito</h5>
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
                                    <label>Cliente</label>
                                    <input type="text" id="inputCliente" class="form-control" autocomplete="off" required>
                                    <input type="hidden" name="cliente_id" id="cliente_id" required>
                                    <div id="autocompleteClienteList" class="autocomplete-items"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label>Tipo de Nota</label><br>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo" value="abono" checked id="tipoAbono">
                                        <label class="form-check-label" for="tipoAbono">Abono a Facturas</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo" value="anticipo" id="tipoAnticipo">
                                        <label class="form-check-label" for="tipoAnticipo">Anticipo (Saldo a favor)</label>
                                    </div>
                                </div>

                                <div class="col-md-3 mb-2">
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

                            <!-- Sección de facturas pendientes (solo si tipo=abono) -->
                            <div id="seccionFacturas">
                                <h6 class="mb-3">Facturas Pendientes de Pago</h6>
                                <button type="button" class="btn btn-sm btn-info mb-2" id="btnCargarFacturas">
                                    <i class="bi bi-arrow-repeat"></i> Cargar Facturas del Cliente
                                </button>
                                <table class="table table-bordered table-sm" id="tablaFacturasPendientes">
                                    <thead>
                                        <tr>
                                            <th>Seleccionar</th>
                                            <th>Factura</th>
                                            <th>Fecha</th>
                                            <th>Total</th>
                                            <th>Abonos</th>
                                            <th>Saldo</th>
                                            <th>Monto Abono</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyFacturasPendientes"></tbody>
                                </table>
                            </div>

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
                            <button type="button" class="btn btn-secondary" id="btnGuardarBorrador">Guardar como Borrador</button>
                            <button type="button" class="btn btn-success" id="btnEmitirNota">Emitir Nota de Crédito</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal Ver Estado de Cuenta -->
        <div class="modal fade" id="modalEstadoCuenta" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Estado de Cuenta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div id="estadoCuentaContent"></div>
                    </div>
                </div>
            </div>
        </div>
        `;

        // Inicializar fecha actual
        document.querySelector('[name="fecha"]').valueAsDate = new Date();

        // Generar número automático
        generarNumero();

        // Event listeners
        document.getElementById('btnMostrarNotas').addEventListener('click', mostrarNotas);
        document.querySelector('[data-bs-target="#modalNuevaNota"]').addEventListener('click', limpiarFormulario);
        document.getElementById('btnGuardarBorrador').addEventListener('click', () => guardarNota(0));
        document.getElementById('btnEmitirNota').addEventListener('click', () => guardarNota(1));
        document.getElementById('btnCargarFacturas').addEventListener('click', cargarFacturasPendientes);

        // Cambio de tipo de nota
        document.querySelectorAll('[name="tipo"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const seccionFacturas = document.getElementById('seccionFacturas');
                if (this.value === 'abono') {
                    seccionFacturas.style.display = 'block';
                } else {
                    seccionFacturas.style.display = 'none';
                    limpiarFacturas();
                }
            });
        });

        // Cambio de tipo IVA
        document.querySelectorAll('[name="tipo_iva"]').forEach(radio => {
            radio.addEventListener('change', calcularTotales);
        });

        // Autocomplete de clientes
        setupAutocompleteCliente();
    }

    function setupAutocompleteCliente() {
        const input = document.getElementById('inputCliente');
        const lista = document.getElementById('autocompleteClienteList');
        const hiddenInput = document.getElementById('cliente_id');

        input.addEventListener('input', async function() {
            const val = this.value.trim();
            lista.innerHTML = '';

            if (val.length < 2) return;

            try {
                const response = await fetch(`../autocomplete/autocomplete_clientes.php?q=${encodeURIComponent(val)}`);
                const clientes = await response.json();

                clientes.forEach(cliente => {
                    const div = document.createElement('div');
                    div.textContent = cliente.label;
                    div.addEventListener('click', function() {
                        input.value = cliente.label;
                        hiddenInput.value = cliente.id;
                        lista.innerHTML = '';
                        
                        // Si tipo=abono, cargar facturas automáticamente
                        const tipoAbono = document.getElementById('tipoAbono').checked;
                        if (tipoAbono) {
                            cargarFacturasPendientes();
                        }
                    });
                    lista.appendChild(div);
                });
            } catch (error) {
                console.error('Error en autocomplete:', error);
            }
        });
    }

    async function cargarFacturasPendientes() {
        const clienteId = document.getElementById('cliente_id').value;

        if (!clienteId) {
            Swal.fire('Error', 'Seleccione un cliente primero', 'error');
            return;
        }

        try {
            const response = await fetch(`../controllers/notaCreditoClienteController.php?action=facturas_pendientes&cliente_id=${clienteId}`);
            const facturas = await response.json();

            window.facturasPendientes = facturas;

            const tbody = document.getElementById('tbodyFacturasPendientes');
            tbody.innerHTML = '';

            if (facturas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay facturas pendientes</td></tr>';
                return;
            }

            facturas.forEach(f => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="text-center">
                        <input type="checkbox" class="form-check-input check-factura" data-factura-id="${f.factura_id}">
                    </td>
                    <td>${f.factura_numero}</td>
                    <td>${f.factura_fecha}</td>
                    <td class="text-end">Q ${parseFloat(f.factura_total).toFixed(2)}</td>
                    <td class="text-end">Q ${parseFloat(f.total_abonos).toFixed(2)}</td>
                    <td class="text-end">Q ${parseFloat(f.saldo_pendiente).toFixed(2)}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm input-monto-abono" 
                               data-factura-id="${f.factura_id}" 
                               step="0.01" min="0" max="${f.saldo_pendiente}" 
                               placeholder="0.00">
                    </td>
                `;
                tbody.appendChild(tr);
            });

            // Event listeners para checkboxes y montos
            document.querySelectorAll('.check-factura').forEach(check => {
                check.addEventListener('change', function() {
                    const facturaId = this.dataset.facturaId;
                    const inputMonto = document.querySelector(`.input-monto-abono[data-factura-id="${facturaId}"]`);
                    
                    if (this.checked) {
                        // Auto-llenar con saldo pendiente
                        const factura = window.facturasPendientes.find(f => f.factura_id == facturaId);
                        inputMonto.value = parseFloat(factura.saldo_pendiente).toFixed(2);
                    } else {
                        inputMonto.value = '';
                    }
                    calcularTotales();
                });
            });

            document.querySelectorAll('.input-monto-abono').forEach(input => {
                input.addEventListener('input', calcularTotales);
            });

        } catch (error) {
            console.error('Error cargando facturas:', error);
            Swal.fire('Error', 'No se pudieron cargar las facturas pendientes', 'error');
        }
    }

    function calcularTotales() {
        const tipo = document.querySelector('[name="tipo"]:checked').value;
        const tipoIva = document.querySelector('[name="tipo_iva"]:checked').value;

        let totalConIva = 0;

        if (tipo === 'abono') {
            // Sumar los montos de abono seleccionados (estos vienen con IVA incluido del total de la factura)
            document.querySelectorAll('.check-factura:checked').forEach(check => {
                const facturaId = check.dataset.facturaId;
                const inputMonto = document.querySelector(`.input-monto-abono[data-factura-id="${facturaId}"]`);
                const monto = parseFloat(inputMonto.value) || 0;
                totalConIva += monto;
            });
        }

        // Para anticipos, el usuario debe ingresar el subtotal manualmente en una próxima versión
        // Por ahora, si es anticipo, dejamos que lo ingresen en observaciones

        const form = document.getElementById('formNuevaNota');
        
        if (tipoIva === 'exenta') {
            // Si es exenta, el total es el subtotal (sin IVA)
            form.subtotal.value = totalConIva.toFixed(2);
            form.gravada.value = '0.00';
            form.iva.value = '0.00';
            form.total.value = totalConIva.toFixed(2);
        } else {
            // Si no es exenta, el monto ingresado YA incluye IVA (viene del total de la factura)
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

    async function guardarNota(sta) {
        const form = document.getElementById('formNuevaNota');
        const tipo = form.tipo.value;
        const clienteId = form.cliente_id.value;
        const editandoId = form.dataset.editandoId;

        if (!clienteId) {
            Swal.fire('Error', 'Seleccione un cliente', 'error');
            return;
        }

        // Validar detalles si es tipo=abono
        const detalles = [];
        if (tipo === 'abono') {
            document.querySelectorAll('.check-factura:checked').forEach(check => {
                const facturaId = check.dataset.facturaId;
                const inputMonto = document.querySelector(`.input-monto-abono[data-factura-id="${facturaId}"]`);
                const monto = parseFloat(inputMonto.value) || 0;

                if (monto > 0) {
                    detalles.push({
                        egreso_cab_id: facturaId,
                        monto_abono: monto,
                        observaciones: ''
                    });
                }
            });

            if (detalles.length === 0) {
                Swal.fire('Error', 'Debe seleccionar al menos una factura y especificar el monto de abono', 'error');
                return;
            }
        }

        const formData = new FormData();
        if (editandoId) {
            formData.append('id', editandoId);
        }
        formData.append('cliente_id', clienteId);
        formData.append('numero', form.numero.value);
        formData.append('fecha_local', form.fecha.value + ' ' + new Date().toTimeString().split(' ')[0]);
        formData.append('tipo', tipo);
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
            const response = await fetch('../controllers/notaCreditoClienteController.php?action=guardar', {
                method: 'POST',
                body: formData
            });

            const resultado = await response.json();

            if (resultado.success) {
                const mensaje = editandoId ? 'actualizada' : 'guardada';
                Swal.fire('Éxito', `Nota de crédito ${mensaje} correctamente`, 'success');
                const modalElement = document.getElementById('modalNuevaNota');
                bootstrap.Modal.getInstance(modalElement).hide();
                limpiarFormulario(); // Limpiar después de guardar
                mostrarNotas();
            } else {
                Swal.fire('Error', resultado.error || 'No se pudo guardar la nota', 'error');
            }
        } catch (error) {
            console.error('Error guardando nota:', error);
            Swal.fire('Error', 'Error al guardar la nota de crédito', 'error');
        }
    }

    async function mostrarNotas() {
        try {
            const response = await fetch('../controllers/notaCreditoClienteController.php?action=listar');
            const notas = await response.json();

            const tbody = document.getElementById('tbodyNotas');
            tbody.innerHTML = '';

            if (notas.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center">No hay notas de crédito registradas</td></tr>';
                return;
            }

            notas.forEach(nota => {
                const estadoBadge = nota.sta == 1 
                    ? '<span class="badge bg-success">Emitido</span>' 
                    : '<span class="badge bg-secondary">Borrador</span>';
                
                const tipoBadge = nota.tipo === 'anticipo'
                    ? '<span class="badge bg-info">Anticipo</span>'
                    : '<span class="badge bg-primary">Abono</span>';

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${nota.numero}</td>
                    <td>${nota.fecha}</td>
                    <td>${nota.cliente}</td>
                    <td>${tipoBadge}</td>
                    <td class="text-end">Q ${parseFloat(nota.total).toFixed(2)}</td>
                    <td>${estadoBadge}</td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="verDetalleNota(${nota.id})">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning" onclick="editarNota(${nota.id})">
                            <i class="bi bi-pencil"></i>
                        </button>
                        ${nota.sta == 0 ? `
                            <button class="btn btn-sm btn-danger" onclick="eliminarNota(${nota.id})">
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

    window.verDetalleNota = async function(id) {
        try {
            const response = await fetch(`../controllers/notaCreditoClienteController.php?action=obtener&id=${id}`);
            const nota = await response.json();

            let html = `
                <div class="card">
                    <div class="card-body">
                        <h5>Nota de Crédito: ${nota.numero}</h5>
                        <p><strong>Cliente:</strong> ${nota.cliente}</p>
                        <p><strong>Fecha:</strong> ${nota.fecha}</p>
                        <p><strong>Tipo:</strong> ${nota.tipo}</p>
                        <p><strong>Total:</strong> Q ${parseFloat(nota.total).toFixed(2)}</p>
                        
                        ${nota.detalles && nota.detalles.length > 0 ? `
                            <h6 class="mt-3">Facturas Aplicadas:</h6>
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Factura</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Abono</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${nota.detalles.map(d => `
                                        <tr>
                                            <td>${d.factura_numero}</td>
                                            <td>${d.factura_fecha}</td>
                                            <td class="text-end">Q ${parseFloat(d.factura_total).toFixed(2)}</td>
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

    window.editarNota = async function(id) {
        try {
            const response = await fetch(`../controllers/notaCreditoClienteController.php?action=obtener&id=${id}`);
            const nota = await response.json();

            // Llenar el formulario
            const form = document.getElementById('formNuevaNota');
            form.numero.value = nota.numero;
            form.fecha.value = nota.fecha.split(' ')[0]; // Solo la fecha sin hora
            form.cliente_id.value = nota.cliente_id;
            document.getElementById('inputCliente').value = nota.cliente;
            
            // Tipo de nota
            if (nota.tipo === 'anticipo') {
                document.getElementById('tipoAnticipo').checked = true;
                document.getElementById('seccionFacturas').style.display = 'none';
            } else {
                document.getElementById('tipoAbono').checked = true;
                document.getElementById('seccionFacturas').style.display = 'block';
            }
            
            // Tipo IVA
            const tipoIva = parseFloat(nota.iva) > 0 ? 'gravada' : 'exenta';
            document.querySelector(`[name="tipo_iva"][value="${tipoIva}"]`).checked = true;
            
            form.forma_pago.value = nota.forma_pago || '';
            form.referencia.value = nota.referencia || '';
            form.banco.value = nota.banco || '';
            form.observaciones.value = nota.observaciones || '';
            
            // Cargar facturas con los detalles
            if (nota.tipo === 'abono' && nota.detalles && nota.detalles.length > 0) {
                // Construir la tabla manualmente con las facturas de la nota
                const tbody = document.getElementById('tbodyFacturasPendientes');
                tbody.innerHTML = '';
                
                nota.detalles.forEach(detalle => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input check-factura" 
                                   data-factura-id="${detalle.egreso_cab_id}" checked>
                        </td>
                        <td>${detalle.factura_numero || 'N/A'}</td>
                        <td>${detalle.factura_fecha || 'N/A'}</td>
                        <td class="text-end">Q ${parseFloat(detalle.factura_total || 0).toFixed(2)}</td>
                        <td class="text-end">-</td>
                        <td class="text-end">-</td>
                        <td>
                            <input type="number" class="form-control form-control-sm input-monto-abono" 
                                   data-factura-id="${detalle.egreso_cab_id}" 
                                   step="0.01" min="0"
                                   value="${detalle.monto_abono}"
                                   placeholder="0.00">
                        </td>
                    `;
                    tbody.appendChild(tr);
                });
                
                // Event listeners
                document.querySelectorAll('.check-factura').forEach(check => {
                    check.addEventListener('change', function() {
                        const facturaId = this.dataset.facturaId;
                        const inputMonto = document.querySelector(`.input-monto-abono[data-factura-id="${facturaId}"]`);
                        if (!this.checked) {
                            inputMonto.value = '';
                        }
                        calcularTotales();
                    });
                });
                
                document.querySelectorAll('.input-monto-abono').forEach(input => {
                    input.addEventListener('input', calcularTotales);
                });
                
                calcularTotales();
            } else {
                // Para anticipos, calcular directamente
                calcularTotales();
            }
            
            // Guardar el ID para actualizar en lugar de crear
            form.dataset.editandoId = id;
            
            // Abrir el modal
            const modal = new bootstrap.Modal(document.getElementById('modalNuevaNota'));
            modal.show();
        } catch (error) {
            console.error('Error:', error);
            Swal.fire('Error', 'No se pudo cargar la nota para editar', 'error');
        }
    };

    window.eliminarNota = async function(id) {
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

                const response = await fetch('../controllers/notaCreditoClienteController.php?action=eliminar', {
                    method: 'POST',
                    body: formData
                });

                const resultado = await response.json();

                if (resultado.success) {
                    Swal.fire('Eliminado', 'Nota de crédito eliminada correctamente', 'success');
                    mostrarNotas();
                } else {
                    Swal.fire('Error', resultado.error || 'No se pudo eliminar la nota', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al eliminar la nota', 'error');
            }
        }
    };

    function limpiarFormulario() {
        const form = document.getElementById('formNuevaNota');
        form.reset();
        form.querySelector('[name="fecha"]').valueAsDate = new Date();
        document.getElementById('cliente_id').value = '';
        document.getElementById('tbodyFacturasPendientes').innerHTML = '';
        delete form.dataset.editandoId;
        generarNumero();
        calcularTotales();
    }

    function limpiarFacturas() {
        document.getElementById('tbodyFacturasPendientes').innerHTML = '';
        window.facturasPendientes = [];
        calcularTotales();
    }

    async function generarNumero() {
        try {
            const response = await fetch('../controllers/notaCreditoClienteController.php?action=generar_numero');
            const data = await response.json();
            document.querySelector('[name="numero"]').value = data.numero;
        } catch (error) {
            console.error('Error generando número:', error);
        }
    }

    // Exponer función globalmente
    window.loadNotasCreditoCliente = loadNotasCreditoCliente;
});
