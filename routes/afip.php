<?php

use App\Http\Controllers\InstallationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::middleware(['auth'])->group(function () {

    //Generar clave privada
    Route::post('afip/makeKey', [InstallationController::class, 'makeKey'])->name('afip.makeKey');
    Route::post('afip/newCert', [InstallationController::class, 'newCert'])->name('config.newCert');
    Route::get('afip/makeCSR', [InstallationController::class, 'makeCSR'])->name('afip.makeCSR');

});
