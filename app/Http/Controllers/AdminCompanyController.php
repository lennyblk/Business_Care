<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;

class AdminCompanyController extends Controller
{
    /**
     * Affiche la liste des entreprises.
     */
    public function index()
    {
        $companies = Company::all();
        return view('dashboards.gestion_admin.admin_company', compact('companies'));
    }

    /**
     * Affiche le formulaire de création d'une entreprise.
     */
    public function create()
    {
        return view('dashboards.gestion_admin.admin_company_create');
    }

    /**
     * Enregistre une nouvelle entreprise.
     */
    public function store(Request $request)
    {
        $company = new Company($request->all());
        $company->save();
        return redirect()->route('admin.company');
    }

    /**
     * Affiche les détails d'une entreprise.
     */
    public function show($id)
    {
        $company = Company::findOrFail($id);
        return view('dashboards.gestion_admin.admin_company_show', compact('company'));
    }

    /**
     * Affiche le formulaire de modification d'une entreprise.
     */
    public function edit($id)
    {
        $company = Company::findOrFail($id);
        return view('dashboards.gestion_admin.admin_company_edit', compact('company'));
    }

    /**
     * Met à jour les informations d'une entreprise.
     */
    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        $company->update($request->all());
        return redirect()->route('admin.company');
    }

    /**
     * Supprime une entreprise.
     */
    public function destroy($id)
    {
        $company = Company::findOrFail($id);
        $company->delete();
        return redirect()->route('admin.company');
    }
}
