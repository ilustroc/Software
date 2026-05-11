<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarteraController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GestionController;
use App\Http\Controllers\PagosController;
use App\Http\Controllers\Reportes\ReporteGestionesController;
use App\Http\Controllers\Reportes\ReportePagosController;
use App\Http\Controllers\TipificacionController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::prefix('gestiones')->name('gestiones.')->group(function () {
        Route::get('/', [GestionController::class, 'index'])->name('index');
        Route::post('/cargar', [GestionController::class, 'cargar'])->name('cargar');
        Route::get('/plantilla', [GestionController::class, 'plantillaManual'])->name('manual.plantilla');
        Route::post('/manual/cargar', [GestionController::class, 'cargarManual'])->name('manual.cargar');

        Route::get('/{legacy}', [GestionController::class, 'legacy'])
            ->whereIn('legacy', ['propia12', 'propia3', 'kpi', 'kpinvest', 'kp-invest', 'propia4', 'apdayc'])
            ->name('legacy');
    });

    Route::prefix('pagos')->name('pagos.')->group(function () {
        Route::get('/', [PagosController::class, 'index'])->name('index');
        Route::get('/plantilla', [PagosController::class, 'template'])->name('template');
        Route::post('/', [PagosController::class, 'store'])->name('store');
        Route::post('/upload', [PagosController::class, 'upload'])->name('upload');
        Route::put('/{pago}', [PagosController::class, 'update'])->whereNumber('pago')->name('update');
        Route::delete('/{pago}', [PagosController::class, 'destroy'])->whereNumber('pago')->name('destroy');
        Route::get('/{legacy}', [PagosController::class, 'legacy'])
            ->whereIn('legacy', ['propia12', 'propia3', 'propia4', 'kpi', 'kpinvest', 'kp-invest', 'apdayc'])
            ->name('legacy');
    });

    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/pagos', [ReportePagosController::class, 'index'])->name('pagos.index');
        Route::get('/pagos/xlsx', [ReportePagosController::class, 'xlsx'])->name('pagos.xlsx');
        Route::get('/gestiones', [ReporteGestionesController::class, 'index'])->name('gestiones.index');
        Route::get('/gestiones/xlsx', [ReporteGestionesController::class, 'xlsx'])->name('gestiones.xlsx');
    });

    Route::prefix('configuracion')->name('configuracion.')->group(function () {
        Route::get('/carteras', [CarteraController::class, 'index'])->name('carteras.index');
        Route::post('/carteras', [CarteraController::class, 'store'])->name('carteras.store');

        Route::get('/tipificaciones', [TipificacionController::class, 'index'])->name('tipificaciones.index');
        Route::post('/tipificaciones', [TipificacionController::class, 'store'])->name('tipificaciones.store');
        Route::post('/tipificaciones/{tipificacion}', [TipificacionController::class, 'update'])->name('tipificaciones.update');
        Route::delete('/tipificaciones/{tipificacion}', [TipificacionController::class, 'destroy'])->name('tipificaciones.destroy');
    });

    Route::redirect('/parametros/tipificaciones', '/configuracion/tipificaciones');
});
