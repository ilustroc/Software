<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class GestionPropia4Controller extends Controller
{
    public function form()
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        return view('gestiones.propia4');
    }

    public function cargar(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        // Validar fechas
        $dataReq = $request->validate([
            'desde' => ['required', 'date'],
            'hasta' => ['required', 'date', 'after_or_equal:desde'],
        ], [], [
            'desde' => 'fecha inicio',
            'hasta' => 'fecha fin',
        ]);

        $desde = $dataReq['desde'];
        $hasta = $dataReq['hasta'];

        $desdeFull = $desde . ' 00:00:00';
        $hastaFull = $hasta . ' 23:59:59';

        try {
            // 1) Ejecutar SP de KPI en el CRM
            $rows = DB::connection('crm')->select(
                'CALL spGestionKpi(?, ?)',
                [$desdeFull, $hastaFull]
            );

            if (empty($rows)) {
                return back()->with(
                    'msg',
                    "El SP no devolvió registros para el rango $desde al $hasta."
                );
            }

            // 2) Preparar data
            $data = [];

            foreach ($rows as $r) {

                // ⬅️ saltar registros sin operación
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
                    'operacion'              => $r->operacion,   // ya garantizado que NO es null
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

            DB::beginTransaction();

            // 3) Borrar tramo por dateprocessed
            DB::table('Gestiones_Propia4')
                ->whereBetween('dateprocessed', [$desdeFull, $hastaFull])
                ->delete();

            // 4) Insertar por bloques
            foreach (array_chunk($data, 500) as $chunk) {
                DB::table('Gestiones_Propia4')->insert($chunk);
            }

            DB::commit();

            return back()->with(
                'msg',
                'Gestiones Propia 4 cargadas correctamente para el rango '
                . $desde . ' al ' . $hasta .
                '. Total registros: ' . count($data)
            );

        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cargar gestiones Propia 4: ' . $e->getMessage());
        }
    }

    /**
     * Convierte un string a decimal (importe_financiamiento).
     */
    private function parseMonto(?string $valor): ?float
    {
        if ($valor === null || trim($valor) === '') {
            return null;
        }

        // Eliminar símbolos extra, comas, espacios
        $limpio = str_replace(['S/', 's/', ' ', ','], ['', '', '', '.'], $valor);

        if (!is_numeric($limpio)) {
            return null;
        }

        return (float) $limpio;
    }

    /**
     * Convierte un string a DATETIME válido o null.
     */
    private function parseFecha(?string $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        $trim = trim($valor);
        if ($trim === '' ||
            stripos($trim, 'inválida') !== false ||
            stripos($trim, 'invalida') !== false) {
            return null;
        }

        $ts = strtotime($trim);
        if ($ts === false) {
            return null;
        }

        return date('Y-m-d H:i:s', $ts);
    }
}
