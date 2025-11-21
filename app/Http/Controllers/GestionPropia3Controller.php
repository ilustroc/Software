<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class GestionPropia3Controller extends Controller
{
    public function form()
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        return view('gestiones.propia3');
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

        // Rango con hora completa
        $desdeFull = $desde . ' 00:00:00';
        $hastaFull = $hasta . ' 23:59:59';

        try {
            // 1) Ejecutar SP de Zigor en el CRM
            $rows = DB::connection('crm')->select(
                'CALL spGestionZigor(?, ?)',
                [$desdeFull, $hastaFull]
            );

            if (empty($rows)) {
                return back()->with(
                    'msg',
                    "El SP no devolvió registros para el rango $desde al $hasta."
                );
            }

            // 2) Preparar data para insertar
            $data = [];

            foreach ($rows as $r) {
                $montoCuota   = $this->parseMonto($r->pagar_por_cuota ?? null);
                $fechaPromesa = $this->parseFecha($r->fecha_promesa ?? null);

                $data[] = [
                    'documento'       => $r->documento ?? null,
                    'nombre'          => $r->nombre ?? null,
                    'value2'          => $r->value2 ?? null,
                    'value1'          => $r->value1 ?? null,
                    'fullname'        => $r->fullname ?? null,
                    'operacion'       => $r->operacion ?? null,
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

            DB::beginTransaction();

            // 3) Borrar SOLO el tramo por dateprocessed
            DB::table('Gestiones_Propia3')
                ->whereBetween('dateprocessed', [$desdeFull, $hastaFull])
                ->delete();

            // 4) Insertar por bloques
            foreach (array_chunk($data, 500) as $chunk) {
                DB::table('Gestiones_Propia3')->insert($chunk);
            }

            DB::commit();

            return back()->with(
                'msg',
                'Gestiones Propia 3 cargadas correctamente para el rango '
                . $desde . ' al ' . $hasta .
                '. Total registros: ' . count($data)
            );

        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cargar gestiones Propia 3: ' . $e->getMessage());
        }
    }

    /**
     * Convierte un string a decimal (pagar_por_cuota).
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
     * Ignora cosas como "Fecha inválida".
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
