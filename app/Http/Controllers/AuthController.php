<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'usuario'   => ['required','string'],
            'password'  => ['required','string'],
        ]);

        $user     = $request->input('usuario');
        $password = $request->input('password');

        // usuario fijo
        if ($user === 'impulsego' && $password === 'Leg@l2024!.') {
            Session::put('usuario', $user);
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'login' => 'Usuario o contraseña incorrectos.',
        ])->withInput();
    }

    public function logout()
    {
        Session::forget('usuario');
        return redirect()->route('login');
    }
}
