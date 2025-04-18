<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\ContractController;
use Illuminate\Support\Facades\Log;
use stdClass;

class AdminCompanyController extends Controller
{
    protected $apiCompanyController;
    protected $apiEmployeeController;
    protected $apiContractController;

    public function __construct()
    {
        $this->apiCompanyController = new CompanyController();
        $this->apiEmployeeController = new EmployeeController();
        $this->apiContractController = new ContractController();
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
            // Appel au contrôleur API pour récupérer les entreprises
            $response = $this->apiCompanyController->index();
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération des entreprises', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', 'Erreur lors de la récupération des entreprises');
            }

            // Convertir le tableau associatif en tableau d'objets
            $companies = $this->arrayToObjects($data['data'] ?? []);

            return view('dashboards.gestion_admin.societe.index', compact('companies'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des entreprises: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la récupération des entreprises');
        }
    }

    public function create()
    {
        return view('dashboards.gestion_admin.societe.create');
    }

    public function store(Request $request)
    {
        try {
            // Validation côté web pour une meilleure expérience utilisateur
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

            // Remapper telephone -> phone si nécessaire (si l'API utilise 'telephone')
            if (!$request->has('telephone') && $request->has('phone')) {
                $request->merge(['telephone' => $request->phone]);
            }

            // Appel au contrôleur API pour créer l'entreprise
            $response = $this->apiCompanyController->store($request);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 201 && $response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la création de l\'entreprise', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'request' => $request->all()
                ]);

                $errors = $data['errors'] ?? ['Une erreur est survenue lors de la création de l\'entreprise'];
                return back()->withErrors($errors)->withInput();
            }

            return redirect()->route('admin.company')->with('success', 'Entreprise créée avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la création d\'une entreprise: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la création de l\'entreprise')->withInput();
        }
    }

    public function show($id)
    {
        try {
            // Appel au contrôleur API pour récupérer l'entreprise
            $companyResponse = $this->apiCompanyController->show($id);
            $companyData = json_decode($companyResponse->getContent(), true);

            if ($companyResponse->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération de l\'entreprise', [
                    'status' => $companyResponse->getStatusCode(),
                    'response' => $companyData,
                    'id' => $id
                ]);
                return back()->with('error', 'Entreprise non trouvée');
            }

            // Convertir le tableau associatif en objet
            $company = $this->arrayToObject($companyData['data'] ?? []);

            // Appel au contrôleur API pour récupérer les employés de cette entreprise
            $employeesResponse = $this->apiCompanyController->getEmployees($id);
            $employeesData = json_decode($employeesResponse->getContent(), true);

            if ($employeesResponse->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération des employés', [
                    'status' => $employeesResponse->getStatusCode(),
                    'response' => $employeesData,
                    'company_id' => $id
                ]);
                $employees = [];
            } else {
                // Convertir le tableau associatif en tableau d'objets
                $employees = $this->arrayToObjects($employeesData['data'] ?? []);
            }

            return view('dashboards.gestion_admin.societe.show', compact('company', 'employees'));
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'affichage d\'une entreprise: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage de l\'entreprise');
        }
    }

    public function edit($id)
    {
        try {
            // Appel au contrôleur API pour récupérer l'entreprise
            $response = $this->apiCompanyController->show($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération de l\'entreprise pour modification', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'id' => $id
                ]);
                return back()->with('error', 'Entreprise non trouvée');
            }

            // Convertir le tableau associatif en objet
            $company = $this->arrayToObject($data['data'] ?? []);

            return view('dashboards.gestion_admin.societe.edit', compact('company'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la modification d\'une entreprise: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la modification de l\'entreprise');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validation côté web
            $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'code_postal' => 'nullable|string|max:10',
                'ville' => 'nullable|string|max:100',
                'pays' => 'nullable|string|max:100',
                'telephone' => 'required|string|max:20',
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

            // Remapper telephone -> phone si nécessaire (si l'API utilise 'telephone')
            if (!$request->has('telephone') && $request->has('phone')) {
                $request->merge(['telephone' => $request->phone]);
            }

            // Appel au contrôleur API pour mettre à jour l'entreprise
            $response = $this->apiCompanyController->update($request, $id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la mise à jour de l\'entreprise', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'id' => $id,
                    'request' => $request->all()
                ]);

                $errors = $data['errors'] ?? ['Une erreur est survenue lors de la mise à jour de l\'entreprise'];
                return back()->withErrors($errors)->withInput();
            }

            return redirect()->route('admin.company')->with('success', 'Entreprise mise à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la mise à jour d\'une entreprise: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour de l\'entreprise')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            // Appel au contrôleur API pour supprimer l'entreprise
            $response = $this->apiCompanyController->destroy($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la suppression de l\'entreprise', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'id' => $id
                ]);
                return back()->with('error', $data['message'] ?? 'Erreur lors de la suppression de l\'entreprise');
            }

            return redirect()->route('admin.company')->with('success', 'Entreprise supprimée avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la suppression d\'une entreprise: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la suppression de l\'entreprise');
        }
    }

    public function contracts($id)
{
    try {
        // Appel au contrôleur API pour récupérer l'entreprise
        $companyResponse = $this->apiCompanyController->show($id);
        $companyData = json_decode($companyResponse->getContent(), true);

        if ($companyResponse->getStatusCode() !== 200) {
            Log::error('Erreur lors de la récupération de l\'entreprise', [
                'status' => $companyResponse->getStatusCode(),
                'response' => $companyData,
                'id' => $id
            ]);
            return back()->with('error', 'Entreprise non trouvée');
        }

        // Convertir le tableau associatif en objet
        $company = $this->arrayToObject($companyData['data'] ?? []);

        // Appel au contrôleur API pour récupérer les contrats de cette entreprise
        $contractsResponse = $this->apiCompanyController->getContracts($id);
        $contractsData = json_decode($contractsResponse->getContent(), true);

        if ($contractsResponse->getStatusCode() !== 200) {
            Log::error('Erreur lors de la récupération des contrats', [
                'status' => $contractsResponse->getStatusCode(),
                'response' => $contractsData,
                'company_id' => $id
            ]);
            $contrats = [];
        } else {
            // Convertir le tableau associatif en tableau d'objets
            $contrats = $this->arrayToObjects($contractsData['data'] ?? []);
        }

        return view('dashboards.gestion_admin.societe.contracts', compact('company', 'contrats'));
    } catch (\Exception $e) {
        Log::error('Exception lors de la récupération des contrats: ' . $e->getMessage());
        return back()->with('error', 'Une erreur est survenue lors de la récupération des contrats');
    }
}
}
