<x-app-layout>
    <div class="container">
        <x-title title="Clientes" urlBack="{{route('home')}}" >
            <a href="{{route('clientes.resumen')}}" class="btn btn-warning">Facturación mensual</a>

            <a href="{{route('clientes.create')}}" class="btn btn-success">Agregar cliente</a>
        </x-title>

            
            @if(count($clientes))
            <table class="table table-striped table-sm" id="tabla_clientes">
                <thead>
                    <tr>
                        <th>Razón Social</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($clientes as $cliente)
                    <tr role="button" onClick="window.location.href='{{route('clientes.dashboard', $cliente->id)}}'">
                        <td>
                            {{$cliente->nombre}} 
                            @if($cliente->requiere_facturacion_mensual)
                                <i class="fas fa-check-circle text-success" title="Requiere facturación mensual"></i>
                                @else
                                <i class="fas fa-times-circle text-secondary" title="No requiere facturación mensual"></i>

                                
                            @endif
                            @if(!$cliente->tipo_documento)
                        <span class="badge bg-danger">Falta el tipo de documento</span>
                        @endif
                        @if(!$cliente->condicion_iva_receptor_id)
                        <span class="badge bg-danger">Falta la condicion frente al IVA</span>
                        @endif
                        </td>

                        <td>
                            <a href="{{route('clientes.dashboard', $cliente->id)}}" class="btn btn-primary btn-sm"><i class="fas fa-list"></i></a>
                        </td>
                        
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <em>No hay clientes registrados</em>
            @endif

        </div>
    </div>

    <form id="formFacturacionMensual" action="{{ route('clientes.facturacion') }}" method="POST" class="d-inline">
        @csrf
    </form>

     @push('scripts')
     <script>

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('btnFacturacionMensual').addEventListener('click', function() {
                Swal.fire({
                    title: 'Facturación mensual',
                    html: `Este proceso le emite una factura con sus correspondientes conceptos a todos los clientes con el ícono: <i class='fas fa-check-circle text-success'></i>. <br>
                    ¿Confirma el proceso? o prefiere <a href="{{route('clientes.index')}}">Ver un resumen</a>`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Facturar',
                    cancelButtonText: 'Cancelar',
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('formFacturacionMensual').submit();
                    }
                });
            });
        });

        const dashboardCliente = (e) => {
            console.log(e)
            // const url = e.target.getAttribute('data-url');
            // window.location.href = url;
        }
        </script>
    @endpush

</x-app-layout>