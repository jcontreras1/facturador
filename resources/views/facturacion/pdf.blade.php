<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura AFIP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            height: 100%; /* Asegura que el cuerpo ocupe toda la altura de la página */
            
        }
        .container {
    width: 100%;
    padding: 10px;
    margin: 0 auto;
    min-height: 100vh; /* Ocupa toda la altura de la página */
    position: relative; /* Necesario para usar posicionamiento absoluto en el pie */
}
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
        }
        .header .details {
            font-size: 10px;
            text-align: left;
        }
        .invoice-info {
            margin-bottom: 20px;
        }
        .invoice-info .invoice-header {
            display: table;
            width: 100%;
        }
        .invoice-info .invoice-header div {
            width: 45%;
            display: inline-block;
        }
        .invoice-info .invoice-header div strong {
            font-size: 12px;
        }
        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .item-table th, .item-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }
        .item-table th {
            background-color: #f2f2f2;
        }
        .total {
            text-align: right;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 10px;
        }
        /* Ajuste de tabla de encabezado con logo */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-table td {
            vertical-align: middle;
            padding: 5px;
        }
        .header-table .left-column {
            width: 46%;
        }
        .header-table .middle-column {
            width: 8%;
            text-align: center;
        }
        .header-table .right-column {
            width: 46%;
            text-align: right;
        }
        .header-table img {
            max-height: 64px;
        }
        
        /* Estilo para el cuadro de los totales */
        .total-container {
            border: 1px solid #000;
            /* padding: 10px; */
            margin-top: 20px;
            width: 100%;
            text-align: right;
        }
        .total-container p {
            margin: 5px 0;
            padding: 10px;
        }
        .total-container .total-label {
            font-weight: bold;
        }

        .ultra-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    
    <div class="container">
        
        <!-- Encabezado de la factura -->
        <table class="header-table">
            <tr>
                <td colspan="3" style="text-align: center; border-bottom: 2px solid #000; padding: 10px;">
                    <strong>ORIGINAL</strong>
                </td>
            </tr>
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
                            <td style="border: 1px solid black;"><h1>{{ strtoupper($factura->tipo_comprobante) }}</h1></td>
                        </tr>
                        <tr>
                            <td><strong>Cod. {{ str_pad(idTipoFactura($factura->tipo_comprobante), 2, '0', STR_PAD_LEFT) }}</strong></td>
                        </tr>
                    </table>
                </td>
                <!-- Columna derecha con título FACTURA -->
                <td class="right-column">
                    <h1>FACTURA</h1>
                </td>
            </tr>
            <tr>
                <td class="left-column" style="padding-left: 10px;">
                    <p>
                        <strong>Razón Social:</strong> {{variable_global('RAZON_SOCIAL')}}<br>
                        <strong>Cuit:</strong>{{variable_global('CUIT_EMPRESA')}}
                    </p>
                </td>
                <td></td>
                <td class="right-column" style="padding-left: 10px;">
                    <p><strong>Punto de Venta: {{ str_pad(variable_global('PUNTO_VENTA'), 5, '0', STR_PAD_LEFT) }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Comp. Nro: {{ str_pad($factura->nro_factura, 8, '0', STR_PAD_LEFT) }}</strong></p>
                    <p><strong>Fecha de emisión:</strong> {{ $factura->created_at->format('d/m/Y') }}</p>
                    <p>
                        <strong>CUIT:</strong> {{ variable_global('CUIT_EMPRESA') }}
                        <br>
                        <strong>Ingresos Brutos:</strong> {{ variable_global('CUIT_EMPRESA') }}
                        <br>
                        <strong>Fecha de inicio de Actividades:</strong> --
                    </p>
                </td>
            </tr>
        </table>
        
        <!-- Tabla de productos -->
        <table class="item-table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Cantidad</th>
                    <th>U. Medida</th>
                    <th>Precio Unit.</th>
                    <th>% Bonif.</th>
                    <th>Imp. Bonif.</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($factura->items as $item)  
                <tr>
                    <td>{{$item->codigo}}</td>
                    <td>{{$item->descripcion}}</td>
                    <td>{{$item->cantidad}}</td>
                    <td>{{$item->unidad}}</td>
                    <td>{{pesosargentinos($item->precio_unitario)}}</td>
                    <td>{{$item->porcentaje_bonificacion}}</td>
                    <td>{{pesosargentinos($item->importe_bonificado)}}</td>
                    <td>{{pesosargentinos($item->total)}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Pie de la factura -->
        <!-- Total de la factura en un cuadro -->
        <div class="ultra-footer">
        <div class="total-container">
            <p>
                <span class="total-label">Subtotal:</span><span class="total-value">${{pesosargentinos($factura->total_neto)}}</span>
                <br>
                <span class="total-label">Importe otros Tributos:</span><span class="total-value">${{pesosargentinos($factura->total_iva)}}</span>
                <br>
                <span class="total-label">Importe total:</span><span class="total-value">${{pesosargentinos($factura->total)}}</span>
            </p>
        </div>
        
        <div class="footer">
            <table style="width: 100%;">
                <tr>
                    <td style="width: 25%; text-align: left;">
                        <img src="{!! imgBase64QRFactura($factura) !!}" alt="QR" />
                    </td>
                    <td style="width: 40%; text-align: left;">
                        <img width="70" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('img/arca_dark.png'))) }}" />
                        <br>
                        Comprobante autorizado
                    </td>
                    <td style="width: 35%; text-align: right;">
                        <strong>CAE Nº: </strong> {{$factura->cae}}<br>
                        <strong>Fecha de Vto. de CAE: </strong> {{$factura->vto_cae}}<br>
                    </td>
                </tr>
            </table>
        </div>
        
        </div>
    </div>
    
</body>
</html>
