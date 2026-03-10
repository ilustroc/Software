<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GestionController;
use App\Http\Controllers\PagosPropia12Controller;
use App\Http\Controllers\PagosPropia3Controller;
use App\Http\Controllers\PagosPropia4Controller;
use App\Http\Controllers\TipificacionController;
use App\Http\Controllers\Reportes\GestionesPropia12ReportController;
use App\Http\Controllers\Reportes\GestionesPropia3ReportController;
use App\Http\Controllers\Reportes\GestionesPropia4ReportController;
use App\Http\Controllers\Reportes\ReportePagosController;

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

    // --- VISTAS DE FORMULARIOS / TABLAS ---
    Route::get('/gestiones/propia12',   [GestionController::class, 'formPropia12'])->name('gestiones.propia12.form');
    Route::get('/gestiones/propia3',    [GestionController::class, 'formPropia3'])->name('gestiones.propia3.form');
    Route::get('/gestiones/kpi',        [GestionController::class, 'formKpi'])->name('gestiones.kpi.form');
    Route::get('/gestiones/amd',        [GestionController::class, 'indexAmd'])->name('gestiones.amd');
    Route::get('/gestiones/abandonados', [GestionController::class, 'indexAbandonados'])->name('gestiones.abandonados');

    // --- ACCIONES UNIFICADAS (Sincronización CRM) ---
    // Esta ruta sirve para: propia12, propia3, kpi, amd y abandonados
    Route::post('/gestiones/{tipo}/cargar', [GestionController::class, 'cargar'])->name('gestiones.generica.cargar');

    // --- CARGA MANUAL Y PLANTILLAS (Excel) ---
    Route::get('/gestiones/manual/plantilla/{tipo}', [GestionController::class, 'plantillaManual'])->name('gestiones.manual.plantilla');
    Route::post('/gestiones/manual/cargar/{tipo}', [GestionController::class, 'cargarManual'])->name('gestiones.manual.cargar');

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

    // REPORTES GESTIONES PROPIA 1 Y 2
    Route::get('/reportes/gestiones/propia12', [GestionesPropia12ReportController::class, 'index'])
        ->name('reportes.gestiones.propia12');

    Route::get('/reportes/gestiones/propia12/xlsx', [GestionesPropia12ReportController::class, 'xlsx'])
        ->name('reportes.gestiones.propia12.xlsx');
    
    // REPORTES GESTIONES PROPIA 3
    Route::get('/reportes/gestiones/propia3', [GestionesPropia3ReportController::class, 'index'])
        ->name('reportes.gestiones.propia3');
    
    Route::get('/reportes/gestiones/propia3/xlsx', [GestionesPropia3ReportController::class, 'xlsx'])
        ->name('reportes.gestiones.propia3.xlsx');

    // REPORTES GESTIONES PROPIA 4
    Route::get('/reportes/gestiones/propia4', [GestionesPropia4ReportController::class, 'index'])
        ->name('reportes.gestiones.propia4');

    Route::get('/reportes/gestiones/propia4/xlsx', [GestionesPropia4ReportController::class, 'xlsx'])
        ->name('reportes.gestiones.propia4.xlsx');
    
    // REPORTES PAGOS
    Route::get('/reportes/pagos', [ReportePagosController::class, 'index'])
        ->name('reportes.pagos.index');

    Route::get('/reportes/pagos/xlsx', [ReportePagosController::class, 'xlsx'])
        ->name('reportes.pagos.xlsx');   
             
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
