<x-app-layout>
    <div class="container">
        <div class="d-flex justify-content-between">
            <h3 class="text-lg font-semibold mb-6">Editar Servicio</h3>
            <div>
                <a href="{{route('items.index')}}" class="btn btn-primary"><i class="fas fa-chevron-left"></i></a>
            </div>
        </div>
        <hr>
        
        <form action="{{ route('items.update', $item->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="codigo" class="form-label">Código</label>
                <input type="text" class="form-control" id="codigo" name="codigo" value="{{ $item->codigo }}" required>
            </div>
            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <input type="text" class="form-control" id="descripcion" name="descripcion" value="{{ $item->descripcion }}" required>
            </div>
            <div class="mb-3">
                <div class="row">
                    <div class="col-12 col-md-8">
                        <label for="precio_unitario" class="form-label">Valor Unitario</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" data-original="{{$item->precio_unitario}}" id="precio_unitario" name="precio_unitario" value="{{ $item->precio_unitario }}" required>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="aumento" class="form-label">Aumentar porcentaje</label>
                        <div class="input-group">
                            <span class="input-group-text">%</span>
                            <input type="number" step="0.01" class="form-control" id="aumento" name="aumento" value="{{ $item->aumento }}">
                        </div>
                    </div>
                </div>
            </div>
            
            
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
        </form>
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var precioUnitarioInput = document.getElementById('precio_unitario');
            var aumentoInput = document.getElementById('aumento');
            var originalPrecioUnitario = parseFloat(precioUnitarioInput.getAttribute('data-original')) || parseFloat(precioUnitarioInput.value) || 0;
            var roundToTwo = function(value) {
                return Math.round((Number(value) + Number.EPSILON) * 100) / 100;
            };

            aumentoInput.addEventListener('input', function() {
                var rawValue = String(this.value).replace(',', '.');
                var aumento = parseFloat(rawValue);

                if (rawValue.trim() === '' || isNaN(aumento)) {
                    precioUnitarioInput.value = roundToTwo(originalPrecioUnitario).toFixed(2);
                    return;
                }

                var aumentoRedondeado = roundToTwo(aumento);
                var newPrecioUnitario = roundToTwo(originalPrecioUnitario * (1 + (aumentoRedondeado / 100)));
                precioUnitarioInput.value = newPrecioUnitario.toFixed(2);
            });

            aumentoInput.addEventListener('blur', function() {
                var rawValue = String(this.value).replace(',', '.');
                var aumento = parseFloat(rawValue);

                if (!isNaN(aumento)) {
                    this.value = roundToTwo(aumento).toFixed(2);
                }
            });
        });
        
    </script>
    @endpush
</x-app-layout>
