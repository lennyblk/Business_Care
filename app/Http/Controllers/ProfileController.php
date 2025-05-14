<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\API\ProfileController as ApiProfileController;

class ProfileController extends Controller
{
    protected $apiController;

    public function __construct()
    {
        $this->apiController = new ApiProfileController();
    }

    /**
     * Afficher la page de profil adaptée au type d'utilisateur
     */
    public function index()
    {
        try {
            $userType = session('user_type');
            $userId = session('user_id');

            Log::info('Tentative d\'accès au profil', [
                'user_type' => $userType,
                'user_id' => $userId
            ]);

            if (!$userType || !$userId) {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté');
            }

            $response = $this->apiController->getProfile($userId, $userType);
            $data = json_decode($response->getContent(), true);

            if (!$data['success']) {
                Log::error('Erreur API profil', $data);
                return back()->with('error', $data['message'] ?? 'Erreur lors de la récupération du profil');
            }

            $profile = (object)$data['data'];

            switch ($userType) {
                case 'employe':
                case 'employee':
                    return view('dashboards.employee.profile', compact('profile'));
                case 'prestataire':    
                case 'provider':
                    return view('dashboards.provider.profile', compact('profile'));
                case 'societe':
                    return view('dashboards.client.profile', compact('profile'));
                default:
                    return redirect()->route('login')
                        ->with('error', 'Type d\'utilisateur non reconnu');
            }

        } catch (\Exception $e) {
            Log::error('Erreur profil', [
                'message' => $e->getMessage(),
                'user_type' => $userType ?? 'unknown',
                'user_id' => $userId ?? 'unknown'
            ]);
            return back()->with('error', 'Une erreur est survenue lors de la récupération du profil');
        }
    }

    /**
     * Afficher le formulaire de modification du profil
     */
    public function edit()
    {
        $userType = session('user_type');
        $userId = session('user_id');

        // Récupérer les données du profil via l'API
        $response = $this->apiController->getProfile($userId, $userType);
        $result = json_decode($response->getContent());

        if (!$response->getStatusCode() === 200) {
            return back()->with('error', 'Erreur lors de la récupération du profil');
        }

        $profile = $result->data;

        // Rediriger vers la vue appropriée selon le type d'utilisateur
        switch($userType) {
            case 'employe':
            case 'employee':
                return view('dashboards.employee.profile-edit', compact('profile'));
            case 'prestataire':
            case 'provider':
                return view('dashboards.provider.profile-edit', compact('profile'));
            case 'societe':
                return view('dashboards.client.profile-edit', compact('profile')); // Changé de company à client
            default:
                return back()->with('error', 'Type d\'utilisateur non valide');
        }
    }

    /**
     * Mettre à jour le profil
     */
    public function update(Request $request)
    {
        try {
            // Vérifier que l'utilisateur est connecté
            if (!session('user_type') || !session('user_id')) {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté pour accéder à cette page.');
            }

            $userType = session('user_type');
            $userId = session('user_id');

            // Appel à l'API pour mettre à jour les informations du profil
            $response = $this->apiController->updateProfile($request, $userId, $userType);
            $data = json_decode($response->getContent(), true);

            if (!$data['success']) {
                return back()->with('error', $data['message'])->withErrors($data['errors'] ?? [])->withInput();
            }

            return redirect()->route('profile.index')->with('success', 'Profil mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du profil: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour du profil.')->withInput();
        }
    }

    /**
     * Afficher le formulaire de modification du mot de passe
     */
    public function editPassword()
    {
        try {
            // Vérifier que l'utilisateur est connecté
            if (!session('user_type') || !session('user_id')) {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté pour accéder à cette page.');
            }

            $userType = session('user_type');

            // Rediriger vers la vue adaptée au type d'utilisateur
            switch ($userType) {
                case 'societe':
                    return view('dashboards.client.profile-password');
                case 'employee':
                    return view('dashboards.employee.profile-password');
                case 'provider':
                    return view('dashboards.provider.profile-password');
                default:
                    return redirect()->route('login')
                        ->with('error', 'Type d\'utilisateur non reconnu.');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du formulaire de modification du mot de passe: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage du formulaire de modification du mot de passe.');
        }
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(Request $request)
    {
        try {
            $userId = session('user_id');
            $userType = session('user_type');

            // Conversion du type d'utilisateur pour l'API
            switch ($userType) {
                case 'prestataire':
                    $type = 'provider';
                    break;
                case 'societe':
                    $type = 'company';
                    break;
                case 'employe':
                    $type = 'employee';
                    break;
                default:
                    return back()->with('error', 'Type d\'utilisateur non reconnu');
            }

            $response = $this->apiController->updatePassword($request, $userId, $type);
            $result = json_decode($response->getContent());

            if ($response->getStatusCode() === 200) {
                return redirect()->route('profile.index')->with('success', 'Mot de passe mis à jour avec succès');
            }

            return back()->with('error', $result->message ?? 'Erreur lors de la mise à jour du mot de passe');
        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }
}
