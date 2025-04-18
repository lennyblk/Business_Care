<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function index()
    {
        try {
            // Récupérer tous les événements avec les relations
            $events = Event::with('company')->get();
            return response()->json(['data' => $events]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des événements: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération des événements'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'event_type' => 'required|in:Webinar,Conference,Sport Event,Workshop',
                'capacity' => 'required|integer',
                'location' => 'nullable|string|max:255',
                'company_id' => 'required|exists:company,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $event = Event::create($request->all());

            return response()->json([
                'message' => 'Activité créée avec succès',
                'data' => $event
            ], 201);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la création de l\'événement: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la création de l\'événement'], 500);
        }
    }

    public function show($id)
    {
        try {
            $event = Event::with('company')->find($id);

            if (!$event) {
                return response()->json(['message' => 'Activité non trouvée'], 404);
            }

            return response()->json(['data' => $event]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération de l\'événement: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération de l\'événement'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return response()->json(['message' => 'Activité non trouvée'], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'required|date',
                'event_type' => 'required|in:Webinar,Conference,Sport Event,Workshop',
                'capacity' => 'required|integer',
                'location' => 'nullable|string|max:255',
                'company_id' => 'required|exists:company,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $event->update($request->all());

            return response()->json([
                'message' => 'Activité mise à jour avec succès',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la mise à jour de l\'événement: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la mise à jour de l\'événement'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return response()->json(['message' => 'Activité non trouvée'], 404);
            }

            $event->delete();

            return response()->json(['message' => 'Activité supprimée avec succès']);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la suppression de l\'événement: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la suppression de l\'événement'], 500);
        }
    }

    public function getByCompany($companyId)
    {
        try {
            $company = Company::find($companyId);

            if (!$company) {
                return response()->json(['message' => 'Entreprise non trouvée'], 404);
            }

            $events = Event::where('company_id', $companyId)->get();

            return response()->json(['data' => $events]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des événements par entreprise: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération des événements'], 500);
        }
    }

    public function getRegisteredEmployees($id)
    {
        try {
            $event = Event::find($id);

            if (!$event) {
                return response()->json(['message' => 'Activité non trouvée'], 404);
            }

            $employees = $event->registeredEmployees;

            return response()->json(['data' => $employees]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des employés inscrits: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération des employés inscrits'], 500);
        }
    }
}
