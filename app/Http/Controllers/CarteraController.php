<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCarteraRequest;
use App\Models\Cartera;
use Throwable;

class CarteraController extends Controller
{
    public function index()
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        $carteras = Cartera::query()
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get();

        return view('parametros.carteras', compact('carteras'));
    }

    public function store(StoreCarteraRequest $request)
    {
        if (!session()->has('usuario')) {
            return redirect()->route('login');
        }

        try {
            $orden = ((int) Cartera::query()->max('orden')) + 10;

            Cartera::query()->create($request->validated() + [
                'orden' => $orden,
            ]);

            return back()->with('msg', 'Cartera creada correctamente.');
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'No se pudo crear la cartera: ' . $e->getMessage());
        }
    }
}
