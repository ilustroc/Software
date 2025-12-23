<?php

namespace App\Http\Controllers\Reportes;

use App\Http\Controllers\Controller;
use App\Exports\Gestiones\GestionesPropia3Export;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class GestionesPropia3ReportController extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        // Default mensual
        $desde = $request->query('desde', Carbon::now()->startOfMonth()->toDateString());
        $hasta = $request->query('hasta', Carbon::now()->endOfMonth()->toDateString());

        $documento    = trim((string) $request->query('documento', ''));   // documento
        $telefono     = trim((string) $request->query('telefono', ''));    // callerid
        $tipificacion = trim((string) $request->query('tipificacion', ''));// value2

        $desdeFull = $desde.' 00:00:00';
        $hastaFull = $hasta.' 23:59:59';

        $q = DB::table('Gestiones_Propia3')
            ->whereBetween('dateprocessed', [$desdeFull, $hastaFull]);

        if ($documento !== '') {
            $q->where('documento', 'like', "%{$documento}%");
        }

        if ($telefono !== '') {
            $q->where('callerid', 'like', "%{$telefono}%");
        }

        if ($tipificacion !== '') {
            $q->where('value2', 'like', "%{$tipificacion}%");
        }

        $registros = $q->orderByDesc('dateprocessed')
            ->paginate(10)
            ->appends($request->query());

        return view('reportes.gestiones.propia3', compact(
            'desde', 'hasta', 'documento', 'telefono', 'tipificacion', 'registros'
        ));
    }

    public function xlsx(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $desde = $request->query('desde', Carbon::now()->startOfMonth()->toDateString());
        $hasta = $request->query('hasta', Carbon::now()->endOfMonth()->toDateString());

        $documento    = trim((string) $request->query('documento', ''));
        $telefono     = trim((string) $request->query('telefono', ''));
        $tipificacion = trim((string) $request->query('tipificacion', ''));

        $filename = "gestiones_propia3_{$desde}_{$hasta}.xlsx";

        return Excel::download(
            new GestionesPropia3Export($desde, $hasta, $telefono, $documento, $tipificacion),
            $filename
        );
    }
}
