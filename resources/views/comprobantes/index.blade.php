<x-app-layout>
    @include('comprobantes.partials.enviarFacturaMail')
    <div class="container">
        <x-title title="Facturación">
            <a href="{{ route('comprobante.create.c') }}" class="btn btn-success">Nueva Factura C</a>
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
                    <td>{{ $comprobante->tipo_comprobante ?? '-' }}</td>
                    <td>{{ $comprobante->nro_factura ? str_pad($comprobante->nro_factura, 8, '0', STR_PAD_LEFT) : 'S/N' }}</td>
                    <td>{{ $comprobante->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $comprobante->cliente_id ? $comprobante->cliente->nombre : 'Cons. Final' }}</td>
                    <td>${{ pesosargentinos($comprobante->total) }}</td>
                    <td>
                        @if($comprobante->enviada_afip)
                        <a href="{{route('comprobante.descargar.pdf', $comprobante)}}" class="btn btn-warning btn-sm" title="Descargar en PDF"><i class="far fa-file-pdf"></i></a>
                        <button onclick="urlEnviarMail('{{ route('comprobante.enviar.mail', $comprobante) }}')" data-bs-toggle="modal" data-bs-target="#modalEnviarFacturaMail" class="btn btn-info btn-sm" title="Enviar por Email"><i class="far fa-envelope"></i></button>
                        {{-- <a href="{{ route('facturacion.show', $comprobante) }}" class="btn btn-primary">Ver</a> --}}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $comprobantes->links() }}
        @endif

        
    </div>
    
    @push('scripts')
    <script>
        const form = document.querySelector('#formEnviarFacturaMail');
        const btns = document.querySelectorAll('.btnBloquear');
        const btnSubmitEnviarFactura = document.querySelector('#btnSubmitEnviarFactura');
        const urlEnviarMail = (url) => {
            form.action = url;
        }
        form.addEventListener('submit', () => {
            btns.forEach(btn => btn.disabled = true);
            btnSubmitEnviarFactura.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        });
    </script>
    @endpush
</x-app-layout>