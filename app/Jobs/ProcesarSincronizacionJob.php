<?php

namespace App\Jobs;

use App\Services\GestionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcesarSincronizacionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // El Job puede intentar procesarse 3 veces si falla
    public $tries = 3;

    public function __construct(
        protected string $tipo,
        protected string $desde,
        protected string $hasta
    ) {}

    public function handle(GestionService $service)
    {
        try {
            $service->sincronizar($this->tipo, $this->desde, $this->hasta);
            Log::info("Sincronización exitosa en segundo plano para: {$this->tipo}");
        } catch (Throwable $e) {
            Log::error("Error en Job de sincronización ({$this->tipo}): " . $e->getMessage());
            throw $e; // Re-lanzar para que el sistema de colas lo reintente
        }
    }
}