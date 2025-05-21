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
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        \Log::info('Tentative de connexion API', $request->all());

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'user_type' => 'required|in:admin,societe,employe,prestataire',
            'company_name' => 'required_if:user_type,societe,employe'
        ]);

        if ($validator->fails()) {
            \Log::error('Validation échouée', $validator->errors()->toArray());
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $credentials = $request->only('email', 'password');
        $userType = $request->input('user_type');
        $companyName = $request->input('company_name');

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
            \Log::info('Recherche d\'un employé', [
                'email' => $credentials['email'],
                'company' => $companyName
            ]);

            $user = Employee::with('company')
                        ->whereHas('company', function($query) use ($companyName) {
                            $query->where('name', $companyName);
                        })
                        ->where('email', $credentials['email'])
                        ->first();

            if ($user) {
                \Log::info('Employé trouvé', ['id' => $user->id]);

                $passwordCorrect = Hash::check($credentials['password'], $user->password);
                \Log::info('Mot de passe correct: ' . ($passwordCorrect ? 'oui' : 'non'));

                if ($passwordCorrect) {
                    $userData = [
                        'id' => $user->id,
                        'email' => $user->email,
                        'name' => $user->first_name . ' ' . $user->last_name,
                        'type' => 'employe',
                        'company_id' => $user->company_id
                    ];
                }
            } else {
                \Log::error('Employé non trouvé');
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
            $token = 'bc_' . Str::random(60);

            \Log::info('Connexion réussie', ['user_id' => $userData['id'], 'type' => $userData['type']]);

            return response()->json([
                'success' => true,
                'message' => 'Connexion réussie',
                'token' => $token,
                'user' => $userData
            ]);
        }

        \Log::error('Échec de la connexion - identifiants invalides');

        return response()->json([
            'success' => false,
            'message' => 'Identifiants invalides'
        ], 401);
    }

    public function register(Request $request)
    {
        $userType = $request->input('user_type');

        $commonRules = [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'user_type' => 'required|in:societe,prestataire',
        ];

        $typeRules = [
            'societe' => [
                'company_name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'code_postal' => 'required|string|max:10',
                'ville' => 'required|string|max:100',
                'telephone' => 'required|string|max:20',
                'siret' => 'nullable|string|max:14',
            ],
            'prestataire' => [
                'name' => 'required|string|max:100',
                'prenom' => 'required|string|max:100',
                'specialite' => 'required|string',
                'telephone' => 'required|string|max:20',
                'bio' => 'nullable|string',
                'tarif_horaire' => 'nullable|numeric|min:0',
            ],
        ];

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
                        'phone' => $request->telephone,
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

    public function registerPending(Request $request)
    {
        return $this->register($request);
    }

    public function logout(Request $request)
    {
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