<x-app-layout>
    <div class="container">
        @guest
        @include('welcome')
        @endguest
        @auth
        <button id="toggleVisibility" class="btn btn-primary mb-3">Alternar Visibilidad</button>
        <div class="row">
            @php
                $facturas = App\Models\Arca\Comprobante::where('tipo_comprobante_id', 5)->whereMonth('created_at', now()->month)->count();
                $notasCredito = App\Models\Arca\Comprobante::where('tipo_comprobante_id', 6)->whereMonth('created_at', now()->month)->count();
                $totalFacturado = pesosargentinos(App\Models\Arca\Comprobante::where('tipo_comprobante_id', 5)->whereMonth('created_at', now()->month)->sum('importe_total'));
                $totalNotasCredito = pesosargentinos(App\Models\Arca\Comprobante::where('tipo_comprobante_id', 6)->whereMonth('created_at', now()->month)->sum('importe_total'));
            @endphp

            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100" style="background-color: coral;">
                    <div class="card-body">
                        <h5 class="card-title">Facturas Emitidas</h5>
                        <hr>
                        <div class="display-1 text-end toggle-value" data-value="{{ $facturas }}">***</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100" style="background-color: cadetblue;">
                    <div class="card-body">
                        <h5 class="card-title">Notas de crédito Emitidas</h5>
                        <hr>
                        <div class="display-1 text-end toggle-value" data-value="{{ $notasCredito }}">***</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100" style="background-color: indianred;">
                    <div class="card-body">
                        <h5 class="card-title">Total facturado</h5>
                        <hr>
                        <div class="display-3 text-end toggle-value" data-value="${{ $totalFacturado }}">***</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-4 mb-3">
                <div class="card h-100" style="background-color: darkgoldenrod;">
                    <div class="card-body">
                        <h5 class="card-title">Total emitido en notas de crédito</h5>
                        <hr>
                        <div class="display-3 text-end toggle-value" data-value="${{ $totalNotasCredito }}">***</div>
                    </div>
                </div>
            </div>
        </div>
        @endauth
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleValues = document.querySelectorAll(".toggle-value");
            const button = document.getElementById("toggleVisibility");
            
            function updateVisibility() {
                const showValues = localStorage.getItem("showValues") === "true";
                toggleValues.forEach(el => {
                    el.textContent = showValues ? el.dataset.value : "***";
                });
            }

            button.addEventListener("click", function() {
                const showValues = localStorage.getItem("showValues") === "true";
                localStorage.setItem("showValues", !showValues);
                updateVisibility();
            });

            updateVisibility();
        });
    </script>
</x-app-layout>
