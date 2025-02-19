<x-app-layout>
    @include('comprobantes.partials.enviarFacturaMail')
    
    <div class="container">
        <div class="d-flex justify-content-between">
            <h3 class="text-lg font-semibold mb-6">Panel del cliente <strong>{{ strtoupper($cliente->nombre) }}</strong></h3>
            <div>
                <a href="{{route('cliente.comprobante.create.c', $cliente)}}" class="btn btn-success"><i class="fas fa-file-invoice"></i> Nueva Factura C</a>
                <a href="{{route('clientes.index')}}" class="btn btn-primary"><i class="fas fa-chevron-left"></i></a>
            </div>
        </div>
        <hr>
        
        @if(!count($comprobantes))
        
        <em>No hay comprobantes registrados</em>
        @else
        <table class="table table-striped table-sm" id="tabla_comprobantes">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Importe</th>
                    <th>Concepto(s)</th>
                    <th>Opciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($comprobantes as $comprobante)
                <tr class="h-100">
                    <td>{{date('d/m/y', strtotime($comprobante->fecha_emision))}}</td>
                    <td>${{pesosargentinos($comprobante->importe_total)}}</td>
                    <td>
                        @foreach($comprobante->detalle as $item)
                        {{$item->descripcion}}<br>
                        @endforeach
                    </td>
                    <td class="">
                        @if($comprobante->cae)
                        <a href="{{route('comprobante.descargar.pdf', $comprobante)}}" class="btn btn-warning btn-sm m-1" title="Descargar en PDF"><i class="far fa-file-pdf"></i></a>
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