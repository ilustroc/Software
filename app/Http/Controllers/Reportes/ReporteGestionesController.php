<?php

namespace App\Http\Controllers\Reportes;

use App\Exports\Gestiones\GestionesExport;
use App\Http\Controllers\Controller;
use App\Models\Cartera;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReporteGestionesController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        [$desde, $hasta, $carteraId, $dni, $gestor] = $this->filters($request);

        $query = $this->baseQuery()
            ->whereBetween('g.dateprocessed', [$desde . ' 00:00:00', $hasta . ' 23:59:59']);

        if ($carteraId) {
            $query->where('g.cartera_id', $carteraId);
        }

        if ($dni !== '') {
            $query->where('g.documento', 'like', "%{$dni}%");
        }

        if ($gestor !== '') {
            $query->where('g.fullname', 'like', "%{$gestor}%");
        }

        $registros = $query
            ->orderByDesc('g.dateprocessed')
            ->paginate(10)
            ->appends($request->query());

        $carteras = $this->carteras();

        return view('reportes.gestiones.index', compact(
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
        $filename = "reporte_gestiones_{$desde}_{$hasta}.xlsx";

        return Excel::download(
            new GestionesExport($carteraId, $desde, $hasta, $dni, $gestor),
            $filename,
        );
    }

    private function filters(Request $request): array
    {
        $desde = $request->query('fecha_inicio', $request->query('desde', Carbon::now()->startOfMonth()->toDateString()));
        $hasta = $request->query('fecha_fin', $request->query('hasta', Carbon::now()->endOfMonth()->toDateString()));
        $carteraId = $request->integer('cartera_id') ?: null;
        $dni = trim((string) $request->query('dni', $request->query('documento', '')));
        $gestor = trim((string) $request->query('gestor', ''));

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

    private function baseQuery()
    {
        return DB::table('gestiones as g')
            ->join('carteras as c', 'c.id', '=', 'g.cartera_id')
            ->select(
                'g.id',
                'c.nombre as cartera_nombre',
                'g.dateprocessed',
                'g.documento',
                'g.nombre',
                'g.callerid',
                'g.value2',
                'g.value1',
                'g.operacion',
                'g.fullname',
                'g.campaign',
                'g.monto_promesa',
                'g.nro_cuota',
                'g.fecha_promesa',
            );
    }
}
