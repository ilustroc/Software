<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PagosPropia12Controller extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $dni = $request->dni;

        $query = DB::table('Pagos_1y2')->orderBy('FECHA', 'desc');

        if ($dni) {
            $query->where('DNI', $dni);
        }

        $pagos = $query->paginate(10)->appends($request->query());

        return view('pagos.propia12', compact('pagos', 'dni'));
    }


    // CARGA MANUAL
    public function store(Request $request)
    {
        $data = $request->validate([
            'dni'       => ['required','string','max:15'],
            'operacion' => ['required','string','max:50'],
            'moneda'    => ['nullable','string','max:20'],
            'fecha'     => ['required','date'],
            'monto'     => ['required','numeric','min:0.01'],
            'gestor'    => ['nullable','string','max:100'],
        ]);

        $data['SISTEMA'] = 1;

        try {
            DB::table('Pagos_1y2')->insert($data);

            return back()->with('msg', 'Pago registrado correctamente.');
        } catch (Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // CARGA MASIVA (Excel/CSV)
    public function upload(Request $request)
    {
        $request->validate([
            'archivo' => ['required','file','mimes:xlsx,csv,txt'],
        ]);

        try {
            $file = $request->file('archivo');
            $path = $file->getRealPath();

            $rows = array_map('str_getcsv', file($path));

            $insert = [];

            foreach ($rows as $r) {
                // Se espera: DNI, OPERACION, MONEDA, FECHA, MONTO, GESTOR
                if (count($r) < 6) continue;

                $insert[] = [
                    'SISTEMA'   => 1,
                    'DNI'       => trim($r[0]),
                    'OPERACION' => trim($r[1]),
                    'MONEDA'    => trim($r[2]),
                    'FECHA'     => date('Y-m-d', strtotime($r[3])),
                    'MONTO'     => floatval($r[4]),
                    'GESTOR'    => trim($r[5]),
                ];
            }

            foreach (array_chunk($insert, 200) as $chunk) {
                DB::table('Pagos_1y2')->insert($chunk);
            }

            return back()->with('msg', 'Pagos cargados masivamente correctamente.');

        } catch (Throwable $e) {
            return back()->with('error', 'Error al cargar archivo: ' . $e->getMessage());
        }
    }

    // EDITAR PAGO
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'moneda' => ['nullable','string','max:20'],
            'fecha'  => ['required','date'],
            'monto'  => ['required','numeric','min:0.01'],
            'gestor' => ['nullable','string','max:100'],
        ]);

        try {
            DB::table('Pagos_1y2')->where('id', $id)->update($data);

            return back()->with('msg', 'Pago actualizado correctamente.');
        } catch (Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            DB::table('Pagos_1y2')->where('id', $id)->where('SISTEMA', 1)->delete();

            return back()->with('msg', 'Pago eliminado correctamente.');
        } catch (Throwable $e) {
            return back()->with('error', 'Error al eliminar pago: ' . $e->getMessage());
        }
    }
}
