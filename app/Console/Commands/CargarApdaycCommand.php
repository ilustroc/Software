<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GestionService;
use Illuminate\Support\Carbon;
use Throwable;

class CargarApdaycCommand extends Command
{
    protected $signature = 'gestiones:apdayc-hora';
    protected $description = 'Carga automática de gestiones APDAYC usando GestionService';

    public function handle(GestionService $service): int
    {
        $end   = Carbon::now()->subHour()->endOfHour();
        $start = Carbon::now()->subHours(2)->startOfHour();

        try {
            $count = $service->sincronizar('apdayc', $start->toDateTimeString(), $end->toDateTimeString());
            $this->info("APDAYC: $count registros sincronizados.");
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Error en APDAYC: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}