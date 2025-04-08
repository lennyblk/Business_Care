<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Provider;
use App\Models\ProviderAvailability;
use App\Models\ServiceEvaluation;
use App\Models\ProviderInvoice;

class AdminProviderController extends Controller
{

    public function index()
    {
        $prestataires = Provider::all();
        return view('dashboards.gestion_admin.prestataires.index', compact('prestataires'));
    }

    public function create()
    {
        return view('dashboards.gestion_admin.prestataires.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'last_name' => 'nullable|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'description' => 'required|string',
            'domains' => 'required|string',
            'email' => 'required|email|unique:provider,email',
            'telephone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'adresse' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:100',
            'siret' => 'nullable|string|max:14',
            'iban' => 'nullable|string|max:34',
            'statut_prestataire' => 'required|in:Candidat,Validé,Inactif',
            'tarif_horaire' => 'nullable|numeric',
        ]);

        Provider::create($request->all());
        return redirect()->route('admin.prestataires.index')->with('success', 'Prestataire créé avec succès.');
    }

    public function show($id)
    {
        $prestataire = Provider::findOrFail($id);
        $disponibilites = ProviderAvailability::where('id', $id)->get();
        $evaluations = ServiceEvaluation::where('id', $id)->get();
        $factures = ProviderInvoice::where('id', $id)->get();

        return view('dashboards.gestion_admin.prestataires.show', compact('prestataire', 'disponibilites', 'evaluations', 'factures'));
    }

    public function edit($id)
    {
        $prestataire = Provider::findOrFail($id);
        return view('dashboards.gestion_admin.prestataires.edit', compact('prestataire'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'last_name' => 'nullable|string|max:100',
            'first_name' => 'nullable|string|max:100',
            'description' => 'required|string',
            'domains' => 'required|string',
            'email' => 'required|email|unique:provider,email,' . $id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'code_postal' => 'nullable|string|max:10',
            'ville' => 'nullable|string|max:100',
            'siret' => 'nullable|string|max:14',
            'iban' => 'nullable|string|max:34',
            'statut_prestataire' => 'required|in:Candidat,Validé,Inactif',
            'tarif_horaire' => 'nullable|numeric',
        ]);

        $prestataire = Provider::findOrFail($id);
        $prestataire->update($request->all());
        return redirect()->route('admin.prestataires.index')->with('success', 'Prestataire mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $prestataire = Provider::findOrFail($id);
        $prestataire->delete();
        return redirect()->route('admin.prestataires.index')->with('success', 'Prestataire supprimé avec succès.');
    }
}
