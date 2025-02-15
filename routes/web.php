<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::resource('/clientes', ClienteController::class);
    Route::resource('/items', ItemController::class);
    Route::resource('/config', ConfigController::class)->only(['index', 'update']);
    Route::post('/config/avatar', [ConfigController::class, 'set_avatar'])->name('config.avatar');
    Route::delete('/config/avatar', [ConfigController::class, 'unset_avatar'])->name('avatar.destroy');

    Route::get('/clientes/{cliente}/dashboard', [ClienteController::class, 'dashboard'])->name('clientes.dashboard');
    Route::get('/clientes/{cliente}/facturar', [ClienteController::class, 'dashboard'])->name('clientes.facturar');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/afip.php';
