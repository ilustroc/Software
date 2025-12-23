<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Exports\Pagos\ReportePagosExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportePagosController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $desde  = $request->query('desde', Carbon::now()->startOfMonth()->toDateString());
        $hasta  = $request->query('hasta', Carbon::now()->endOfMonth()->toDateString());
        $agente = trim((string) $request->query('agente', ''));
        $cartera= trim((string) $request->query('cartera', '')); // '' = todas

        // UNION: Pagos_1y2 + Pagos_3 + Pagos_4 (mismos campos)
        $q1 = DB::table('Pagos_1y2')->selectRaw("
            'Propia 1 y 2' as cartera,
            DNI, OPERACION, MONEDA, FECHA, MONTO, GESTOR
        ");

        $q2 = DB::table('Pagos_3')->selectRaw("
            'Propia 3' as cartera,
            DNI, OPERACION, MONEDA, FECHA, MONTO, GESTOR
        ");

        $q3 = DB::table('Pagos_4')->selectRaw("
            'Propia 4' as cartera,
            DNI, OPERACION, MONEDA, FECHA, MONTO, GESTOR
        ");

        $union = $q1->unionAll($q2)->unionAll($q3);

        $q = DB::query()
            ->fromSub($union, 'p')
            ->select('p.*')
            ->whereBetween('p.FECHA', [$desde, $hasta]);

        if ($agente !== '') {
            $q->where('p.GESTOR', 'like', "%{$agente}%");
        }

        if ($cartera !== '') {
            $q->where('p.cartera', $cartera);
        }

        $registros = $q->orderByDesc('p.FECHA')
            ->paginate(10)
            ->appends($request->query());

        $carteras = ['Propia 1 y 2', 'Propia 3', 'Propia 4'];

        return view('reportes.pagos.index', compact(
            'desde', 'hasta', 'agente', 'cartera', 'carteras', 'registros'
        ));
    }

    public function xlsx(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $desde  = $request->query('desde', Carbon::now()->startOfMonth()->toDateString());
        $hasta  = $request->query('hasta', Carbon::now()->endOfMonth()->toDateString());
        $agente = trim((string) $request->query('agente', ''));
        $cartera= trim((string) $request->query('cartera', ''));

        $filename = "reporte_pagos_{$desde}_{$hasta}.xlsx";

        return Excel::download(
            new ReportePagosExport($desde, $hasta, $agente, $cartera),
            $filename
        );
    }
}
