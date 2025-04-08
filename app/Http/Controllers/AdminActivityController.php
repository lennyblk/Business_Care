<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Company;

class AdminActivityController extends Controller
{

    public function index()
    {
        $events = Event::with('company')->get(); 
        return view('dashboards.gestion_admin.activites.index', compact('events'));
    }


    public function create()
    {
        $companies = Company::all();
        return view('dashboards.gestion_admin.activites.create', compact('companies'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'event_type' => 'required|in:Webinar,Conference,Sport Event,Workshop',
            'capacity' => 'required|integer',
            'location' => 'nullable|string|max:255',
            'company_id' => 'required|exists:company,id',
        ]);

        Event::create($request->all());
        return redirect()->route('admin.activities.index')->with('success', 'Activité créée avec succès.');
    }

    public function show($id)
    {
        $event = Event::with('company')->findOrFail($id);
        return view('dashboards.gestion_admin.activites.show', compact('event'));
    }


    public function edit($id)
    {
        $event = Event::findOrFail($id);
        $companies = Company::all(); 
        return view('dashboards.gestion_admin.activites.edit', compact('event', 'companies'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
            'event_type' => 'required|in:Webinar,Conference,Sport Event,Workshop',
            'capacity' => 'required|integer',
            'location' => 'nullable|string|max:255',
            'company_id' => 'required|exists:company,id',
        ]);

        $event = Event::findOrFail($id);
        $event->update($request->all());
        return redirect()->route('admin.activities.index')->with('success', 'Activité mise à jour avec succès.');
    }


    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();
        return redirect()->route('admin.activities.index')->with('success', 'Activité supprimée avec succès.');
    }
}
