<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderAvailability;
use App\Models\ServiceEvaluation;
use App\Models\ProviderInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class ProviderController extends Controller
{
    /**
     * Récupère la liste de tous les prestataires
     */
    public function index()
    {
        $providers = Provider::all();
        return response()->json(['data' => $providers]);
    }

    /**
     * Crée un nouveau prestataire
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'last_name' => 'nullable|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'description' => 'required|string',
            'domains' => 'required|string',
            'email' => 'required|email|unique:provider,email',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'adresse' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:100',
            'siret' => 'nullable|string|max:14',
            'iban' => 'nullable|string|max:34',
            'statut_prestataire' => 'required|in:Candidat,Validé,Inactif',
            'tarif_horaire' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        // Hash password
        $data['password'] = Hash::make($data['password']);

        $provider = Provider::create($data);

        return response()->json([
            'message' => 'Prestataire créé avec succès',
            'data' => $provider
        ], 201);
    }

    /**
     * Récupère les détails d'un prestataire
     */
    public function show($id)
    {
        $provider = Provider::find($id);

        if (!$provider) {
            return response()->json(['message' => 'Prestataire non trouvé'], 404);
        }

        return response()->json(['data' => $provider]);
    }

    /**
     * Met à jour un prestataire existant
     */
    public function update(Request $request, $id)
    {
        $provider = Provider::find($id);

        if (!$provider) {
            return response()->json(['message' => 'Prestataire non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'last_name' => 'nullable|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'description' => 'required|string',
            'domains' => 'required|string',
            'email' => 'required|email|unique:provider,email,' . $id,
            'telephone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8',
            'adresse' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:100',
            'siret' => 'nullable|string|max:14',
            'iban' => 'nullable|string|max:34',
            'statut_prestataire' => 'required|in:Candidat,Validé,Inactif',
            'tarif_horaire' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $provider->update($data);

        return response()->json([
            'message' => 'Prestataire mis à jour avec succès',
            'data' => $provider
        ]);
    }

    /**
     * Supprime un prestataire
     */
    public function destroy($id)
    {
        $provider = Provider::find($id);

        if (!$provider) {
            return response()->json(['message' => 'Prestataire non trouvé'], 404);
        }

        $provider->delete();

        return response()->json(['message' => 'Prestataire supprimé avec succès']);
    }

    /**
     * Récupère les disponibilités d'un prestataire
     */
    public function getAvailabilities($id)
    {
        $provider = Provider::find($id);

        if (!$provider) {
            return response()->json(['message' => 'Prestataire non trouvé'], 404);
        }

        $availabilities = ProviderAvailability::where('provider_id', $id)->get();

        return response()->json(['data' => $availabilities]);
    }

    /**
     * Récupère les évaluations d'un prestataire
     */
    public function getEvaluations($id)
    {
        $provider = Provider::find($id);

        if (!$provider) {
            return response()->json(['message' => 'Prestataire non trouvé'], 404);
        }

        $evaluations = ServiceEvaluation::where('provider_id', $id)->get();

        return response()->json(['data' => $evaluations]);
    }

    /**
     * Récupère les factures d'un prestataire
     */
    public function getInvoices($id)
    {
        $provider = Provider::find($id);

        if (!$provider) {
            return response()->json(['message' => 'Prestataire non trouvé'], 404);
        }

        $invoices = ProviderInvoice::where('provider_id', $id)->get();

        return response()->json(['data' => $invoices]);
    }
}
