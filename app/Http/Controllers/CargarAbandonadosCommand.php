<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GestionService;
use Illuminate\Support\Carbon;
use Throwable;

class CargarAbandonadosCommand extends Command
{
    protected $signature = 'gestiones:abandonados-hora';
    protected $description = 'Carga automática de llamadas Abandonadas usando GestionService';

    public function handle(GestionService $service): int
    {
        $end   = Carbon::now()->subHour()->endOfHour();
        $start = Carbon::now()->subHours(2)->startOfHour();

        try {
            $count = $service->sincronizar('abandonados', $start->toDateTimeString(), $end->toDateTimeString());
            $this->info("Abandonados: $count registros sincronizados.");
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Error en Abandonados: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}