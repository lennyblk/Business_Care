<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::with('company')->get();
        return response()->json(['data' => $contracts]);
    }

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

    public function show($id)
    {
        $contract = Contract::with('company')->find($id);

        if (!$contract) {
            return response()->json(['message' => 'Contrat non trouvé'], 404);
        }

        return response()->json(['data' => $contract]);
    }

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

    public function destroy($id)
    {
        $contract = Contract::find($id);

        if (!$contract) {
            return response()->json(['message' => 'Contrat non trouvé'], 404);
        }

        $contract->delete();

        return response()->json(['message' => 'Contrat supprimé avec succès']);
    }

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
