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
        }
        .container {
            width: 100%;
            padding: 10px;
            margin: 0 auto;
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
                    <img src="data:image/png;base64,{{ $avatar }}" alt="Logo">
                @else
                    <h1>{{ strtoupper(variable_global('RAZON_SOCIAL')) }}</h1>
                @endif
            </td>
            <!-- Columna del medio con tipo de comprobante -->
            <td class="middle-column">
                <table style="width: 100%; text-align: center;">
                    <tr>
                        <td><h1>{{ strtoupper($factura->tipo_comprobante) }}</h1></td>
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
                <p><strong>Razón Social:</strong> Empresa XYZ S.A.</p>
                <p><strong>Cuit:</strong> 20-12345678-9</p>
                <p><strong>Dirección:</strong> Calle Ficticia 123, Ciudad, Argentina</p>
                <p><strong>Condición ante el IVA:</strong> Responsable Inscripto</p>
            </td>
            <td></td>
            <td class="right-column" style="padding-left: 10px;">
                <p><strong>Punto de Venta: {{ str_pad(variable_global('PUNTO_VENTA'), 5, '0', STR_PAD_LEFT) }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Comp. Nro: {{ str_pad($factura->nro_factura, 8, '0', STR_PAD_LEFT) }}</strong></p>
                <p><strong>Fecha de emisión:</strong> {{ $factura->created_at->format('d/m/Y') }}</p>
                <p><strong>CUIT:</strong> {{ variable_global('CUIT_EMPRESA') }}</p>
                <p><strong>Ingresos Brutos:</strong> {{ variable_global('CUIT_EMPRESA') }}</p>
                <p><strong>Fecha de inicio de Actividades:</strong> --</p>
            </td>
        </tr>
    </table>

    <!-- Información de la factura -->
    <div class="invoice-info">
        <div class="invoice-header">
            <div>
                <p><strong>Fecha de emisión:</strong> 18/12/2024</p>
                <p><strong>Tipo de comprobante:</strong> Factura A</p>
                <p><strong>Número de factura:</strong> 0001-00012345</p>
            </div>
            <div>
                <p><strong>Fecha de vencimiento:</strong> 18/01/2025</p>
                <p><strong>CAE:</strong> 12345678901234</p>
                <p><strong>CAE Vto:</strong> 18/12/2024</p>
            </div>
        </div>
    </div>

    <!-- Tabla de productos -->
    <table class="item-table">
        <thead>
            <tr>
                <th>Cantidad</th>
                <th>Descripción</th>
                <th>Precio Unitario</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Producto A</td>
                <td>$500.00</td>
                <td>$500.00</td>
            </tr>
            <tr>
                <td>2</td>
                <td>Producto B</td>
                <td>$300.00</td>
                <td>$600.00</td>
            </tr>
            <tr>
                <td>1</td>
                <td>Producto C</td>
                <td>$150.00</td>
                <td>$150.00</td>
            </tr>
        </tbody>
    </table>

    <!-- Total de la factura -->
    <div class="total">
        <p><strong>Subtotal:</strong> $1250.00</p>
        <p><strong>I.V.A. (21%):</strong> $262.50</p>
        <p><strong>Total a Pagar:</strong> $1512.50</p>
    </div>

    <!-- Pie de la factura -->
    <div class="footer">
        <p>Este documento no es un comprobante fiscal autorizado. Generado a través de AFIP.</p>
        <p>Para consultar el estado de la factura ingrese a www.afip.gob.ar</p>
    </div>

</div>

</body>
</html>
