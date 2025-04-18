<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\EmployeeController as APIEmployeeController;
use App\Http\Controllers\API\CompanyController as APICompanyController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use stdClass;

class ClientEmployeController extends Controller
{
    protected $apiEmployeeController;
    protected $apiCompanyController;

    public function __construct()
    {
        $this->apiEmployeeController = new APIEmployeeController();
        $this->apiCompanyController = new APICompanyController();
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
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return redirect()->route('dashboard.client')
                ->with('error', 'Vous devez être associé à une société pour accéder à cette page.');
        }

        try {
            // Appel au contrôleur API pour récupérer les employés de l'entreprise
            $response = $this->apiEmployeeController->getByCompany($company->id);
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

            return view('dashboards.client.employees.index', compact('employees'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des employés: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la récupération des employés');
        }
    }

    public function create()
    {
        return view('dashboards.client.employees.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $company = $user->company;

        if (!$company) {
            return redirect()->route('dashboard.client')
                ->with('error', 'Vous devez être associé à une société pour ajouter un employé.');
        }

        try {
            // Validation côté web
            $request->validate([
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'email' => 'required|email|unique:employee,email',
                'telephone' => 'nullable|string|max:20',
                'position' => 'required|string|max:100',
                'departement' => 'nullable|string|max:100',
                'password' => 'required|string|min:8',
                'preferences_langue' => 'nullable|string|max:10',
                'id_carte_nfc' => 'nullable|string|max:50',
            ]);

            // Ajout de l'ID de l'entreprise
            $request->merge(['company_id' => $company->id]);

            // Appel au contrôleur API pour créer l'employé
            $response = $this->apiEmployeeController->store($request);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 201) {
                Log::error('Erreur lors de la création de l\'employé', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'request' => $request->all()
                ]);

                $errors = $data['errors'] ?? ['Une erreur est survenue lors de la création de l\'employé'];
                return back()->withErrors($errors)->withInput();
            }

            return redirect()->route('client.employees.index')
                ->with('success', 'Employé ajouté avec succès.');
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

            // Vérification que l'employé appartient bien à l'entreprise de l'utilisateur
            $user = Auth::user();
            if ($employee->company_id !== $user->company_id) {
                abort(403, 'Vous n\'êtes pas autorisé à accéder à cet employé.');
            }

            return view('dashboards.client.employees.show', compact('employee'));
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'affichage d\'un employé: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage de l\'employé');
        }
    }

    public function edit($id)
    {
        try {
            // Appel au contrôleur API pour récupérer l'employé
            $response = $this->apiEmployeeController->show($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération de l\'employé pour modification', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'id' => $id
                ]);
                return back()->with('error', 'Employé non trouvé');
            }

            // Convertir le tableau associatif en objet
            $employee = $this->arrayToObject($data['data'] ?? []);

            // Vérification que l'employé appartient bien à l'entreprise de l'utilisateur
            $user = Auth::user();
            if ($employee->company_id !== $user->company_id) {
                abort(403, 'Vous n\'êtes pas autorisé à modifier cet employé.');
            }

            return view('dashboards.client.employees.edit', compact('employee'));
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
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
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

            return redirect()->route('client.employees.show', $id)
                ->with('success', 'Employé mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la mise à jour d\'un employé: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour de l\'employé')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            // Vérification préalable que l'employé appartient à l'entreprise
            $user = Auth::user();
            $response = $this->apiEmployeeController->show($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return back()->with('error', 'Employé non trouvé');
            }

            $employee = $this->arrayToObject($data['data']);
            if ($employee->company_id !== $user->company_id) {
                abort(403, 'Vous n\'êtes pas autorisé à supprimer cet employé.');
            }

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

            return redirect()->route('client.employees.index')
                ->with('success', 'Employé supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la suppression d\'un employé: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la suppression de l\'employé');
        }
    }
}
