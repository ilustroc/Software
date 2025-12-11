<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GestionPropiaController;
use App\Http\Controllers\AbandonadosController;
use App\Http\Controllers\PagosPropia12Controller;
use App\Http\Controllers\PagosPropia3Controller;
use App\Http\Controllers\PagosPropia4Controller;
use App\Http\Controllers\GestionPropia3Controller;
use App\Http\Controllers\GestionPropia4Controller;
use App\Http\Controllers\TipificacionController;
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

    Route::post('/gestiones/propia12/import-excel', [GestionPropiaController::class, 'importExcel'])
        ->name('gestiones.propia12.importExcel');
        
    // GESTIONES PROPIA 3 (Zigor)
    Route::get('/gestiones/propia3', [GestionPropia3Controller::class, 'form'])
        ->name('gestiones.propia3.form');

    Route::post('/gestiones/propia3/cargar', [GestionPropia3Controller::class, 'cargar'])
        ->name('gestiones.propia3.cargar');

    Route::post('/gestiones/propia3/cargar-sms', [GestionPropia3Controller::class, 'cargarSms'])
        ->name('gestiones.propia3.cargarSms');
        
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

    Route::get('/pagos/propia12/plantilla-csv', [PagosPropia12Controller::class, 'template'])
        ->name('pagos.propia12.template');

    // PAGOS PROPIA 3
    Route::get('/pagos/propia3', [PagosPropia3Controller::class, 'index'])
        ->name('pagos.propia3.index');

    Route::get('/pagos/propia3/plantilla', [PagosPropia3Controller::class, 'template'])
        ->name('pagos.propia3.template');

    Route::post('/pagos/propia3', [PagosPropia3Controller::class, 'store'])
        ->name('pagos.propia3.store');
        
    Route::post('/pagos/propia3/upload', [PagosPropia3Controller::class, 'upload'])
        ->name('pagos.propia3.upload');

    Route::put('/pagos/propia3/{id}', [PagosPropia3Controller::class, 'update'])
        ->name('pagos.propia3.update');

    Route::delete('/pagos/propia3/{id}', [PagosPropia3Controller::class, 'destroy'])
        ->name('pagos.propia3.destroy');

    // PAGOS PROPIA 4
    Route::get('/pagos/propia4',  [PagosPropia4Controller::class, 'index'])
        ->name('pagos.propia4.index');

    Route::get('/pagos/propia4/plantilla', [PagosPropia4Controller::class, 'template'])
        ->name('pagos.propia4.template');

    Route::post('/pagos/propia4', [PagosPropia4Controller::class, 'store'])
        ->name('pagos.propia4.store');

    Route::post('/pagos/propia4/upload', [PagosPropia4Controller::class, 'upload'])
        ->name('pagos.propia4.upload');

    Route::put('/pagos/propia4/{id}', [PagosPropia4Controller::class, 'update'])
        ->name('pagos.propia4.update');

    Route::delete('/pagos/propia4/{id}', [PagosPropia4Controller::class, 'destroy'])
        ->name('pagos.propia4.destroy');

    // TIPIFICACIONES
    Route::get('/parametros/tipificaciones', [TipificacionController::class, 'index'])
        ->name('parametros.tipificaciones.index');
    
    Route::post('/parametros/tipificaciones', [TipificacionController::class, 'store'])
        ->name('parametros.tipificaciones.store');
    
    Route::post('/parametros/tipificaciones/{tipificacion}', [TipificacionController::class, 'update'])
        ->name('parametros.tipificaciones.update');
    
    Route::delete('/parametros/tipificaciones/{tipificacion}', [TipificacionController::class, 'destroy'])
        ->name('parametros.tipificaciones.destroy');
});
