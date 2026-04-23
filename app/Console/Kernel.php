<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Carteras principales (Cada hora)
        $schedule->command('gestiones:propia12-hora')->hourly();
        $schedule->command('gestiones:kpi-hora')->hourly();
        $schedule->command('gestiones:ivr-hora')->hourly();
        $schedule->command('gestiones:propia3-hora')->hourly();
        $schedule->command('gestiones:amd-hora')->hourly();
        $schedule->command('gestiones:abandonados-hora')->hourly();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}