<x-app-layout>
    <div class="container">
        <div class="d-flex justify-content-between">
            <h3 class="text-lg font-semibold mb-6">Configuraciones</h3>
            <div>
                <a href="{{route('home')}}" class="btn btn-primary"><i class="fas fa-chevron-left"></i></a>
            </div>
        </div>
        <hr>
        <div class="row">
            
            {{-- Avatar de la Empresa --}}
            <div class="col-12 col-md-3 mb-3">
                <div class="card info h-100">
                    <div class="card-body">
                        @if(variable_global('AVATAR') && Storage::disk('public')->exists(str_replace(url('/storage'), '', variable_global('AVATAR'))))
                        <img class="img-fluid img-thumbnail" width="100%" src="{{variable_global('AVATAR')}}">
                        @else
                        <div class="alert alert-warning">No hay una imagen cargada.</div>
                        @endif
                        <div class="py-1"></div>
                        <h6 class="card-title">Imagen para el membrete de los posibles documentos.</h6>
                        <div class="py-1"></div>
                        
                        <form method="POST" action="{{route('config.avatar')}}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" class="form-control form-control-sm" name="avatar" accept="image/*" required />
                            <hr>
                            <button class="btn btn-success">Guardar</button>
                            @if(variable_global('AVATAR'))
                            <button onclick="eliminarAvatar()" class="btn btn-danger" type="button" title="Remover imagen"><i class="fas fa-trash"></i></button>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            
            {{-- Cuit --}}
            <div class="col-12 col-md-3 mb-3">
                <div class="card info h-100">
                    <div class="card-body">
                        <div class="display-3">
                            <i class="far fa-building text-muted"></i>
                        </div>
                        <h5 class="card-title">CUIT/CUIL</h5>
                        <small>Obligatorio para la firma digital de facturas</small>
                        <hr>
                        <form method="post" action="{{route('config.update', obj_variable_global('CUIT_EMPRESA')->id)}}" >
                            @csrf @method('patch')
                            <input class="form form-control mb-3" name="valor" value="{{variable_global('CUIT_EMPRESA')}}">
                            <button class="btn btn-success">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
            
            {{-- Nombre empresa --}}
            <div class="col-12 col-md-3 mb-3">
                <div class="card info h-100">
                    <div class="card-body">
                        <div class="display-3">
                            <i class="far fa-building text-muted"></i>
                        </div>
                        <h5 class="card-title">Razón Social</h5>
                        <small>Tal cual está presentada en afip</small>
                        <hr>
                        
                        <form method="post" action="{{route('config.update', obj_variable_global('RAZON_SOCIAL')->id)}}" >
                            @csrf @method('patch')
                            <input class="form form-control mb-3" name="valor" value="{{variable_global('RAZON_SOCIAL')}}">
                            <button class="btn btn-success">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Domicilio Fiscal --}}
            <div class="col-12 col-md-3 mb-3">
                <div class="card info h-100">
                    <div class="card-body">
                        <div class="display-3">
                            <i class="far fa-building text-muted"></i>
                        </div>
                        <h5 class="card-title">Domicilio Fiscal</h5>
                        {{-- <small>Tal cual está presentada en afip</small> --}}
                        <hr>
                        
                        <form method="post" action="{{route('config.update', obj_variable_global('DOMICILIO_FISCAL')->id)}}" >
                            @csrf @method('patch')
                            <input class="form form-control mb-3" name="valor" value="{{variable_global('DOMICILIO_FISCAL')}}">
                            <button class="btn btn-success">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Fecha inicio actividades --}}
            <div class="col-12 col-md-3 mb-3">
                <div class="card info h-100">
                    <div class="card-body">
                        <div class="display-3">
                            <i class="far fa-building text-muted"></i>
                        </div>
                        <h5 class="card-title">Fecha de inicio de actividades</h5>
                        <small>Si no se dispone del dato, solicitar al contador/contadora</small>
                        <hr>
                        
                        <form method="post" action="{{route('config.update', obj_variable_global('FECHA_INICIO_ACTIVIDADES')->id)}}" >
                            @csrf @method('patch')
                            <input class="form form-control mb-3" type="date" name="valor" value="{{variable_global('FECHA_INICIO_ACTIVIDADES')}}">
                            <button class="btn btn-success">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>

                      {{-- Condicion frente al IVA --}}
                      <div class="col-12 col-md-3 mb-3">
                        <div class="card info h-100">
                            <div class="card-body">
                                <div class="display-3">
                                    <i class="far fa-building text-muted"></i>
                                </div>
                                <h5 class="card-title">Condición frente al IVA</h5>
                                <small></small>
                                <hr>
                                <form method="post" action="{{route('config.update', obj_variable_global('CONDICION_IVA')->id)}}" >
                                    @csrf @method('patch')
                                    <select class="form-control form-select mb-3" name="valor">
                                        {{-- Sin valor --}}
                                        <option value="">Seleccione una opción</option>
                                        {{-- Opciones --}}
                                        <option value="IVA Responsable Inscripto" {{variable_global('CONDICION_IVA') == 'IVA Responsable Inscripto' ? 'selected' : ''}}>IVA Responsable Inscripto</option>
                                        <option value="IVA Responsable No Inscripto" {{variable_global('CONDICION_IVA') == 'IVA Responsable No Inscripto' ? 'selected' : ''}}>IVA Responsable No Inscripto</option>
                                        <option value="IVA Exento" {{variable_global('CONDICION_IVA') == 'IVA Exento' ? 'selected' : ''}}>IVA Exento</option>
                                        <option value="IVA No Responsable" {{variable_global('CONDICION_IVA') == 'IVA No Responsable' ? 'selected' : ''}}>IVA No Responsable</option>
                                        <option value="IVA Sujeto Exento" {{variable_global('CONDICION_IVA') == 'IVA Sujeto Exento' ? 'selected' : ''}}>IVA Sujeto Exento</option>
                                        <option value="Consumidor Final" {{variable_global('CONDICION_IVA') == 'Consumidor Final' ? 'selected' : ''}}>Consumidor Final</option>
                                        <option value="Responsable Monotributo" {{variable_global('CONDICION_IVA') == 'Responsable Monotributo' ? 'selected' : ''}}>Responsable Monotributo</option>
                                        <option value="Sujeto No Categorizado" {{variable_global('CONDICION_IVA') == 'Sujeto No Categorizado' ? 'selected' : ''}}>Sujeto No Categorizado</option>
                                        <option value="Proveedor del Exterior" {{variable_global('CONDICION_IVA') == 'Proveedor del Exterior' ? 'selected' : ''}}>Proveedor del Exterior</option>
                                        <option value="Cliente del Exterior" {{variable_global('CONDICION_IVA') == 'Cliente del Exterior' ? 'selected' : ''}}>Cliente del Exterior</option>
                                        <option value="IVA Liberado – Ley Nº 19.640" {{variable_global('CONDICION_IVA') == 'IVA Liberado – Ley Nº 19.640' ? 'selected' : ''}}>IVA Liberado – Ley Nº 19
                                    </select>
                                    <button class="btn btn-success">Guardar</button>
                                </form>
                            </div>
                        </div>
                    </div>
            
            {{-- Arca --}}
            <div class="col-12 col-md-3 mb-3">
                <div class="card info h-100">
                    <div class="card-body">
                        <div class="display-3">
                            <img src="{{asset('img/arca.png')}}" width="" class="bg-secondary rounded-1 p-2 mb-2" />
                        </div>
                        <h5 class="card-title">Clave privada para firma de certificados y facturas</h5>
                        <hr>
                        @if(!variable_global('AFIP_KEY'))
                        <form method="post" action="{{route('afip.makeKey')}}" >
                            @csrf
                            <button class="btn btn-warning">Generar clave <i class="fas fa-key"></i></button>
                        </form>
                        @else
                        <div class="alert alert-info"><i class="fas fa-info-circle"></i> Ya existe una clave generada. No es necesario generar una nueva.</div>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- Arca Descargar solicitud --}}
            <div class="col-12 col-md-3 mb-3">
                <div class="card info h-100">
                    <div class="card-body">
                        <div class="display-3">
                            <i class="fas fa-file-contract text-muted"></i>
                        </div>
                        <h5 class="card-title">Descargar archivo de solicitud de certificado (Para presentar en ARCA)</h5>
                        <hr>
                        <a href="{{route('afip.makeCSR')}}" class="btn btn-primary">Descargar Solicitud CSR</a>
                    </div>
                </div>
            </div>
            
            {{-- Certificado para facturación de la empresa --}}
            <div class="col-12 col-md-3 mb-3">
                <div class="card info h-100">
                    <div class="card-body">
                        @if(variable_global('AFIP_CERTIFICADO'))
                        @if(variable_global('VENCIMIENTO_CERTIFICADO') && Carbon\Carbon::parse(variable_global('VENCIMIENTO_CERTIFICADO'))->isPast())
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Certificado vencido</strong>
                            <br>
                            Vencido {{ Carbon\Carbon::parse(variable_global('VENCIMIENTO_CERTIFICADO'))->diffForHumans() }}. Descargue un nuevo CSR y preséntelo en ARCA para obtener un nuevo certificado. O póngase en contacto con un contador para continuar con la gestión.
                        </div>
                        @else
                        <div class="alert alert-success">
                            <i class="fas fa-check"></i>
                            Certificado cargado. 
                            
                            Vencimiento: <strong>{{ date('d/m/Y', strtotime(variable_global('VENCIMIENTO_CERTIFICADO'))) }}</strong> <em><small>({{ Carbon\Carbon::parse(variable_global('VENCIMIENTO_CERTIFICADO'))->diffForHumans() }})</small></em>
                        </div>
                        @endif
                        @else
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            No hay certificado cargado aún
                        </div>
                        @endif
                        <div class="py-1"></div>
                        <h6 class="card-title">Certificado emitido por Afip (usualmente entregado por contador)</h6>
                        <div class="py-1"></div>
                        
                        <form method="POST" action="{{route('config.newCert')}}" enctype="multipart/form-data">
                            @csrf
                            <input type="file" class="form-control form-control-sm mb-2" required name="cert" value="{{ old('cert') }}">
                            <input type="date" class="form-control form-control-sm" name="vencimiento" value="{{variable_global('VENCIMIENTO_CERTIFICADO') }}" required />
                            <hr>
                            <button class="btn btn-success">Renovar</button>
                        </form>
                    </div>
                </div>
            </div>
            
            {{-- Punto de venta de facturación --}}
            <div class="col-12 col-md-3 mb-3">
                <div class="card info h-100">
                    <div class="card-body">
                        <div class="display-3">
                            <i class="far fa-building text-muted"></i>
                        </div>
                        <h5 class="card-title">Punto de Venta</h5>
                        <small>Sin este valor no se puede facturar</small>
                        <hr>
                        <form method="post" action="{{route('config.update', obj_variable_global('PUNTO_VENTA')->id)}}" >
                            @csrf @method('patch')
                            <input class="form form-control mb-3" name="valor" value="{{variable_global('PUNTO_VENTA')}}">
                            <button class="btn btn-success">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
            
            
        </div>
        
        <form method="POST" id="formEliminarMembrete" action="{{route('avatar.destroy')}}">@csrf @method('DELETE')</form>
        
        @push('scripts')
        <script>
            const eliminarAvatar = () => {
                Swal.fire({
                    icon: 'question',
                    title: '¿Eliminar la imagen del membrete?',
                    showCancelButton: true,
                    confirmButtonText: 'Si',
                    cancelButtonText: 'No',
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('formEliminarMembrete').submit();
                    }
                });
            }
        </script>
        @endpush
    </x-app-layout>