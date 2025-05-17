<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckAuth
{
    public function handle(Request $request, Closure $next)
    {
        Log::info('CheckAuth - Session data:', [
            'user_id' => session('user_id'),
            'user_type' => session('user_type'),
            'route' => $request->route()->getName()
        ]);

        if (!session()->has('user_id')) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Vous devez être connecté pour accéder à cette page.']);
        }

        $userType = session('user_type');
        $route = $request->route()->getName();

        if ($userType === 'prestataire') {
            $allowedPrefixes = [
                'dashboard.provider',
                'provider.',
                'profile.',
                'provider.assignments.'
            ];

            foreach ($allowedPrefixes as $prefix) {
                if (str_starts_with($route, $prefix)) {
                    return $next($request);
                }
            }

            return redirect()->route('dashboard.provider')
                ->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
        }

        if ($userType === 'employe') {
            $allowedPrefixes = [
                'dashboard.employee',
                'employee.',
                'events.',
                'profile.'
            ];

            foreach ($allowedPrefixes as $prefix) {
                if (str_starts_with($route, $prefix)) {
                    return $next($request);
                }
            }

            return redirect()->route('dashboard.employee')
                ->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
        }

        switch ($userType) {
            case 'societe':
                if (!str_starts_with($route, 'dashboard.client') &&
                    !str_starts_with($route, 'contracts.') &&
                    !str_starts_with($route, 'quotes.') &&
                    !str_starts_with($route, 'employees.') &&
                    !str_starts_with($route, 'employees.import-form') &&
                    !str_starts_with($route, 'employees.import') &&
                    !str_starts_with($route, 'employees.download-template') &&
                    !str_starts_with($route, 'payments.') &&
                    !str_starts_with($route, 'invoices.') &&
                    !str_starts_with($route, 'stripe.') &&
                    !str_starts_with($route, 'client.associations.') &&
                    !str_starts_with($route, 'client.event_proposals.') &&
                    !str_starts_with($route, 'profile.')) {
                    return redirect()->route('dashboard.client')
                        ->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
                }
                break;

            case 'employee':
                if (!str_starts_with($route, 'dashboard.employee') &&
                    !str_starts_with($route, 'events.') &&
                    !str_starts_with($route, 'employee.events.') &&
                    !str_starts_with($route, 'employee.advice.') &&
                    !str_starts_with($route, 'employee.preferences.') &&
                    !str_starts_with($route, 'profile.')) {
                    return redirect()->route('dashboard.employee')
                        ->withErrors(['error' => 'Vous n\'avez pas accès à cette page.']);
                }
                break;

            case 'provider':
                if (!str_starts_with($route, 'dashboard.provider') &&
                    !str_starts_with($route, 'provider.assignments.') &&
                    !str_starts_with($route, 'provider.evaluations.') &&
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
                    !str_starts_with($route, 'admin.advice') &&
                    !str_starts_with($route, 'employees.import-form') &&
                    !str_starts_with($route, 'employees.import') &&
                    !str_starts_with($route, 'employees.download-template') &&
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
