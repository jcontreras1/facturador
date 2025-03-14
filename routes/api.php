<?php

use App\Http\Controllers\Negocio\Comprobante\Factura\AController;
use App\Http\Controllers\Negocio\Comprobante\Factura\BController;
use App\Http\Controllers\Negocio\Comprobante\Factura\CController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');




Route::middleware('auth:sanctum')->group(function () {
   
    route::get('/contribuyente/{doc}', [App\Http\Controllers\Negocio\ContribuyenteController::class, 'infoContribuyente']);
    route::post('/comprobante/a', [AController::class, 'store'])->name('comprobante.a.store');
    route::post('/comprobante/b', [BController::class, 'store'])->name('comprobante.b.store');
    route::post('/comprobante/c', [CController::class, 'store'])->name('comprobante.c.store');

});