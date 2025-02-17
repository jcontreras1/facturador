<x-app-layout>
    <div class="container">
        <div class="d-flex justify-content-between">
            <h3 class="text-lg font-semibold mb-6">Panel del cliente</h3>
            <div>
                <a href="{{route('clientes.facturar', $cliente)}}" class="btn btn-success"><i class="fas fa-file-invoice"></i> Facturar</a>
                <a href="{{route('clientes.index')}}" class="btn btn-primary"><i class="fas fa-chevron-left"></i></a>
            </div>
        </div>
        <hr>
        
        @if(!count($comprobantes))
        
        <em>No hay comprobantes registrados</em>
        @else
        <table class="table table-striped table-sm" id="tabla_comprobantes">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Importe</th>
                    <th>Estado</th>
                    <th>Opciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($comprobantes as $comprobante)
                <tr>
                    <td>{{date('d/m/y', strtotime($comprobante->fecha_emision))}}</td>
                    <td>${{pesosargentinos($comprobante->importe_total)}}</td>
                    <td>{{$comprobante->estado}}</td>
                    <td>
                        <a href="#" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>
                        {{-- <a href="{{route('comprobantes.show', $comprobante->id)}}" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a> --}}
                    </td>
                </tr>
                @endforeach
            </tbody>
            @endif
            
        </x-app-layout>