<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'user_type' => 'required|in:admin,societe,employe,prestataire',
            'company_name' => 'required_if:user_type,societe,employe'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $credentials = $request->only('email', 'password');
        $userType = $request->input('user_type');
        $companyName = $request->input('company_name');

        // Recherche de l'utilisateur en fonction de son type
        $user = null;
        $userData = null;

        if ($userType === 'admin') {
            $user = Admin::where('email', $credentials['email'])->first();

            if ($user && $credentials['password'] === $user->password) {
                $userData = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'type' => 'admin',
                ];
            }
        }
        elseif ($userType === 'societe') {
            $user = Company::where('email', $credentials['email'])
                          ->where('name', $companyName)
                          ->first();

            if ($user && Hash::check($credentials['password'], $user->password)) {
                $userData = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->name,
                    'type' => 'societe',
                ];
            }
        }
        elseif ($userType === 'employe') {
            $user = Employee::with('company')
                          ->whereHas('company', function($query) use ($companyName) {
                              $query->where('name', $companyName);
                          })
                          ->where('email', $credentials['email'])
                          ->first();

            if ($user && Hash::check($credentials['password'], $user->password)) {
                $userData = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'type' => 'employe',
                    'company_id' => $user->company_id
                ];
            }
        }
        elseif ($userType === 'prestataire') {
            $user = Provider::where('email', $credentials['email'])->first();

            if ($user && Hash::check($credentials['password'], $user->password)) {
                $userData = [
                    'id' => $user->id,
                    'email' => $user->email,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'type' => 'prestataire',
                ];
            }
        }

        if (isset($userData)) {
            return response()->json([
                'success' => true,
                'user' => $userData
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Identifiants invalides'
        ], 401);
    }

    public function register(Request $request)
    {
        // Adaptation pour la validation avec les champs du formulaire web
        $userType = $request->input('user_type');
        
        // Validation commune pour tous les types d'utilisateurs
        $commonRules = [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'user_type' => 'required|in:societe,employe,prestataire',
        ];

        // Règles par type d'utilisateur
        $typeRules = [
            'societe' => [
                'company_name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'code_postal' => 'required|string|max:10',
                'ville' => 'required|string|max:100',
                'telephone' => 'required|string|max:20', // Adaptation pour le formulaire web
                'siret' => 'nullable|string|max:14',
            ],
            'employe' => [
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'company_name' => 'required|string|exists:company,name',
                'position' => 'required|string|max:100',
                'departement' => 'nullable|string|max:100',
                'telephone' => 'nullable|string|max:20',
            ],
            'prestataire' => [
                'name' => 'required|string|max:100', // Pour le nom de famille
                'prenom' => 'required|string|max:100', // Pour le prénom
                'specialite' => 'required|string',
                'telephone' => 'required|string|max:20',
                'bio' => 'nullable|string',
                'tarif_horaire' => 'nullable|numeric|min:0',
            ],
        ];

        // on récupère les règles spécifiques au type d'utilisateur
        $validationRules = array_merge($commonRules, $typeRules[$userType] ?? []);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $hashedPassword = Hash::make($request->password);
            $userData = null;

            switch ($request->user_type) {
                case 'societe':
                    $company = Company::create([
                        'name' => $request->company_name,
                        'address' => $request->address,
                        'code_postal' => $request->code_postal,
                        'ville' => $request->ville,
                        'pays' => 'France',
                        'phone' => $request->telephone, // Adaptation pour le formulaire web
                        'email' => $request->email,
                        'siret' => $request->siret,
                        'password' => $hashedPassword,
                        'creation_date' => now(),
                        'formule_abonnement' => 'Starter',
                        'statut_compte' => 'Actif',
                        'date_debut_contrat' => now()
                    ]);

                    $userData = [
                        'id' => $company->id,
                        'email' => $company->email,
                        'name' => $company->name,
                        'type' => 'societe'
                    ];
                    break;

                case 'employe':
                    $company = Company::where('name', $request->company_name)->first();

                    if (!$company) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Entreprise non trouvée'
                        ], 404);
                    }

                    $employee = Employee::create([
                        'company_id' => $company->id,
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'telephone' => $request->telephone,
                        'position' => $request->position,
                        'departement' => $request->departement,
                        'date_creation_compte' => now(),
                        'password' => $hashedPassword,
                        'preferences_langue' => 'fr'
                    ]);

                    $userData = [
                        'id' => $employee->id,
                        'email' => $employee->email,
                        'name' => $employee->first_name . ' ' . $employee->last_name,
                        'type' => 'employe',
                        'company_id' => $employee->company_id
                    ];
                    break;

                case 'prestataire':
                    $provider = Provider::create([
                        'last_name' => $request->name,
                        'first_name' => $request->prenom,
                        'description' => $request->bio ?? 'Pas de description',
                        'domains' => $request->specialite,
                        'email' => $request->email,
                        'telephone' => $request->telephone,
                        'password' => $hashedPassword,
                        'statut_prestataire' => 'Candidat',
                        'tarif_horaire' => $request->tarif_horaire
                    ]);

                    $userData = [
                        'id' => $provider->id,
                        'email' => $provider->email,
                        'name' => $provider->first_name . ' ' . $provider->last_name,
                        'type' => 'prestataire'
                    ];
                    break;
            }

            if ($userData) {
                return response()->json([
                    'success' => true,
                    'user' => $userData,
                    'message' => 'Inscription réussie'
                ], 201);
            }

            return response()->json([
                'success' => false,
                'message' => 'Échec de l\'inscription'
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage(),
                'error_details' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Gère les demandes d'inscription en attente
     */
    public function registerPending(Request $request)
    {
        // On réutilise la même logique d'inscription mais avec un statut différent
        // Cette méthode est utilisée par le contrôleur web
        return $this->register($request);
    }

    public function logout(Request $request)
    {
        // Pour une authentification basée sur les sessions:
        if ($request->session()->has('user_id')) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Déconnexion réussie'
        ]);
    }
}