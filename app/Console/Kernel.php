<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Services\GestionService;
use Illuminate\Support\Carbon;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // --- 1. CARTERAS (Vía Commands) ---
        // Tus commands ya calculan el rango de 2 horas internamente.
        $schedule->command('gestiones:propia12-hora')->hourly();
        $schedule->command('gestiones:kpi-hora')->hourly();
        $schedule->command('gestiones:propia3-hora')->hourly();

        // --- 2. AMD (Cada 30 min, rango de 2 horas atrás) ---
        $schedule->call(function (GestionService $service) {
            $end   = Carbon::now()->subHour()->endOfHour();
            $start = Carbon::now()->subHours(2)->startOfHour();
            
            // Sincroniza y el Service se encarga de borrar el rango antes de insertar
            $service->sincronizar('amd', $start->toDateTimeString(), $end->toDateTimeString());
        })->everyThirtyMinutes();

        // --- 3. ABANDONADOS (Cada 15 min, rango de 2 horas atrás) ---
        $schedule->call(function (GestionService $service) {
            $end   = Carbon::now()->subHour()->endOfHour();
            $start = Carbon::now()->subHours(2)->startOfHour();
            
            // Sincroniza y el Service se encarga de borrar el rango antes de insertar
            $service->sincronizar('abandonados', $start->toDateTimeString(), $end->toDateTimeString());
        })->everyFifteenMinutes();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}