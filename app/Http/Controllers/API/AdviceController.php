<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Advice;
use App\Models\AdviceCategory;
use App\Models\AdviceTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AdviceController extends Controller
{
    // GET /api/advices
    public function index()
    {
        try {
            $advices = Advice::where('is_active', true)
                ->with('category', 'tags', 'media')
                ->get();
            return response()->json(['data' => $advices], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des conseils: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération des conseils'], 500);
        }
    }

    // POST /api/advices
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required',
                'category_id' => 'required|exists:advice_category,id',
                'publish_date' => 'required|date',
                'expiration_date' => 'nullable|date|after:publish_date',
                'is_personalized' => 'boolean',
                'min_formule' => 'required|string|in:Basic,Premium',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $advice = Advice::create($request->all());

            // Gérer l'upload des médias
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    if ($file->isValid() && in_array($file->extension(), ['jpg', 'jpeg', 'png', 'gif'])) {
                        // Enregistrer l'image dans le dossier
                        $path = $file->store('public/advice_media'); // Stocker dans storage/app/public/advice_media

                        // Enregistrer les informations dans la table advice_media
                        \App\Models\AdviceMedia::create([
                            'advice_id' => $advice->id, // Lien avec le conseil
                            'media_type' => 'image',
                            'media_url' => str_replace('public/', 'storage/', $path), // Convertir pour l'accès public
                            'title' => $file->getClientOriginalName(),
                        ]);
                    }
                }
            }

            // Attacher les tags
            if ($request->has('tags')) {
                $advice->tags()->sync($request->input('tags'));
            }

            return response()->json(['message' => 'Conseil créé avec succès', 'data' => $advice], 201);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la création du conseil: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la création du conseil'], 500);
        }
    }

    // GET /api/advices/{id}
    public function show($id)
    {
        try {
            $advice = Advice::with('category', 'tags', 'media')->findOrFail($id);
            return response()->json(['data' => $advice], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération du conseil: ' . $e->getMessage());
            return response()->json(['message' => 'Conseil non trouvé'], 404);
        }
    }

    // PUT /api/advices/{id}
    public function update(Request $request, $id)
    {
        try {
            $advice = Advice::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'content' => 'required',
                'category_id' => 'required|exists:advice_category,id',
                'publish_date' => 'required|date',
                'expiration_date' => 'nullable|date|after:publish_date',
                'is_personalized' => 'boolean',
                'min_formule' => 'required|string|in:Basic,Premium',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $advice->update($request->all());

            // Gérer l'upload des médias
            if ($request->hasFile('media')) {
                foreach ($request->file('media') as $file) {
                    if ($file->isValid() && in_array($file->extension(), ['jpg', 'jpeg', 'png', 'gif'])) {
                        $path = $file->store('public/advice_media'); // Stocker dans storage/app/public/advice_media
                        $advice->media()->create([
                            'media_type' => 'image',
                            'media_url' => str_replace('public/', 'storage/', $path), // Convertir pour l'accès public
                            'title' => $file->getClientOriginalName(),
                        ]);
                    }
                }
            }

            // Attacher les tags
            if ($request->has('tags')) {
                $advice->tags()->sync($request->input('tags'));
            }

            return response()->json(['message' => 'Conseil mis à jour avec succès', 'data' => $advice], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la mise à jour du conseil: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la mise à jour du conseil'], 500);
        }
    }

    // DELETE /api/advices/{id}
    public function destroy($id)
    {
        try {
            $advice = Advice::findOrFail($id);
            $advice->is_active = false;
            $advice->save();

            return response()->json(['message' => 'Conseil désactivé avec succès'], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la désactivation du conseil: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la désactivation du conseil'], 500);
        }
    }

    // Nouvelle méthode pour récupérer les conseils par formule d'abonnement
    public function getByFormule($formule)
    {
        try {
            $advices = Advice::where('min_formule', '<=', $formule)
                ->where('is_active', true)
                ->with('category', 'tags', 'media')
                ->where('is_published', true)
                ->where(function($query) {
                    $query->whereNull('expiration_date')
                          ->orWhere('expiration_date', '>=', now());
                })
                ->get();

            return response()->json(['data' => $advices], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des conseils par formule: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    // Nouvelle méthode pour les conseils personnalisés
    public function getPersonalizedAdvices(Request $request)
    {
        try {
            $employeeId = $request->input('employee_id');
            $formule = $request->input('formule');

            if ($formule !== 'Premium') {
                return response()->json(['message' => 'Formule non autorisée'], 403);
            }

            $advices = Advice::where('is_personalized', true)
                ->where('is_published', true)
                ->with(['personalizedAdvice' => function($query) use ($employeeId) {
                    // Logique de filtrage basée sur les critères de l'employé
                }])
                ->get();

            return response()->json(['data' => $advices], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des conseils personnalisés: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    // Nouvelle méthode pour les catégories
    public function categories()
    {
        try {
            $categories = AdviceCategory::where('is_active', true)->get();
            return response()->json(['data' => $categories], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des catégories: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    // Nouvelle méthode pour les tags
    public function tags()
    {
        try {
            $tags = AdviceTag::all();
            return response()->json(['data' => $tags], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des tags: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    // GET /api/advices/scheduled
    public function scheduled()
    {
        try {
            $scheduledAdvices = \App\Models\AdviceSchedule::with('advice')
                ->orderBy('scheduled_date', 'asc')
                ->get();
            return response()->json(['data' => $scheduledAdvices], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des conseils programmés: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    // POST /api/advices/{id}/schedule
    public function saveSchedule(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'scheduled_date' => 'required|date|after:today',
                'target_audience' => 'required|in:All,Specific',
                'target_criteria' => 'nullable|required_if:target_audience,Specific',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $schedule = new \App\Models\AdviceSchedule([
                'advice_id' => $id,
                'scheduled_date' => $request->scheduled_date,
                'target_audience' => $request->target_audience,
                'target_criteria' => $request->target_criteria
            ]);

            $schedule->save();

            return response()->json(['message' => 'Conseil programmé avec succès', 'data' => $schedule], 201);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la programmation du conseil: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    // PATCH /api/advices/schedule/{id}/toggle
    public function toggleSchedule($id)
    {
        try {
            $schedule = \App\Models\AdviceSchedule::findOrFail($id);
            $schedule->is_disabled = !$schedule->is_disabled;
            $schedule->save();

            return response()->json([
                'message' => 'Statut de la programmation modifié avec succès',
                'data' => $schedule
            ], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors du changement de statut: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }
}
