<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContractController extends Controller
{
    // GET /api/contracts
    public function index()
    {
        try {
            $contracts = Contract::all();
            return response()->json(['data' => $contracts]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des contrats: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    // POST /api/contracts
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => 'required|exists:company,id',
                'services' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'amount' => 'required|numeric',
                'payment_method' => 'required|string',
                'formule_abonnement' => 'required|in:Starter,Basic,Premium'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $contract = Contract::create($request->all());

            return response()->json([
                'message' => 'Contrat créé avec succès',
                'data' => $contract
            ], 201);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la création du contrat: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la création du contrat'], 500);
        }
    }

    // GET /api/contracts/{id}
    public function show($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            return response()->json(['data' => $contract]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération du contrat: ' . $e->getMessage());
            return response()->json(['message' => 'Contrat non trouvé'], 404);
        }
    }

    // PUT /api/contracts/{id}
    public function update(Request $request, $id)
    {
        try {
            $contract = Contract::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'services' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'amount' => 'required|numeric',
                'payment_method' => 'required|string',
                'formule_abonnement' => 'required|in:Starter,Basic,Premium'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $contract->update($request->all());

            return response()->json([
                'message' => 'Contrat mis à jour avec succès',
                'data' => $contract
            ]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la mise à jour du contrat: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la mise à jour du contrat'], 500);
        }
    }

    // DELETE /api/contracts/{id}
    public function destroy($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $contract->delete();

            return response()->json(['message' => 'Contrat supprimé avec succès']);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la suppression du contrat: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la suppression du contrat'], 500);
        }
    }

    // GET /api/companies/{companyId}/contracts
    public function getByCompany($companyId)
    {
        try {
            $company = Company::find($companyId);

            if (!$company) {
                return response()->json(['message' => 'Entreprise non trouvée'], 404);
            }

            $contracts = Contract::where('company_id', $companyId)->get();

            return response()->json(['data' => $contracts]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des contrats par entreprise: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération des contrats'], 500);
        }
    }
}
