<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\EmployeeController as APIEmployeeController;
use App\Http\Controllers\API\CompanyController as APICompanyController;
use Illuminate\Support\Facades\Log;
use stdClass;

class AdminEmployeeController extends Controller
{
    protected $apiEmployeeController;
    protected $apiCompanyController;

    // Utilisez l'injection de dépendances plutôt que new
    public function __construct(APIEmployeeController $apiEmployeeController, APICompanyController $apiCompanyController)
    {
        $this->apiEmployeeController = $apiEmployeeController;
        $this->apiCompanyController = $apiCompanyController;
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
            // Appel au contrôleur API pour récupérer les employés
            $response = $this->apiEmployeeController->index();
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération des employés', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', 'Erreur lors de la récupération des employés');
            }

            // Convertir le tableau associatif en tableau d'objets
            $employees = $this->arrayToObjects($data['data'] ?? []);

            return view('dashboards.gestion_admin.salaries.index', compact('employees'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des employés: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la récupération des employés');
        }
    }

    public function create()
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

            return view('dashboards.gestion_admin.salaries.create', compact('companies'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la création d\'un employé: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la création d\'un employé');
        }
    }

    public function store(Request $request)
    {
        try {
            // Validation côté web pour une meilleure expérience utilisateur
            $request->validate([
                'company_id' => 'nullable|exists:company,id',
                'last_name' => 'required|string|max:50',
                'first_name' => 'required|string|max:50',
                'email' => 'required|email|unique:employee,email',
                'telephone' => 'nullable|string|max:20',
                'position' => 'required|string|max:100',
                'departement' => 'nullable|string|max:100',
                'password' => 'required|string|min:8',
                'preferences_langue' => 'nullable|string|max:10',
                'id_carte_nfc' => 'nullable|string|max:50',
            ]);

            // Appel au contrôleur API pour créer l'employé
            $response = $this->apiEmployeeController->store($request);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 201 && $response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la création de l\'employé', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'request' => $request->all()
                ]);

                $errors = $data['errors'] ?? ['Une erreur est survenue lors de la création de l\'employé'];
                return back()->withErrors($errors)->withInput();
            }

            return redirect()->route('admin.salaries.index')->with('success', 'Employé créé avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la création d\'un employé: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la création de l\'employé')->withInput();
        }
    }

    public function show($id)
    {
        try {
            // Appel au contrôleur API pour récupérer l'employé
            $response = $this->apiEmployeeController->show($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération de l\'employé', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'id' => $id
                ]);
                return back()->with('error', 'Employé non trouvé');
            }

            // Convertir le tableau associatif en objet
            $employee = $this->arrayToObject($data['data'] ?? []);

            return view('dashboards.gestion_admin.salaries.show', compact('employee'));
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'affichage d\'un employé: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage de l\'employé');
        }
    }

    public function edit($id)
    {
        try {
            // Appel au contrôleur API pour récupérer l'employé
            $employeeResponse = $this->apiEmployeeController->show($id);
            $employeeData = json_decode($employeeResponse->getContent(), true);

            if ($employeeResponse->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération de l\'employé pour modification', [
                    'status' => $employeeResponse->getStatusCode(),
                    'response' => $employeeData,
                    'id' => $id
                ]);
                return back()->with('error', 'Employé non trouvé');
            }

            // Convertir le tableau associatif en objet
            $employee = $this->arrayToObject($employeeData['data'] ?? []);

            // Appel au contrôleur API pour récupérer les entreprises
            $companiesResponse = $this->apiCompanyController->index();
            $companiesData = json_decode($companiesResponse->getContent(), true);

            if ($companiesResponse->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération des entreprises', [
                    'status' => $companiesResponse->getStatusCode(),
                    'response' => $companiesData
                ]);
                return back()->with('error', 'Erreur lors de la récupération des entreprises');
            }

            // Convertir le tableau associatif en tableau d'objets
            $companies = $this->arrayToObjects($companiesData['data'] ?? []);

            return view('dashboards.gestion_admin.salaries.edit', compact('employee', 'companies'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la modification d\'un employé: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la modification de l\'employé');
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Validation côté web
            $request->validate([
                'last_name' => 'required|string|max:50',
                'first_name' => 'required|string|max:50',
                'email' => 'required|email|unique:employee,email,' . $id,
                'telephone' => 'nullable|string|max:20',
                'position' => 'required|string|max:100',
                'departement' => 'nullable|string|max:100',
                'password' => 'nullable|string|min:8',
                'preferences_langue' => 'nullable|string|max:10',
                'id_carte_nfc' => 'nullable|string|max:50',
            ]);

            // Appel au contrôleur API pour mettre à jour l'employé
            $response = $this->apiEmployeeController->update($request, $id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la mise à jour de l\'employé', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'id' => $id,
                    'request' => $request->all()
                ]);

                $errors = $data['errors'] ?? ['Une erreur est survenue lors de la mise à jour de l\'employé'];
                return back()->withErrors($errors)->withInput();
            }

            return redirect()->route('admin.salaries.index')->with('success', 'Employé mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la mise à jour d\'un employé: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour de l\'employé')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            // Appel au contrôleur API pour supprimer l'employé
            $response = $this->apiEmployeeController->destroy($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la suppression de l\'employé', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'id' => $id
                ]);
                return back()->with('error', $data['message'] ?? 'Erreur lors de la suppression de l\'employé');
            }

            return redirect()->route('admin.salaries.index')->with('success', 'Employé supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la suppression d\'un employé: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la suppression de l\'employé');
        }
    }
}
