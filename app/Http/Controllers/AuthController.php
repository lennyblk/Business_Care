<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'user_type' => 'required|in:societe,employe,prestataire,admin',
        ]);

        // Logique d'authentification

        return redirect()->intended('/dashboard/client');
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
            'user_type' => 'required|in:societe,employe,prestataire',
            'company_name' => 'required_if:user_type,societe',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        // Logique d'enregistrement

        return redirect()->route('login')->with('success', 'Compte créé avec succès!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
