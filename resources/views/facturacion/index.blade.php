<x-app-layout>
    <div class="container">
        <x-title title="Facturación">
            <a href="{{ route('facturacion.create.c') }}" class="btn btn-success">Nueva Factura C</a>
        </x-title>


        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Número</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($facturas as $factura)
                <tr>
                    <td>{{ $factura->tipo_comprobante ?? '-' }}</td>
                    <td>{{ $factura->nro_factura ? str_pad($factura->nro_factura, 8, '0', STR_PAD_LEFT) : 'S/N' }}</td>
                    <td>{{ $factura->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $factura->cliente_id ? $factura->cliente->nombre : 'Cons. Final' }}</td>
                    <td>${{ pesosargentinos($factura->total) }}</td>
                    <td>
                        <a href="#" class="btn btn-warning btn-sm" title="Descargar en PDF"><i class="far fa-file-pdf"></i></a>
                        <a href="#" class="btn btn-info btn-sm" title="Enviar por Email"><i class="far fa-envelope"></i></a>
                        {{-- <a href="{{ route('facturacion.show', $factura) }}" class="btn btn-primary">Ver</a> --}}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $facturas->links() }}


    </div>
</x-app-layout>