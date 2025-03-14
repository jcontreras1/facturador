<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura AFIP</title>
    <link rel="stylesheet" href="{{ public_path('assets/css/pdf.css') }}">
</head>
<body>
    
    <div class="container">
        
        <!-- Encabezado de la factura -->
        <table class="header-table">
            {{-- <tr> --}}
                {{-- <td colspan="3" style="text-align: center; border-bottom: 2px solid #000; padding: 10px;"> --}}
                    {{-- <strong>ORIGINAL</strong> --}}
                {{-- </td> --}}
            {{-- </tr> --}}
            <tr>
                <!-- Columna izquierda con logo o nombre -->
                <td class="left-column">
                    @if(variable_global('AVATAR'))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents(variable_global('AVATAR'))) }}" alt="Logo">
                    @else
                    <h1>{{ strtoupper(variable_global('RAZON_SOCIAL')) }}</h1>
                    @endif
                </td>
                <!-- Columna del medio con tipo de comprobante -->
                <td class="middle-column">
                    <table style="width: 100%; text-align: center;">
                        <tr>
                            <td style="border: 1px solid black;"><h1>{{ strtoupper($comprobante->tipoComprobante->letra) }}</h1></td>
                        </tr>
                        <tr>
                            <td><strong>Cod. {{ str_pad($comprobante->tipoComprobante->codigo_afip, 2, '0', STR_PAD_LEFT) }}</strong></td>
                        </tr>
                    </table>
                </td>
                <td class="right-column">
                    <h1>{{ tipo_comprobante($comprobante) }}</h1>
                </td>
            </tr>
            <tr>
                <td class="left-column" style="padding-left: 10px;">
                    <p>
                        <strong>Razón Social: </strong> {{variable_global('RAZON_SOCIAL')}}<br>
                        <strong>CUIT:</strong> {{ variable_global('CUIT_EMPRESA') }}
                        <br>
                        <strong>Domicilio Comercial: </strong> {{variable_global('DOMICILIO_FISCAL')}}<br>
                        <strong>Condición frente al IVA: </strong>{{variable_global('CONDICION_IVA')}}
                    </p>
                </td>
                <td></td>
                <td class="right-column" style="padding-left: 10px;">
                    <p>
                        <strong>Punto de Venta: {{ str_pad(variable_global('PUNTO_VENTA'), 5, '0', STR_PAD_LEFT) }} 
                        &nbsp;&nbsp;&nbsp;
                        Comp. Nro: {{ str_pad($comprobante->nro_comprobante, 8, '0', STR_PAD_LEFT) }}</strong>
                        <br>
                        <strong>Fecha de emisión:</strong> {{ $comprobante->created_at->format('d/m/Y') }}
                        <br>
                        <strong>Ingresos Brutos:</strong> {{ variable_global('CUIT_EMPRESA') }}
                        <br>
                        @if(variable_global('FECHA_INICIO_ACTIVIDADES') !== '')
                        <strong>Fecha de inicio de Actividades:</strong> {{ date('d/m/Y', strtotime(variable_global('FECHA_INICIO_ACTIVIDADES'))) }}
                        @endif
                    </p>
                </td>
            </tr>
        </table>
                {{-- productos y servicios --}}
                @if($comprobante->concepto !== 1) 
                <div class="bordered-container">
                    <strong>Período Facturado Desde: </strong> {{ $comprobante->fecha_servicio_desde ? date('d/m/Y', strtotime($comprobante->fecha_servicio_desde)) : '' }}
                    &nbsp;&nbsp;&nbsp;
                    <strong>Hasta: </strong> {{ $comprobante->fecha_servicio_hasta ? date('d/m/Y', strtotime($comprobante->fecha_servicio_hasta)) : '' }}
                    &nbsp;&nbsp;&nbsp;
                    <strong>Fecha de Vto. para el pago: </strong> {{ $comprobante->fecha_vencimiento_pago ? date('d/m/Y', strtotime($comprobante->fecha_vencimiento_pago)) : '' }}
                </div>
                @endif
        <div class="bordered-container">
            <strong>Condición IVA: </strong> {{$comprobante->condicionIvaReceptor?->descripcion}}
            <br>
            <strong>Apellido y Nombre o Razón Social: </strong> {{$comprobante->razon_social}}
            <br>
            <strong>CUIT: </strong> {{$comprobante->cuit_dni}}
            <br>
            <strong>Domicilio: </strong> {{$comprobante->domicilio}}<br>
        </div>

        
        <!-- Tabla de productos -->
        <table class="item-table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Cant.</th>
                    <th>U. Medida</th>
                    <th>Precio Unit.</th>
                    <th>% Bonif.</th>
                    {{-- <th>Imp. Bonif.</th> --}}
                    <th>Subtotal</th>
                    
                    @if($comprobante->tipoComprobante->codigo == 'A')
                    <th>IVA</th>
                    <th>Subtotal c/IVA</th>
                    
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($comprobante->detalle as $item)  
                <tr>
                    <td class="item-description">
                        @if($item->codigo)
                        [{{$item->codigo}}]
                        @endif
                        {{$item->descripcion}}
                    </td>
                    <td>{{$item->cantidad}}</td>
                    <td>{{$item->unidad_medida}}</td>
                    <td>{{pesosargentinos($item->importe_unitario)}}</td>
                    <td>{{$item->porcentaje_descuento}}</td>
                    {{-- <td>{{pesosargentinos($item->importe_descuento)}}</td> --}}
                    <td>{{pesosargentinos($item->importe_subtotal)}}</td>
                    
                    @if($comprobante->tipoComprobante->codigo == 'A')
                    
                    <td>{{ $item->iva->descripcion }}</td>
                    <td>{{pesosargentinos($item->importe_subtotal_con_iva)}}</td>
                    
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Pie de la factura -->
        <!-- Total de la factura en un cuadro -->
        <div class="ultra-footer">
            <div class="total-container">

                @if($comprobante->tipoComprobante->codigo == 'A')
                <span class="total-label">Importe Neto Gravado:</span><span class="total-value"> ${{pesosargentinos($comprobante->detalle->sum('importe_subtotal'))}}</span>
                <br>
                @foreach ($comprobante->detalle->groupBy('iva_id') as $ivaId => $items)
                    @php
                        $iva = $items->first()->iva;
                        $baseImp = $items->sum('importe_subtotal');
                        $importeIva = $items->sum(function($item) {
                            return $item->importe_subtotal_con_iva - $item->importe_subtotal;
                        });
                    @endphp
                    <span class="total-label">IVA {{$iva->descripcion}}:</span><span class="total-value"> ${{pesosargentinos($importeIva)}}</span>
                    <br>
                @endforeach
                <span class="total-label">Importe otros Tributos:</span><span class="total-value"> ${{pesosargentinos($comprobante->importe_total_tributos)}}</span>
                <br>
                <span class="total-label">Importe total:</span><span class="total-value"> ${{pesosargentinos($comprobante->importe_total)}}</span>
                
                @else

                {{-- Monotributo --}}
                
                <span class="total-label">Subtotal:</span><span class="total-value"> ${{pesosargentinos($comprobante->importe_neto)}}</span>
                <br>
                <span class="total-label">Importe otros Tributos:</span><span class="total-value"> ${{pesosargentinos($comprobante->importe_total_tributos)}}</span>
                <br>
                <span class="total-label">Importe total:</span><span class="total-value"> ${{pesosargentinos($comprobante->importe_total)}}</span>
                
                @endif
            </div>
            
            <div class="footer">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 25%; text-align: left;">
                            <img src="{!! imgBase64QRFactura($comprobante) !!}" alt="QR" />
                        </td>
                        <td style="width: 40%; text-align: left;">
                            <img width="80" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/arca_dark.png'))) }}" />
                            <br>
                            Comprobante autorizado
                        </td>
                        <td style="width: 35%; text-align: right;">
                            <strong>CAE Nº: </strong> {{$comprobante->cae}}<br>
                            
                            <strong>Fecha de Vto. de CAE: </strong> 
                            @if($comprobante->fecha_vencimiento_cae)
                            {{Carbon\Carbon::parse($comprobante->fecha_vencimiento_cae)->format('d/m/Y')}}<br>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            
        </div>
    </div>
    
</body>
</html>
