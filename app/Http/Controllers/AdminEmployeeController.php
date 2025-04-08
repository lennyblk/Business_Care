<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;

class AdminEmployeeController extends Controller
{

    public function index()
    {
        $employees = Employee::all();
        return view('dashboards.gestion_admin.salaries.index', compact('employees'));
    }


    public function create()
    {
    $companies = \App\Models\Company::all();
    return view('dashboards.gestion_admin.salaries.create', compact('companies'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'nullable|exists:company,id',
            'last_name' => 'required|string|max:50',
            'first_name' => 'required|string|max:50',
            'email' => 'required|email|unique:employee,email',
            'telephone' => 'nullable|string|max:20',
            'position' => 'required|string|max:100',
            'departement' => 'nullable|string|max:100',
            'password' => 'required|string|min:8',
            'preferences_langue' => 'nullable|string|max:10',
            'id_carte_nfc' => 'nullable|string|max:50',
        ]);

        Employee::create($request->all());
        return redirect()->route('admin.salaries.index')->with('success', 'Salarié créé avec succès.');
    }


    public function show($id)
    {
        $employee = Employee::findOrFail($id);
        return view('dashboards.gestion_admin.salaries.show', compact('employee'));
    }

    public function edit($id)
    {
        $employee = Employee::findOrFail($id);
        return view('dashboards.gestion_admin.salaries.edit', compact('employee'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'last_name' => 'required|string|max:50',
            'first_name' => 'required|string|max:50',
            'email' => 'required|email|unique:employee,email,' . $id,
            'telephone' => 'nullable|string|max:20',
            'position' => 'required|string|max:100',
            'departement' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:8',
            'preferences_langue' => 'nullable|string|max:10',
            'id_carte_nfc' => 'nullable|string|max:50',
        ]);

        $employee = Employee::findOrFail($id);
        $data = $request->all();
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $employee->timestamps = false;
        $employee->update($data);
        return redirect()->route('admin.salaries.index')->with('success', 'Salarié mis à jour avec succès.');
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return redirect()->route('admin.salaries.index')->with('success', 'Salarié supprimé avec succès.');
    }
}
