<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Vérifiez si l'utilisateur est connecté
        if (!session('is_logged_in')) {
            return redirect()->route('login')->withErrors(['message' => 'Veuillez vous connecter pour accéder à cette page']);
        }

        // Vérifiez le type d'utilisateur pour les routes spécifiques
        $userType = session('user_type');
        $route = $request->route()->getName();

        // S'assurer que l'utilisateur accède à son propre tableau de bord
        if ($route === 'dashboard.client' && $userType !== 'societe') {
            return redirect()->route("dashboard.{$userType}");
        }

        if ($route === 'dashboard.employee' && $userType !== 'employe') {
            return redirect()->route("dashboard.{$userType}");
        }

        if ($route === 'dashboard.provider' && $userType !== 'prestataire') {
            return redirect()->route("dashboard.{$userType}");
        }

        if ($route === 'dashboard.admin' && $userType !== 'admin') {
            return redirect()->route("dashboard.{$userType}");
        }

        return $next($request);
    }
}
