<?php

namespace App\Http\Controllers;

use App\Models\Cartera;
use App\Models\Gestion;
use App\Models\Llamada;
use App\Models\Pago;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $hoy = Carbon::today();
        $inicioMes = $hoy->copy()->startOfMonth()->toDateString();
        $finMes = $hoy->copy()->endOfMonth()->toDateString();

        $pagosRegistradosHoy = Pago::query()
            ->whereDate('created_at', $hoy)
            ->count();

        $montoPagadoHoy = (float) Pago::query()
            ->whereDate('fecha', $hoy)
            ->sum('monto');

        $pagosPorCarteraDia = $this->pagosPorCartera(
            $hoy->toDateString(),
            $hoy->toDateString(),
        );

        $pagosPorCarteraMes = $this->pagosPorCartera($inicioMes, $finMes);

        $gestionesCargadasHoy = Gestion::query()
            ->whereDate('created_at', $hoy)
            ->count();

        $gestionesPorCarteraDia = DB::table('gestiones as g')
            ->join('carteras as c', 'c.id', '=', 'g.cartera_id')
            ->whereBetween('g.dateprocessed', [
                $hoy->copy()->startOfDay()->toDateTimeString(),
                $hoy->copy()->endOfDay()->toDateTimeString(),
            ])
            ->select(
                'c.nombre as cartera',
                DB::raw('count(*) as total')
            )
            ->groupBy('c.id', 'c.nombre')
            ->orderByDesc('total')
            ->get();

        $ultimaCarga = $this->ultimaCarga();
        $carteraMayorPagoDia = $pagosPorCarteraDia->first();
        $carterasActivas = Cartera::query()->activa()->count();

        $estadoSistema = [
            'estado' => 'Operativo',
            'detalle' => $carterasActivas . ' carteras activas',
        ];

        return view('dashboard', compact(
            'pagosRegistradosHoy',
            'montoPagadoHoy',
            'pagosPorCarteraDia',
            'pagosPorCarteraMes',
            'gestionesCargadasHoy',
            'gestionesPorCarteraDia',
            'ultimaCarga',
            'estadoSistema',
            'carteraMayorPagoDia',
        ));
    }

    private function pagosPorCartera(string $desde, string $hasta)
    {
        return DB::table('pagos')
            ->join('carteras', 'carteras.id', '=', 'pagos.cartera_id')
            ->whereNull('pagos.deleted_at')
            ->whereBetween('pagos.fecha', [$desde, $hasta])
            ->select(
                'carteras.nombre as cartera',
                DB::raw('count(*) as cantidad'),
                DB::raw('coalesce(sum(pagos.monto), 0) as total')
            )
            ->groupBy('carteras.id', 'carteras.nombre')
            ->orderByDesc('total')
            ->get();
    }

    private function ultimaCarga(): ?array
    {
        return collect([
            ['tipo' => 'Pagos', 'fecha' => Pago::query()->max('created_at')],
            ['tipo' => 'Gestiones', 'fecha' => Gestion::query()->max('created_at')],
            ['tipo' => 'Llamadas', 'fecha' => Llamada::query()->max('created_at')],
        ])
            ->filter(fn (array $item) => filled($item['fecha']))
            ->sortByDesc(fn (array $item) => Carbon::parse($item['fecha'])->timestamp)
            ->first();
    }
}
