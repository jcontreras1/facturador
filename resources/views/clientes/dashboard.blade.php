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
        
        
        @if(!count($facturas))
        
        <em>No hay facturas registradas</em>
        @else
        <table class="table table-striped table-sm" id="tabla_facturas">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Importe</th>
                    <th>Estado</th>
                    <th>Opciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($cliente->facturas as $factura)
                <tr>
                    <td>{{$factura->fecha}}</td>
                    <td>{{$factura->importe}}</td>
                    <td>{{$factura->estado}}</td>
                    <td>
                        <a href="{{route('facturas.show', $factura->id)}}" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
            @endif
            
        </x-app-layout>