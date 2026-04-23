<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GestionService;
use Illuminate\Support\Carbon;
use Throwable;

class CargarIvrCommand extends Command
{
    protected $signature = 'gestiones:ivr-hora';
    protected $description = 'Carga automática de llamadas IVR usando GestionService';

    public function handle(GestionService $service): int
    {
        $end   = Carbon::now()->subHour()->endOfHour();
        $start = Carbon::now()->subHours(2)->startOfHour();

        try {
            $count = $service->sincronizar('ivr', $start->toDateTimeString(), $end->toDateTimeString());
            $this->info("IVR: $count registros sincronizados.");
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Error en IVR: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}