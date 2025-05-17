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
                'employee_id' => 'required|exists:employee,id',
                'event_id' => 'required|exists:event,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $event = Event::find($request->event_id);
            if ($event->registeredEmployees()->where('employee_id', $request->employee_id)->exists()) {
                return response()->json(['message' => 'Vous êtes déjà inscrit à cet événement.'], 409);
            }

            $event->registeredEmployees()->attach($request->employee_id);

            return response()->json(['message' => 'Inscription réussie.'], 201);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de l\'inscription à l\'événement: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de l\'inscription à l\'événement.'], 500);
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

    public function destroy(Request $request, $id)
    {
        try {
            $employeeId = $request->input('employee_id');

            $event = Event::find($id);
            if (!$event) {
                return response()->json(['message' => 'Événement non trouvé'], 404);
            }

            $isRegistered = $event->registeredEmployees()->where('employee_id', $employeeId)->exists();
            if (!$isRegistered) {
                return response()->json(['message' => 'Inscription non trouvée'], 404);
            }

            $event->registeredEmployees()->detach($employeeId);

            return response()->json(['message' => 'Inscription annulée avec succès'], 200);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de l\'annulation de l\'inscription: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de l\'annulation de l\'inscription'], 500);
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

    
    private function arrayToObject($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        $object = new \stdClass();
        foreach ($array as $key => $value) {
            $object->$key = is_array($value) ? $this->arrayToObject($value) : $value;
        }
        return $object;
    }

    public function getRegisteredEmployees($employeeId)
    {
        try {
            // Retrieve events where the employee is registered
            $events = Event::whereHas('registeredEmployees', function ($query) use ($employeeId) {
                $query->where('employee_id', $employeeId);
            })->get();

            // Convert events to objects
            $events = $events->map(function ($event) {
                return $this->arrayToObject($event->toArray());
            });

            return response()->json(['data' => $events]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des événements inscrits: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération des événements inscrits'], 500);
        }
    }

    public function getHistory($employeeId)
    {
        try {
            $events = Event::whereHas('registrations', function($query) use ($employeeId) {
                $query->where('employee_id', $employeeId);
            })
            ->where('date', '<', now())
            ->with(['serviceEvaluations' => function($query) use ($employeeId) {
                $query->where('employee_id', $employeeId);
            }])
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($event) use ($employeeId) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'description' => $event->description,
                    'date' => $event->date,
                    'location' => $event->location,
                    'hasEvaluation' => $event->serviceEvaluations->isNotEmpty()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        } catch (\Exception $e) {
            Log::error('API: Erreur dans getHistory: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }
}
