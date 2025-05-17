<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\EmployeeController as APIEmployeeController;
use App\Http\Controllers\API\CompanyController as APICompanyController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use stdClass;

class ClientEmployeeController extends Controller
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
        // Récupérer l'ID de l'entreprise depuis la session
        $companyId = session('user_id');

        Log::info('Tentative d\'accès à la liste des collaborateurs', [
            'company_id' => $companyId,
            'session_data' => session()->all()
        ]);

        if (!$companyId) {
            Log::warning('Tentative d\'accès sans ID d\'entreprise');
            return redirect()->route('login')
                ->with('error', 'Vous n\'êtes pas connecté ou votre session a expiré.');
        }

        try {
            // Appel au contrôleur API pour récupérer les employés de l'entreprise
            Log::info('Appel API getByCompany', ['company_id' => $companyId]);

            $response = $this->apiEmployeeController->getByCompany($companyId);

            // Log de la réponse brute
            Log::info('Réponse API brute', [
                'status_code' => $response->getStatusCode(),
                'content' => $response->getContent()
            ]);

            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Échec de la récupération des employés', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', 'Erreur lors de la récupération des employés: ' . ($data['message'] ?? 'Erreur inconnue'));
            }

            // Vérifier si 'data' existe dans la réponse
            if (!isset($data['data'])) {
                Log::error('Format de réponse API inattendu', ['response' => $data]);
                return back()->with('error', 'Format de réponse inattendu de l\'API');
            }

            // Convertir le tableau associatif en tableau d'objets
            $employees = $this->arrayToObjects($data['data'] ?? []);

            Log::info('Employés récupérés avec succès', ['count' => count($employees)]);

            return view('dashboards.client.employees.index', compact('employees'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des employés', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Une erreur est survenue lors de la récupération des employés: ' . $e->getMessage());
        }
    }

    public function create()
    {
        return view('dashboards.client.employees.create');
    }

    public function store(Request $request)
    {
        // Récupérer l'ID de l'entreprise directement depuis la session
        $companyId = session('user_id');

        if (!$companyId) {
            return redirect()->route('login')
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
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

            // Ajout de l'ID de l'entreprise directement à partir de la session
            $request->merge(['company_id' => $companyId]);

            // Ajouter des logs pour déboguer
            Log::info('Tentative d\'ajout d\'un employé', [
                'company_id' => $companyId,
                'employee_data' => $request->except('password')
            ]);

            // Appel au contrôleur API pour créer l'employé
            $response = $this->apiEmployeeController->store($request);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 201) {
                Log::error('Erreur lors de la création de l\'employé', [
                    'status' => $response->getStatusCode(),
                    'response' => $data,
                    'request' => $request->except('password')
                ]);

                $errors = $data['errors'] ?? ['Une erreur est survenue lors de la création de l\'employé'];
                return back()->withErrors($errors)->withInput();
            }

            return redirect()->route('employees.index')
                ->with('success', 'Collaborateur ajouté avec succès.');
        } catch (\Exception $e) {
            Log::error('Exception lors de la création d\'un employé', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Une erreur est survenue lors de la création du collaborateur: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Affiche le formulaire d'import CSV
     */
    public function showImportForm()
    {
        return view('dashboards.client.employees.import-form');
    }

    /**
     * Traite l'import CSV d'employés
     */
    public function importCsv(Request $request)
    {
        // Récupérer l'ID de l'entreprise directement depuis la session
        $companyId = session('user_id');

        Log::info('Début de la méthode importCsv', [
            'company_id' => $companyId,
            'has_file' => $request->hasFile('csv_file')
        ]);

        if (!$companyId) {
            Log::error('Session expirée - Aucun ID d\'entreprise trouvé');
            return redirect()->route('login')
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        }

        try {
            // Validation du fichier
            Log::info('Validation du fichier');
            $validator = Validator::make($request->all(), [
                'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            ]);

            if ($validator->fails()) {
                Log::error('Validation du fichier échouée', ['errors' => $validator->errors()->all()]);
                return back()->withErrors($validator)->withInput();
            }

            // Récupérer le contenu du fichier
            $file = $request->file('csv_file');
            Log::info('Fichier reçu', [
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize()
            ]);

            $path = $file->getRealPath();
            $csvData = array_map(function($line) {
                return str_getcsv($line, ';');
            }, file($path));

            Log::info('Fichier CSV analysé', [
                'line_count' => count($csvData),
                'sample' => array_slice($csvData, 0, 3)
            ]);


            // Vérifier que le fichier n'est pas vide
            if (count($csvData) <= 1) { // On considère que la première ligne est l'en-tête
                Log::warning('Fichier CSV vide ou ne contenant que l\'en-tête');
                return back()->with('error', 'Le fichier CSV est vide ou ne contient que l\'en-tête.');
            }

            // Récupérer les en-têtes (première ligne)
            $headers = array_map('trim', array_map('strtolower', $csvData[0]));
            Log::info('En-têtes extraits', ['headers' => $headers]);

            // Vérifier que les en-têtes requis sont présents
            $requiredHeaders = ['first_name', 'last_name', 'email', 'position'];
            $missingHeaders = array_diff($requiredHeaders, $headers);

            if (!empty($missingHeaders)) {
                Log::error('En-têtes requis manquants', ['missing' => $missingHeaders]);
                return back()->with('error', 'Le fichier CSV ne contient pas tous les champs requis : ' . implode(', ', $missingHeaders));
            }

            // Initialiser les compteurs
            $importedCount = 0;
            $errorCount = 0;
            $errors = [];

            // Parcourir les lignes du CSV (sauf l'en-tête)
            for ($i = 1; $i < count($csvData); $i++) {
                $row = $csvData[$i];
                if (empty(array_filter($row))) {
                    Log::info('Ligne vide ignorée', ['line' => $i + 1]);
                    continue; // Ignorer les lignes vides
                }

                $rowData = [];

                // Créer un tableau associatif avec les données de la ligne
                foreach ($headers as $index => $header) {
                    if (isset($row[$index])) {
                        $rowData[$header] = trim($row[$index]);
                    }
                }

                Log::info('Traitement de la ligne ' . ($i + 1), [
                    'row_data' => array_merge($rowData, ['password' => '[MASKED]'])
                ]);

                // Ajouter l'ID de l'entreprise
                $rowData['company_id'] = $companyId;

                // Générer un mot de passe temporaire si non fourni
                if (!isset($rowData['password']) || empty($rowData['password'])) {
                    $rowData['password'] = $this->generatePassword();
                    $rowData['password_confirmation'] = $rowData['password']; // Pour la validation
                    Log::info('Mot de passe généré pour l\'employé', ['email' => $rowData['email'] ?? 'inconnu']);
                } else {
                    // S'assurer que password_confirmation est défini pour la validation
                    $rowData['password_confirmation'] = $rowData['password'];
                }

                // Définir les préférences de langue par défaut si non fournies
                if (!isset($rowData['preferences_langue']) || empty($rowData['preferences_langue'])) {
                    $rowData['preferences_langue'] = 'fr';
                }

                // Valider les données
                $validator = Validator::make($rowData, [
                    'company_id' => 'required|exists:company,id',
                    'first_name' => 'required|string|max:50',
                    'last_name' => 'required|string|max:50',
                    'email' => 'required|email|unique:employee,email',
                    'telephone' => 'nullable|string|max:20',
                    'position' => 'required|string|max:100',
                    'departement' => 'nullable|string|max:100',
                    'password' => 'required|string|min:8',
                    'preferences_langue' => 'nullable|string|in:fr,en,es,de',
                    'id_carte_nfc' => 'nullable|string|max:50|unique:employee,id_carte_nfc',
                ]);

                if ($validator->fails()) {
                    $errorCount++;
                    $errorMessages = $validator->errors()->all();
                    $errors[] = "Ligne " . ($i + 1) . ": " . implode(', ', $errorMessages);
                    Log::warning('Validation échouée pour la ligne ' . ($i + 1), [
                        'errors' => $errorMessages,
                        'email' => $rowData['email'] ?? 'inconnu'
                    ]);
                    continue;
                }

                try {
                    // Hashage du mot de passe
                    $rowData['password'] = Hash::make($rowData['password']);

                    // Ajouter la date de création du compte
                    $rowData['date_creation_compte'] = now();

                    // Créer l'employé directement via le modèle
                    $employee = \App\Models\Employee::create($rowData);
                    $importedCount++;
                    Log::info('Employé créé avec succès', ['id' => $employee->id, 'email' => $employee->email]);
                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Ligne " . ($i + 1) . ": " . $e->getMessage();
                    Log::error('Erreur lors de la création de l\'employé à la ligne ' . ($i + 1), [
                        'message' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'email' => $rowData['email'] ?? 'inconnu'
                    ]);
                }
            }

            // Préparer le message de retour
            $message = "Import terminé. $importedCount employés importés avec succès.";
            if ($errorCount > 0) {
                $message .= " $errorCount erreurs rencontrées.";
                Log::warning('Import terminé avec des erreurs', [
                    'imported' => $importedCount,
                    'errors' => $errorCount,
                    'error_details' => $errors
                ]);
                return back()->with('warning', $message)->with('import_errors', $errors);
            }

            Log::info('Import terminé avec succès', ['imported' => $importedCount]);
            return redirect()->route('employees.index')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'import CSV des employés', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Une erreur est survenue lors de l\'import CSV : ' . $e->getMessage());
        }
    }

    private function generatePassword($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $password .= $characters[$index];
        }

        return $password;
    }


    public function downloadCsvTemplate()
    {
        $headers = [
            'first_name', 'last_name', 'email', 'telephone', 'position',
            'departement', 'password', 'preferences_langue', 'id_carte_nfc'
        ];

        $data = [
            $headers,
            ['Jean', 'Dupont', 'jean.dupont@example.com', '0601020304', 'Développeur', 'IT', 'Mot2passe!', 'fr', ''],
            ['Marie', 'Martin', 'marie.martin@example.com', '0602030405', 'Designer', 'Marketing', 'Mot2passe!', 'fr', ''],
        ];

        $callback = function() use($data) {
            $file = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template_employes.csv"',
        ]);
    }

    public function show($id)
    {
        try {
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
