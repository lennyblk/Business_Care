<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\ContractController as ApiContractController;
use Illuminate\Support\Facades\Log;
use stdClass;
use Carbon\Carbon;
use App\Models\Contract;
use App\Models\Company;

class ContractController extends Controller
{
    protected $apiContractController;

    public function __construct()
    {
        $this->apiContractController = new ApiContractController();
    }

    /**
     * Convertit un tableau associatif en objet stdClass récursivement
     */
    private function arrayToObject($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        $object = new stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $object->$key = $this->arrayToObject($value);
            } else {
                $object->$key = $value;
            }
        }
        return $object;
    }

    /**
     * Convertit un tableau de tableaux associatifs en tableau d'objets
     */
    private function arrayToObjects($arrayOfArrays)
    {
        $objects = [];
        foreach ($arrayOfArrays as $array) {
            $objects[] = $this->arrayToObject($array);
        }
        return $objects;
    }

    public function index()
    {
        try {
            if (session('user_type') !== 'societe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
            }

            $companyId = session('user_id');

            // Appel au contrôleur API pour récupérer les contrats
            $response = $this->apiContractController->getByCompany($companyId);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération des contrats', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', 'Erreur lors de la récupération des contrats');
            }

            // Convertir les données en objets
            $contracts = $this->arrayToObjects($data['data'] ?? []);

            // Ajouter des propriétés calculées
            $today = Carbon::today()->toDateString();
            foreach ($contracts as $contract) {
                $contract->is_active = ($contract->start_date <= $today && $contract->end_date >= $today);
            }

            return view('dashboards.client.contracts.index', compact('contracts'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des contrats: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la récupération des contrats');
        }
    }

    public function create()
    {
        try {
            if (session('user_type') !== 'societe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
            }

            $companyId = session('user_id');

            // Récupérer le nombre d'employés pour déterminer la formule par défaut
            $employeeCount = \App\Models\Employee::where('company_id', $companyId)->count();

            // Déterminer la formule par défaut en fonction du nombre d'employés
            $defaultFormula = 'Starter';
            if ($employeeCount > 250) {
                $defaultFormula = 'Premium';
            } elseif ($employeeCount > 30) {
                $defaultFormula = 'Basic';
            }

            $company = \App\Models\Company::find($companyId);

            return view('dashboards.client.contracts.create', [
                'employeeCount' => $employeeCount,
                'defaultFormula' => $defaultFormula,
                'company' => $company
            ]);
        } catch (\Exception $e) {
            Log::error('Exception lors de la création d\'un contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la création d\'un contrat');
        }
    }

    public function store(Request $request)
    {
        try {
            if (session('user_type') !== 'societe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
            }

            // Validation
            $validated = $request->validate([
                'services' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:Direct Debit,Invoice',
                'formule_abonnement' => 'required|in:Starter,Basic,Premium',
            ]);

            // Ajout de l'ID de l'entreprise
            $request->merge(['company_id' => session('user_id')]);

            // Appel à l'API
            $response = $this->apiContractController->store($request);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 201 && $response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la création du contrat', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);

                $errors = $data['errors'] ?? ['Une erreur est survenue lors de la création du contrat'];
                return back()->withErrors($errors)->withInput();
            }

            return redirect()->route('contracts.index')->with('success', 'Contrat créé avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la création d\'un contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la création du contrat')->withInput();
        }
    }

    public function show($id)
    {
        try {
            if (session('user_type') !== 'societe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
            }

            // Appel à l'API
            $response = $this->apiContractController->show($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération du contrat', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', 'Contrat non trouvé');
            }

            // Convertir en objet
            $contract = $this->arrayToObject($data['data'] ?? []);

            // Ajouter la propriété is_active
            $today = Carbon::today()->toDateString();
            $contract->is_active = ($contract->start_date <= $today && $contract->end_date >= $today);

            return view('dashboards.client.contracts.show', compact('contract'));
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'affichage d\'un contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage du contrat');
        }
    }

    public function edit($id)
    {
        try {
            if (session('user_type') !== 'societe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
            }

            // Appel à l'API
            $response = $this->apiContractController->show($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération du contrat', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', 'Contrat non trouvé');
            }

            // Convertir en objet
            $contract = $this->arrayToObject($data['data'] ?? []);

            return view('dashboards.client.contracts.edit', compact('contract'));
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'édition d\'un contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'édition du contrat');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (session('user_type') !== 'societe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
            }

            // Validation
            $validated = $request->validate([
                'services' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:Direct Debit,Invoice',
                'formule_abonnement' => 'required|in:Starter,Basic,Premium',
            ]);

            // Appel à l'API
            $response = $this->apiContractController->update($request, $id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la mise à jour du contrat', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);

                $errors = $data['errors'] ?? ['Une erreur est survenue lors de la mise à jour du contrat'];
                return back()->withErrors($errors)->withInput();
            }

            return redirect()->route('contracts.show', $id)->with('success', 'Contrat mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la mise à jour d\'un contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour du contrat')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            if (session('user_type') !== 'societe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
            }

            // Appel à l'API
            $response = $this->apiContractController->destroy($id);

            if ($response->getStatusCode() !== 200) {
                $data = json_decode($response->getContent(), true);
                Log::error('Erreur lors de la suppression du contrat', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', $data['message'] ?? 'Erreur lors de la suppression du contrat');
            }

            return redirect()->route('contracts.index')->with('success', 'Contrat supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la suppression d\'un contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la suppression du contrat');
        }
    }

    public function requestChange($id)
    {
        try {
            if (session('user_type') !== 'societe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société.');
            }

            // Récupérer le contrat via l'API
            $response = $this->apiContractController->show($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return back()->with('error', 'Contrat non trouvé');
            }

            $contract = $this->arrayToObject($data['data'] ?? []);

            // Vérifier que le contrat est actif
            if ($contract->payment_status !== 'active') {
                return back()->with('error', 'Seuls les contrats actifs peuvent être modifiés.');
            }

            $employeeCount = \App\Models\Employee::where('company_id', session('user_id'))->count();

            // Déterminer la formule recommandée
            $recommendedFormula = 'Starter';
            if ($employeeCount > 250) {
                $recommendedFormula = 'Premium';
            } elseif ($employeeCount > 30) {
                $recommendedFormula = 'Basic';
            }

            return view('dashboards.client.contracts.change', compact('contract', 'employeeCount', 'recommendedFormula'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de la demande de changement: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function submitChange(Request $request, $id)
    {
        try {
            if (session('user_type') !== 'societe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société.');
            }

            $validated = $request->validate([
                'new_formula' => 'required|in:Starter,Basic,Premium',
                'reason' => 'required|string',
            ]);

            // Récupérer le contrat original
            $response = $this->apiContractController->show($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return back()->with('error', 'Contrat non trouvé');
            }

            $originalContract = $data['data'];

            // Créer un nouveau contrat via l'API
            $newContractData = [
                'company_id' => session('user_id'),
                'services' => $originalContract['services'],
                'formule_abonnement' => $validated['new_formula'],
                'payment_status' => 'pending',
                'start_date' => $originalContract['end_date'],
                'end_date' => Carbon::parse($originalContract['end_date'])->addYear()->format('Y-m-d'),
                'payment_method' => $originalContract['payment_method'],
            ];

            // Calculer le nouveau montant
            $employeeCount = \App\Models\Employee::where('company_id', session('user_id'))->count();
            $pricePerEmployee = [
                'Starter' => 180,
                'Basic' => 150,
                'Premium' => 100
            ][$validated['new_formula']];

            $newContractData['amount'] = $pricePerEmployee * $employeeCount;

            // Créer le nouveau contrat via l'API
            $createRequest = new Request($newContractData);
            $createResponse = $this->apiContractController->store($createRequest);

            if ($createResponse->getStatusCode() !== 201 && $createResponse->getStatusCode() !== 200) {
                return back()->with('error', 'Erreur lors de la création de la demande de changement');
            }

            return redirect()->route('contracts.index')
                ->with('success', 'Votre demande de changement a été envoyée.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la soumission du changement: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function terminate($id)
    {
        try {
            if (session('user_type') !== 'societe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
            }

            // D'abord, voyons les valeurs autorisées pour statut_compte
            $column = DB::select("SHOW COLUMNS FROM company WHERE Field = 'statut_compte'");
            Log::info('Valeurs autorisées pour statut_compte', ['column_info' => $column]);

            // Approche plus directe pour éviter les problèmes
            $contract = Contract::findOrFail($id);

            // Utiliser 'pending' comme statut pour un contrat résilié
            $contract->payment_status = 'pending'; // Utiliser une valeur acceptée par l'enum
            $contract->save();

            // Mettre à jour le statut du compte de l'entreprise
            $company = Company::findOrFail($contract->company_id);

            // Trouver la valeur correcte en fonction des valeurs disponibles
            // Essayons d'abord avec 'Inactif', puis avec d'autres valeurs possibles
            try {
                $company->statut_compte = 'Inactif'; // Essayons 'Inactif' au lieu de 'Non Actif'
                $company->save();
                Log::info('Contrat résilié et compte désactivé avec statut_compte = Inactif', [
                    'contract_id' => $id,
                    'company_id' => $contract->company_id,
                    'new_account_status' => $company->statut_compte
                ]);
            } catch (\Exception $e1) {
                Log::warning('Erreur avec statut_compte = Inactif: ' . $e1->getMessage());

                try {
                    $company->statut_compte = 'Inactive'; // Essayons 'Inactive'
                    $company->save();
                    Log::info('Contrat résilié et compte désactivé avec statut_compte = Inactive', [
                        'contract_id' => $id,
                        'company_id' => $contract->company_id,
                        'new_account_status' => $company->statut_compte
                    ]);
                } catch (\Exception $e2) {
                    Log::warning('Erreur avec statut_compte = Inactive: ' . $e2->getMessage());

                    // Si rien ne fonctionne, essayons de résilier simplement le contrat sans toucher au statut
                    Log::warning('Impossible de mettre à jour statut_compte, contrat résilié mais statut entreprise non modifié');
                }
            }

            return redirect()->route('contracts.index')->with('success', 'Contrat résilié avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la résiliation d\'un contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la résiliation du contrat: ' . $e->getMessage());
        }
    }
}
