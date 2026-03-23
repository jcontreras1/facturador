<x-app-layout>
	<div id="app">
		<div class="container">
			<factura-b :cliente="{{ $cliente ?? null }}" :condiciones-iva="{{ $condicionesIva }}" :ivas="{{ $ivas }}"></factura-b>
		</div>
	</div>
</x-app-layout>
