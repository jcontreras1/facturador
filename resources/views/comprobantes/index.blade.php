<x-app-layout>
    <!-- Modal -->
    @include('comprobantes.partials.enviarFacturaMail')
    <div class="container">
        <x-title title="Comprobantes">
            <a href="{{ route('lote.create.c') }}" class="btn btn-success">
                <i class="fas fa-user-secret"></i> Facturar C por Monto
            </a>
            <a href="{{ route('comprobante.create.c') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nueva Factura C
            </a>
        </x-title>
        
        @if(count($comprobantes) == 0)
        <em>No hay comprobantes para mostrar</em>
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
                    <td>{{ $comprobante->tipoComprobante?->descripcion }}</td>
                    <td>{{ $comprobante->nro_comprobante ? str_pad($comprobante->nro_comprobante, 8, '0', STR_PAD_LEFT) : 'S/N' }}</td>
                    <td>{{ $comprobante->created_at->format('d/m/Y H:i') }}</td>
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
                        <a href="{{route('comprobante.descargar.termica', $comprobante)}}" class="btn btn-primary btn-sm btnDescargar" title="Descargar en PDF"><i class="far fa-file-pdf"></i></a>
                        <a href="{{route('comprobante.descargar.pdf', $comprobante)}}" class="btn btn-primary btn-sm" title="Descargar en PDF"><i class="far fa-file-pdf"></i></a>
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