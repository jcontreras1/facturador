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
                    <tr>
                        <td role="button" onClick="window.location.href='{{route('clientes.dashboard', $cliente->id)}}'">
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
                            @if(esMonotributista())
                            <a href="{{route('cliente.comprobante.create.c', $cliente)}}" title="Nueva factura C" class="btn btn-success btn-sm"><i class="far fa-file-alt"></i></a>
                            @endif
                            <a href="{{route('clientes.edit', $cliente->id)}}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
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

</x-app-layout>