<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials)) {
            return back()->withErrors([
                'email' => 'Credenciais inválidas',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('auth.token');
    }
}