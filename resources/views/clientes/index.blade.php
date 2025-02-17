<x-app-layout>
    <div class="container">
        <x-title title="Clientes" urlBack="{{route('home')}}" >
            <a href="{{route('clientes.create')}}" class="btn btn-success">Agregar cliente</a>
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

     @push('scripts')
     <script>
        const dashboardCliente = (e) => {
            console.log(e)
            // const url = e.target.getAttribute('data-url');
            // window.location.href = url;
        }
        </script>
    @endpush

</x-app-layout>