<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ServiceEvaluation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProviderEvaluationController extends Controller
{
    public function getProviderEvaluations($providerId)
    {
        try {
            // Requête de base pour obtenir les évaluations
            $baseQuery = ServiceEvaluation::select('service_evaluation.*')
                ->join('event', 'service_evaluation.event_id', '=', 'event.id')
                ->join('provider_assignment', 'provider_assignment.event_proposal_id', '=', 'event.event_proposal_id')
                ->where('provider_assignment.provider_id', $providerId)
                ->where('provider_assignment.status', 'Accepted');

            // Récupérer les évaluations paginées
            $evaluations = (clone $baseQuery)
                ->with('event')
                ->orderBy('service_evaluation.evaluation_date', 'desc')
                ->paginate(10);

            // Calculer les statistiques
            $stats = [
                'averageRating' => (clone $baseQuery)->avg('rating') ?? 0,
                'totalEvaluations' => (clone $baseQuery)->count(),
                'evaluatedEvents' => (clone $baseQuery)->distinct('service_evaluation.event_id')->count()
            ];

            Log::info('Statistiques des évaluations récupérées pour le prestataire', [
                'provider_id' => $providerId,
                'stats' => $stats,
                'total_evaluations' => count($evaluations)
            ]);

            return response()->json([
                'status' => 'success',
                'evaluations' => $evaluations,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('API Error in ProviderEvaluationController@getProviderEvaluations: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }
}
