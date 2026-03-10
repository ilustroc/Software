<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GestionService;
use Illuminate\Support\Carbon;
use Throwable;

class CargarAmdCommand extends Command
{
    protected $signature = 'gestiones:amd-hora';
    protected $description = 'Carga automática de llamadas AMD usando GestionService';

    public function handle(GestionService $service): int
    {
        $end   = Carbon::now()->subHour()->endOfHour();
        $start = Carbon::now()->subHours(2)->startOfHour();

        try {
            $count = $service->sincronizar('amd', $start->toDateTimeString(), $end->toDateTimeString());
            $this->info("AMD: $count registros sincronizados.");
            return Command::SUCCESS;
        } catch (Throwable $e) {
            $this->error("Error en AMD: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}