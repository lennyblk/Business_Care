<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;

class AdminActivityController extends Controller
{
    /**
     * Affiche la liste des activités.
     */
    public function index()
    {
        $events = Event::all();
        return view('dashboards.gestion_admin.activites.index', compact('events'));
    }

    /**
     * Affiche le formulaire de création d'une activité.
     */
    public function create()
    {
        return view('dashboards.gestion_admin.activites.create');
    }

    /**
     * Enregistre une nouvelle activité.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'event_type' => 'required|in:Webinar,Conference,Sport Event,Workshop',
            'capacity' => 'required|integer',
            'location' => 'nullable|string|max:255',
        ]);

        Event::create($request->all());
        return redirect()->route('admin.activities.index')->with('success', 'Activité créée avec succès.');
    }

    /**
     * Affiche les détails d'une activité.
     */
    public function show($id)
    {
        $event = Event::findOrFail($id);
        return view('dashboards.gestion_admin.activites.show', compact('event'));
    }

    /**
     * Affiche le formulaire de modification d'une activité.
     */
    public function edit($id)
    {
        $event = Event::findOrFail($id);
        return view('dashboards.gestion_admin.activites.edit', compact('event'));
    }

    /**
     * Met à jour les informations d'une activité.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'event_type' => 'required|in:Webinar,Conference,Sport Event,Workshop',
            'capacity' => 'required|integer',
            'location' => 'nullable|string|max:255',
        ]);

        $event = Event::findOrFail($id);
        $event->update($request->all());
        return redirect()->route('admin.activities.index')->with('success', 'Activité mise à jour avec succès.');
    }

    /**
     * Supprime une activité.
     */
    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();
        return redirect()->route('admin.activities.index')->with('success', 'Activité supprimée avec succès.');
    }
}
