<x-app-layout>
    @include('facturacion.partials.enviarFacturaMail')
    <div class="container">
        <x-title title="Facturación">
            <a href="{{ route('facturacion.create.c') }}" class="btn btn-success">Nueva Factura C</a>
        </x-title>
        
        
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
                @foreach($facturas as $factura)
                <tr>
                    <td>{{ $factura->tipo_comprobante ?? '-' }}</td>
                    <td>{{ $factura->nro_factura ? str_pad($factura->nro_factura, 8, '0', STR_PAD_LEFT) : 'S/N' }}</td>
                    <td>{{ $factura->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $factura->cliente_id ? $factura->cliente->nombre : 'Cons. Final' }}</td>
                    <td>${{ pesosargentinos($factura->total) }}</td>
                    <td>
                        @if($factura->enviada_afip)
                        <a href="{{route('facturacion.descargar.pdf', $factura)}}" class="btn btn-warning btn-sm" title="Descargar en PDF"><i class="far fa-file-pdf"></i></a>
                        <button onclick="urlEnviarMail('{{ route('facturacion.enviar.mail', $factura) }}')" data-bs-toggle="modal" data-bs-target="#modalEnviarFacturaMail" class="btn btn-info btn-sm" title="Enviar por Email"><i class="far fa-envelope"></i></button>
                        {{-- <a href="{{ route('facturacion.show', $factura) }}" class="btn btn-primary">Ver</a> --}}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $facturas->links() }}

        
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