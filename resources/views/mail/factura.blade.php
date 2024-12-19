<x-mail::message>
# Nueva factura

Hola, 


En los adjuntos, encontrarás la factura <strong>{{ $factura->tipo_comprobante . 
'-' . 
str_pad($factura->punto_venta, 8, '0', STR_PAD_LEFT) . 
'-' . 
str_pad($factura->nro_factura, 8, '0', STR_PAD_LEFT) }}</strong> emitida desde {{ config('app.name') }}.

No es necesario responder a este correo. Si tienes alguna pregunta, por favor, contacta a nuestro equipo de soporte en {{ config('mail.from.address') }}.
 
Gracias,<br>
{{ config('app.name') }}
</x-mail::message>