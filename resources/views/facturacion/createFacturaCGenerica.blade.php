<x-app-layout>
    
    <div class="container">
        <h3>Crear Comprobante</h3>
        <hr>
        
        <form action="{{ route('facturacion.store.c') }}" id="form" method="POST">
            @csrf            
            <input type="hidden" class="form-control" name="importeTotal" id="importeTotalEscondido" required>
            <div class="card mb-3">
                <div class="card-header fs-4"><i class="fas fa-file-invoice-dollar"></i> Datos de la Factura</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <label for="fecha">Fecha</label>
                            <input type="date" class="form-control" name="fecha" id="fecha" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="concepto">Concepto</label>
                            <select class="form-control form-select" name="concepto" id="concepto" required>
                                <option value="1">Productos</option>
                                <option value="2" selected>Servicios</option>
                                <option value="3">Productos y Servicios</option>
                            </select>
                            
                        </div>
                    </div>
                </div>
            </div>
            
            
            <div class="card mb-3">
                <div class="card-header fs-4"><i class="fas fa-user-alt"></i> Cliente</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <label for="tipoDocumento">Tipo de Cliente</label>
                            <select class="form-control form-select" name="tipoDocuemnto" id="tipoDocumento" required>
                                <option value="80">CUIT</option>
                                <option value="86">CUIL</option>
                                <option value="96">DNI</option>
                                <option value="99" selected>Consumidor Final</option>
                            </select>
                        </div>
                        
                        <div class="col-12 col-md-6 mb-3">
                            <label for="documento">DNI/CUIL/CUIT</label>
                            <input type="number" class="form-control" name="documento" id="documento">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header fs-4"><i class="fas fa-list"></i> Detalle</div>
                <div class="card-body">
                    <button type="button" class="btn btn-primary mt-3" id="agregarLineaBtn">Agregar Línea <i class="fas fa-caret-down"></i></button>
                    <div class="lineas-detalle mt-3" id="lineas-detalle"></div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-success mt-3 float-end">Emitir Comprobante </button>


        <div class="mt-3">
            <h4>Total: $<span id="importeTotal">0.00</span></h4>
        </div>
        </form>
        
    </div>
    @push('scripts')
    <script>
        // document.addEventListener('DOMContentLoaded', function() {
        const lineasDetalleContainer = document.getElementById('lineas-detalle');
        const agregarLineaBtn = document.getElementById('agregarLineaBtn');
        const importeTotal = document.getElementById('importeTotal');
        const importeTotalEscondido = document.getElementById('importeTotalEscondido');
        const formulario = document.getElementById('form');
        const unidadesDeMedida = ['unidad', 'metros', 'kilos', 'litros'];
        
        let lineas = [];
        
        // Función para actualizar el total de la factura
        function actualizarTotal() {
            const total = lineas.reduce((acc, linea) => acc + linea.subtotal, 0);
            importeTotal.textContent  = total.toFixed(2);
            importeTotalEscondido.value = total.toFixed(2);
        }
        
        // Función para calcular el subtotal y el importe bonificado
        function calcularSubtotal(idLinea) {
            const linea = lineas[idLinea];
            
            linea.codigo = document.getElementById(`codigo${idLinea}`).value;
            linea.descripcion = document.getElementById(`descripcion${idLinea}`).value;
            linea.cantidad = parseFloat(document.getElementById(`cantidad${idLinea}`).value) || 0;
            linea.unidadDeMedida = document.getElementById(`unidadDeMedida${idLinea}`).value;
            linea.precioUnitario = parseFloat(document.getElementById(`precioUnitario${idLinea}`).value) || 0;
            
            const subtotal = linea.precioUnitario * linea.cantidad - linea.importeBonificado;
            linea.subtotal = subtotal;
            
            document.getElementById(`subtotal${idLinea}`).value = subtotal.toFixed(2);
            
            actualizarTotal();
        }
        
        // Función para agregar una nueva línea
        function agregarLinea() {
            const idLinea = lineas.length;
            
            // Crear la fila HTML
            const div = document.createElement('div');
            div.classList.add('linea-detalle', 'row', 'mb-3');
            div.setAttribute('data-id', idLinea);
            
            div.innerHTML = `
            <div class="col-12 col-md-1">
                <label for="codigo${idLinea}">Código</label>
                <input type="text" class="form-control form-control-sm" name="codigo[]" maxlength="4" id="codigo${idLinea}" data-id="${idLinea}" oninput="calcularSubtotal(${idLinea})">
            </div>
            <div class="col-12 col-md-4">
                <label for="descripcion${idLinea}">Descripción</label>
                <input type="text" class="form-control form-control-sm" name="descripcion[]" id="descripcion${idLinea}" data-id="${idLinea}" oninput="calcularSubtotal(${idLinea})">
            </div>
            <div class="col-12 col-md-1">
                <label for="cantidad${idLinea}">Cantidad</label>
                <input type="number" class="form-control form-control-sm" name="cantidad[]" min="0" id="cantidad${idLinea}" value="1" data-id="${idLinea}" oninput="calcularSubtotal(${idLinea})" required>
            </div>
            <div class="col-12 col-md-1">
                <label for="unidadDeMedida${idLinea}">Unidad</label>
                <select class="form-control form-control-sm form-select-sm" name="unidadMedida[]" id="unidadDeMedida${idLinea}" data-id="${idLinea}" onchange="calcularSubtotal(${idLinea})">
                    ${unidadesDeMedida.map(um => `<option value="${um}" ${um === 'unidad' ? 'selected' : ''}>${um}</option>`).join('')}
                </select>
            </div>
            <div class="col-12 col-md-1">
                <label for="precioUnitario${idLinea}">Precio Unit.</label>
                <input type="number" class="form-control form-control-sm" name="precioUnitario[]" id="precioUnitario${idLinea}" data-id="${idLinea}" value="0" oninput="calcularSubtotal(${idLinea})" required>
            </div>
            <div class="col-12 col-md-1">
                <label for="bonificacion${idLinea}">% Bonif.</label>
                <input type="number" class="form-control form-control-sm" name="porcentajeBonificacion[]" id="bonificacion${idLinea}" data-id="${idLinea}" value="0" oninput="calcularBonificacion(${idLinea})" required>
            </div>
            <div class="col-12 col-md-1">
                <label for="importeBonificado${idLinea}">$ Bonif.</label>
                <input type="number" class="form-control form-control-sm" name="importeBonificado[]" id="importeBonificado${idLinea}" data-id="${idLinea}" value="0" readonly>
            </div>
            <div class="col-12 col-md-1">
                <label for="subtotal${idLinea}">Subtotal</label>
                <input type="number" class="form-control form-control-sm" name="subtotal[]" id="subtotal${idLinea}" data-id="${idLinea}" value="0" readonly>
            </div>
            <div class="col-12 col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger bnt-sm" onclick="eliminarLinea(${idLinea})"><i class="fas fa-trash-alt"></i></button>
            </div>
        `;
            
            // Agregar la línea al contenedor de líneas
            lineasDetalleContainer.appendChild(div);
            
            // Agregar la nueva línea al arreglo
            lineas.push({
                id: idLinea,
                codigo: '',
                descripcion: '',
                cantidad: 1,
                unidadDeMedida: 'unidad',
                precioUnitario: 0,
                bonificacion: 0,
                importeBonificado: 0,
                subtotal: 0
            });
        }
        
        
        
        // Función para calcular el importe bonificado
        function calcularBonificacion(idLinea) {
            const linea = lineas[idLinea];
            
            linea.bonificacion = parseFloat(document.getElementById(`bonificacion${idLinea}`).value) || 0;
            linea.importeBonificado = (linea.precioUnitario * linea.cantidad) * (linea.bonificacion / 100);
            
            document.getElementById(`importeBonificado${idLinea}`).value = linea.importeBonificado.toFixed(2);
            
            calcularSubtotal(idLinea);
        }
        
        // Función para eliminar una línea
        function eliminarLinea(idLinea) {
            lineas.splice(idLinea, 1); // Eliminar la línea del arreglo de líneas
            document.querySelector(`.linea-detalle[data-id="${idLinea}"]`).remove();
            actualizarTotal();
        }
        
        // Agregar una línea cuando se hace clic en el botón
        agregarLineaBtn.addEventListener('click', agregarLinea);
        // });

            // Validar que haya al menos una línea de detalle antes de enviar el formulario
    formulario.addEventListener('submit', function(event) {
        if (lineas.length === 0) {
            event.preventDefault(); // Evitar el envío del formulario
            Swal.fire({
                icon: 'error',
                title: '¡Error!',
                text: 'Debe haber al menos una línea de detalle en la factura.',
            });
            return;
        }

        //chequear el valor total para que no sea 0
        if (importeTotalEscondido.value == 0) {
            event.preventDefault(); // Evitar el envío del formulario
            Swal.fire({
                icon: 'error',
                title: '¡Error!',
                text: 'El total de la factura no puede ser 0.',
            });
            return;
        }

    });

        
        
    </script>
    @endpush
</x-app-layout>