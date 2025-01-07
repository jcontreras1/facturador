<?php
use App\Http\Controllers\InstallationController;
use App\Http\Controllers\Negocio\ContribuyenteController;
use App\Http\Controllers\Negocio\FacturacionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth'])->group(function () {
    
    //Generar clave privada
    Route::post('afip/makeKey', [InstallationController::class, 'makeKey'])->name('afip.makeKey');
    Route::post('afip/newCert', [InstallationController::class, 'newCert'])->name('config.newCert');
    Route::get('afip/makeCSR', [InstallationController::class, 'makeCSR'])->name('afip.makeCSR');
    
    Route::get('/facturacion', [FacturacionController::class, 'index'])->name('facturacion.index');
    Route::get('/factura/{factura}/descargar/pdf', [FacturacionController::class, 'descargarPdf'])->name('facturacion.descargar.pdf');
    Route::post('factura/{factura}/enviar/mail', [FacturacionController::class, 'enviarMail'])->name('facturacion.enviar.mail');
    Route::get('/factura/c', [FacturacionController::class, 'createFacturaCGenerica'])->name('facturacion.create.c');
    Route::post('/factura/c', [FacturacionController::class, 'facturaCGenerica'])->name('facturacion.store.c');
    
    
    Route::get('/test', [ContribuyenteController::class, 'padronv4']);
    
    
});
