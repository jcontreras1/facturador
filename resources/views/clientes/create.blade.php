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
                <div class="row">
                    <div class="col-12">
                        <label class="text">Razón Social <span class="text-danger">*</span></label>
                        <input name="nombre" id="nuevo_cliente_razon_social" class="form-control" type="text" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label class="text">CUIT/CUIL/DNI</label>
                        <input name="cuit" id="nuevo_cliente_cuit" class="form-control" type="text">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label class="text">Domicilio</label>
                        <input name="domicilio" class="form-control" type="text">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label class="text">Teléfono</label>
                        <input name="telefono" class="form-control" type="text">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">
                        <label class="text">Email</label>
                        <input name="email" class="form-control" type="email">
                    </div>
                </div>

                <button class="btn btn-success">Guardar</button>
                
            </form>
        </div>
    </div>
    
    
    @push('scripts')
    <script>
        
    </script>
    @endpush
</x-app-layout>