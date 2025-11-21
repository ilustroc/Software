<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Throwable;

class CargarGestionesPropia4Command extends Command
{
    protected $signature = 'gestiones:propia4-hora';
    protected $description = 'Carga automática de gestiones Propia 4 (KPI) para las últimas 2 horas';

    public function handle(): int
    {
        // Igual que tu comando de Propia 1y2:
        // si son las 15:00 -> carga de 13:00:00 a 14:59:59
        $end   = Carbon::now()->subHour()->endOfHour();
        $start = Carbon::now()->subHours(2)->startOfHour();

        $desdeFull = $start->format('Y-m-d H:i:s');
        $hastaFull = $end->format('Y-m-d H:i:s');

        $this->info("Cargando gestiones Propia 4 desde $desdeFull hasta $hastaFull ...");

        try {
            // 1) Ejecutar SP en el CRM
            $rows = DB::connection('crm')->select(
                'CALL spGestionKpi(?, ?)',
                [$desdeFull, $hastaFull]
            );

            if (empty($rows)) {
                $this->info('El SP no devolvió registros.');
                return Command::SUCCESS;
            }

            // 2) Preparar datos
            $data = [];

            foreach ($rows as $r) {

                // importante: saltar registros sin operación (para no romper la tabla)
                if (empty($r->operacion)) {
                    continue;
                }

                $montoFin   = $this->parseMonto($r->importeFinanciamiento ?? null);
                $fechaProm  = $this->parseFecha($r->fechaPromesa ?? null);

                $data[] = [
                    'documento'              => $r->documento ?? null,
                    'cliente'                => $r->cliente ?? null,
                    'value2'                 => $r->value2 ?? null,
                    'value1'                 => $r->value1 ?? null,
                    'fullname'               => $r->fullname ?? null,
                    'operacion'              => $r->operacion,
                    'entidad'                => $r->entidad ?? null,
                    'dateprocessed'          => $r->dateprocessed ?? null,
                    'fechaAgenda'            => $r->fechaAgenda ?? null,
                    'callerid'               => $r->callerid ?? null,
                    'comment'                => $r->comment ?? null,
                    'importe_financiamiento' => $montoFin,
                    'nroCuotas'              => $r->nroCuotas ?? null,
                    'fecha_promesa'          => $fechaProm,
                    'campaign'               => $r->campaign ?? null,
                ];
            }

            if (empty($data)) {
                $this->info('No hay registros válidos (con operación) en ese rango.');
                return Command::SUCCESS;
            }

            DB::beginTransaction();

            // 3) Borrar solo el tramo en Gestiones_Propia4
            DB::table('Gestiones_Propia4')
                ->whereBetween('dateprocessed', [$desdeFull, $hastaFull])
                ->delete();

            // 4) Insertar nuevas gestiones
            foreach (array_chunk($data, 500) as $chunk) {
                DB::table('Gestiones_Propia4')->insert($chunk);
            }

            DB::commit();

            $this->info('Carga Propia 4 completada. Registros insertados: ' . count($data));
            return Command::SUCCESS;

        } catch (Throwable $e) {
            DB::rollBack();
            $this->error('Error en carga automática Propia 4: ' . $e->getMessage());
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
