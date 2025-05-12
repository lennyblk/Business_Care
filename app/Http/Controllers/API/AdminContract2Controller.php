<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminContract2Controller extends Controller
{
    public function getAllContracts()
    {
        try {
            $contracts = Contract::with('company')
                                ->orderBy('id', 'desc')
                                ->get();

            return response()->json(['data' => $contracts], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des contrats: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function getContract($id)
    {
        try {
            $contract = Contract::with('company')->findOrFail($id);
            return response()->json(['data' => $contract], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du contrat: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function markAsPaid($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $contract->payment_status = 'paid';
            $contract->save();

            return response()->json([
                'message' => 'Contrat marqué comme payé avec succès',
                'data' => $contract
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage du contrat comme payé: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }
}
