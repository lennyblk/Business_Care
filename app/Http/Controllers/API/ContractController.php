<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContractController extends Controller
{
    /**
     * Récupère la liste de tous les contrats
     */
    public function index()
    {
        $contracts = Contract::with('company')->get();
        return response()->json(['data' => $contracts]);
    }

    /**
     * Crée un nouveau contrat
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:company,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'services' => 'required|string',
            'amount' => 'required|numeric',
            'payment_method' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contract = Contract::create($request->all());

        return response()->json([
            'message' => 'Contrat créé avec succès',
            'data' => $contract
        ], 201);
    }

    /**
     * Récupère les détails d'un contrat
     */
    public function show($id)
    {
        $contract = Contract::with('company')->find($id);

        if (!$contract) {
            return response()->json(['message' => 'Contrat non trouvé'], 404);
        }

        return response()->json(['data' => $contract]);
    }

    /**
     * Met à jour un contrat existant
     */
    public function update(Request $request, $id)
    {
        $contract = Contract::find($id);

        if (!$contract) {
            return response()->json(['message' => 'Contrat non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:company,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'services' => 'required|string',
            'amount' => 'required|numeric',
            'payment_method' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $contract->update($request->all());

        return response()->json([
            'message' => 'Contrat mis à jour avec succès',
            'data' => $contract
        ]);
    }

    /**
     * Supprime un contrat
     */
    public function destroy($id)
    {
        $contract = Contract::find($id);

        if (!$contract) {
            return response()->json(['message' => 'Contrat non trouvé'], 404);
        }

        $contract->delete();

        return response()->json(['message' => 'Contrat supprimé avec succès']);
    }

    /**
     * Récupère les contrats d'une entreprise spécifique
     */
    public function getByCompany($companyId)
    {
        $company = Company::find($companyId);

        if (!$company) {
            return response()->json(['message' => 'Entreprise non trouvée'], 404);
        }

        $contracts = Contract::where('company_id', $companyId)->get();

        return response()->json(['data' => $contracts]);
    }
}
