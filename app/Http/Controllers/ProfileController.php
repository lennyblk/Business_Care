<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Afficher la page de profil adaptée au type d'utilisateur
     */
    public function index()
    {
        try {
            // Vérifier que l'utilisateur est connecté
            if (!session('user_type') || !session('user_id')) {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté pour accéder à cette page.');
            }

            $userType = session('user_type');
            $userId = session('user_id');

            // Appel à l'API pour récupérer les informations du profil
            $response = app('App\Http\Controllers\API\ProfileController')->getProfile($userId, $userType);
            $data = json_decode($response->getContent(), true);

            if (!$data['success']) {
                return back()->with('error', $data['message']);
            }

            $profile = $data['data'];

            // Rediriger vers la vue adaptée au type d'utilisateur
            switch ($userType) {
                case 'societe':
                    return view('dashboards.client.profile', compact('profile'));
                case 'employee':
                    return view('dashboards.employee.profile', compact('profile'));
                case 'provider':
                    return view('dashboards.provider.profile', compact('profile'));
                default:
                    return redirect()->route('login')
                        ->with('error', 'Type d\'utilisateur non reconnu.');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du profil: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage du profil.');
        }
    }

    /**
     * Afficher le formulaire de modification du profil
     */
    public function edit()
    {
        try {
            // Vérifier que l'utilisateur est connecté
            if (!session('user_type') || !session('user_id')) {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté pour accéder à cette page.');
            }

            $userType = session('user_type');
            $userId = session('user_id');

            // Appel à l'API pour récupérer les informations du profil
            $response = app('App\Http\Controllers\API\ProfileController')->getProfile($userId, $userType);
            $data = json_decode($response->getContent(), true);

            if (!$data['success']) {
                return back()->with('error', $data['message']);
            }

            $profile = $data['data'];

            // Rediriger vers la vue adaptée au type d'utilisateur
            switch ($userType) {
                case 'societe':
                    return view('dashboards.client.profile-edit', compact('profile'));
                case 'employee':
                    return view('dashboards.employee.profile-edit', compact('profile'));
                case 'provider':
                    return view('dashboards.provider.profile-edit', compact('profile'));
                default:
                    return redirect()->route('login')
                        ->with('error', 'Type d\'utilisateur non reconnu.');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du formulaire de modification: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage du formulaire de modification.');
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
            $response = app('App\Http\Controllers\API\ProfileController')->updateProfile($request, $userId, $userType);
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
            // Vérifier que l'utilisateur est connecté
            if (!session('user_type') || !session('user_id')) {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté pour accéder à cette page.');
            }

            $userType = session('user_type');
            $userId = session('user_id');

            // Appel à l'API pour mettre à jour le mot de passe
            $response = app('App\Http\Controllers\API\ProfileController')->updatePassword($request, $userId, $userType);
            $data = json_decode($response->getContent(), true);

            if (!$data['success']) {
                return back()->with('error', $data['message'])->withErrors($data['errors'] ?? [])->withInput();
            }

            return redirect()->route('profile.index')->with('success', 'Mot de passe mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du mot de passe: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour du mot de passe.')->withInput();
        }
    }
}
