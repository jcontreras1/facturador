<x-app-layout>
    
    <div class="container">
        
            @guest
            @include('welcome')
            @endguest
        @auth        
        <div class="row">
            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100" style="background-color: coral;">
                    <div class="card-body">
                        <h5 class="card-title">Facturas Emitidas</h5>
                        <hr>
                        <div class="display-1 text-end">{{ App\Models\Arca\Comprobante::where('tipo_comprobante_id', 5)->whereMonth('created_at', now()->month)->count() }}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100" style="background-color: cadetblue;">
                    <div class="card-body">
                        <h5 class="card-title">Notas de crédito Emitidas</h5>
                        <hr>
                        <div class="display-1 text-end">{{ App\Models\Arca\Comprobante::where('tipo_comprobante_id', 6)->whereMonth('created_at', now()->month)->count() }}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100" style="background-color: indianred;">
                    <div class="card-body">
                        <h5 class="card-title">Total facturado</h5>
                        <hr>
                        <div class="display-3 text-end">${{ pesosargentinos(App\Models\Arca\Comprobante::where('tipo_comprobante_id', 5)->whereMonth('created_at', now()->month)->sum('importe_total')) }}</div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100" style="background-color: darkgoldenrod;">
                    <div class="card-body">
                        <h5 class="card-title">Total emitido en notas de crédito</h5>
                        <hr>
                        <div class="display-3 text-end">${{ pesosargentinos(App\Models\Arca\Comprobante::where('tipo_comprobante_id', 6)->whereMonth('created_at', now()->month)->sum('importe_total')) }}</div>
                    </div>
                </div>
            </div>
            
            
        </div>
        @endauth
    </div>
    
    
</x-app-layout>
