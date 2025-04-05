<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    /**
     * Récupère la liste de toutes les activités/événements
     */
    public function index()
    {
        $events = Event::all();
        return response()->json(['data' => $events]);
    }

    /**
     * Crée une nouvelle activité/événement
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'event_type' => 'required|in:Webinar,Conference,Sport Event,Workshop',
            'capacity' => 'required|integer',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = Event::create($request->all());

        return response()->json([
            'message' => 'Activité créée avec succès',
            'data' => $event
        ], 201);
    }

    /**
     * Récupère les détails d'une activité/événement
     */
    public function show($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Activité non trouvée'], 404);
        }

        return response()->json(['data' => $event]);
    }

    /**
     * Met à jour une activité/événement existante
     */
    public function update(Request $request, $id)
    {
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event->update($request->all());

        return response()->json([
            'message' => 'Activité mise à jour avec succès',
            'data' => $event
        ]);
    }

    /**
     * Supprime une activité/événement
     */
    public function destroy($id)
    {
        $event = Event::find($id);

        if (!$event) {
            return response()->json(['message' => 'Activité non trouvée'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Activité supprimée avec succès']);
    }
}
