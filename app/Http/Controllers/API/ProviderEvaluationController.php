<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ServiceEvaluation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProviderEvaluationController extends Controller
{
    public function getProviderEvaluations($providerId)
    {
        try {
            $evaluations = ServiceEvaluation::with(['event', 'employee'])
                ->select(
                    'service_evaluation.*',
                    'event.name as event_name',
                    'event.date as event_date',
                    'employee.first_name as employee_first_name',
                    'employee.last_name as employee_last_name'
                )
                ->join('event', 'event.id', '=', 'service_evaluation.event_id')
                ->join('employee', 'employee.id', '=', 'service_evaluation.employee_id')
                ->join('provider_assignment', function($join) use ($providerId) {
                    $join->where('provider_assignment.provider_id', '=', $providerId)
                         ->where('provider_assignment.status', '=', 'Accepted');
                })
                ->orderBy('service_evaluation.evaluation_date', 'desc')
                ->paginate(10);

            // Calculate statistics
            $stats = ServiceEvaluation::join('event', 'event.id', '=', 'service_evaluation.event_id')
                ->join('provider_assignment', function($join) use ($providerId) {
                    $join->where('provider_assignment.provider_id', '=', $providerId)
                         ->where('provider_assignment.status', '=', 'Accepted');
                })
                ->select(
                    DB::raw('ROUND(AVG(rating), 1) as average_rating'),
                    DB::raw('COUNT(*) as total_evaluations'),
                    DB::raw('COUNT(DISTINCT service_evaluation.event_id) as evaluated_events')
                )
                ->first();

            return response()->json([
                'status' => 'success',
                'evaluations' => $evaluations,
                'stats' => [
                    'averageRating' => $stats->average_rating ?? 0,
                    'totalEvaluations' => $stats->total_evaluations ?? 0,
                    'evaluatedEvents' => $stats->evaluated_events ?? 0
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('API Error in ProviderEvaluationController@getProviderEvaluations: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des évaluations'
            ], 500);
        }
    }
}
