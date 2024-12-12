<x-app-layout>
    @include('items.partials.create')
    <div class="container">
        <div class="d-flex justify-content-between">
            <h3 class="text-lg font-semibold mb-6">Servicios</h3>
            <div>
                <a href="#" data-bs-toggle="modal" data-bs-target="#modal_create_item" class="btn btn-success"><i class="fas fa-plus"></i> Agregar servicio</a>
                <a href="{{route('home')}}" class="btn btn-primary"><i class="fas fa-chevron-left"></i></a>
            </div>
        </div>
        <hr>
        
        
        @if(!count($items))
        
        <em>No hay servicios registrados</em>
        @else
        <table class="table table-striped table-sm" id="tabla_items">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Valor Unit.</th>
                    <th>Opciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{$item->codigo}}</td>
                    <td>{{$item->descripcion}}</td>
                    <td>${{pesosargentinos($item->precio_unitario)}}</td>
                    <td>
                        <a href="{{route('items.edit', $item->id)}}" class="btn btn-warning" title="Editar"><i class="fas fa-pencil"></i></a>
                        <button onclick="deleteItem('{{route('items.destroy', $item->id)}}')" class="btn btn-danger" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        
        <form id="formDelete" method="POST">
            @csrf
            @method('DELETE')
        </form>
    </div>
    
    @push('scripts')
    <script>
        const deleteItem = (url) => {
            Swal.fire({
                title: '¿Eliminar este servicio?',
                // text: "¡No podrás revertir esto!",
                icon: 'question',
                showCancelButton: true,
                // confirmButtonColor: '#d33',
                // cancelButtonColor: '#3085d6',
                confirmButtonText: 'Eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('formDelete').action = url;
                    document.getElementById('formDelete').submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout>