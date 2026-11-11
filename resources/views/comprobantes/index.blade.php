<x-app-layout>
    <!-- Modal -->
    @include('comprobantes.partials.enviarFacturaMail')
    <div class="container">
        <x-title title="Comprobantes">

            @if(esMonotributista())
            <a href="{{ route('lote.create.c') }}" class="btn btn-success">
                <i class="fas fa-user-secret"></i> Facturar C por Monto
            </a>
            <a href="{{ route('comprobante.create.c') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nueva Factura C
            </a>
            @elseif(emisor_puede_emitir_factura('A'))
            <a href="{{ route('comprobante.create.a') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Factura A
            </a>
            <a href="{{ route('comprobante.create.b') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Factura B
            </a>
            @endif
        </x-title>
        
        <form method="GET" action="{{ route('comprobantes.index') }}" class="row g-2 mb-3 align-items-end">
            @if($tiposComprobante->count() > 1)
            <div class="col-12 col-md-3">
                <label for="tipo_comprobante_id" class="form-label">Tipo de comprobante</label>
                <select id="tipo_comprobante_id" name="tipo_comprobante_id" class="form-select">
                    <option value="">Todos los tipos</option>
                    @foreach($tiposComprobante as $tipo)
                    <option value="{{ $tipo->id }}" @selected(($valoresFiltros['tipo_comprobante_id'] ?? '') == $tipo->id)>{{ $tipo->descripcion }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-12 col-md-2">
                <label for="numero" class="form-label">Número</label>
                <input type="search" id="numero" name="numero" class="form-control" inputmode="numeric" maxlength="8"
                    placeholder="Ej.: 00000123" value="{{ $valoresFiltros['numero'] ?? '' }}">
            </div>
            <div class="col-12 col-md-2">
                <label for="fecha_desde" class="form-label">Emitido desde</label>
                <input type="date" id="fecha_desde" name="fecha_desde" class="form-control" value="{{ $valoresFiltros['fecha_desde'] ?? '' }}">
            </div>
            <div class="col-12 col-md-2">
                <label for="fecha_hasta" class="form-label">Emitido hasta</label>
                <input type="date" id="fecha_hasta" name="fecha_hasta" class="form-control" value="{{ $valoresFiltros['fecha_hasta'] ?? '' }}">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="{{ route('comprobantes.index') }}" class="btn btn-outline-secondary">Limpiar</a>
            </div>
            @foreach(['tipo_comprobante_id', 'numero', 'fecha_desde', 'fecha_hasta'] as $campo)
                @error($campo)
                <div class="col-12 text-danger" role="alert">{{ $message }}</div>
                @enderror
            @endforeach
        </form>

        @if(count($comprobantes) == 0)
        <em>{{ collect($filtros)->filter(fn ($valor) => filled($valor))->isNotEmpty() ? 'No se encontraron comprobantes para la búsqueda.' : 'No hay comprobantes para mostrar' }}</em>
        @else
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($comprobantes as $comprobante)
                <tr>
                    <td>
                        {{ $comprobante->tipoComprobante?->descripcion }}
                        @if($comprobante->anulacion_id)
                        <em><small>(anulada)</small></em>
                        @endif
                    </td>
                    <td>{{ $comprobante->nro_comprobante ? str_pad($comprobante->nro_comprobante, 8, '0', STR_PAD_LEFT) : 'S/N' }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($comprobante->fecha_emision ?? $comprobante->created_at)->format('d/m/Y') }}</td>
                    <td>
                        @if($comprobante->cliente)
                        <i class="fas fa-user text-warning"></i>&nbsp;
                        <a href="{{ route('clientes.dashboard', $comprobante->cliente) }}">{{ $comprobante->cliente->nombre }}</a>
                        @else
                        {{ $comprobante->razon_social ?? 'Cons. Final' }}
                        @endif
                    </td>
                    <td>${{ pesosargentinos($comprobante->importe_total) }}</td>
                    <td class="d-flex gap-1">
                        @if($comprobante->cae)
                        @if(variable_global('TIPO_IMPRESORA') == 'AMBAS' || variable_global('TIPO_IMPRESORA') == 'TERMICA')
                        <a href="{{route('comprobante.descargar.termica', $comprobante)}}" class="btn btn-primary btn-sm btnDescargar" title="Imprimir ticket"><i class="fas fa-print"></i></a>
                        @endif
                        @if(variable_global('TIPO_IMPRESORA') == 'AMBAS' || variable_global('TIPO_IMPRESORA') == 'A4')
                        <a href="{{route('comprobante.descargar.pdf', $comprobante)}}" class="btn btn-primary btn-sm" title="Descargar en PDF"><i class="far fa-file-pdf"></i></a>
                        @endif
                        <button 
                        onclick="urlEnviarMail('{{ route('comprobante.enviar.mail', $comprobante) }}', '{{$comprobante->cliente?->email}}')" 
                        data-bs-toggle="modal" data-bs-target="#modalEnviarFacturaMail" 
                        class="btn btn-primary btn-sm" title="Enviar por Email"><i class="far fa-envelope"></i></button>
                        {{-- <a href="{{ route('facturacion.show', $comprobante) }}" class="btn btn-primary">Ver</a> --}}
                        @if($comprobante->anulacion_id == null && $comprobante->tipoComprobante?->codigo !== 'CC')
                        <button 
                        onclick="anularFactura(
                        '{{route('comprobante.anular', $comprobante)}}',
                        '{{$comprobante->id}}',
                        '{{str_pad( $comprobante->nro_comprobante, 8, '0', STR_PAD_LEFT)}}'
                        )" class="btn btn-danger btn-sm" title="anular"><i class="fas fa-ban"></i></button>
                        @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $comprobantes->links() }}
        @endif
        
        <form id="formAnularFactura" method="POST" style="display: none;">
            @csrf
        </form>
        
    </div>
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btnDescargar').forEach(function(button) {
                button.addEventListener('click', function(event) {
                    event.preventDefault(); // Prevenir el comportamiento por defecto del enlace
                    
                    // Obtener la URL de la ruta del enlace
                    const url = button.getAttribute('href');
                    
                    // Realizar la solicitud con Axios
                    axios.get(url)
                    .then(function(response) {
                        // Crear una nueva ventana
                        const newWindow = window.open('', '_blank', 'width=800,height=600');
                        
                        // Escribir el contenido recibido en la nueva ventana
                        newWindow.document.write(response.data);
                        
                        // Asegurarse de que el contenido esté cargado antes de imprimir
                        newWindow.document.close();
                        
                        newWindow.onload = function() {
                        // Imprimir el contenido una vez que se haya cargado todo
                        newWindow.print();
                    };
                    })
                    .catch(function(error) {
                        console.error("Error al cargar el contenido:", error);
                        alert("Ocurrió un error al intentar cargar el contenido.");
                    });
                });
            });
        });
        const btns = document.querySelectorAll('.btnBloquear');
        
        
        function anularFactura (url, id, numero){
            Swal.fire({
                title: 'Anular Factura',
                text: `¿Anular la factura N° ${numero}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Anular',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    if (!window.iniciarEmisionComprobante('Emitiendo nota de crédito...')) {
                        return;
                    }
                    let form = document.getElementById('formAnularFactura');
                    form.action = url;
                    form.submit();            
                }
            });
        }
        
        function urlEnviarMail(url, mail) {
            document.getElementById('modalMailCliente').value = mail;
            document.getElementById('formEnviarFacturaMail').action = url;
        }
        btnSubmitEnviarFactura = document.getElementById('btnSubmitEnviarFactura');
        btnSubmitEnviarFactura.addEventListener('click', (e) => {
            e.preventDefault();
            btns.forEach(btn => btn.disabled = true);
            btnSubmitEnviarFactura.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            let form = document.getElementById('formEnviarFacturaMail');
            form.submit();
        });
        // });
        
    </script>
    @endpush
</x-app-layout>