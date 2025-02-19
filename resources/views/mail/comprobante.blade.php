<x-mail::message>
# Nuevo comprobante

Hola, 


En los adjuntos, encontrarás el comprobante <strong>{{ titulo_comprobante($comprobante) }}</strong> 
emitida desde {{ config('app.name') }}.

No es necesario responder a este correo. Si tienes alguna pregunta, por favor, contacta a nuestro equipo de soporte en 
{{ config('mail.from.address') }}.
 
Gracias,<br>
{{ config('app.name') }}
</x-mail::message>