<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Contract; // Corriger l'importation de la classe Contract

class AdminCompanyController extends Controller
{
    /**
     * Affiche la liste des entreprises.
     */
    public function index()
    {
        $companies = Company::all();
        return view('dashboards.gestion_admin.societe.index', compact('companies'));
    }

    /**
     * Affiche le formulaire de création d'une entreprise.
     */
    public function create()
    {
        return view('dashboards.gestion_admin.societe.create');
    }

    /**
     * Enregistre une nouvelle entreprise.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:100',
            'pays' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20',
            'creation_date' => 'required|date',
            'email' => 'required|email|unique:company,email',
            'password' => 'required|string|max:255',
            'siret' => 'nullable|string|max:14',
            'formule_abonnement' => 'required|in:Starter,Basic,Premium',
            'statut_compte' => 'required|in:Actif,Inactif',
            'date_debut_contrat' => 'nullable|date',
            'date_fin_contrat' => 'nullable|date',
            'mode_paiement_prefere' => 'nullable|string|max:50',
            'employee_count' => 'nullable|integer',
        ]);

        Company::create($request->all());
        return redirect()->route('admin.company')->with('success', 'Entreprise créée avec succès.');
    }

    /**
     * Affiche les détails d'une entreprise.
     */
    public function show($id)
    {
        $company = Company::findOrFail($id);
        return view('dashboards.gestion_admin.societe.show', compact('company'));
    }

    /**
     * Affiche le formulaire de modification d'une entreprise.
     */
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return view('dashboards.gestion_admin.societe.edit', compact('company'));
    }

    /**
     * Met à jour les informations d'une entreprise.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:100',
            'pays' => 'nullable|string|max:100',
            'phone' => 'required|string|max:20',
            'creation_date' => 'required|date',
            'email' => 'required|email|unique:company,email,' . $id,
            'password' => 'nullable|string|max:255',
            'siret' => 'nullable|string|max:14',
            'formule_abonnement' => 'required|in:Starter,Basic,Premium',
            'statut_compte' => 'required|in:Actif,Inactif',
            'date_debut_contrat' => 'nullable|date',
            'date_fin_contrat' => 'nullable|date',
            'mode_paiement_prefere' => 'nullable|string|max:50',
            'employee_count' => 'nullable|integer',
        ]);

        $company = Company::findOrFail($id);
        $data = $request->all();
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $company->update($data);
        return redirect()->route('admin.company')->with('success', 'Entreprise mise à jour avec succès.');
    }

    /**
     * Supprime une entreprise.
     */
    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();
        return redirect()->route('admin.company')->with('success', 'Entreprise supprimée avec succès.');
    }

    /**
     * Affiche les contrats d'une entreprise.
     */
    public function contracts($id)
    {
        $company = Company::findOrFail($id);
        $contrats = Contract::where('company_id', $id)->get();
        return view('dashboards.gestion_admin.societe.contracts', compact('company', 'contrats'));
    }
}
