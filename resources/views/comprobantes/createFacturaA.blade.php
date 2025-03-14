<x-app-layout>
    <div id="app">
        <div class="container">
            <factura-a :cliente="{{ $cliente ?? null }}" :condiciones-iva="{{ $condicionesIva }}" :ivas="{{ $ivas }}"></factura-a>  
        </div>
    </div>
</x-app-layout>