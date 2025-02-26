<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura AFIP</title>
    <link rel="stylesheet" href="{{ asset('assets/css/termica.css') }}">
</head>
<body>
    <div class="container">
        @if(variable_global('AVATAR'))
        <img src="data:image/png;base64,{{ base64_encode(file_get_contents(variable_global('AVATAR'))) }}" alt="Logo" class="logo">
        @else
        <h1>{{ strtoupper(variable_global('RAZON_SOCIAL')) }}</h1>
        @endif
    </div>
    <hr>
    <!-- Detalles de la empresa y comprobante -->
    <div class="info">
        <p style="font-size: 12px"><strong>{{ strtoupper(variable_global('RAZON_SOCIAL')) }}</strong></p>
        <p><strong>CUIT Nro.:</strong> {{ variable_global('CUIT_EMPRESA') }}</p>
        <p><strong>Ing. Brutos:</strong> {{ variable_global('CUIT_EMPRESA') }}</p>
        <p><strong>Dirección:</strong> {{ variable_global('DOMICILIO_FISCAL') }}</p>
        <p><strong>{{ variable_global('CONDICION_IVA') }}</strong></p>
        @if(variable_global('FECHA_INICIO_ACTIVIDADES') !== '')
        <strong>Inicio de Actividades:</strong> {{ date('d/m/Y', strtotime(variable_global('FECHA_INICIO_ACTIVIDADES'))) }}
        @endif
        <br><br>
        <hr>
        <div class="" style="text-align: center;">
            Cod. {{ $comprobante->tipoComprobante->codigo_afip }} - {{ strtoupper($comprobante->tipoComprobante->descripcion) }}
            <br>
            <strong>
                {{ str_pad($comprobante->punto_venta, 5, '0', STR_PAD_LEFT) }} -
                {{ str_pad($comprobante->nro_comprobante, 8, '0', STR_PAD_LEFT) }}
            </strong>
            <br>
            {{ date('d/m/Y', strtotime($comprobante->fecha_emision)) }}&nbsp; {{ date('H:i', strtotime($comprobante->created_at)) }}
        </div>
    </div>
    <hr>
    <strong>Condición IVA: </strong> {{$comprobante->condicionIvaReceptor?->descripcion}}<br>
        @if($comprobante->razon_social)
        <strong>Cliente: </strong> {{$comprobante->razon_social}}<br>
        @endif
        @if($comprobante->cuit_dni)
        <strong>CUIT: </strong> {{$comprobante->cuit_dni}}<br>
        @endif
        @if($comprobante->domicilio)
        <strong>Domicilio: </strong> {{$comprobante->domicilio}}
        @endif
   
</div>

<!-- Productos y servicios -->
@if($comprobante->concepto !== 1 && false) 
<div class="">
    <strong>Período Facturado Desde:</strong> {{ $comprobante->fecha_servicio_desde ? date('d/m/Y', strtotime($comprobante->fecha_servicio_desde)) : '' }} 
    <br>
    <strong>Hasta:</strong> {{ $comprobante->fecha_servicio_hasta ? date('d/m/Y', strtotime($comprobante->fecha_servicio_hasta)) : '' }}
    <br>
    <strong>Fecha de Vto. para el pago: </strong> {{ $comprobante->fecha_vencimiento_pago ? date('d/m/Y', strtotime($comprobante->fecha_vencimiento_pago)) : '' }}
</div>
@endif


<hr>
<!-- Detalle de Productos -->
<table style="width: 100%;">
    <thead>
        <tr>
            <th>Detalle</th>
            {{-- <th>Cant.</th> --}}
            <th align="right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($comprobante->detalle as $item)  
        <tr>
            <td>{{$item->cantidad}} x {{$item->descripcion}}</td>
            {{-- <td></td> --}}
            <td align="right">${{pesosargentinos($item->importe_subtotal)}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Totales -->
<div class="total-container">
    <strong>Subtotal:</strong> ${{pesosargentinos($comprobante->importe_neto)}}<br>
    <strong>Importe otros Tributos:</strong> ${{pesosargentinos($comprobante->importe_total_tributos)}}<br>
    <strong>Total:</strong> ${{pesosargentinos($comprobante->importe_total)}}<br>
</div>
<div class="footer">
    <p><strong>CAE Nº:</strong> {{$comprobante->cae}}</p>
    <p><strong>Fecha de Vto. CAE:</strong> @if($comprobante->fecha_vencimiento_cae) {{Carbon\Carbon::parse($comprobante->fecha_vencimiento_cae)->format('d/m/Y')}} @endif</p>
    <img src="{!! imgBase64QRFactura($comprobante) !!}" alt="QR" class="qr">
</div>
</div>
</body>
</html>
