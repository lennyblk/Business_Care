<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class CheckAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('user_id')) {
            return redirect()->route('login')->withErrors(['error' => 'Vous devez être connecté pour accéder à cette page.']);
        }

        $userType = session('user_type');
        $route = $request->route()->getName();

        switch ($userType) {
            case 'societe':
                if (!str_starts_with($route, 'dashboard.client') &&
                    !str_starts_with($route, 'contracts.') &&
                    !str_starts_with($route, 'quotes.') &&
                    !str_starts_with($route, 'employees.') &&
                    !str_starts_with($route, 'payments.') &&
                    !str_starts_with($route, 'invoices.') &&
                    !str_starts_with($route, 'stripe.') &&
                    !str_starts_with($route, 'client.event_proposals.') &&
                    !str_starts_with($route, 'profile.')) {
                    return redirect()->route('dashboard.client')
                        ->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
                }
                break;

            case 'employee':
                if (!str_starts_with($route, 'dashboard.employee') &&
                    !str_starts_with($route, 'events.') &&
                    !str_starts_with($route, 'employee.events') &&
                    !str_starts_with($route, 'profile.')) {
                    return redirect()->route('dashboard.employee')
                        ->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
                }
                break;

            case 'provider':
                if (!str_starts_with($route, 'dashboard.provider') &&
                    !str_starts_with($route, 'provider.assignments.') &&
                    !str_starts_with($route, 'profile.')) {
                    return redirect()->route('dashboard.provider')
                        ->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
                }
                break;

            case 'admin':
                if (!str_starts_with($route, 'dashboard.admin') &&
                    !str_starts_with($route, 'admin.company') &&
                    !str_starts_with($route, 'admin.prestataires') &&
                    !str_starts_with($route, 'admin.salaries') &&
                    !str_starts_with($route, 'admin.activities') &&
                    !str_starts_with($route, 'admin.inscriptions') &&
                    !str_starts_with($route, 'admin.contracts') &&
                    !str_starts_with($route, 'admin.contracts2') &&
                    !str_starts_with($route, 'admin.invoices') &&
                    !str_starts_with($route, 'admin.event_proposals.') &&
                    !str_starts_with($route, 'profile.')) {
                    return redirect()->route('dashboard.admin')
                        ->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
                }
                break;

            default:
                return redirect()->route('login')->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
        }

        return $next($request);
    }
}
