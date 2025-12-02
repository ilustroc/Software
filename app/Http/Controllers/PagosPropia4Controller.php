<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PagosPropia4Controller extends Controller
{
    public function index(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $dni = $request->dni;

        $query = DB::table('Pagos_4')->orderBy('FECHA', 'desc');

        if ($dni) {
            $query->where('DNI', $dni);
        }

        $pagos = $query->paginate(10)->appends($request->query());

        return view('pagos.propia4', compact('pagos', 'dni'));
    }

    // DESCARGA PLANTILLA CSV
    public function template()
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $headers = [
            'Content-Type'        => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="plantilla_pagos_4.csv"',
        ];

        $callback = function () {
            $out = fopen('php://output', 'w');

            // Encabezados esperados por upload():
            // DNI, OPERACION, MONEDA, FECHA, MONTO, GESTOR
            fputcsv($out, ['DNI', 'OPERACION', 'MONEDA', 'FECHA', 'MONTO', 'GESTOR']);

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    // CARGA MANUAL
    public function store(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'dni'       => ['required','string','max:15'],
            'operacion' => ['required','string','max:50'],
            'moneda'    => ['nullable','string','max:20'],
            'fecha'     => ['required','date'],
            'monto'     => ['required','numeric','min:0.01'],
            'gestor'    => ['nullable','string','max:100'],
        ]);

        $data['SISTEMA'] = 4;

        try {
            DB::table('Pagos_4')->insert($data);

            return back()->with('msg', 'Pago registrado correctamente.');
        } catch (Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // CARGA MASIVA (CSV / TXT simple)
    public function upload(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $request->validate([
            'archivo' => ['required','file','mimes:csv,txt,xlsx'],
        ]);

        try {
            $file = $request->file('archivo');
            $path = $file->getRealPath();

            $rows = array_map('str_getcsv', file($path));

            $insert = [];

            foreach ($rows as $index => $r) {
                // Saltar encabezado si viene en la primera fila
                if ($index === 0 && isset($r[0]) && strtoupper(trim($r[0])) === 'DNI') {
                    continue;
                }

                // Se espera: DNI, OPERACION, MONEDA, FECHA, MONTO, GESTOR
                if (count($r) < 6) {
                    continue;
                }

                $dni       = trim($r[0]);
                $operacion = trim($r[1]);
                $moneda    = trim($r[2]);
                $fechaRaw  = trim($r[3]);
                $montoRaw  = trim($r[4]);
                $gestor    = trim($r[5]);

                if ($dni === '' || $operacion === '' || $fechaRaw === '' || $montoRaw === '') {
                    continue;
                }

                $fecha = date('Y-m-d', strtotime($fechaRaw));
                $monto = floatval(str_replace([','], ['.'], $montoRaw));

                $insert[] = [
                    'SISTEMA'   => 4,
                    'DNI'       => $dni,
                    'OPERACION' => $operacion,
                    'MONEDA'    => $moneda !== '' ? $moneda : null,
                    'FECHA'     => $fecha,
                    'MONTO'     => $monto,
                    'GESTOR'    => $gestor !== '' ? $gestor : null,
                ];
            }

            if (empty($insert)) {
                return back()->with('error', 'El archivo no contiene filas válidas para importar.');
            }

            foreach (array_chunk($insert, 200) as $chunk) {
                DB::table('Pagos_4')->insert($chunk);
            }

            return back()->with('msg', 'Pagos cargados masivamente correctamente. Total: '.count($insert));

        } catch (Throwable $e) {
            return back()->with('error', 'Error al cargar archivo: ' . $e->getMessage());
        }
    }

    // EDITAR PAGO
    public function update(Request $request, $id)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'moneda' => ['nullable','string','max:20'],
            'fecha'  => ['required','date'],
            'monto'  => ['required','numeric','min:0.01'],
            'gestor' => ['nullable','string','max:100'],
        ]);

        try {
            DB::table('Pagos_4')->where('id', $id)->update($data);

            return back()->with('msg', 'Pago actualizado correctamente.');
        } catch (Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        try {
            DB::table('Pagos_4')->where('id', $id)->where('SISTEMA', 4)->delete();

            return back()->with('msg', 'Pago eliminado correctamente.');
        } catch (Throwable $e) {
            return back()->with('error', 'Error al eliminar pago: ' . $e->getMessage());
        }
    }
}
