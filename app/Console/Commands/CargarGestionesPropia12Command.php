<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Throwable;

class CargarGestionesPropia12Command extends Command
{
    protected $signature = 'gestiones:propia12-hora';
    protected $description = 'Carga automática de gestiones Propia 1 y 2 para la última hora completa';

    public function handle(): int
    {
        // Tomamos la ÚLTIMA HORA COMPLETA.
        // Ej: si se ejecuta 13:00, carga de 12:00:00 a 12:59:59
        $end   = Carbon::now()->subHour()->endOfHour();
        $start = Carbon::now()->subHours(2)->startOfHour();

        $desdeFull = $start->format('Y-m-d H:i:s');
        $hastaFull = $end->format('Y-m-d H:i:s');

        $this->info("Cargando gestiones Propia 1y2 desde $desdeFull hasta $hastaFull ...");

        try {
            // 1) Ejecutar SP en el CRM
            $rows = DB::connection('crm')->select(
                'CALL spGestionPropia(?, ?)',
                [$desdeFull, $hastaFull]
            );

            if (empty($rows)) {
                $this->info('El SP no devolvió registros.');
                return Command::SUCCESS;
            }

            // 2) Preparar datos
            $data = [];

            foreach ($rows as $r) {
                $montoCuota   = $this->parseMonto($r->pagar_por_cuota ?? null);
                $fechaPromesa = $this->parseFecha($r->fecha_promesa ?? null);

                $data[] = [
                    'nombre'          => $r->nombre ?? null,
                    'value2'          => $r->value2 ?? null,
                    'value1'          => $r->value1 ?? null,
                    'fullname'        => $r->fullname ?? null,
                    'documento'       => $r->documento ?? null, // ajusta si en el SP se llama distinto
                    'operacion'       => $r->operacion ?? null,
                    'entidad'         => $r->entidad ?? null,
                    'cartera'         => $r->cartera ?? null,
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

            DB::beginTransaction();

            // 3) Borrar solo el tramo de esa hora en Gestiones_1y2
            DB::table('Gestiones_1y2')
                ->whereBetween('dateprocessed', [$desdeFull, $hastaFull])
                ->delete();

            // 4) Insertar nuevas gestiones
            foreach (array_chunk($data, 500) as $chunk) {
                DB::table('Gestiones_1y2')->insert($chunk);
            }

            DB::commit();

            $this->info('Carga completada. Registros insertados: ' . count($data));
            return Command::SUCCESS;

        } catch (Throwable $e) {
            DB::rollBack();
            $this->error('Error en carga automática: ' . $e->getMessage());
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
        if ($trim === '' || stripos($trim, 'inválida') !== false || stripos($trim, 'invalida') !== false) {
            return null;
        }

        $ts = strtotime($trim);
        if ($ts === false) return null;

        return date('Y-m-d H:i:s', $ts);
    }
}
