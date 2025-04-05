<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    protected $apiBaseUrl;

    public function __construct()
    {
        // Définir l'URL de base de l'API (à ajuster selon votre configuration)
        $this->apiBaseUrl = env('API_BASE_URL', 'http://localhost:8000/api');
    }

    // Affichage du formulaire de connexion
    public function loginForm()
    {
        return view('auth.login');
    }

    // Traitement de la connexion
    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'user_type' => 'required|in:admin,societe,employe,prestataire',
                'company_name' => 'required_if:user_type,societe,employe'
            ]);

            // Appel à l'API d'authentification
            $response = Http::post($this->apiBaseUrl . '/auth/login', [
                'email' => $validatedData['email'],
                'password' => $validatedData['password'],
                'user_type' => $validatedData['user_type'],
                'company_name' => $validatedData['company_name'] ?? null
            ]);

            $result = $response->json();

            if ($response->successful() && $result['success']) {
                // Stocker les informations utilisateur en session
                $user = $result['user'];
                session([
                    'user_id' => $user['id'],
                    'user_email' => $user['email'],
                    'user_name' => $user['name'],
                    'user_type' => $user['type'],
                    'company_id' => $user['company_id'] ?? null
                ]);

                \Log::info('Connexion réussie via API', [
                    'user_id' => $user['id'],
                    'user_type' => $user['type']
                ]);

                // Redirection selon le type d'utilisateur
                switch ($user['type']) {
                    case 'admin':
                        return redirect()->route('dashboard.admin');
                    case 'societe':
                        return redirect()->route('dashboard.client');
                    case 'employe':
                        return redirect()->route('dashboard.employee');
                    case 'prestataire':
                        return redirect()->route('dashboard.provider');
                    default:
                        return redirect()->route('home');
                }
            }

            \Log::warning('Échec de connexion via API', [
                'email' => $validatedData['email'],
                'response' => $result
            ]);

            return back()->withErrors(['email' => 'Identifiants invalides'])->withInput();

        } catch (\Exception $e) {
            \Log::error('Erreur de connexion', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['email' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    // Déconnexion
    public function logout(Request $request)
    {
        // Appel à l'API de déconnexion (si nécessaire)
        // Http::post($this->apiBaseUrl . '/auth/logout');

        $request->session()->flush();
        return redirect()->route('home');
    }

    // Affichage du formulaire d'inscription
    public function registerForm()
    {
        return view('auth.register');
    }

    // Traitement de l'inscription
    public function register(Request $request)
    {
        try {
            \Log::info('Données brutes reçues', [
                'all_data' => $request->all()
            ]);

            // Validation des données
            $validatedData = $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6',
                'user_type' => 'required|in:societe,employe,prestataire',

                // Validation société
                'company_name' => 'required_if:user_type,societe',
                'address' => 'required_if:user_type,societe',
                'code_postal' => 'required_if:user_type,societe',
                'ville' => 'required_if:user_type,societe',
                'phone' => 'required_if:user_type,societe',
                'siret' => 'nullable|digits:14',

                // Validation employé
                'first_name' => 'required_if:user_type,employe,prestataire',
                'last_name' => 'required_if:user_type,employe,prestataire',
                'position' => 'required_if:user_type,employe',
                'departement' => 'nullable',
                'telephone' => 'nullable',

                // Validation prestataire
                'name' => 'required_if:user_type,prestataire',
                'prenom' => 'required_if:user_type,prestataire',
                'specialite' => 'required_if:user_type,prestataire',
                'bio' => 'nullable',
                'tarif_horaire' => 'nullable|numeric|min:0'
            ]);

            // Appel à l'API d'inscription
            $response = Http::post($this->apiBaseUrl . '/auth/register', $validatedData);
            $result = $response->json();

            if ($response->successful() && $result['success']) {
                // Stocker les informations utilisateur en session
                $user = $result['user'];
                session([
                    'user_id' => $user['id'],
                    'user_email' => $user['email'],
                    'user_name' => $user['name'],
                    'user_type' => $user['type'],
                    'company_id' => $user['company_id'] ?? null
                ]);

                \Log::info('Inscription réussie via API, session créée', [
                    'user_id' => $user['id'],
                    'user_type' => $user['type']
                ]);

                // Redirection selon le type d'utilisateur
                switch ($user['type']) {
                    case 'societe':
                        return redirect()->route('dashboard.client');
                    case 'employe':
                        return redirect()->route('dashboard.employee');
                    case 'prestataire':
                        return redirect()->route('dashboard.provider');
                    default:
                        return redirect()->route('home');
                }
            }

            \Log::error('Échec de l\'inscription via API', [
                'email' => $validatedData['email'],
                'response' => $result
            ]);

            return back()->withErrors(['error' => $result['message'] ?? 'Échec de l\'inscription'])->withInput();

        } catch (\Exception $e) {
            \Log::error('Erreur de traitement de l\'inscription', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }
}
