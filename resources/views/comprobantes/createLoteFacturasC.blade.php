<x-app-layout>
    
    <div class="container">
        <x-title title="Lote de comprobantes C">
        </x-title>
        <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="fas fa-info-circle fa-2x me-4"></i>
            <div>
                Este módulo se usa para emitir una o varias facturas C por un monto determinado sin declarar comprador; 
                <strong>se asume que es un consumidor final</strong>. El sistema calculará la cantidad de faturas a emitir basándose
                en el monto definido en la sección de configuración de este sistema. Para más información, visite el sitio
                oficial de ARCA: <a href="https://www.afip.gob.ar/fe/emision-autorizacion/datos-comprobantes.asp" target="_blank">
                    https://www.afip.gob.ar/fe/emision-autorizacion/datos-comprobantes.asp
                </a>
                <hr>
                Valor actual del tope sin declarar comprador: 
                <strong>${{ pesosargentinos(variable_global('TOPE_FACTURACION_CONSUMIDOR_FINAL')) }}</strong>
            </div>
        </div>
        
        <form id="formPost" action="{{ route('lote.store.c') }}" method="POST">
            @csrf
            
            <div class="card mb-3">
                <div class="card-header fs-5"><i class="fas fa-file-invoice-dollar"></i> Datos de la Factura</div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <label for="fecha">Fecha</label>
                            <input type="date" name="fecha_emision" value="{{ old('fecha_emision', date('Y-m-d')) }}" id="fecha" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="concepto">Concepto</label>
                            <select id="concepto" name="concepto" class="form-select" required>
                                <option value="1">Productos</option>
                                <option value="2" selected>Servicios</option>
                                <option value="3">Productos y Servicios</option>
                            </select>
                        </div>
                    </div>
                    <div class="row" id="serviciosFields" style="display: none;">
                        <div class="col-12 col-md-4">
                            <label for="fechaInicioServicios">Fecha de inicio de servicios</label>
                            <input type="date" name="fecha_servicio_desde" value="{{old('fecha_servicio_desde', date('Y-m-d'))}}" id="fechaInicioServicios" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="fechaFinServicios">Fecha de finalización de servicios</label>
                            <input type="date" name="fecha_servicio_hasta" value="{{ old('fecha_servicio_hasta', date('Y-m-d')) }}" id="fechaFinServicios" class="form-control" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="fechaVencimientoPago">Fecha de vencimiento para el pago</label>
                            <input type="date" name="fecha_vencimiento_pago" value="{{ old('fecha_vencimiento_pago', date('Y-m-d')) }}" id="fechaVencimientoPago" class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-header fs-5"><i class="fas fa-dollar-sign"></i> Importe y Descripción</div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <div class="input-group mb-3">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" value="{{old('importe_total')}}" name="importe_total" id="importeTotal" class="form-control" placeholder="Importe a facturar" required>
                            </div>
                            <div class="input-group mb-3">
                                <span class="input-group-text">Tope de Facturación</span>
                                <input type="number" step="0.01" value="{{old('tope_facturacion')}}" name="tope_facturacion" id="topeFacturacionInput" class="form-control" placeholder="Tope de Facturación">
                            </div>
                            <div class="input-group">
                                <span class="input-group-text">Cant. Comprobantes</span>
                                <input type="number" value="{{old('cant_comprobantes')}}" name="cant_comprobantes" id="cantComprobantes" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text">Descripción</span>
                                <textarea id="descripcion" name="descripcion" class="form-control" rows="3" required>{{old('descripcion')}}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-header fs-5"><i class="fas fa-calculator"></i> Resumen de Comprobantes</div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <p>Se emitirán <strong id="cantidadComprobantes">0</strong> comprobantes.</p>
                            <p>Importe de cada comprobante: <strong id="importeComprobante">0.00</strong></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12 float-end">
                    <button id="btnFacturar" type="submit" class="btn btn-lg btn-success">Emitir facturas</button>
                </div>
            </div>
            
        </form>
        
        
        
        
    </div>
    
    @push('scripts')
    <script>
        document.getElementById('formPost').onsubmit = function(event) {
            event.preventDefault();
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...';
            });
            this.submit();
        };
        
        document.addEventListener('DOMContentLoaded', function () {
            
            const importeTotalInput = document.getElementById('importeTotal');
            const cantidadComprobantesElement = document.getElementById('cantidadComprobantes');
            const importeComprobanteElement = document.getElementById('importeComprobante');
            const topeFacturacion = {{ variable_global('TOPE_FACTURACION_CONSUMIDOR_FINAL') }};
            const cantComprobantesInput = document.getElementById('cantComprobantes');
            const topeFacturacionInput = document.getElementById('topeFacturacionInput');
            
            
            
            function calcularComprobantes() {
                const importeTotal = parseFloat(importeTotalInput.value);
                const tope = parseFloat(topeFacturacionInput.value) || topeFacturacion;
                if (!isNaN(importeTotal) && importeTotal > 0) {
                    const cantidadComprobantes = Math.ceil(importeTotal / tope);
                    const importeComprobante = (importeTotal / cantidadComprobantes).toFixed(2);
                    cantidadComprobantesElement.textContent = cantidadComprobantes;
                    importeComprobanteElement.textContent = importeComprobante;
                    cantComprobantesInput.value = cantidadComprobantes;
                } else {
                    cantidadComprobantesElement.textContent = '0';
                    importeComprobanteElement.textContent = '0.00';
                    cantComprobantesInput.value = '0';
                }
            }
            calcularComprobantes();
            importeTotalInput.addEventListener('input', calcularComprobantes);
            topeFacturacionInput.addEventListener('input', calcularComprobantes);
            
            const conceptoSelect = document.getElementById('concepto');
            
            const serviciosFields = document.getElementById('serviciosFields');
            
            conceptoSelect.addEventListener('change', function () {
                if (conceptoSelect.value == '2' || conceptoSelect.value == '3') {
                    serviciosFields.style.display = 'flex';
                } else {
                    serviciosFields.style.display = 'none';
                }
            });
            conceptoSelect.dispatchEvent(new Event('change'));
        });
    </script>
    @endpush
</x-app-layout>