<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Vérifier si l'utilisateur est connecté via la session
        if (!session()->has('user_id')) {
            return redirect()->route('login');
        }

        // Récupérer le type d'utilisateur depuis la session
        $userType = session('user_type');
        $route = $request->route()->getName();

        // Vérifier les permissions selon le type d'utilisateur
        switch ($userType) {
            case 'societe':
                if (!str_starts_with($route, 'dashboard.client')) {
                    return redirect()->route('dashboard.client');
                }
                break;

            case 'employe':
                if (!str_starts_with($route, 'dashboard.employee')) {
                    return redirect()->route('dashboard.employee');
                }
                break;

            case 'prestataire':
                if (!str_starts_with($route, 'dashboard.provider')) {
                    return redirect()->route('dashboard.provider');
                }
                break;

            default:
                return redirect()->route('login');
        }

        return $next($request);
    }
}
