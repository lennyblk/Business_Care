<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Service;
use App\Models\Activity;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ContractController extends Controller
{
    /**
     * Affiche la liste des contrats de la société cliente
     */
    public function index()
    {
        // Vérification de l'authentification
        if (session('user_type') !== 'societe') {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
        }
        
        $companyId = session('user_id');
        
        // Récupération des contrats de la société
        $contracts = Contract::where('company_id', $companyId)
                           ->orderBy('start_date', 'desc')
                           ->paginate(10);
        
        // Ajout d'un attribut calculé pour déterminer si le contrat est actif
        $today = Carbon::today()->toDateString();
        foreach ($contracts as $contract) {
            $contract->is_active = ($contract->start_date <= $today && $contract->end_date >= $today);
        }
        
        return view('dashboards.client.contracts.index', compact('contracts'));
    }

    /**
     * Affiche le formulaire de création de contrat
     */
    public function create()
{
    // Vérification de l'authentification
    if (session('user_type') !== 'societe') {
        return redirect()->route('login')
            ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
    }
    
    $companyId = session('user_id');
    $company = \App\Models\Company::find($companyId);
    
    // Récupérer le nombre d'employés pour déterminer la formule par défaut
    $employeeCount = \App\Models\Employee::where('company_id', $companyId)->count();
    
    // Déterminer la formule par défaut en fonction du nombre d'employés
    $defaultFormula = 'Starter';
    if ($employeeCount > 250) {
        $defaultFormula = 'Premium';
    } elseif ($employeeCount > 30) {
        $defaultFormula = 'Basic';
    }
    
    return view('dashboards.client.contracts.create', [
        'employeeCount' => $employeeCount,
        'defaultFormula' => $defaultFormula,
        'company' => $company
    ]);
}

    /**
     * Enregistre un nouveau contrat
     */
    /**
 * Enregistre un nouveau contrat
 */
    public function store(Request $request)
    {
        // Vérification de l'authentification
        if (session('user_type') !== 'societe') {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
        }
        
        $validated = $request->validate([
            'services' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Direct Debit,Invoice',
            'formule_abonnement' => 'required|in:Starter,Basic,Premium',
        ]);
        
        $companyId = session('user_id');
        
        // Création du contrat
        $contract = new Contract([
            'company_id' => $companyId,
            'services' => $request->services,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'formule_abonnement' => $request->formule_abonnement,
            'statut_contrat' => 'En cours de validation',
        ]);
        
        $contract->save();
        
        // Enregistrement de l'activité
        if (class_exists('App\Models\Activity')) {
            Activity::create([
                'company_id' => $companyId,
                'user_id' => session('user_id'),
                'title' => 'Nouveau contrat créé',
                'description' => 'Contrat pour les services ' . $request->services . ' créé avec la formule ' . $request->formule_abonnement,
                'type' => 'contract',
                'subject_type' => Contract::class,
                'subject_id' => $contract->id,
            ]);
        }
        
        // Redirection selon la méthode de paiement
        if ($request->payment_method == 'Direct Debit') {
            return redirect()->route('contracts.show', $contract->id)
                ->with('success', 'Contrat créé avec succès. Un conseiller va vous contacter pour finaliser le prélèvement.');
        }
        
        return redirect()->route('contracts.show', $contract->id)
            ->with('success', 'Contrat créé avec succès. Une facture vous sera envoyée prochainement.');
    }
    /**
     * Affiche les détails d'un contrat
     */
    public function show($id)
    {
        // Vérification de l'authentification
        if (session('user_type') !== 'societe') {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
        }
        
        $companyId = session('user_id');
        
        // Récupération du contrat
        $contract = Contract::findOrFail($id);
        
        // Vérification que le contrat appartient bien à la société
        if ($contract->company_id != $companyId) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à ce contrat.');
        }
        
        // Calcul de l'état du contrat
        $today = Carbon::today()->toDateString();
        $contract->is_active = ($contract->start_date <= $today && $contract->end_date >= $today);
        
        return view('dashboards.client.contracts.show', compact('contract'));
    }

    /**
     * Affiche le formulaire d'édition d'un contrat
     */
    public function edit($id)
    {
        // Vérification de l'authentification
        if (session('user_type') !== 'societe') {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
        }
        
        $companyId = session('user_id');
        
        // Récupération du contrat
        $contract = Contract::findOrFail($id);
        
        // Vérification que le contrat appartient bien à la société
        if ($contract->company_id != $companyId) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à ce contrat.');
        }
        
        // Les services disponibles pour le formulaire d'édition
        $services = Service::where('is_active', true)->get();
        
        return view('dashboards.client.contracts.edit', compact('contract', 'services'));
    }

    /**
     * Met à jour un contrat existant
     */
    public function update(Request $request, $id)
    {
        // Vérification de l'authentification
        if (session('user_type') !== 'societe') {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
        }
        
        $companyId = session('user_id');
        
        // Récupération du contrat
        $contract = Contract::findOrFail($id);
        
        // Vérification que le contrat appartient bien à la société
        if ($contract->company_id != $companyId) {
            abort(403, 'Vous n\'êtes pas autorisé à modifier ce contrat.');
        }
        
        // Validation des données
        $validated = $request->validate([
            'services' => 'required|string',
            'end_date' => 'required|date|after:start_date',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Direct Debit,Invoice',
        ]);
        
        // Mise à jour du contrat (on ne modifie pas la date de début)
        $contract->services = $request->services;
        $contract->end_date = $request->end_date;
        $contract->amount = $request->amount;
        $contract->payment_method = $request->payment_method;
        $contract->save();
        
        // Enregistrement de l'activité
        if (class_exists('App\Models\Activity')) {
            Activity::create([
                'company_id' => $companyId,
                'user_id' => session('user_id'),
                'title' => 'Contrat modifié',
                'description' => 'Contrat #' . $contract->id . ' a été modifié',
                'type' => 'contract',
                'subject_type' => Contract::class,
                'subject_id' => $contract->id,
            ]);
        }
        
        return redirect()->route('contracts.show', $contract->id)
            ->with('success', 'Contrat mis à jour avec succès.');
    }

    /**
     * Supprime un contrat
     */
    public function destroy($id)
    {
        // Vérification de l'authentification
        if (session('user_type') !== 'societe') {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
        }
        
        $companyId = session('user_id');
        
        // Récupération du contrat
        $contract = Contract::findOrFail($id);
        
        // Vérification que le contrat appartient bien à la société
        if ($contract->company_id != $companyId) {
            abort(403, 'Vous n\'êtes pas autorisé à supprimer ce contrat.');
        }
        
        // Vérification si le contrat est actif
        $today = Carbon::today()->toDateString();
        $isActive = ($contract->start_date <= $today && $contract->end_date >= $today);
        
        if ($isActive) {
            // Pour un contrat actif, on ne le supprime pas mais on demande une résiliation
            // On pourrait ajouter un champ de résiliation dans la table contrat si nécessaire
            
            // Enregistrement de l'activité de demande de résiliation
            if (class_exists('App\Models\Activity')) {
                Activity::create([
                    'company_id' => $companyId,
                    'user_id' => session('user_id'),
                    'title' => 'Demande de résiliation de contrat',
                    'description' => 'Demande de résiliation pour le contrat #' . $contract->id,
                    'type' => 'contract',
                    'subject_type' => Contract::class,
                    'subject_id' => $contract->id,
                ]);
            }
            
            return redirect()->route('contracts.index')
                ->with('info', 'Votre demande de résiliation a été enregistrée. Un conseiller va vous contacter.');
        }
        
        // Pour un contrat non actif ou terminé, on peut le supprimer
        $contractId = $contract->id;
        $contract->delete();
        
        // Enregistrement de l'activité
        if (class_exists('App\Models\Activity')) {
            Activity::create([
                'company_id' => $companyId,
                'user_id' => session('user_id'),
                'title' => 'Contrat supprimé',
                'description' => 'Contrat #' . $contractId . ' a été supprimé',
                'type' => 'contract',
                'subject_type' => 'App\Models\Contract',
                'subject_id' => null,
            ]);
        }
        
        return redirect()->route('contracts.index')
            ->with('success', 'Contrat supprimé avec succès.');
    }
}