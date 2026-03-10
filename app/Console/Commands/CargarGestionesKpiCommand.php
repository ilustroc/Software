<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GestionService;
use Illuminate\Support\Carbon;
use Throwable;

class CargarGestionesKpiCommand extends Command
{
    protected $signature = 'gestiones:kpi-hora';
    protected $description = 'Carga automática KPI usando GestionService';

    public function handle(GestionService $service): int
    {
        $end   = Carbon::now()->subHour()->endOfHour();
        $start = Carbon::now()->subHours(2)->startOfHour();

        try {
            $count = $service->sincronizar('kpi', $start->toDateTimeString(), $end->toDateTimeString());
            $this->info("KPI: $count registros sincronizados.");
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Error en KPI: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}