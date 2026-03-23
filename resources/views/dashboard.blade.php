<x-app-layout>
    <div class="container">
        @guest
        @include('welcome')
        @endguest
        @auth
        @php
        $facturasQuery = App\Models\Arca\Comprobante::where('tipo_comprobante_id', 5)->whereMonth('created_at', now()->month);
        $notasCreditoQuery = App\Models\Arca\Comprobante::where('tipo_comprobante_id', 6)->whereMonth('created_at', now()->month);

        $facturas = $facturasQuery->count();
        $notasCredito = $notasCreditoQuery->count();

        $totalFacturadoBruto = (float) $facturasQuery->sum('importe_total');
        $totalNotasCreditoBruto = (float) $notasCreditoQuery->sum('importe_total');
        $baseIibb = max($totalFacturadoBruto - $totalNotasCreditoBruto, 0);
        $alicuotaIibb = 0.025;
        $totalIibb = $baseIibb * $alicuotaIibb;
        $totalIibbDescuento18 = $totalIibb * (1 - 0.18);
        $totalIibbDescuento15 = $totalIibb * (1 - 0.15);

        $totalFacturado = pesosargentinos($totalFacturadoBruto);
        $totalNotasCredito = pesosargentinos($totalNotasCreditoBruto);
        $baseImponibleIibb = pesosargentinos($baseIibb);
        $totalAPagarIIBB = pesosargentinos($totalIibb);
        $totalAPagarIIBBDel1Al10 = pesosargentinos($totalIibbDescuento18);
        $totalAPagarIIBBDel11Al23 = pesosargentinos($totalIibbDescuento15);
        @endphp

        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h2 class="mb-1">Resumen del mes</h2>
                <p class="text-muted mb-0">Facturación, notas de crédito y proyección de Ingresos Brutos.</p>
            </div>
            <button type="button" id="toggleVisibility" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-eye me-1"></i> Mostrar u ocultar importes
            </button>
        </div>

        <div class="row">
            <div class="col-12 col-md-6 col-xl-3 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #ff8a65, #ff7043); color: #fff;">
                    <div class="card-body">
                        <div class="text-uppercase small opacity-75 mb-2">Comprobantes emitidos</div>
                        <h5 class="card-title mb-3">Facturas del mes</h5>
                        <div class="display-2 text-end toggle-value" data-value="{{ $facturas }}">***</div>
                        <div class="mt-3 small opacity-75">Cantidad de facturas emitidas en el mes actual.</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #4db6ac, #00897b); color: #fff;">
                    <div class="card-body">
                        <div class="text-uppercase small opacity-75 mb-2">Ajustes del mes</div>
                        <h5 class="card-title mb-3">Notas de crédito</h5>
                        <div class="display-2 text-end toggle-value" data-value="{{ $notasCredito }}">***</div>
                        <div class="mt-3 small opacity-75">Estas notas reducen la base imponible para IIBB.</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #ef5350, #c62828); color: #fff;">
                    <div class="card-body">
                        <div class="text-uppercase small opacity-75 mb-2">Facturación bruta</div>
                        <h5 class="card-title mb-3">Total facturado</h5>
                        <div class="display-5 text-end toggle-value" data-value="${{ $totalFacturado }}">***</div>
                        <div class="mt-3 small opacity-75">Suma total de facturas emitidas durante el mes.</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #f9a825, #f57f17); color: #1f1f1f;">
                    <div class="card-body">
                        <div class="text-uppercase small opacity-75 mb-2">Descuentos emitidos</div>
                        <h5 class="card-title mb-3">Total en notas de crédito</h5>
                        <div class="display-5 text-end toggle-value" data-value="${{ $totalNotasCredito }}">***</div>
                        <div class="mt-3 small opacity-75">Este importe se descuenta de la base sobre la que calculás IIBB.</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-3 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #42a5f5, #1565c0); color: #fff;">
                    <div class="card-body">
                        <div class="text-uppercase small opacity-75 mb-2">Base neta mensual</div>
                        <h5 class="card-title mb-3">Base imponible IIBB</h5>
                        <div class="display-5 text-end toggle-value" data-value="${{ $baseImponibleIibb }}">***</div>
                        <div class="mt-3 small opacity-75">Fórmula: facturado menos notas de crédito del mismo período.</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-9 mb-3">
                <div class="card h-100 border-0 shadow-sm" style="background: linear-gradient(135deg, #66bb6a, #2e7d32); color: #fff;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                            <div>
                                <div class="text-uppercase small opacity-75 mb-2">Ingresos Brutos</div>
                                <h5 class="card-title mb-1">Escenarios de pago sobre el 2,5%</h5>
                                <div class="small opacity-75">La base imponible ya descuenta las notas de crédito del mes.</div>
                            </div>
                            <div class="text-end">
                                <div class="small opacity-75">Sin descuento</div>
                                <div class="fs-3 toggle-value" data-value="${{ $totalAPagarIIBB }}">***</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="bg-white bg-opacity-10 rounded p-3 h-100">
                                    <div class="small text-uppercase opacity-75">Pago del 1 al 10</div>
                                    <div class="fs-4 fw-semibold toggle-value" data-value="${{ $totalAPagarIIBBDel1Al10 }}">***</div>
                                    <div class="small opacity-75">18% de descuento sobre el importe de IIBB.</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="bg-white bg-opacity-10 rounded p-3 h-100">
                                    <div class="small text-uppercase opacity-75">Pago del 11 al 23</div>
                                    <div class="fs-4 fw-semibold toggle-value" data-value="${{ $totalAPagarIIBBDel11Al23 }}">***</div>
                                    <div class="small opacity-75">15% de descuento sobre el importe de IIBB.</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="bg-white bg-opacity-10 rounded p-3 h-100">
                                    <div class="small text-uppercase opacity-75">Pago luego del 23</div>
                                    <div class="fs-4 fw-semibold toggle-value" data-value="${{ $totalAPagarIIBB }}">***</div>
                                    <div class="small opacity-75">Se abona el 2,5% completo sin descuento.</div>
                                </div>
                            </div>
                        </div>
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
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.toggle("fa-eye", !showValues);
                        icon.classList.toggle("fa-eye-slash", showValues);
                    }
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
