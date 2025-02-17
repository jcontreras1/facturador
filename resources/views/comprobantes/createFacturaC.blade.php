<x-app-layout>
    <div class="container">
          <factura-c :cliente="{{ $cliente ?? null }}" :condiciones-iva="{{ $condicionesIva }}"></factura-c>  
    </div>
</x-app-layout>