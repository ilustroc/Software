<?php

namespace App\Http\Controllers\Reportes;

use App\Exports\Pagos\ReportePagosExport;
use App\Http\Controllers\Controller;
use App\Models\Cartera;
use App\Models\Pago;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportePagosController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        [$desde, $hasta, $carteraId, $dni, $gestor] = $this->filters($request);

        $query = Pago::query()
            ->with('cartera')
            ->whereBetween('fecha', [$desde, $hasta]);

        if ($carteraId) {
            $query->where('cartera_id', $carteraId);
        }

        if ($dni !== '') {
            $query->where('dni', 'like', "%{$dni}%");
        }

        if ($gestor !== '') {
            $query->where('gestor', 'like', "%{$gestor}%");
        }

        $registros = $query
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($request->query());

        $carteras = $this->carteras();

        return view('reportes.pagos.index', compact(
            'desde',
            'hasta',
            'carteraId',
            'dni',
            'gestor',
            'carteras',
            'registros',
        ));
    }

    public function xlsx(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        [$desde, $hasta, $carteraId, $dni, $gestor] = $this->filters($request);
        $filename = "reporte_pagos_{$desde}_{$hasta}.xlsx";

        return Excel::download(
            new ReportePagosExport($desde, $hasta, $carteraId, $dni, $gestor),
            $filename,
        );
    }

    private function filters(Request $request): array
    {
        $desde = $request->query('fecha_inicio', $request->query('desde', Carbon::now()->startOfMonth()->toDateString()));
        $hasta = $request->query('fecha_fin', $request->query('hasta', Carbon::now()->endOfMonth()->toDateString()));
        $carteraId = $request->integer('cartera_id') ?: null;
        $dni = trim((string) $request->query('dni', ''));
        $gestor = trim((string) $request->query('gestor', $request->query('agente', '')));

        return [$desde, $hasta, $carteraId, $dni, $gestor];
    }

    private function carteras()
    {
        return Cartera::query()
            ->activa()
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();
    }
}
