<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Throwable;

class CargarGestionesPropia3Command extends Command
{
    protected $signature = 'gestiones:propia3-hora';
    protected $description = 'Carga automática de gestiones Propia 3 (Zigor) para las últimas 2 horas';

    public function handle(): int
    {
        // Igual lógica:
        // si se ejecuta a las 15:00 -> carga 13:00:00 a 14:59:59
        $end   = Carbon::now()->subHour()->endOfHour();
        $start = Carbon::now()->subHours(2)->startOfHour();

        $desdeFull = $start->format('Y-m-d H:i:s');
        $hastaFull = $end->format('Y-m-d H:i:s');

        $this->info("Cargando gestiones Propia 3 desde $desdeFull hasta $hastaFull ...");

        try {
            // 1) Ejecutar SP en el CRM
            $rows = DB::connection('crm')->select(
                'CALL spGestionZigor(?, ?)',
                [$desdeFull, $hastaFull]
            );

            if (empty($rows)) {
                $this->info('El SP no devolvió registros.');
                return Command::SUCCESS;
            }

            // 2) Preparar datos
            $data = [];

            foreach ($rows as $r) {

                // por seguridad, saltar si no hay operacion
                if (empty($r->operacion)) {
                    continue;
                }

                $montoCuota   = $this->parseMonto($r->pagar_por_cuota ?? null);
                $fechaPromesa = $this->parseFecha($r->fecha_promesa ?? null);

                $data[] = [
                    'documento'       => $r->documento ?? null,
                    'nombre'          => $r->nombre ?? null,
                    'value2'          => $r->value2 ?? null,
                    'value1'          => $r->value1 ?? null,
                    'fullname'        => $r->fullname ?? null,
                    'operacion'       => $r->operacion,
                    'ctl'             => $r->ctl ?? null,
                    'dateprocessed'   => $r->dateprocessed ?? null,
                    'fechaAgenda'     => $r->fechaAgenda ?? null,
                    'callerid'        => $r->callerid ?? null,
                    'comment'         => $r->comment ?? null,
                    'pagar_por_cuota' => $montoCuota,
                    'nroCuotas'       => $r->nroCuotas ?? null,
                    'fecha_promesa'   => $fechaPromesa,
                    'campaign'        => $r->campaign ?? null,
                ];
            }

            if (empty($data)) {
                $this->info('No hay registros válidos (con operación) en ese rango.');
                return Command::SUCCESS;
            }

            DB::beginTransaction();

            // 3) Borrar tramo en Gestiones_Propia3
            DB::table('Gestiones_Propia3')
                ->whereBetween('dateprocessed', [$desdeFull, $hastaFull])
                ->delete();

            // 4) Insertar nuevas gestiones
            foreach (array_chunk($data, 500) as $chunk) {
                DB::table('Gestiones_Propia3')->insert($chunk);
            }

            DB::commit();

            $this->info('Carga Propia 3 completada. Registros insertados: ' . count($data));
            return Command::SUCCESS;

        } catch (Throwable $e) {
            DB::rollBack();
            $this->error('Error en carga automática Propia 3: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function parseMonto(?string $valor): ?float
    {
        if ($valor === null || trim($valor) === '') {
            return null;
        }

        $limpio = str_replace(['S/', 's/', ' ', ','], ['', '', '', '.'], $valor);

        if (!is_numeric($limpio)) {
            return null;
        }

        return (float) $limpio;
    }

    private function parseFecha(?string $valor): ?string
    {
        if ($valor === null) return null;

        $trim = trim($valor);
        if ($trim === '' ||
            stripos($trim, 'inválida') !== false ||
            stripos($trim, 'invalida') !== false) {
            return null;
        }

        $ts = strtotime($trim);
        if ($ts === false) return null;

        return date('Y-m-d H:i:s', $ts);
    }
}
