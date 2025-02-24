<x-app-layout>
    <div class="container">
        <x-title title="Clientes" urlBack="{{route('home')}}" >
            @php
                $allClientsValid = $clientes->every(function($cliente) {
                    return $cliente->tipo_documento && $cliente->condicion_iva_receptor_id;
                });
            @endphp
            <button id="btnFacturarNotificar" class="btn btn-success" {{ $allClientsValid ? '' : 'disabled' }}>Facturar y notificar</button>
            <button id="btnFacturar" class="btn btn-success" {{ $allClientsValid ? '' : 'disabled' }}>Facturar</button>
        </x-title>
        <div class="fs-5 mb-2">
            Total a facturar: <strong>${{ pesosargentinos( $clientes->sum(function($cliente){
                return $cliente->servicios->sum(function($servicio){
                    return $servicio->importe_total * $servicio->cantidad;
                });
            })) }}</strong>
        </div>
        @if(!$allClientsValid)
        <div class="alert alert-warning" role="alert">
            <strong>Atención:</strong> Hay clientes que no tienen definido el tipo de documento o la condición frente al IVA. Por favor, verifique los datos antes de continuar.    
        </div>
        @endif
        <hr>
        <div class="row">
            @foreach($clientes as $cliente)
            @if(count($cliente->servicios))
            <div class="col-12 mb-2" >
                <div class="card p-4">
                    <div class="fs-5 mb-2">
                        <i class="fas fa-user"></i> {{ $cliente->nombre }} 
                        <br>
                        Notificar a: <em><a href="mailto:{{ $cliente->email }}">{{ $cliente->email }}</a></em>
                        <br>
                        @if(!$cliente->tipo_documento)
                        <span class="badge bg-danger">Falta el tipo de documento</span>
                        @endif
                        @if(!$cliente->condicion_iva_receptor_id)
                        <span class="badge bg-danger">Falta la condicion frente al IVA</span>
                        @endif
                    </div>
                    <table class="table table-striped mb-3">
                        <thead>
                            <tr>
                                <th>Cant.</th>
                                <th>Descripción</th>
                                <th>Importe Unitario</th>
                                <th>Subotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cliente->servicios as $servicio)
                            <tr>
                                <td>{{ $servicio->cantidad }}</td>
                                <td>{{ $servicio->descripcion }}</td>
                                <td>${{ pesosargentinos($servicio->importe_total) }}</td>
                                <td>${{ pesosargentinos( $servicio->importe_total * $servicio->cantidad ) }}</td>
                            </tr>
                            @endforeach
                            <tr class="fs-5">
                                <td colspan="3" class="text-end"><strong>Total</strong></td>
                                <td><strong>${{ pesosargentinos($cliente->servicios->sum(function($servicio) {
                                    return $servicio->importe_total * $servicio->cantidad;
                                })) }}</strong></td>
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        </div>
        
        
        
    </div>
</div>

<form id="formFacturacionMensual" action="{{ route('clientes.facturacion') }}" method="POST">
    @csrf
</form>

@push('scripts')
<script>
    
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('btnFacturar').addEventListener('click', function() {
            facturar(false);
        });
        
        document.getElementById('btnFacturarNotificar').addEventListener('click', function() {
            facturar(true);
        });
    });
    
    function facturar(notificar){
        let title = notificar ? 'Facturación y notificación' : 'Facturación';
        let html = notificar ? `Al notificar a cada cliente, este proceso puede tardar un poco más. ¿Confirma el proceso?</a>` : `Este proceso le emite una factura a todos los clientes de la anterior lista ¿Confirma el proceso?</a>`;
        Swal.fire({
            title: title,
            html: html,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: notificar ? 'Facturar y notificar' : 'Facturar',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                try {
                    const url = `{{ route('clientes.facturacion') }}?notificar=${notificar}`;
                    const response = await axios.post(url);
                    if (!response.status == 201 || response.data.msg !== "") {
                        return Swal.showValidationMessage(`Algunos clientes no pudieron ser facturados: ${response.data.msg}`);
                    }
                    return true;
                } catch (error) {
                    if(error.response && error.response.data) {
                        return Swal.showValidationMessage(`Falló el envío: ${error.response.data.msg}`);
                    }
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
        })
        .then((result) => {
            if (result.isConfirmed) {
                Swal.fire('Facturación y notificación', 'Proceso finalizado', 'success');
            }
        });
    }
    
</script>
@endpush

</x-app-layout>