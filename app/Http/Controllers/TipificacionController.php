<?php

namespace App\Http\Controllers;

use App\Models\Tipificacion;
use Illuminate\Http\Request;
use Throwable;

class TipificacionController extends Controller
{
    public function index()
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $tipificaciones = Tipificacion::orderBy('orden')->get();

        return view('parametros.tipificaciones', compact('tipificaciones'));
    }

    public function store(Request $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'tipificacion' => ['required','string','max:200'],
            'resultado'    => ['required','string','max:100'],
            'mc'           => ['required','string','max:50'],
            'peso'         => ['required','integer','min:0'],
        ]);

        // si no envías orden, lo ponemos al final
        $maxOrden = Tipificacion::max('orden') ?? 0;
        $data['orden'] = $maxOrden + 1;

        Tipificacion::create($data);

        return back()->with('msg', 'Tipificación creada correctamente.');
    }

    public function update(Request $request, Tipificacion $tipificacion)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $data = $request->validate([
            'tipificacion' => ['required','string','max:200'],
            'resultado'    => ['required','string','max:100'],
            'mc'           => ['required','string','max:50'],
            'peso'         => ['required','integer','min:0'],
            'orden'        => ['required','integer','min:1'],
        ]);

        $tipificacion->update($data);

        return back()->with('msg', 'Tipificación actualizada.');
    }

    public function destroy(Tipificacion $tipificacion)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        try {
            $tipificacion->delete();
            return back()->with('msg', 'Tipificación eliminada.');
        } catch (Throwable $e) {
            return back()->with('error', 'No se pudo eliminar: ' . $e->getMessage());
        }
    }
}
