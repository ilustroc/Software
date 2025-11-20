<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class GestionPropiaController extends Controller
{
    public function form()
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        return view('gestiones.propia12');
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

        // Crear fechas con hora completa
        $desdeFull = $desde . ' 00:00:00';
        $hastaFull = $hasta . ' 23:59:59';
        
        try {
            // 2) Ejecutar el SP con rango de fechas en el CRM
            $rows = DB::connection('crm')->select(
                'CALL spGestionPropia(?, ?)',
                [$desdeFull, $hastaFull]
            );

            if (empty($rows)) {
                return back()->with('msg', "El SP no devolvió registros para el rango $desde al $hasta.");
            }

            // 3) Preparar datos a insertar
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

            // 4) BORRAR SOLO EL TRAMO en nuestra tabla
            // Borrar tramo por dateprocessed
            DB::table('Gestiones_1y2')
                ->whereBetween('dateprocessed', [$desdeFull, $hastaFull])
                ->delete();

            // 5) Insertar nuevos registros
            foreach (array_chunk($data, 500) as $chunk) {
                DB::table('Gestiones_1y2')->insert($chunk);
            }

            DB::commit();

            return back()->with('msg',
                'Gestiones cargadas correctamente para el rango ' . $desde . ' al ' . $hasta .
                '. Total registros: ' . count($data)
            );

        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cargar gestiones: ' . $e->getMessage());
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
        if ($trim === '' || stripos($trim, 'inválida') !== false || stripos($trim, 'invalida') !== false) {
            return null;
        }

        $ts = strtotime($trim);
        if ($ts === false) {
            return null;
        }

        // Formato MySQL DATETIME
        return date('Y-m-d H:i:s', $ts);
    }
}
