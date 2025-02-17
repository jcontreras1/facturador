<?php
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\Negocio\ComprobanteController;
use App\Http\Controllers\Negocio\ContribuyenteController;
// use App\Http\Controllers\Negocio\FacturaBController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth'])->group(function () {
    
    //Generar clave privada
    Route::post('afip/makeKey', [InstallationController::class, 'makeKey'])->name('afip.makeKey');
    Route::post('afip/newCert', [InstallationController::class, 'newCert'])->name('config.newCert');
    Route::get('afip/makeCSR', [InstallationController::class, 'makeCSR'])->name('afip.makeCSR');
    
    Route::get('/comprobantes', [ComprobanteController::class, 'index'])->name('comprobantes.index');
    Route::get('/comprobante/{comprobante}/descargar/pdf', [ComprobanteController::class, 'descargarPdf'])->name('comprobante.descargar.pdf');
    Route::post('comprobante/{comprobante}/enviar/mail', [ComprobanteController::class, 'enviarMail'])->name('comprobante.enviar.mail');
    Route::get('/comprobante/c', [ComprobanteController::class, 'createFacturaC'])->name('comprobante.create.c');
    Route::get('/cliente/{cliente}/comprobante/c', [ComprobanteController::class, 'createFacturaCCliente'])->name('cliente.comprobante.create.c');
    // Route::post('/comprobante/c', [FacturaBController::class, 'facturar'])->name('comprobante.store.c');
    
    
    Route::get('/test', [ContribuyenteController::class, 'padronv4']);
    
    
});
