<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Association;
use Illuminate\Http\Request;

class AssociationApiController extends Controller
{
    /**
     * Affiche la liste des associations
     */
    public function index()
    {
        $associations = Association::all();
        return response()->json([
            'success' => true,
            'data' => $associations
        ]);
    }

    /**
     * Récupère les détails d'une association
     */
    public function show($id)
    {
        $association = Association::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $association
        ]);
    }

    /**
     * Traite un don à une association
     */
    public function processDonation(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method_id' => 'required|string'
        ]);

        $association = Association::findOrFail($id);
        $companyId = session('company_id');

        // Ici, vous ajouteriez la logique pour traiter le paiement via Stripe
        // et enregistrer la donation dans votre base de données

        try {
            // Simulation de traitement de paiement
            // Dans un cas réel, vous intégreriez Stripe ici

            // Enregistrer la donation
            $donation = \App\Models\Donation::create([
                'association_id' => $id,
                'company_id' => $companyId,
                'amount' => $request->amount,
                'payment_status' => 'completed',
                'payment_date' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Don effectué avec succès',
                'data' => $donation
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement du don: ' . $e->getMessage()
            ], 500);
        }
    }
}
