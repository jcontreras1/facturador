<x-app-layout>
    <div class="container">
        @guest
        @include('welcome')
        @endguest
        @auth
        <div class="row">
            @php
            $facturas = App\Models\Arca\Comprobante::where('tipo_comprobante_id', 5)->whereMonth('created_at', now()->month)->count();
            $notasCredito = App\Models\Arca\Comprobante::where('tipo_comprobante_id', 6)->whereMonth('created_at', now()->month)->count();
            $totalFacturado = pesosargentinos(App\Models\Arca\Comprobante::where('tipo_comprobante_id', 5)->whereMonth('created_at', now()->month)->sum('importe_total'));
            $totalNotasCredito = pesosargentinos(App\Models\Arca\Comprobante::where('tipo_comprobante_id', 6)->whereMonth('created_at', now()->month)->sum('importe_total'));
            $totalAPagarIIBB = floatval(pesosargentinos(0.025 * App\Models\Arca\Comprobante::where('tipo_comprobante_id', 5)->whereMonth('created_at', now()->month)->sum('importe_total')));
            @endphp
            
            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100" style="background-color: coral;">
                    <div class="card-body">
                        <h5 class="card-title">Facturas del Mes</h5>
                        <hr>
                        <div class="d-flex justify-content-between align-items-baseline">
                            <i title="Mostrar u ocultar información" id="toggleVisibility" class="fas fa-eye fa-2x" role="button"></i>
                            <div class="display-1 text-end toggle-value" data-value="{{ $facturas }}">***</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100" style="background-color: cadetblue;">
                    <div class="card-body">
                        <h5 class="card-title">Notas de crédito del Mes</h5>
                        <hr>
                        <div class="d-flex justify-content-end">
                            <div class="display-1 text-end toggle-value" data-value="{{ $notasCredito }}">***</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100" style="background-color: indianred;">
                    <div class="card-body">
                        <h5 class="card-title">Total facturado</h5>
                        <hr>
                        <div class="d-flex justify-content-end">
                            <div class="display-3 text-end toggle-value" data-value="${{ $totalFacturado }}">***</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100" style="background-color: darkgoldenrod;">
                    <div class="card-body">
                        <h5 class="card-title">Total emitido en notas de crédito</h5>
                        <hr>
                        <div class="d-flex justify-content-end">
                            <div class="display-3 text-end toggle-value" data-value="${{ $totalNotasCredito }}">***</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100" style="background-color:green;">
                    <div class="card-body">
                        <h5 class="card-title
                        ">Total a pagar de IIBB</h5>
                        <hr>
                        <div class="d-flex justify-content-end">
                            <div class="display-3 text-end toggle-value" data-value="${{ $totalAPagarIIBB }}">***</div>
                        </div> 
                            <hr>
                            <i class="fas fa-info-circle"></i> <small>Pagando antes del 10: 
                                <strong class="toggle-value" data-value="${{ pesosargentinos($totalAPagarIIBB - 0.018 * $totalAPagarIIBB) }}">***</strong></small>
                            <br>
                            <i class="fas fa-info-circle"></i> <small>Pagando entre el 11 y el vencimiento: 
                                <strong class="toggle-value" data-value="${{ pesosargentinos($totalAPagarIIBB - (0.015 * $totalAPagarIIBB)) }}">***</strong></small>
                        </div>
                </div>
            </div>
            
        </div>
        @endauth
    </div>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleButtons = document.querySelectorAll("#toggleVisibility");
            const toggleValues = document.querySelectorAll(".toggle-value");
            
            function updateVisibility() {
                const showValues = localStorage.getItem("showValues") === "true";
                
                toggleButtons.forEach(btn => {
                    btn.classList.toggle("fa-eye", !showValues);
                    btn.classList.toggle("fa-eye-slash", showValues);
                });
                
                toggleValues.forEach(el => {
                    el.textContent = showValues ? el.dataset.value : "***";
                });
            }
            
            toggleButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    const showValues = localStorage.getItem("showValues") === "true";
                    localStorage.setItem("showValues", !showValues);
                    updateVisibility();
                });
            });
            
            updateVisibility();
        });
    </script>
</x-app-layout>
