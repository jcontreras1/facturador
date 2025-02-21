<x-mail::message>
# Nuevo comprobante

Hola, 


Te adjuntamos tu {{$comprobante->tipoComprobante->descripcion}}: <strong>{{ titulo_comprobante($comprobante) }}</strong> 
emitida desde {{ config('app.name') }}.

Este es un mensaje generado de forma automática. No es necesario responder.
 
Gracias,<br>
{{ config('app.name') }}
</x-mail::message>