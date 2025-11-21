<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class AmdController extends Controller
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

        $query = DB::table('Llamadas_AMD')
            ->whereBetween('calldate', [$desdeFull, $hastaFull])
            ->orderBy('calldate', 'desc');

        $registros = $query->paginate(20)->appends($request->query());

        return view('gestiones.amd', compact('registros', 'desde', 'hasta'));
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
            // SP en el CRM
            $rows = DB::connection('crm')->select(
                'CALL spLlamadasAMD(?, ?)',
                [$desdeFull, $hastaFull]
            );

            if (empty($rows)) {
                return back()->with('msg', 'El SP no devolvió registros AMD en ese rango.');
            }

            $insert = [];

            foreach ($rows as $r) {
                $insert[] = [
                    'calldate'    => $r->calldate ?? null,
                    'campaign'    => $r->campaign ?? null,
                    'dst'         => $r->dst ?? null,
                    'disposition' => $r->disposition ?? null,
                    'userfield'   => $r->userfield ?? null,
                    'contact'     => $r->contact ?? null,
                    'dialbase'    => $r->dialbase ?? null,
                    'doc'         => $r->doc ?? null,
                ];
            }

            DB::beginTransaction();

            // borrar tramo antes de insertar
            DB::table('Llamadas_AMD')
                ->whereBetween('calldate', [$desdeFull, $hastaFull])
                ->delete();

            foreach (array_chunk($insert, 500) as $chunk) {
                DB::table('Llamadas_AMD')->insert($chunk);
            }

            DB::commit();

            return back()->with('msg', 'Llamadas AMD cargadas correctamente: ' . count($insert));

        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Error al cargar AMD: ' . $e->getMessage());
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

        $rows = DB::table('Llamadas_AMD')
            ->whereBetween('calldate', [$desdeFull, $hastaFull])
            ->orderBy('calldate', 'desc')
            ->get();

        $filename = 'Llamadas_AMD_' . $request->desde . '_a_' . $request->hasta . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'calldate','campaign','dst','disposition',
                'userfield','contact','dialbase','doc'
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->calldate,
                    $r->campaign,
                    $r->dst,
                    $r->disposition,
                    $r->userfield,
                    $r->contact,
                    $r->dialbase,
                    $r->doc,
                ]);
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
