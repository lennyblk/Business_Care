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
            $data = null;

            switch ($userType) {
                case 'employe':
                case 'employee':
                    $profile = Employee::with('company')->findOrFail($id);
                    $company = $profile->company;
                    
                    $data = (object)[
                        'id' => $profile->id,
                        'first_name' => $profile->first_name,
                        'last_name' => $profile->last_name,
                        'name' => $profile->first_name . ' ' . $profile->last_name,
                        'email' => $profile->email,
                        'telephone' => $profile->telephone,
                        'position' => $profile->position ?? '',
                        'departement' => $profile->departement ?? '',
                        'date_creation_compte' => $profile->date_creation_compte,
                        'company' => $company ? [
                            'name' => $company->name,
                            'address' => $company->address,
                            'ville' => $company->ville,
                            'pays' => $company->pays,
                            'code_postal' => $company->code_postal
                        ] : null
                    ];
                    break;
                    
                case 'prestataire':    
                case 'provider':
                    $profile = Provider::where('id', $id)->firstOrFail();
                    $data = (object)[
                        'id' => $profile->id,
                        'first_name' => $profile->first_name,
                        'last_name' => $profile->last_name,
                        'email' => $profile->email,
                        'telephone' => $profile->telephone,
                        'adresse' => $profile->adresse,
                        'code_postal' => $profile->code_postal,
                        'ville' => $profile->ville,
                        'activity_type' => $profile->activity_type ?? '',
                        'other_activity' => $profile->other_activity ?? '',
                        'description' => $profile->description ?? '',
                        'domains' => $profile->domains ?? '',
                        'tarif_horaire' => $profile->tarif_horaire ?? 0,
                        'siret' => $profile->siret ?? ''
                    ];
                    break;
                    
                case 'societe':
                case 'company':    
                    $profile = Company::findOrFail($id);
                    $data = (object)[
                        'id' => $profile->id,
                        'name' => $profile->name,
                        'email' => $profile->email,
                        'telephone' => $profile->telephone,
                        'address' => $profile->address,
                        'code_postal' => $profile->code_postal,
                        'ville' => $profile->ville,
                        'pays' => $profile->pays,
                        'siret' => $profile->siret,
                        'formule_abonnement' => $profile->formule_abonnement,
                        'statut_compte' => $profile->statut_compte,
                        'date_debut_contrat' => $profile->date_debut_contrat,
                        'date_fin_contrat' => $profile->date_fin_contrat,
                        'employee_count' => $profile->employee_count
                    ];
                    break;

                default:
                    Log::error('Type utilisateur invalide dans getProfile', ['type' => $userType, 'id' => $id]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Type d\'utilisateur non valide'
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur dans getProfile', [
                'message' => $e->getMessage(),
                'type' => $userType,
                'id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du profil: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour les informations du profil
     */
    public function updateProfile(Request $request, $id, $userType)
    {
        try {
            switch ($userType) {
                case 'employe':
                case 'employee':
                    $type = 'employee';
                    break;
                case 'prestataire':    
                case 'provider':
                    $type = 'provider';
                    break;
                case 'societe':
                case 'company':
                    $type = 'societe';
                    break;
                default:
                    Log::error('Type utilisateur invalide dans updateProfile', ['type' => $userType]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Type d\'utilisateur non valide'
                    ], 400);
            }

            $commonRules = [
                'email' => 'required|email|max:255',
                'telephone' => 'required|string|max:20',
            ];

            switch ($type) {
                case 'societe':
                    $validationRules = array_merge($commonRules, [
                        'name' => 'required|string|max:255',
                        'address' => 'required|string|max:255',
                        'code_postal' => 'required|string|max:10',
                        'ville' => 'required|string|max:100',
                        'pays' => 'required|string|max:100',
                        'siret' => 'nullable|string|max:14'
                    ]);
                    break;
                case 'employee':
                    $validationRules = array_merge($commonRules, [
                        'first_name' => 'required|string|max:100',
                        'last_name' => 'required|string|max:100',
                        'position' => 'required|string|max:100',
                        'departement' => 'nullable|string|max:100'
                    ]);
                    break;
                case 'provider':
                    $validationRules = array_merge($commonRules, [
                        'first_name' => 'required|string|max:100',
                        'last_name' => 'required|string|max:100',
                        'description' => 'required|string',
                        'domains' => 'nullable|string',
                        'adresse' => 'nullable|string|max:255',
                        'code_postal' => 'nullable|string|max:10',
                        'ville' => 'nullable|string|max:100',
                        'activity_type' => 'nullable|string',
                        'other_activity' => 'required_if:activity_type,autre|nullable|string',
                        'tarif_horaire' => 'required|numeric|min:0',
                        'siret' => 'nullable|string|max:14'
                    ]);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Type d\'utilisateur non valide'
                    ], 400);
            }

            $validator = Validator::make($request->all(), $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            switch ($type) {
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
                    break;
                case 'employee':
                    $profile = Employee::findOrFail($id);
                    $profile->first_name = $request->first_name;
                    $profile->last_name = $request->last_name;
                    $profile->email = $request->email;
                    $profile->telephone = $request->telephone;
                    $profile->position = $request->position;
                    $profile->departement = $request->departement;
                    break;
                case 'provider':
                    $profile = Provider::findOrFail($id);
                    $profile->first_name = $request->first_name;
                    $profile->last_name = $request->last_name;
                    $profile->description = $request->description;
                    $profile->domains = $request->domains ?? '';
                    $profile->adresse = $request->adresse;
                    $profile->code_postal = $request->code_postal;
                    $profile->ville = $request->ville;
                    $profile->activity_type = $request->activity_type;
                    $profile->other_activity = $request->other_activity;
                    $profile->tarif_horaire = $request->tarif_horaire;
                    $profile->siret = $request->siret;
                    break;
            }

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
            switch ($userType) {
                case 'employe':
                case 'employee':
                    $type = 'employee';
                    break;
                case 'prestataire':
                case 'provider':
                    $type = 'provider';
                    break;
                case 'societe':
                case 'company':
                    $type = 'societe';
                    break;
                default:
                    Log::error('Type utilisateur invalide dans updatePassword', ['type' => $userType]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Type d\'utilisateur non valide'
                    ], 400);
            }

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

            switch ($type) {
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

            if (!Hash::check($request->current_password, $profile->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le mot de passe actuel est incorrect'
                ], 422);
            }

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
