<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{

    public function index()
    {
        $employees = Employee::with('company')->get();
        return response()->json(['data' => $employees]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:company,id',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:employee,email',
            'telephone' => 'nullable|string|max:20',
            'position' => 'required|string|max:100',
            'departement' => 'nullable|string|max:100',
            'password' => 'required|string|min:8',
            'preferences_langue' => 'nullable|string|max:10',
            'id_carte_nfc' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        $data['password'] = Hash::make($data['password']);
        $data['date_creation_compte'] = now();

        $employee = Employee::create($data);

        return response()->json([
            'message' => 'Employé créé avec succès',
            'data' => $employee
        ], 201);
    }

    public function show($id)
    {
        $employee = Employee::with('company')->find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employé non trouvé'], 404);
        }

        return response()->json(['data' => $employee]);
    }

    public function update(Request $request, $id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employé non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:company,id',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:employee,email,' . $id,
            'telephone' => 'nullable|string|max:20',
            'position' => 'required|string|max:100',
            'departement' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:8',
            'preferences_langue' => 'nullable|string|max:10',
            'id_carte_nfc' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if (isset($data['password']) && !empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $employee->update($data);

        return response()->json([
            'message' => 'Employé mis à jour avec succès',
            'data' => $employee
        ]);
    }

    public function destroy($id)
    {
        $employee = Employee::find($id);

        if (!$employee) {
            return response()->json(['message' => 'Employé non trouvé'], 404);
        }

        $employee->delete();

        return response()->json(['message' => 'Employé supprimé avec succès']);
    }

    public function getByCompany($companyId)
    {
        $company = Company::find($companyId);

        if (!$company) {
            return response()->json(['message' => 'Entreprise non trouvée'], 404);
        }

        $employees = Employee::where('company_id', $companyId)->get();

        return response()->json(['data' => $employees]);
    }
}
