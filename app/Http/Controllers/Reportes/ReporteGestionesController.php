<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Exports\Gestiones\GestionesExport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReporteGestionesController extends Controller
{
    /**
     * Mapeo de configuración por tipo
     */
    private function getConfig($tipo)
    {
        return match($tipo) {
            'propia12' => ['tabla' => 'Gestiones_1y2',    'vista' => 'reportes.gestiones.propia12'],
            'propia3'  => ['tabla' => 'Gestiones_Propia3', 'vista' => 'reportes.gestiones.propia3'],
            'propia4'  => ['tabla' => 'Gestiones_Propia4', 'vista' => 'reportes.gestiones.propia4'],
            default    => abort(404, "Tipo de reporte no válido")
        };
    }

    public function index(Request $request, $tipo)
    {
        if (!session()->has('usuario')) return redirect()->route('login');

        $config = $this->getConfig($tipo);

        // Filtros de fecha (Default mensual)
        $desde = $request->query('desde', Carbon::now()->startOfMonth()->toDateString());
        $hasta = $request->query('hasta', Carbon::now()->endOfMonth()->toDateString());

        // Filtros de búsqueda (Sin teléfono)
        $documento    = trim((string) $request->query('documento', ''));
        $tipificacion = trim((string) $request->query('tipificacion', ''));

        $query = DB::table($config['tabla'])
            ->whereBetween('dateprocessed', [$desde . ' 00:00:00', $hasta . ' 23:59:59']);

        if ($documento !== '') {
            $query->where('documento', 'like', "%{$documento}%");
        }

        if ($tipificacion !== '') {
            $query->where('value2', 'like', "%{$tipificacion}%");
        }

        $registros = $query->orderByDesc('dateprocessed')
            ->paginate(10)
            ->appends($request->query());

        return view($config['vista'], compact(
            'desde', 'hasta', 'documento', 'tipificacion', 'registros', 'tipo'
        ));
    }

    public function xlsx(Request $request, $tipo)
    {
        if (!session()->has('usuario')) return redirect()->route('login');

        $config = $this->getConfig($tipo);

        $desde = $request->query('desde', Carbon::now()->startOfMonth()->toDateString());
        $hasta = $request->query('hasta', Carbon::now()->endOfMonth()->toDateString());
        $documento = trim((string) $request->query('documento', ''));
        $tipificacion = trim((string) $request->query('tipificacion', ''));

        $filename = "gestiones_{$tipo}_{$desde}_{$hasta}.xlsx";

        return Excel::download(
            new GestionesExport($config['tabla'], $desde, $hasta, $documento, $tipificacion),
            $filename
        );
    }
}