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
        $companyId = session('user_id');
        

        if (!$companyId) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de déterminer l\'entreprise de l\'employé'
            ], 400);
        }

        try {
            // Enregistrer la donation avec company_id au lieu de employee_id
            $donation = \App\Models\Donation::create([
                'association_id' => $id,
                'company_id' => $companyId,
                'donation_type' => 'Financial',
                'amount_or_description' => $request->amount,
                'donation_date' => now(),
                'status' => 'Validated'
            ]);

            // Créer la facture pour le don
            $invoice = \App\Models\Invoice::create([
                'company_id' => $companyId,
                'issue_date' => now(),
                'due_date' => now(),
                'total_amount' => $request->amount,
                'payment_status' => 'Paid',
                'details' => "Don à l'association: " . $association->name,
                'is_donation' => 1
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Don effectué avec succès',
                'data' => [
                    'donation' => $donation,
                    'invoice' => $invoice
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du traitement du don: ' . $e->getMessage()
            ], 500);
        }
    }
}
