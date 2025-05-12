<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Récupérer les informations de profil selon le type d'utilisateur
     */
    public function getProfile($id, $userType)
    {
        try {
            switch ($userType) {
                case 'societe':
                    $profile = Company::findOrFail($id);
                    break;
                case 'employee':
                    $profile = Employee::findOrFail($id);
                    break;
                case 'provider':
                    $profile = Provider::findOrFail($id);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Type d\'utilisateur non valide'
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $profile
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du profil: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour les informations du profil
     */
    public function updateProfile(Request $request, $id, $userType)
    {
        try {
            // Validation commune à tous les types d'utilisateurs
            $commonRules = [
                'email' => 'required|email|max:255',
                'telephone' => 'required|string|max:20',
            ];

            // Validation spécifique selon le type d'utilisateur
            switch ($userType) {
                case 'societe':
                    $validationRules = array_merge($commonRules, [
                        'name' => 'required|string|max:255',
                        'address' => 'required|string|max:255',
                        'code_postal' => 'required|string|max:10',
                        'ville' => 'required|string|max:100',
                        'pays' => 'required|string|max:100',
                        'siret' => 'nullable|string|max:14',
                        'effectif' => 'nullable|integer',
                        'secteur_activite' => 'nullable|string|max:100'
                    ]);
                    break;
                case 'employee':
                    $validationRules = array_merge($commonRules, [
                        'first_name' => 'required|string|max:100',
                        'last_name' => 'required|string|max:100',
                        'function' => 'nullable|string|max:100',
                        'department' => 'nullable|string|max:100'
                    ]);
                    break;
                case 'provider':
                    $validationRules = array_merge($commonRules, [
                        'name' => 'required|string|max:255',
                        'address' => 'nullable|string|max:255',
                        'speciality' => 'required|string|max:100',
                        'description' => 'nullable|string',
                        'website' => 'nullable|url'
                    ]);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Type d\'utilisateur non valide'
                    ], 400);
            }

            // Validation des données
            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Mise à jour selon le type d'utilisateur
            switch ($userType) {
                case 'societe':
                    $profile = Company::findOrFail($id);
                    $profile->name = $request->name;
                    $profile->email = $request->email;
                    $profile->address = $request->address;
                    $profile->code_postal = $request->code_postal;
                    $profile->ville = $request->ville;
                    $profile->pays = $request->pays;
                    $profile->telephone = $request->telephone;
                    $profile->siret = $request->siret;
                    $profile->effectif = $request->effectif;
                    $profile->secteur_activite = $request->secteur_activite;
                    break;
                case 'employee':
                    $profile = Employee::findOrFail($id);
                    $profile->first_name = $request->first_name;
                    $profile->last_name = $request->last_name;
                    $profile->email = $request->email;
                    $profile->telephone = $request->telephone;
                    $profile->function = $request->function;
                    $profile->department = $request->department;
                    break;
                case 'provider':
                    $profile = Provider::findOrFail($id);
                    $profile->name = $request->name;
                    $profile->email = $request->email;
                    $profile->telephone = $request->telephone;
                    $profile->address = $request->address;
                    $profile->speciality = $request->speciality;
                    $profile->description = $request->description;
                    $profile->website = $request->website;
                    break;
            }

            // Enregistrement des modifications
            $profile->save();

            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès',
                'data' => $profile
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du profil: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour le mot de passe
     */
    public function updatePassword(Request $request, $id, $userType)
    {
        try {
            // Validation des données
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Récupération du profil selon le type d'utilisateur
            switch ($userType) {
                case 'societe':
                    $profile = Company::findOrFail($id);
                    break;
                case 'employee':
                    $profile = Employee::findOrFail($id);
                    break;
                case 'provider':
                    $profile = Provider::findOrFail($id);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Type d\'utilisateur non valide'
                    ], 400);
            }

            // Vérification du mot de passe actuel
            if (!Hash::check($request->current_password, $profile->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le mot de passe actuel est incorrect'
                ], 422);
            }

            // Mise à jour du mot de passe
            $profile->password = Hash::make($request->new_password);
            $profile->save();

            return response()->json([
                'success' => true,
                'message' => 'Mot de passe mis à jour avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du mot de passe: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du mot de passe',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
