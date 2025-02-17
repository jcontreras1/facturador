<x-mail::message>
# Nuevo comprobante

Hola, 


En los adjuntos, encontrarás el comprobante <strong>{{ $comprobante->tipoComprobante->codigo . 
'-' . 
str_pad($comprobante->punto_venta, 8, '0', STR_PAD_LEFT) . 
'-' . 
str_pad($comprobante->nro_comprobante, 8, '0', STR_PAD_LEFT) }}</strong> 
emitida desde {{ config('app.name') }}.

No es necesario responder a este correo. Si tienes alguna pregunta, por favor, contacta a nuestro equipo de soporte en 
{{ config('mail.from.address') }}.
 
Gracias,<br>
{{ config('app.name') }}
</x-mail::message>