<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AbandonadosController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $desde = $request->desde ?? date('Y-m-d');
        $hasta = $request->hasta ?? date('Y-m-d');

        $desdeFull = $desde . ' 00:00:00';
        $hastaFull = $hasta . ' 23:59:59';

        $query = DB::table('Llamadas_Abandonadas')
            ->whereBetween('fecha_evento', [$desdeFull, $hastaFull])
            ->orderBy('fecha_evento', 'desc');

        $registros = $query->paginate(20)->appends($request->query());

        return view('gestiones.abandonados', compact('registros', 'desde', 'hasta'));
    }

    public function cargar(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'desde' => ['required','date'],
            'hasta' => ['required','date','after_or_equal:desde'],
        ]);

        $desdeFull = $data['desde'] . ' 00:00:00';
        $hastaFull = $data['hasta'] . ' 23:59:59';

        try {
            $rows = DB::connection('crm')->select(
                'CALL spLlamadasAbandonadas(?, ?)',
                [$desdeFull, $hastaFull]
            );

            if (empty($rows)) {
                return back()->with('msg', 'El SP no devolvió llamadas abandonadas en ese rango.');
            }

            $insert = [];

            foreach ($rows as $r) {
                $insert[] = [
                    'fecha_evento' => $r->DATETIME ?? null,
                    'event'        => $r->EVENT ?? null,
                    'callidnum'    => $r->CALLIDNUM ?? null,
                    'guid'         => $r->GUID ?? null,
                    'queue'        => $r->QUEUE ?? null,
                    'enterdate'    => $r->ENTERDATE ?? null,
                    'posabandon'   => $r->POSABANDON ?? null,
                    'posoriginal'  => $r->POSORIGINAL ?? null,
                    'callerid'     => $r->CALLERID ?? null,
                    'timewait'     => $r->TIMEWAIT ?? null,
                    'documento'    => $r->DOCUMENTO ?? null,
                ];
            }

            DB::beginTransaction();

            DB::table('Llamadas_Abandonadas')
                ->whereBetween('fecha_evento', [$desdeFull, $hastaFull])
                ->delete();

            foreach (array_chunk($insert, 500) as $chunk) {
                DB::table('Llamadas_Abandonadas')->insert($chunk);
            }

            DB::commit();

            return back()->with('msg', 'Llamadas abandonadas cargadas correctamente: ' . count($insert));

        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cargar abandonados: ' . $e->getMessage());
        }
    }

    public function descargar(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $request->validate([
            'desde' => ['required','date'],
            'hasta' => ['required','date','after_or_equal:desde'],
        ]);

        $desdeFull = $request->desde . ' 00:00:00';
        $hastaFull = $request->hasta . ' 23:59:59';

        $rows = DB::table('Llamadas_Abandonadas')
            ->whereBetween('fecha_evento', [$desdeFull, $hastaFull])
            ->orderBy('fecha_evento', 'desc')
            ->get();

        $filename = 'Abandonados_' . $request->desde . '_a_' . $request->hasta . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'fecha_evento','event','callidnum','guid','queue',
                'enterdate','posabandon','posoriginal','callerid','timewait','documento'
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->fecha_evento,
                    $r->event,
                    $r->callidnum,
                    $r->guid,
                    $r->queue,
                    $r->enterdate,
                    $r->posabandon,
                    $r->posoriginal,
                    $r->callerid,
                    $r->timewait,
                    $r->documento,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
