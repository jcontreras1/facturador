<x-app-layout>
    <div class="container">
        <x-title title="Clientes" urlBack="{{route('home')}}" >
            <a href="{{route('clientes.resumen')}}" class="btn btn-warning">Facturación mensual</a>

            <a href="{{route('clientes.create')}}" class="btn btn-success">Agregar cliente</a>
        </x-title>

            <form method="GET" action="{{ route('clientes.index') }}" class="row g-2 mb-3">
                <div class="col-12 col-md-6">
                    <input
                        type="search"
                        name="q"
                        class="form-control"
                        aria-label="Buscar clientes por razón social, CUIT o email"
                        placeholder="Buscar por razón social, CUIT o email..."
                        value="{{ $search }}"
                    >
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
                <div class="col-auto">
                    <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>

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
                            @php($tipoFactura = tipo_factura_para_receptor($cliente->condicionIva))
                            @if($tipoFactura)
                            <a href="{{route('cliente.comprobante.create.'.strtolower($tipoFactura), $cliente)}}" title="Nueva factura {{$tipoFactura}}" class="btn btn-success btn-sm"><i class="far fa-file-alt"></i> {{$tipoFactura}}</a>
                            @endif
                            <a href="{{route('clientes.edit', $cliente->id)}}" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i></a>
                            <a href="{{route('clientes.dashboard', $cliente->id)}}" class="btn btn-primary btn-sm"><i class="fas fa-list"></i></a>
                        </td>
                        
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $clientes->links() }}
            @else
            <em>{{ $search !== '' ? 'No se encontraron clientes para la búsqueda.' : 'No hay clientes registrados' }}</em>
            @endif
    </div>

</x-app-layout>