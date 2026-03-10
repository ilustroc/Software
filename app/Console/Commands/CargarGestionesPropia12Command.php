<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GestionService;
use Illuminate\Support\Carbon;
use Throwable;

class CargarGestionesPropia12Command extends Command
{
    protected $signature = 'gestiones:propia12-hora';
    protected $description = 'Carga automática Propia 1 y 2 usando GestionService';

    public function handle(GestionService $service): int
    {
        $end   = Carbon::now()->subHour()->endOfHour();
        $start = Carbon::now()->subHours(2)->startOfHour();

        try {
            $count = $service->sincronizar('propia12', $start->toDateTimeString(), $end->toDateTimeString());
            $this->info("Propia 1y2: $count registros sincronizados.");
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Error en Propia 1y2: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}