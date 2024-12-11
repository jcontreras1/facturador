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
                        <input name="razon_social" id="nuevo_cliente_razon_social" class="form-control" type="text" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label class="text">CUIT <span class="text-danger">*</span></label>
                        <input id="campo_cuit_cliente" name="cuit" id="nuevo_cliente_cuit" class="form-control" type="text">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label class="text">Domicilio</label>
                        <input name="domicilio" id="nuevo_cliente_domicilio" class="form-control" type="text">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label class="text">Teléfono</label>
                        <input name="telefono" id="nuevo_cliente_telefono" class="form-control" type="text">
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <label class="text">Email</label>
                        <input name="email" id="nuevo_cliente_email" class="form-control" type="email">
                    </div>
                </div>
            </form>
        </div>
    </div>

</x-app-layout>