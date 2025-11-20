<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GestionPropiaController;
use App\Http\Controllers\PagosPropia12Controller;

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

    Route::get('/gestiones/propia12', [GestionPropiaController::class, 'form'])
        ->name('gestiones.propia12.form');

    Route::post('/gestiones/propia12/cargar', [GestionPropiaController::class, 'cargar'])
        ->name('gestiones.propia12.cargar');

    Route::get('/pagos/propia12', [PagosPropia12Controller::class, 'index'])
        ->name('pagos.propia12.index');
    Route::post('/pagos/propia12/upload', [PagosPropia12Controller::class, 'upload'])
        ->name('pagos.propia12.upload');
    Route::post('/pagos/propia12/store', [PagosPropia12Controller::class, 'store'])
        ->name('pagos.propia12.store');
    Route::post('/pagos/propia12/update/{id}', [PagosPropia12Controller::class, 'update'])
        ->name('pagos.propia12.update'); 
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