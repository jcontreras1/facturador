<x-app-layout>
    <div class="container">
        <x-title title="Clientes" urlBack="{{route('clientes.index')}}" >
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
        @if(!count($clientes))
        <em>No hay clientes con facturación mensual definida.</em>
        @endif
        
        <div class="row">
            <form id="formFacturacionMensual" action="{{ route('clientes.facturacion') }}" method="POST">
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
                            
                            <div class="row mb-3 mt-2">
                                <input type="hidden" name="cliente_id[]" value="{{ $cliente->id }}">
                                <div class="col-12 col-md-4 mb-3">
                                    <label>Fecha servicio Desde</label>
                                    <input type="date" id="fechaDesdeServicio{{$cliente->id}}" name="fechaDesde[]" class="form-control" value="{{date('Y-m-d')}}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label>Fecha servicio Hasta</label>
                                    <input type="date" id="fechaHastaServicio{{$cliente->id}}" name="fechaHasta[]" class="form-control" value="{{date('Y-m-d')}}">
                                </div>
                                <div class="col-12 col-md-4">
                                    <label>Fecha vencimiento Pago</label>
                                    <input type="date" id="fechaVencimientoPago{{$cliente->id}}" name="fechaVencimiento[]" class="form-control" value="{{date('Y-m-d')}}">
                                </div>
                                <div class="col-12">
                                    <button type="button" onclick="setMesAnterior('{{$cliente->id}}')" class="btn btn-info btn-sm mr-2"><i class="far fa-lightbulb"></i> Todo el mes pasado</button>
                                    <button type="button" onclick="setMes('{{$cliente->id}}')" class="btn btn-info btn-sm mr-2"><i class="far fa-lightbulb"></i> Todo este mes</button>
                                    <button type="button" onclick="setHoy('{{$cliente->id}}')" class="btn btn-info btn-sm mr-2"><i class="far fa-lightbulb"></i> Hoy</button>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    @endif
                    @endforeach
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>



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
    
    function setMesAnterior(clienteId) {
        let fechaDesde = new Date('{{ today()->subMonth()->startOfMonth()->format('Y-m-d') }}');
        let fechaHasta = new Date('{{ today()->subMonth()->endOfMonth()->format('Y-m-d') }}');
        let fechaVencimientoPago = new Date('{{ today()->startOfMonth()->addDays(9)->format('Y-m-d') }}');
        document.getElementById('fechaDesdeServicio'+clienteId).value = fechaDesde.toISOString().split('T')[0];
        document.getElementById('fechaHastaServicio'+clienteId).value = fechaHasta.toISOString().split('T')[0];
        document.getElementById('fechaVencimientoPago'+clienteId).value = fechaVencimientoPago.toISOString().split('T')[0];
    }
    
    function setMes(clienteId) {
        let fechaDesde = new Date('{{ today()->startOfMonth()->format('Y-m-d') }}');
        let fechaHasta = new Date('{{ today()->endOfMonth()->format('Y-m-d') }}');
        let fechaVencimientoPago = new Date('{{ today()->addMonth()->startOfMonth()->addDays(9)->format('Y-m-d') }}');
        document.getElementById('fechaDesdeServicio'+clienteId).value = fechaDesde.toISOString().split('T')[0];
        document.getElementById('fechaHastaServicio'+clienteId).value = fechaHasta.toISOString().split('T')[0];
        document.getElementById('fechaVencimientoPago'+clienteId).value = fechaVencimientoPago.toISOString().split('T')[0];
    }
    
    function setHoy(clienteId) {
        let fecha = new Date('{{ today()->format('Y-m-d') }}');
        document.getElementById('fechaDesdeServicio'+clienteId).value = fecha.toISOString().split('T')[0];
        document.getElementById('fechaHastaServicio'+clienteId).value = fecha.toISOString().split('T')[0];
        document.getElementById('fechaVencimientoPago'+clienteId).value = fecha.toISOString().split('T')[0];
    }
    
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
                    const form = document.getElementById('formFacturacionMensual');
                    const formData = new FormData(form);
                    const response = await axios.post(url, formData);
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
                Swal.fire('Facturación y notificación', 'Proceso finalizado', 'success').then(() => {
                    location.href = '{{ route("comprobantes.index") }}';
                });
            }
        });
    }
    
</script>
@endpush

</x-app-layout>