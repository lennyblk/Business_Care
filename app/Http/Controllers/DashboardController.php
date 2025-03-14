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
        $this->middleware('auth');
    }

    /**
     * Tableau de bord pour les clients/sociétés
     */
    public function client()
    {
        // Vous pouvez ajouter ici la logique pour charger les données spécifiques au client
        // Par exemple: $data = User::find(Auth::id())->clientData();

        return view('dashboards.client');
    }

    /**
     * Tableau de bord pour les employés
     */
    public function employee()
    {
        // Logique pour les employés
        return view('dashboards.employee');
    }

    /**
     * Tableau de bord pour les prestataires
     */
    public function provider()
    {
        // Logique pour les prestataires
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
