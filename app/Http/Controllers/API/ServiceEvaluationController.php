<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Employee;
use App\Models\ServiceEvaluation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceEvaluationController extends Controller
{
    public function getEvaluationData($id)
    {
        try {
            $event = Event::findOrFail($id);
            $employeeId = session('user_id');
            $employee = Employee::where('id', $employeeId)->firstOrFail();

            return response()->json([
                'status' => 'success',
                'event' => $event,
                'employee' => $employee
            ]);
        } catch (\Exception $e) {
            Log::error('API Error in ServiceEvaluationController@getEvaluationData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }

    public function store(Request $request, $id)
    {
        try {
            $employeeId = session('user_id');
            $employee = Employee::where('id', $employeeId)->firstOrFail();

            // Vérifier si une évaluation existe déjà
            $existingEvaluation = ServiceEvaluation::where('event_id', $id)
                ->where('employee_id', $employee->id)
                ->first();

            if ($existingEvaluation) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Vous avez déjà évalué cet événement'
                ], 400);
            }

            $evaluation = ServiceEvaluation::create([
                'event_id' => $id,
                'employee_id' => $employee->id,
                'rating' => $request->input('rating'),
                'comment' => $request->input('comment'),
                'evaluation_date' => now()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Évaluation enregistrée avec succès',
                'data' => $evaluation
            ]);
        } catch (\Exception $e) {
            Log::error('API Error in ServiceEvaluationController@store: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de l\'enregistrement'
            ], 500);
        }
    }
}
