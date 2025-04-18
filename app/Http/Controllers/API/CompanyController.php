<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    public function index()
    {
        try {
            $companies = Company::all();
            return response()->json(['data' => $companies], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des entreprises: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération des entreprises'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'code_postal' => 'nullable|string|max:10',
                'ville' => 'nullable|string|max:100',
                'pays' => 'nullable|string|max:100',
                'telephone' => 'required|string|max:20',
                'creation_date' => 'required|date',
                'email' => 'required|email|unique:company,email',
                'password' => 'required|string|max:255',
                'siret' => 'nullable|string|max:14',
                'formule_abonnement' => 'required|in:Starter,Basic,Premium',
                'statut_compte' => 'required|in:Actif,Inactif',
                'date_debut_contrat' => 'nullable|date',
                'date_fin_contrat' => 'nullable|date',
                'mode_paiement_prefere' => 'nullable|string|max:50',
                'employee_count' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Hachage du mot de passe si ce n'est pas déjà fait
            $data = $request->all();
            if ($request->has('password') && !preg_match('/^\$2y\$/', $data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $company = Company::create($data);

            return response()->json([
                'message' => 'Entreprise créée avec succès',
                'data' => $company
            ], 201);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la création de l\'entreprise: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la création de l\'entreprise'], 500);
        }
    }

    public function show($id)
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                return response()->json(['message' => 'Entreprise non trouvée'], 404);
            }

            return response()->json(['data' => $company]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération de l\'entreprise: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération de l\'entreprise'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                return response()->json(['message' => 'Entreprise non trouvée'], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'code_postal' => 'nullable|string|max:10',
                'ville' => 'nullable|string|max:100',
                'pays' => 'nullable|string|max:100',
                'telephone' => 'required|string|max:20',
                'creation_date' => 'required|date',
                'email' => 'required|email|unique:company,email,' . $id,
                'password' => 'nullable|string',
                'siret' => 'nullable|string|max:14',
                'formule_abonnement' => 'required|in:Starter,Basic,Premium',
                'statut_compte' => 'required|in:Actif,Inactif',
                'date_debut_contrat' => 'nullable|date',
                'date_fin_contrat' => 'nullable|date',
                'mode_paiement_prefere' => 'nullable|string|max:50',
                'employee_count' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();

            // Traitement du mot de passe
            if (empty($data['password'])) {
                unset($data['password']);
            } elseif (!preg_match('/^\$2y\$/', $data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $company->update($data);

            return response()->json([
                'message' => 'Entreprise mise à jour avec succès',
                'data' => $company
            ]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la mise à jour de l\'entreprise: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la mise à jour de l\'entreprise'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                return response()->json(['message' => 'Entreprise non trouvée'], 404);
            }

            $company->delete();

            return response()->json(['message' => 'Entreprise supprimée avec succès']);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la suppression de l\'entreprise: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la suppression de l\'entreprise'], 500);
        }
    }

    public function getEmployees($companyId)
    {
        try {
            $company = Company::find($companyId);

            if (!$company) {
                return response()->json(['message' => 'Entreprise non trouvée'], 404);
            }

            $employees = Employee::where('company_id', $companyId)->get();

            return response()->json(['data' => $employees]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des employés de l\'entreprise: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération des employés'], 500);
        }
    }

    public function getContracts($companyId)
    {
        try {
            $company = Company::find($companyId);

            if (!$company) {
                return response()->json(['message' => 'Entreprise non trouvée'], 404);
            }

            $contracts = Contract::where('company_id', $companyId)->get();

            return response()->json(['data' => $contracts]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des contrats de l\'entreprise: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération des contrats'], 500);
        }
    }
}
