<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Advice;
use App\Models\EmployeeAdviceView;
use App\Models\EmployeeAdvicePreference;
use App\Models\AdviceFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmployeeAdviceController extends Controller
{
    public function index(Request $request)
    {
        try {
            if (session('user_type') !== 'employe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant qu\'employé pour accéder à cette page.');
            }

            $employeeId = session('user_id');
            $employee = \App\Models\Employee::with('company')->find($employeeId);

            if (!$employee) {
                Log::error('Utilisateur non authentifié ou introuvable.');
                return response()->json(['message' => 'Utilisateur non authentifié'], 401);
            }

            Log::info('Utilisateur authentifié', ['employee_id' => $employee->id]);

            $formula = $employee->company->formule_abonnement ?? null;
            if (is_null($formula)) {
                Log::error('Formule d\'abonnement manquante pour l\'entreprise.', ['company_id' => $employee->company_id]);
                return response()->json(['message' => 'Formule d\'abonnement manquante'], 400);
            }

            $preferences = $employee->preferences;

            Log::info('Formule et préférences récupérées', [
                'formula' => $formula,
                'preferences' => $preferences,
            ]);

            $advices = Advice::where('min_formule', '<=', $formula)
                ->whereHas('schedules', function($query) {
                    $query->where('is_sent', true)
                          ->where('is_disabled', 0)  
                          ->whereNotNull('sent_at');
                })
                ->where(function ($query) use ($preferences) {
                    if ($preferences) {
                        $query->whereIn('category_id', $preferences->preferred_categories ?? [])
                              ->orWhereHas('tags', function ($q) use ($preferences) {
                                  $q->whereIn('id', $preferences->preferred_tags ?? []);
                              });
                    }
                })
                ->with(['category', 'tags', 'media', 'schedules' => function($query) {
                    $query->where('is_sent', true)
                          ->whereNotNull('sent_at');
                }])
                ->get();

            Log::info('Conseils récupérés', ['count' => $advices->count()]);

            return response()->json(['data' => $advices], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des conseils: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    // Enregistrer les vues de conseils
    public function storeView(Request $request, $adviceId)
    {
        try {
            $employee = $request->user();

            EmployeeAdviceView::create([
                'employee_id' => $employee->id,
                'advice_id' => $adviceId,
                'viewed_at' => now(),
                'time_spent' => $request->input('time_spent', 0),
            ]);

            return response()->json(['message' => 'Vue enregistrée avec succès'], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement de la vue: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function storeFeedback(Request $request, $adviceId)
    {
        try {
            $employee = $request->user();

            $feedback = AdviceFeedback::create([
                'employee_id' => $employee->id,
                'advice_id' => $adviceId,
                'rating' => $request->input('rating'),
                'comment' => $request->input('comment'),
                'is_helpful' => $request->input('is_helpful', false),
            ]);

            return response()->json(['message' => 'Feedback soumis avec succès', 'data' => $feedback], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la soumission du feedback: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function storePreferences(Request $request)
    {
        try {
            $employee = $request->user();

            $preferences = EmployeeAdvicePreference::updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'preferred_categories' => $request->input('preferred_categories', []),
                    'preferred_tags' => $request->input('preferred_tags', []),
                    'interests' => $request->input('interests', []),
                ]
            );

            return response()->json(['message' => 'Préférences enregistrées avec succès', 'data' => $preferences], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'enregistrement des préférences: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function personalized(Request $request)
    {
        try {
            $employee = $request->user();
            $preferences = $employee->preferences;

            $advices = Advice::where('is_personalized', true)
                ->where(function ($query) use ($preferences) {
                    if ($preferences) {
                        $query->whereIn('category_id', $preferences->preferred_categories)
                              ->orWhereHas('tags', function ($q) use ($preferences) {
                                  $q->whereIn('id', $preferences->preferred_tags);
                              });
                    }
                })
                ->with('category', 'tags', 'media')
                ->get();

            return response()->json(['data' => $advices], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des conseils personnalisés: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }
}
