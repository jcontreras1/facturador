<x-app-layout>
    
    <div class="container">
        <x-title title="Clientes" urlBack="{{route('clientes.index')}}" />
        <form action="{{route('clientes.store')}}" method="POST">
            @csrf
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title
                    ">Sección de facturación</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <label class="text">CUIT/CUIL/DNI</label>
                            <div class="input-group">
                                <input name="cuit" id="nuevo_cliente_cuit" class="form-control" type="text">
                                <button title="Buscar en padron de afip" class="btn btn-secondary" type="button" id="searchCliente">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="text">Condición de IVA</label>
                            <select name="condicion_iva_receptor_id" class="form-select">
                                @foreach (\App\Models\Arca\IvaReceptor::all() as $condicion)
                                <option value="{{$condicion->id}}" @if($condicion->id == 7) selected @endif >{{$condicion->descripcion}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="text">Tipo Documento</label>
                            <select name="tipo_documento_afip" class="form-select">
                                @foreach (\App\Models\Arca\TipoDocumento::getOptions() as $tipo)
                                <option value="{{$tipo['value']}}" @if($tipo['value'] == 80) selected @endif >{{$tipo['descripcion']}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!-- Spinner element -->
                    <div id="spinner" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Buscando...
                    </div>
                </div>
            </div>
            
            
            <div class="row">
                <div class="col-12">
                    <label class="text">Razón Social <span class="text-danger">*</span></label>
                    <input name="nombre" id="nuevo_cliente_razon_social" class="form-control" type="text" required>
                </div>
            </div>
            
            <div class="row">
                <div class="col-12">
                    <label class="text">Domicilio</label>
                    <input name="direccion" id="nuevo_cliente_domicilio" class="form-control" type="text">
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
    
    
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('searchCliente').addEventListener('click', async function(){
                let cuit = document.getElementById('nuevo_cliente_cuit').value;
                if(!cuit)
                return;
                
                let url = `/api/contribuyente/${cuit}`;
                let spinner = document.getElementById('spinner');
                
                try {
                    spinner.style.display = 'block'; // Show spinner
                    const response = await axios.get(url);
                    if(response.status === 200) {
                        let data = response.data;
                        document.getElementById('nuevo_cliente_razon_social').value = data.razonSocial;
                        document.getElementById('nuevo_cliente_domicilio').value = data.domicilio;
                    }
                } catch (error) {
                    console.log(error);
                    let msgError = error.response.data.error ?? 'No se encontró el usuario con el documento ingresado.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: msgError,
                    });
                } finally {
                    spinner.style.display = 'none'; // Hide spinner
                }
            });
        });
    </script>
    @endpush
</x-app-layout>