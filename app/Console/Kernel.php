<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('gestiones:propia12-hora')
            ->hourly()
            ->appendOutputTo(storage_path('logs/gestiones_propia12.log'));

        $schedule->command('gestiones:propia4-hora')
            ->hourly()
            ->appendOutputTo(storage_path('logs/gestiones_propia4.log'));

        $schedule->command('gestiones:propia3-hora')
            ->hourly()
            ->appendOutputTo(storage_path('logs/gestiones_propia3.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
