<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        return view('dashboards.client');
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
        // Logique d'administration
        // Par exemple, charger des statistiques globales

        return view('dashboards.admin');
    }
}
