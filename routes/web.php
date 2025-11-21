<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GestionPropiaController;
use App\Http\Controllers\AbandonadosController;
use App\Http\Controllers\PagosPropia12Controller;
use App\Http\Controllers\GestionPropia3Controller;
use App\Http\Controllers\GestionPropia4Controller;
use App\Http\Controllers\AmdController;

Route::middleware('web')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }
        return view('dashboard');
    })->name('dashboard');

    // GESTIONES PROPIA 1 Y 2
    Route::get('/gestiones/propia12', [GestionPropiaController::class, 'form'])
        ->name('gestiones.propia12.form');

    Route::post('/gestiones/propia12/cargar', [GestionPropiaController::class, 'cargar'])
        ->name('gestiones.propia12.cargar');

    // GESTIONES PROPIA 3 (Zigor)
    Route::get('/gestiones/propia3', [GestionPropia3Controller::class, 'form'])
        ->name('gestiones.propia3.form');

    Route::post('/gestiones/propia3/cargar', [GestionPropia3Controller::class, 'cargar'])
        ->name('gestiones.propia3.cargar');

    // GESTIONES PROPIA 4 (KPI)
    Route::get('/gestiones/propia4', [GestionPropia4Controller::class, 'form'])
        ->name('gestiones.propia4.form');

    Route::post('/gestiones/propia4/cargar', [GestionPropia4Controller::class, 'cargar'])
        ->name('gestiones.propia4.cargar');

    // ABANDONADAS
    Route::get('/gestiones/abandonados', [AbandonadosController::class, 'index'])
        ->name('gestiones.abandonados');

    Route::post('/gestiones/abandonados/cargar', [AbandonadosController::class, 'cargar'])
        ->name('gestiones.abandonados.cargar');

    Route::get('/gestiones/abandonados/descargar', [AbandonadosController::class, 'descargar'])
        ->name('gestiones.abandonados.descargar');

    // AMD
    Route::get('/gestiones/amd', [AmdController::class, 'index'])
        ->name('gestiones.amd');

    Route::post('/gestiones/amd/cargar', [AmdController::class, 'cargar'])
        ->name('gestiones.amd.cargar');

    Route::get('/gestiones/amd/descargar', [AmdController::class, 'descargar'])
        ->name('gestiones.amd.descargar');

    // PAGOS PROPIA 1 Y 2
    Route::get('/pagos/propia12', [PagosPropia12Controller::class, 'index'])
        ->name('pagos.propia12.index');

    Route::post('/pagos/propia12/upload', [PagosPropia12Controller::class, 'upload'])
        ->name('pagos.propia12.upload');

    Route::post('/pagos/propia12/store', [PagosPropia12Controller::class, 'store'])
        ->name('pagos.propia12.store');

    Route::post('/pagos/propia12/update/{id}', [PagosPropia12Controller::class, 'update'])
        ->name('pagos.propia12.update');

    Route::delete('/pagos/propia12/{id}', [PagosPropia12Controller::class, 'destroy'])
        ->name('pagos.propia12.destroy');
});
