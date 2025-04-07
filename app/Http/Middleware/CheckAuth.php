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
            return redirect()->route('login')->withErrors(['error' => 'Vous devez être connecté pour accéder à cette page.']);
        }

        // Récupérer le type d'utilisateur depuis la session
        $userType = session('user_type');
        $route = $request->route()->getName();

        // Vérifier les permissions selon le type d'utilisateur
        switch ($userType) {
            case 'societe':
                if (!str_starts_with($route, 'dashboard.client') &&
                    !str_starts_with($route, 'contracts.')&&
                    !str_starts_with($route, 'quotes.')) {
                    return redirect()->route('dashboard.client')->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
                }
                break;

            case 'employe':
                if (!str_starts_with($route, 'dashboard.employee') &&
                    !str_starts_with($route, 'events.') &&
                    !str_starts_with($route, 'employee.events')) {
                    return redirect()->route('dashboard.employee')
                        ->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
                }
                break;

            case 'prestataire':
                if (!str_starts_with($route, 'dashboard.provider')) {
                    return redirect()->route('dashboard.provider')->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
                }
                break;

            case 'admin':
                // Autoriser l'accès aux routes admin.company
                if (!str_starts_with($route, 'dashboard.admin') &&
                    !str_starts_with($route, 'admin.company') &&
                    !str_starts_with($route, 'admin.prestataires') &&
                    !str_starts_with($route, 'admin.salaries') &&
                    !str_starts_with($route, 'admin.activities')) {
                    return redirect()->route('dashboard.admin')->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
                }
                break;

            default:
                return redirect()->route('login')->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
        }

        return $next($request);
    }
}
