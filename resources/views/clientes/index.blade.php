<x-app-layout>
    <div class="container mx-auto px-4">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            
            <h2 class=" mb-4 text-xl font-semibold d-flex justify-content-between">
                Clientes
                <a href="{{route('clientes.create')}}" class="btn btn-success">Agregar cliente</a>
            </h2>
            <hr>
            @if(count($clientes))
            <table class="table table-striped table-sm" id="tabla_clientes">
                <thead>
                    <tr>
                        <th>Razón Social</th>
                        <th>Deuda</th>
                        <th>Honorarios</th>
                        @can('administrar')
                        <th>Opciones</th>
                        @endcan
                    </tr>
                </thead>
                <tbody>
                    @foreach($clientes as $cliente)
                    <tr class="row-click" data-url="{{route('cliente.show', $cliente->id)}}">
                        <td>
                            {{$cliente->razon_social}} 
                            @if($cliente->activo)
                            <i class="fas fa-circle text-success" data-toggle="tooltip" title="Cliente activo"></i>
                            @else
                            <i class="fas fa-times-circle text-danger" data-toggle="tooltip" title="Cliente inactivo"></i>
                            @endif
                            
                        </td>
                        <td>${{pesosargentinos($cliente->deuda)}}</td>
                        <td>${{pesosargentinos($cliente->honorarios)}}</td>
                        @can('administrar')
                        <td>
                            <a data-toggle="tooltip" title="Panel del cliente" class="btn btn-primary btn-sm" href="{{route('cliente.show', $cliente->id)}}"><i class="far fa-clipboard"></i></a>
                            @if(!sistema_vencido())
                            <button data-toggle="tooltip" title="Editar datos de Empresa" data-id="{{$cliente->id}}" data-url="{{route('cliente.update', $cliente->id)}}" class="btn btn-warning editar_cliente btn-sm" ><i class="fas fa-pencil-alt"></i></button>
                            <button data-toggle="tooltip" title="Eliminar" data-url="{{route('cliente.destroy', $cliente->id)}}" class="btn btn-danger eliminar_cliente btn-sm"><i class="fas fa-trash"></i></button>
                            @endif
                        </td>
                        @endcan
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