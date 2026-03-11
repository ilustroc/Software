<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PagosController extends Controller
{
    // Mapeo de tipos a tablas y sistemas
    private function getMeta($tipo) {
        return match($tipo) {
            'propia12' => ['tabla' => 'Pagos_1y2', 'sistema' => 1, 'label' => 'Propia 1 y 2'],
            'propia3'  => ['tabla' => 'Pagos_3',   'sistema' => 3, 'label' => 'Propia 3'],
            'propia4'  => ['tabla' => 'Pagos_4',   'sistema' => 4, 'label' => 'Propia 4'],
            default    => abort(404, "Tipo de cartera no válida")
        };
    }

    public function index(Request $request, $tipo)
    {
        if (!session()->has('usuario')) return redirect()->route('login');

        $meta = $this->getMeta($tipo);
        $dni = $request->dni;
        $query = DB::table($meta['tabla'])->orderBy('FECHA', 'desc');

        if ($dni) {
            $query->where('DNI', $dni);
        }

        $pagos = $query->paginate(10)->appends($request->query());
        
        // Retornamos la vista correspondiente (puedes usar una sola vista genérica también)
        return view("pagos.$tipo", compact('pagos', 'dni', 'tipo'));
    }

    public function template($tipo)
    {
        $meta = $this->getMeta($tipo);
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"plantilla_pagos_{$tipo}.csv\"",
        ];

        return response()->stream(function () {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['DNI', 'OPERACION', 'MONEDA', 'FECHA', 'MONTO', 'GESTOR']);
            fclose($out);
        }, 200, $headers);
    }

    public function store(Request $request, $tipo)
    {
        $meta = $this->getMeta($tipo);
        $data = $request->validate([
            'dni'       => ['required','string','max:15'],
            'operacion' => ['required','string','max:50'],
            'moneda'    => ['nullable','string','max:20'],
            'fecha'     => ['required','date'],
            'monto'     => ['required','numeric','min:0.01'],
            'gestor'    => ['nullable','string','max:100'],
        ]);

        $data['SISTEMA'] = $meta['sistema'];

        try {
            DB::table($meta['tabla'])->insert($data);
            return back()->with('msg', 'Pago registrado correctamente.');
        } catch (Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function upload(Request $request, $tipo)
    {
        $meta = $this->getMeta($tipo);
        $request->validate(['archivo' => ['required','file','mimes:csv,txt']]);

        try {
            $path = $request->file('archivo')->getRealPath();
            $rows = array_map('str_getcsv', file($path));
            $insert = [];

            foreach ($rows as $index => $r) {
                if ($index === 0 && strtoupper(trim($r[0] ?? '')) === 'DNI') continue;
                if (count($r) < 5 || empty($r[0])) continue;

                $insert[] = [
                    'SISTEMA'   => $meta['sistema'],
                    'DNI'       => trim($r[0]),
                    'OPERACION' => trim($r[1]),
                    'MONEDA'    => trim($r[2]) ?: null,
                    'FECHA'     => date('Y-m-d', strtotime(str_replace('/', '-', $r[3]))),
                    'MONTO'     => (float) str_replace([',', ' '], ['', ''], $r[4]),
                    'GESTOR'    => trim($r[5] ?? null),
                ];
            }

            foreach (array_chunk($insert, 200) as $chunk) {
                DB::table($meta['tabla'])->insert($chunk);
            }

            return back()->with('msg', "Carga masiva en {$meta['label']} completada: " . count($insert) . " registros.");
        } catch (Throwable $e) {
            return back()->with('error', 'Error al cargar: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $tipo, $id)
    {
        $meta = $this->getMeta($tipo);
        $data = $request->validate([
            'moneda' => ['nullable','string','max:20'],
            'fecha'  => ['required','date'],
            'monto'  => ['required','numeric','min:0.01'],
            'gestor' => ['nullable','string','max:100'],
        ]);

        try {
            DB::table($meta['tabla'])->where('id', $id)->update($data);
            return back()->with('msg', 'Pago actualizado.');
        } catch (Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($tipo, $id)
    {
        $meta = $this->getMeta($tipo);
        try {
            DB::table($meta['tabla'])->where('id', $id)->delete();
            return back()->with('msg', 'Pago eliminado.');
        } catch (Throwable $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}