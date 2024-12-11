<x-app-layout>

    <div class="container mx-auto px-4">
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <h2 class=" mb-4 text-xl font-semibold d-flex justify-content-between">
                Agregar un cliente
                <a href="{{route('clientes.index')}}" class="btn btn-primary"><i class="fas fa-chevron-left"></i></a>
            </h2>
            <hr>
        </div>
        <div class="p-6 text-gray-900 dark:text-gray-100">
            <form action="{{route('clientes.store')}}" method="POST">
                @csrf
                
            </form>
        </div>
    </div>

</x-app-layout>