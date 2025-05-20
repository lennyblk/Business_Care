<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\Contract;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QuoteController extends Controller
{
    public function destroy($id)
    {
        try {
            $quote = Quote::findOrFail($id);

            if ($quote->status !== 'Pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les devis en attente peuvent être supprimés.'
                ], 400);
            }

            $quote->delete();

            return response()->json([
                'success' => true,
                'message' => 'Devis supprimé avec succès.'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du devis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression du devis.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function accept($id)
    {
        try {
            $quote = Quote::findOrFail($id);

            if ($quote->status !== 'Pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce devis a déjà été traité.'
                ], 400);
            }

            $quote->status = 'Accepted';
            $quote->save();

            $services = $this->generateServicesDescription($quote);

            $contract = Contract::create([
                'company_id' => $quote->company_id,
                'quote_id' => $quote->id,
                'formule_abonnement' => $quote->formule_abonnement,
                'services' => $services,
                'start_date' => now(),
                'end_date' => now()->addYear(),
                'amount' => $quote->total_amount / 12,
                'payment_method' => 'Direct Debit',
                'payment_status' => 'pending' 
            ]);

            if (class_exists('App\Models\Activity')) {
                Activity::create([
                    'company_id' => $quote->company_id,
                    'user_id' => Auth::id() ?? $quote->company_id,
                    'title' => 'Devis accepté',
                    'description' => 'Devis #' . $quote->id . ' a été accepté',
                    'type' => 'quote',
                    'subject_type' => Quote::class,
                    'subject_id' => $quote->id,
                ]);

                Activity::create([
                    'company_id' => $quote->company_id,
                    'user_id' => Auth::id() ?? $quote->company_id,
                    'title' => 'Demande de contrat créée',
                    'description' => 'Demande de contrat #' . $contract->id . ' créée à partir du devis #' . $quote->id,
                    'type' => 'contract',
                    'subject_type' => Contract::class,
                    'subject_id' => $contract->id,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Devis accepté avec succès. Une demande de contrat a été créée.',
                'contract_id' => $contract->id
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'acceptation du devis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'acceptation du devis.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            $quote = Quote::findOrFail($id);

            if ($quote->status !== 'Pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce devis a déjà été traité.'
                ], 400);
            }

            $quote->status = 'Rejected';
            $quote->save();

            return response()->json([
                'success' => true,
                'message' => 'Devis rejeté avec succès.'
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du rejet du devis: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du rejet du devis.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateServicesDescription(Quote $quote)
    {
        $services = "Formule " . $quote->formule_abonnement . " pour " . $quote->company_size . " salariés.\n\n";

        // Activités
        if ($quote->formule_abonnement == 'Starter') {
            $services .= "- 2 activités avec participation des prestataires\n";
            $services .= "- 1 RDV médical (présentiel/visio)\n";
            $services .= "- RDV médicaux supplémentaires : 75€/RDV\n";
            $services .= "- Accès au chatbot : 6 questions\n";
            $services .= "- Accès aux fiches pratiques BC : Illimité\n";
            $services .= "- Conseils hebdomadaires : Non\n";
            $services .= "- Événements / Communautés : Accès illimité\n";
        } elseif ($quote->formule_abonnement == 'Basic') {
            $services .= "- 3 activités avec participation des prestataires\n";
            $services .= "- 2 RDV médicaux (présentiel/visio)\n";
            $services .= "- RDV médicaux supplémentaires : 75€/RDV\n";
            $services .= "- Accès au chatbot : 20 questions\n";
            $services .= "- Accès aux fiches pratiques BC : Illimité\n";
            $services .= "- Conseils hebdomadaires : Oui (non personnalisés)\n";
            $services .= "- Événements / Communautés : Accès illimité\n";
        } else { // Premium
            $services .= "- 4 activités avec participation des prestataires\n";
            $services .= "- 3 RDV médicaux (présentiel/visio)\n";
            $services .= "- RDV médicaux supplémentaires : 50€/RDV\n";
            $services .= "- Accès au chatbot : Illimité\n";
            $services .= "- Accès aux fiches pratiques BC : Illimité\n";
            $services .= "- Conseils hebdomadaires : Oui personnalisés (suggestion d'activités)\n";
            $services .= "- Événements / Communautés : Accès illimité\n";
        }

        if (!empty($quote->services_details)) {
            $services .= "\nDétails complémentaires :\n" . $quote->services_details;
        }

        return $services;
    }
}
