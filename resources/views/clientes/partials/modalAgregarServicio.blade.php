<!-- Modal -->
<div class="modal fade" id="modalAgregarServicio" tabindex="-1" aria-labelledby="modalAgregarServicioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('servicioCliente.store', $cliente) }}" method="POST" id="form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAgregarServicioLabel">Agregar Servicio Mensual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-2" role="alert">
                        Si se selecciona un producto/servicio de la lista desplegable, una vez que se actualice el precio del producto/servicio 
                        en el <a href="{{ route('items.index') }}">menú de servicios<a>, todas las facturas saldrán con el precio nuevo. Mientras que si se usa un item manual, deberá actualizarse de 
                            forma manual.
                        </div>
                        <div class="mb-1">
                            <label for="item_id" class="form-label">Item</label>
                            <select class="form-select" id="item_id" name="item_id">
                                <option data-importe="" value="">-- Sin Seleccionar --</option>
                                @foreach($items as $item)
                                <option data-importe="{{$item->precio_unitario}}" value="{{ $item->id }}">{{ $item->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-1">
                            <label for="descripcion_manual" class="form-label">Item manual</label>
                            <textarea rows="3" required type="text" class="form-control mb-1" id="descripcion_manual" name="descripcion_manual" placeholder="Descripción"></textarea>
                            <small id="descripcion_manual_warning" class="text-warning mb-2" style="display: none;">
                                Si se completa este campo, no se tendrá en cuenta al ITEM de arriba seleccionado.
                            </small>
                        </div>
                        
                        <div class="mb-1">
                            <label for="cantidad" class="form-label">Cantidad <span class="text-danger">*</span></label>
                            <input type="number" value="1" class="form-control" id="cantidad" name="cantidad" required>
                        </div>
                        @if(!esMonotributista())
                        <div class="mb-1">
                            <label for="iva_id" class="form-label">IVA</label>
                            <select class="form-select" id="iva_id" name="iva_id">
                                <option value="">Seleccione un IVA</option>
                                @foreach($ivas as $iva)
                                <option value="{{ $iva->id }}">{{ $iva->descripcion }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-1">
                            <label for="importe_neto" class="form-label">Importe Neto</label>
                            <input type="number" class="form-control" id="importe_neto" name="importe_unitario_neto">
                        </div>
                        @endif
                        <div class="mb-1">
                            <label for="importe_total" class="form-label">Importe Unitario</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="importe_total" step="0.01" name="importe_unitario_subtotal" required>
                            </div>
                        </div>
                        <hr>
                        <div class="mb-1">
                            <label for="valor_total" class="form-label">Valor Total</label>
                            <p id="valor_total">$0.00</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            
            const itemSelect = document.getElementById('item_id');
            const descripcionManual = document.getElementById('descripcion_manual');
            const descripcionManualWarning = document.getElementById('descripcion_manual_warning');
            
            // Función para manejar la lógica del campo de descripción manual
            function handleDescripcionManual() {
                if (itemSelect.value !== '') {
                    // Si se selecciona un item, no es necesario el textarea
                    descripcionManual.removeAttribute('required');
                    descripcionManualWarning.style.display = 'none';
                } else {
                    // Si no se selecciona un item, el textarea se vuelve obligatorio
                    descripcionManual.setAttribute('required', 'required');
                    descripcionManualWarning.style.display = 'block';
                }
            }
    
            itemSelect.addEventListener('change', function(e) {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const importe = selectedOption.getAttribute('data-importe');
                importeTotalInput.value = importe;
                updateValorTotal();
                
                handleDescripcionManual();  // Llamamos a la función para manejar el estado de descripción manual
            });
            
            // Lógica para calcular el total
            const cantidadInput = document.getElementById('cantidad');
            const importeTotalInput = document.getElementById('importe_total');
            const valorTotalDisplay = document.getElementById('valor_total');
            
            function updateValorTotal() {
                const cantidad = parseFloat(cantidadInput.value) || 0;
                const importeTotal = parseFloat(importeTotalInput.value) || 0;
                const valorTotal = cantidad * importeTotal;
                valorTotalDisplay.textContent = `$${valorTotal.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }
            
            cantidadInput.addEventListener('input', updateValorTotal);
            importeTotalInput.addEventListener('input', updateValorTotal);
    
            // Inicializa el estado del campo textarea al cargar la página
            handleDescripcionManual();  // Asegura que la lógica funcione desde el principio
    
        });
    </script>
    
    @endpush