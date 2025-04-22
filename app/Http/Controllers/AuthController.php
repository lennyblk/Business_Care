<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\AuthController as APIAuthController;
use App\Http\Controllers\API\PendingRegistrationController;

class AuthController extends Controller
{
    protected $apiAuthController;
    protected $pendingRegistrationController;

    public function __construct()
    {
        $this->apiAuthController = new APIAuthController();
        $this->pendingRegistrationController = new PendingRegistrationController();
    }

    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            // Déléguer l'authentification au contrôleur API
            $response = $this->apiAuthController->login($request);
            $responseData = json_decode($response->getContent(), true);

            if ($response->getStatusCode() === 200 && $responseData['success']) {
                // Récupérer les données de l'utilisateur
                $user = $responseData['user'];

                // Enregistrer les données de l'utilisateur en session
                session([
                    'user_id' => $user['id'],
                    'user_email' => $user['email'],
                    'user_name' => $user['name'],
                    'user_type' => $user['type'],
                    'company_id' => $user['company_id'] ?? null
                ]);

                \Log::info('Connexion réussie', [
                    'user_id' => $user['id'],
                    'user_type' => $user['type'],
                    'company_id' => $user['company_id'] ?? null
                ]);

                // Rediriger selon le type d'utilisateur
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

            // En cas d'échec
            return back()->withErrors(['email' => $responseData['message'] ?? 'Identifiants invalides'])->withInput();

        } catch (\Exception $e) {
            \Log::error('Erreur de connexion', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['email' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    public function logout(Request $request)
    {
        // Appeler le contrôleur API pour la déconnexion
        $this->apiAuthController->logout($request);

        // Vider la session locale
        $request->session()->flush();

        return redirect()->route('home');
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        try {
            \Log::info('Données d\'inscription reçues', [
                'all_data' => $request->all()
            ]);

            $response = $this->pendingRegistrationController->register($request);
            $responseData = json_decode($response->getContent(), true);

            if ($response->getStatusCode() === 201) {
                // Succès : redirection vers la page d'accueil avec message
                return redirect()->route('home')->with('success',
                    'Votre demande d\'inscription a été envoyée avec succès. ' .
                    'Un administrateur l\'examinera prochainement et vous recevrez ' .
                    'une notification par email lorsqu\'elle sera traitée.');
            }

            // Échec : retour au formulaire avec erreurs
            \Log::error('Échec de l\'inscription en attente', $responseData);
            return back()->withErrors(['error' => $responseData['message'] ?? 'Échec de l\'inscription'])
                         ->withInput();

        } catch (\Exception $e) {
            \Log::error('Erreur d\'inscription', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])
                        ->withInput();
        }
    }

    public function registerPending(Request $request)
    {
        // Rediriger vers la méthode register
        return $this->register($request);
    }
}
