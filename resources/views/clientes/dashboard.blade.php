<x-app-layout>
    @include('comprobantes.partials.enviarFacturaMail')
    @include('clientes.partials.modalAgregarServicio')
    <div class="container">
        <div class="d-flex justify-content-between">
            <h3 class="mb-3">
                Panel del cliente <strong>{{ strtoupper($cliente->nombre) }}</strong>
                <br>
                <small class="text-muted fs-5"><em>{{$cliente->tipo_documento}} {{ $cliente->cuit }} - {{$cliente->condicionIva?->descripcion}}</em></small>
            </h3>
            <div>
                <a href="{{route('cliente.comprobante.create.c', $cliente)}}" class="btn btn-success"><i class="fas fa-file-invoice"></i> Nueva Factura C</a>
                <a href="{{route('clientes.edit', $cliente)}}" class="btn btn-warning"><i class="fas fa-edit"></i> Editar</a>
                <a href="{{route('clientes.index')}}" class="btn btn-primary"><i class="fas fa-chevron-left"></i></a>
            </div>
        </div>
        <hr>
        
        <div class="card mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <form action="{{ route('cliente.toggleRequiereFacturacion', $cliente) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-secondary">
                                {{ $cliente->requiere_facturacion_mensual ? 'Deshabilitar Facturación Mensual' : 'Habilitar Facturación Mensual' }}
                            </button>
                        </form>
                        
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAgregarServicio">
                            Agregar Servicio Mensual <i class="fas fa-caret-down"></i>
                        </button>
                    </div>
                    <div class="fs-5">
                        @if($cliente->requiere_facturacion_mensual)
                            <i class="fas fs-3 fa-check text-success"></i> Este cliente tiene facturación mensual
                        @else
                            <i class="fas fs-3 fa-times text-danger"></i> Este cliente no tiene facturación mensual
                        @endif
                    </div>
                    </div>
                    <hr>
                    
                    @if(count($servicios) > 0)
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cantidad</th>
                                <th>Descripción</th>
                                @if(!esMonotributista())
                                <th>IVA ID</th>
                                <th>Importe Neto</th>
                                @endif
                                <th>Importe Unitario</th>
                                <th>Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($servicios as $servicio)
                            <tr>
                                <td>{{ $servicio->cantidad }}</td>
                                <td>{{ $servicio->descripcion }}</td>
                                @if(!esMonotributista())
                                <td>{{ $servicio->iva_id }}</td>
                                <td>{{ $servicio->importe_neto }}</td>
                                @endif
                                <td>${{ pesosargentinos($servicio->importe_total) }}</td>
                                <td>
                                    
                                    <form action="{{ route('servicioCliente.destroy', ['cliente' => $cliente, 'servicioCliente' => $servicio->id]) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>
            
            @if(!count($comprobantes))
            <em>No hay comprobantes registrados</em>
            @else
            <table class="table table-striped table-sm" id="tabla_comprobantes">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Importe</th>
                        <th>Concepto(s)</th>
                        <th>Opciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($comprobantes as $comprobante)
                    <tr class="h-100">
                        <td>{{date('d/m/y', strtotime($comprobante->fecha_emision))}}</td>
                        <td>${{pesosargentinos($comprobante->importe_total)}}</td>
                        <td>
                            @foreach($comprobante->detalle as $item)
                            {{$item->descripcion}}<br>
                            @endforeach
                        </td>
                        <td class="">
                            @if($comprobante->cae)
                            <a href="{{route('comprobante.descargar.pdf', $comprobante)}}" class="btn btn-warning btn-sm m-1" title="Descargar en PDF"><i class="far fa-file-pdf"></i></a>
                            <button 
                            onclick="urlEnviarMail('{{ route('comprobante.enviar.mail', $comprobante) }}', '{{$comprobante->cliente?->email}}')" 
                            data-bs-toggle="modal" data-bs-target="#modalEnviarFacturaMail" class="btn btn-info btn-sm" title="Enviar por Email"><i class="far fa-envelope"></i></button>
                            {{-- <a href="{{ route('facturacion.show', $comprobante) }}" class="btn btn-primary">Ver</a> --}}
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
        
        
        @push('scripts')
        <script>
            const btns = document.querySelectorAll('.btnBloquear');
            const btnSubmitEnviarFactura =document.getElementById('btnSubmitEnviarFactura');
            
            function urlEnviarMail(url, mail){
                document.getElementById('modalMailCliente').value = mail;
                document.getElementById('formEnviarFacturaMail').action = url;
            }
            
            btnSubmitEnviarFactura.addEventListener('click', (e) => {
                console.log(e);
                e.preventDefault();
                btns.forEach(btn => btn.disabled = true);
                btnSubmitEnviarFactura.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                form.submit();
                
            });
        </script>
        @endpush
    </x-app-layout>