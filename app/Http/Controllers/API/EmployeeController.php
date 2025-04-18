<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function index()
    {
        try {
            $employees = Employee::with('company')->get();
            return response()->json([
                'success' => true,
                'data' => $employees
            ]);
        } catch (\Exception $e) {
            Log::error('EmployeeController@index - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des employés',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => 'required|exists:company,id',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'email' => 'required|email|unique:employee,email',
                'telephone' => 'nullable|string|max:20|regex:/^[0-9\+\-\s]+$/',
                'position' => 'required|string|max:100',
                'departement' => 'nullable|string|max:100',
                'password' => 'required|string|min:8|confirmed',
                'preferences_langue' => 'nullable|string|in:fr,en,es,de',
                'id_carte_nfc' => 'nullable|string|max:50|unique:employee,id_carte_nfc',
            ], [
                'password.confirmed' => 'La confirmation du mot de passe ne correspond pas',
                'telephone.regex' => 'Le format du téléphone est invalide',
                'id_carte_nfc.unique' => 'Cette carte NFC est déjà attribuée à un autre employé'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();
            $data['password'] = Hash::make($data['password']);
            $data['date_creation_compte'] = now();

            $employee = Employee::create($data);

            Log::info('Nouvel employé créé: ' . $employee->id);

            return response()->json([
                'success' => true,
                'message' => 'Employé créé avec succès',
                'data' => $employee
            ], 201);
        } catch (\Exception $e) {
            Log::error('EmployeeController@store - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'employé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $employee = Employee::with('company')->find($id);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employé non trouvé'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $employee
            ]);
        } catch (\Exception $e) {
            Log::error('EmployeeController@show - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'employé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employé non trouvé'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'company_id' => 'required|exists:company,id',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'email' => 'required|email|unique:employee,email,' . $id,
                'telephone' => 'nullable|string|max:20|regex:/^[0-9\+\-\s]+$/',
                'position' => 'required|string|max:100',
                'departement' => 'nullable|string|max:100',
                'password' => 'nullable|string|min:8|confirmed',
                'preferences_langue' => 'nullable|string|in:fr,en,es,de',
                'id_carte_nfc' => 'nullable|string|max:50|unique:employee,id_carte_nfc,' . $id,
            ], [
                'telephone.regex' => 'Le format du téléphone est invalide',
                'id_carte_nfc.unique' => 'Cette carte NFC est déjà attribuée à un autre employé'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $data = $request->all();

            if (isset($data['password']) && !empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $employee->update($data);

            Log::info('Employé mis à jour: ' . $employee->id);

            return response()->json([
                'success' => true,
                'message' => 'Employé mis à jour avec succès',
                'data' => $employee
            ]);
        } catch (\Exception $e) {
            Log::error('EmployeeController@update - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'employé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $employee = Employee::find($id);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employé non trouvé'
                ], 404);
            }

            $employee->delete();

            Log::info('Employé supprimé: ' . $id);

            return response()->json([
                'success' => true,
                'message' => 'Employé supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('EmployeeController@destroy - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'employé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getByCompany($companyId)
    {
        try {
            $company = Company::find($companyId);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Entreprise non trouvée'
                ], 404);
            }

            $employees = Employee::where('company_id', $companyId)->get();

            return response()->json([
                'success' => true,
                'data' => $employees
            ]);
        } catch (\Exception $e) {
            Log::error('EmployeeController@getByCompany - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des employés',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
