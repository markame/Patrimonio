<?php

use App\Http\Controllers\AutenticacaoController;
use App\Http\Controllers\ChamadoController;
use App\Http\Controllers\ConviteController;
use App\Http\Controllers\EtiquetaController;
use App\Http\Controllers\PatrimonioController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/convites/{token}', [ConviteController::class, 'show'])->name('convites.show');
    Route::post('/convites/{token}', [ConviteController::class, 'aceitar'])->middleware('throttle:10,1')->name('convites.aceitar');
    Route::view('/entrar', 'entrar')->name('login');
    Route::post('/entrar', [AutenticacaoController::class, 'entrar'])->middleware('throttle:5,1')->name('entrar');
});
Route::get('/p/{codigo}', [PatrimonioController::class, 'publico'])->name('patrimonios.publico');
Route::get('/storage/patrimonios/{arquivo}', [PatrimonioController::class, 'foto'])
    ->where('arquivo', '[A-Za-z0-9_-]+\.(jpg|jpeg|png|webp)')
    ->name('patrimonios.foto');
Route::post('/p/{codigo}/identificacao', [PatrimonioController::class, 'identificar'])->middleware('throttle:10,1')->name('patrimonios.identificar');
Route::post('/p/{codigo}/encerrar-identificacao', [PatrimonioController::class, 'encerrarIdentificacao'])->name('patrimonios.encerrar-identificacao');
Route::post('/p/{codigo}/chamados', [ChamadoController::class, 'store'])->middleware('throttle:5,1')->name('chamados.store');
Route::middleware('auth')->group(function () {
    Route::get('/', [PatrimonioController::class, 'painel'])->name('painel');
    Route::get('/patrimonios/etiquetas-a4.pdf', [EtiquetaController::class, 'baixar'])->name('patrimonios.etiquetas-pdf');
    Route::post('/sair', [AutenticacaoController::class, 'sair'])->name('sair');
    Route::resource('usuarios', UsuarioController::class)->only(['index', 'create', 'store'])->middleware('can:gerenciar-usuarios');
    Route::post('/convites', [ConviteController::class, 'store'])->middleware('can:gerenciar-usuarios')->name('convites.store');
    Route::post('/convites/{convite}/revogar', [ConviteController::class, 'revogar'])->middleware('can:gerenciar-usuarios')->name('convites.revogar');
    Route::get('/patrimonios/{patrimonio}/etiqueta', [PatrimonioController::class, 'etiqueta'])->name('patrimonios.etiqueta');
    Route::resource('patrimonios', PatrimonioController::class)->except('destroy');
    Route::resource('chamados', ChamadoController::class)->only(['index', 'show', 'update']);
});
