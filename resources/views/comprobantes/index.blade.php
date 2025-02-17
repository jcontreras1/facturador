<x-app-layout>
    {{-- @include('comprobantes.partials.enviarFacturaMail') --}}
    <!-- Modal -->
    <div class="modal fade" id="modalEnviarFacturaMail" tabindex="-1" aria-labelledby="modalEnviarFacturaMailLabel">
        <form id="formEnviarFacturaMail" method="post" action="" >
            @csrf
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="modalEnviarFacturaMailLabel">Enviar por mail</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" id="modalMailCliente" class="form-control" name="email" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btnBloquear" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" id="btnSubmitEnviarFactura" class="btn btn-primary btnBloquear">Enviar</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
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
                        <a href="{{route('comprobante.descargar.pdf', $comprobante)}}" class="btn btn-warning btn-sm" title="Descargar en PDF"><i class="far fa-file-pdf"></i></a>
                        <button 
                        onclick="urlEnviarMail('{{ route('comprobante.enviar.mail', $comprobante) }}', '{{$comprobante->cliente?->email}}')" 
                        data-bs-toggle="modal" data-bs-target="#modalEnviarFacturaMail" class="btn btn-info btn-sm" title="Enviar por Email"><i class="far fa-envelope"></i></button>
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
        const btns = document.querySelectorAll('.btnBloquear');
        const btnSubmitEnviarFactura =document.getElementById('btnSubmitEnviarFactura');
        
        function urlEnviarMail(url, mail){
            document.getElementById('modalMailCliente').value = mail;
            document.getElementById('formEnviarFacturaMail').action = url;
        }
        
        btnSubmitEnviarFactura.addEventListener('click', (e) => {
            console.log(e);
            e.preventDefault();
            btns.forEach(btn => btn.disabled = true);
            btnSubmitEnviarFactura.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
            form.submit();
            
        });
    </script>
    @endpush
</x-app-layout>