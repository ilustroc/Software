<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GestionController;
use App\Http\Controllers\PagosController;
use App\Http\Controllers\TipificacionController;
use App\Http\Controllers\Reportes\ReporteGestionesController;
use App\Http\Controllers\Reportes\ReportePagosController;

Route::middleware('web')->group(function () {

    // --- AUTENTICACIÓN ---
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return session()->has('usuario') ? view('dashboard') : redirect()->route('login');
    })->name('dashboard');

    // --- GESTIONES (CRM & MANUAL) ---
    Route::prefix('gestiones')->group(function () {
        Route::get('/propia12',   [GestionController::class, 'formPropia12'])->name('gestiones.propia12.form');
        Route::get('/propia3',    [GestionController::class, 'formPropia3'])->name('gestiones.propia3.form');
        Route::get('/kpi',        [GestionController::class, 'formKpi'])->name('gestiones.kpi.form');
        Route::get('/amd',        [GestionController::class, 'indexAmd'])->name('gestiones.amd');
        Route::get('/ivr', [GestionController::class, 'indexIvr'])->name('gestiones.ivr');
        Route::get('/abandonados', [GestionController::class, 'indexAbandonados'])->name('gestiones.abandonados');

        // Sincronización CRM
        Route::post('/{tipo}/cargar', [GestionController::class, 'cargar'])->name('gestiones.generica.cargar');
        
        // Carga Manual
        Route::get('/manual/plantilla/{tipo}', [GestionController::class, 'plantillaManual'])->name('gestiones.manual.plantilla');
        Route::post('/manual/cargar/{tipo}', [GestionController::class, 'cargarManual'])->name('gestiones.manual.cargar');
    });

    // --- PAGOS (Propia 1y2, 3 y 4) ---
    Route::prefix('pagos')->group(function () {
        Route::get('/{tipo}',            [PagosController::class, 'index'])->name('pagos.index');
        Route::get('/{tipo}/plantilla',  [PagosController::class, 'template'])->name('pagos.template');
        Route::post('/{tipo}/store',     [PagosController::class, 'store'])->name('pagos.store');
        Route::post('/{tipo}/upload',    [PagosController::class, 'upload'])->name('pagos.upload');
        Route::put('/{tipo}/{id}',       [PagosController::class, 'update'])->name('pagos.update'); // Usamos PUT para update
        Route::delete('/{tipo}/{id}',    [PagosController::class, 'destroy'])->name('pagos.destroy');
    });

    // --- REPORTES ---
    Route::prefix('reportes')->group(function () {
        
        // Gestiones
        Route::prefix('gestiones')->group(function () {
            Route::get('/{tipo}', [ReporteGestionesController::class, 'index'])->name('reportes.gestiones.index');
            Route::get('/{tipo}/xlsx', [ReporteGestionesController::class, 'xlsx'])->name('reportes.gestiones.xlsx');
        });

        // Pagos
        Route::get('/pagos', [ReportePagosController::class, 'index'])->name('reportes.pagos.index');
        Route::get('/pagos/xlsx', [ReportePagosController::class, 'xlsx'])->name('reportes.pagos.xlsx'); 
    });

    // --- PARÁMETROS ---
    Route::prefix('parametros')->group(function () {
        Route::get('/tipificaciones', [TipificacionController::class, 'index'])->name('parametros.tipificaciones.index');
        Route::post('/tipificaciones', [TipificacionController::class, 'store'])->name('parametros.tipificaciones.store');
        Route::post('/tipificaciones/{tipificacion}', [TipificacionController::class, 'update'])->name('parametros.tipificaciones.update');
        Route::delete('/tipificaciones/{tipificacion}', [TipificacionController::class, 'destroy'])->name('parametros.tipificaciones.destroy');
    });

});