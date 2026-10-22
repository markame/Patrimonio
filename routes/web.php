<?php

use App\Http\Controllers\AutenticacaoController;
use App\Http\Controllers\ChamadoController;
use App\Http\Controllers\PatrimonioController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::view('/entrar', 'entrar')->name('login');
    Route::post('/entrar', [AutenticacaoController::class, 'entrar'])->middleware('throttle:5,1')->name('entrar');
});
Route::get('/p/{codigo}', [PatrimonioController::class, 'publico'])->name('patrimonios.publico');
Route::post('/p/{codigo}/chamados', [ChamadoController::class, 'store'])->middleware('throttle:5,1')->name('chamados.store');
Route::middleware('auth')->group(function () {
    Route::get('/', [PatrimonioController::class, 'painel'])->name('painel');
    Route::post('/sair', [AutenticacaoController::class, 'sair'])->name('sair');
    Route::resource('usuarios', UsuarioController::class)->only(['index', 'create', 'store'])->middleware('can:gerenciar-usuarios');
    Route::get('/patrimonios/{patrimonio}/etiqueta', [PatrimonioController::class, 'etiqueta'])->name('patrimonios.etiqueta');
    Route::resource('patrimonios', PatrimonioController::class)->except('destroy');
    Route::resource('chamados', ChamadoController::class)->only(['index', 'show', 'update']);
});
