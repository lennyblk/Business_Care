<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon; // Pour la gestion des dates
use App\Models\Company;
use App\Models\Employee;
use App\Models\Provider;
use App\Models\Contract;
use App\Models\Event;
use App\Models\Quote;
use App\Models\Invoice;
use App\Models\Activity;

class DashboardController extends Controller
{
    /**
     * Constructeur avec middleware d'authentification
     */
    public function __construct()
    {
        \Log::info('DashboardController constructeur');
        // Retirer ce middleware car nous utilisons notre propre système d'authentification
        // $this->middleware('auth');
    }

    /**
     * Tableau de bord pour les clients/sociétés
     */
    public function client()
    {
        \Log::info('Tentative d\'accès au dashboard client', [
            'session_data' => [
                'user_type' => session('user_type'),
                'user_id' => session('user_id')
            ]
        ]);

        if (session('user_type') !== 'societe') {
            return redirect()->route('dashboard.' . session('user_type'));
        }

        \Log::info('Accès autorisé au dashboard client');

        // Récupération des données pour la société
        $companyId = session('user_id');
        $company = Company::find($companyId);

        if (!$company) {
            return view('dashboards.client', [
                'activeContracts' => 0,
                'employeesCount' => 0,
                'pendingQuotes' => 0,
                'unpaidInvoices' => 0,
                'recentActivities' => collect([]), // Collection vide
            ]);
        }

        // Date actuelle pour calculer les contrats actifs
        $today = Carbon::today()->toDateString();

        // Récupération des données pour le tableau de bord
        $data = [
            'activeContracts' => Contract::where('company_id', $companyId)
                                         ->where('start_date', '<=', $today)
                                         ->where('end_date', '>=', $today)
                                         ->count(),

            'employeesCount' => Employee::where('company_id', $companyId)
                                            ->count(),

            'pendingQuotes' => Quote::where('company_id', $companyId)
                                    ->where('status', 'pending')
                                    ->count(),

            'unpaidInvoices' => Invoice::where('company_id', $companyId)
                                       ->where('payment_status', 'unpaid')
                                       ->count(),

            'recentActivities' => Event::where('company_id', $companyId)
                                         ->take(5)
                                         ->get(),
        ];

        return view('dashboards.client', $data);
    }

    /**
 * Tableau de bord pour les employés
 */
public function employee()
{
    \Log::info('Tentative d\'accès au dashboard employé', [
        'session_data' => [
            'user_type' => session('user_type'),
            'user_id' => session('user_id'),
            'user_email' => session('user_email')
        ]
    ]);

    if (session('user_type') !== 'employe') {
        \Log::warning('Accès refusé au dashboard employé', [
            'user_type' => session('user_type')
        ]);
        return redirect()->route('dashboard.' . session('user_type'));
    }

    \Log::info('Accès autorisé au dashboard employé');

    // Récupérer l'employé connecté
    $employeeId = session('user_id');
    $employee = Employee::find($employeeId);

    if (!$employee) {
        // Si nous ne pouvons pas trouver l'employé, utilisons une solution temporaire (comme dans votre EmployeeController)
        $employee = Employee::first();
    }

    // Si nous avons un employé
    if ($employee) {
        // Récupérer son entreprise
        $companyId = $employee->company_id;

        // Récupérer les événements auxquels l'employé est inscrit
        $eventRegistrations = \App\Models\EventRegistration::where('employee_id', $employee->id)->get();
        $eventsCount = $eventRegistrations->count();

        // Récupérer les IDs des événements auxquels l'employé est inscrit
        $eventIds = $eventRegistrations->pluck('event_id')->toArray();

        // Récupérer les détails des événements à venir (date >= aujourd'hui)
        $today = date('Y-m-d');
        $upcomingEvents = Event::whereIn('id', $eventIds)
                          ->where('date', '>=', $today)
                          ->orderBy('date', 'asc')
                          ->take(5)
                          ->get();

        return view('dashboards.employee', compact('employee', 'eventsCount', 'upcomingEvents'));
    }

    // Si aucun employé n'est trouvé, afficher une vue par défaut
    return view('dashboards.employee');
}

    /**
     * Tableau de bord pour les prestataires
     */
    public function provider()
    {
        \Log::info('Tentative d\'accès au dashboard prestataire', [
            'session_data' => [
                'user_type' => session('user_type'),
                'user_id' => session('user_id')
            ]
        ]);

        if (session('user_type') !== 'prestataire') {
            return redirect()->route('dashboard.' . session('user_type'));
        }

        \Log::info('Accès autorisé au dashboard prestataire');
        return view('dashboards.provider');
    }

    /**
     * Tableau de bord pour les administrateurs
     */
    public function admin()
    {
        $companyCount = Company::count();
        $employeeCount = Employee::count();
        $providerCount = Provider::count();
        $contractCount = Contract::count();
        $activityCount = Event::count();

        // Date actuelle pour calculer les contrats actifs
        $today = Carbon::today()->toDateString();

        // Nouvelles statistiques pour l'admin
        $activeContractsCount = Contract::where('start_date', '<=', $today)
                                        ->where('end_date', '>=', $today)
                                        ->count();

        $pendingQuotesCount = Quote::where('status', 'pending')->count();
        $unpaidInvoicesCount = Invoice::where('status', 'unpaid')->count();
        $recentActivities = Activity::latest()->take(10)->get();

        return view('dashboards.admin', compact(
            'companyCount',
            'employeeCount',
            'providerCount',
            'contractCount',
            'activityCount',
            'activeContractsCount',
            'pendingQuotesCount',
            'unpaidInvoicesCount',
            'recentActivities'
        ));
    }
}
