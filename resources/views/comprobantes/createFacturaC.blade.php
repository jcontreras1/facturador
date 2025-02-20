<x-app-layout>
    <div id="app">
        <div class="container">
            <factura-c :cliente="{{ $cliente ?? null }}" :condiciones-iva="{{ $condicionesIva }}"></factura-c>  
        </div>
    </div>
</x-app-layout>