<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Event;
use App\Models\EventRegistration;
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

    // Nouvelles méthodes pour les événements
    public function getCurrentEmployee($userId = null, $userEmail = null)
    {
        try {
            $employee = null;

            // D'abord, essayer avec l'ID
            if ($userId && $userId > 0) {
                $employee = Employee::find($userId);
            }

            // Si aucun employé n'est trouvé avec l'ID, on essaye avec l'email
            if (!$employee && $userEmail) {
                $employee = Employee::where('email', $userEmail)->first();
            }

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
            Log::error('EmployeeController@getCurrentEmployee - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'employé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getEmployeeEvents($employeeId)
    {
        try {
            $employee = Employee::find($employeeId);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employé non trouvé'
                ], 404);
            }

            $companyId = $employee->company_id;

            // On récupère les événements auxquels l'employé est inscrit
            $myEventIds = EventRegistration::where('employee_id', $employeeId)
                          ->pluck('event_id')
                          ->toArray();

            // On récupère uniquement les événements pour l'entreprise de cet employé
            $allEvents = Event::where('company_id', $companyId)
                        ->whereNotIn('id', $myEventIds)
                        ->get();

            // On récupère les événements auxquels l'employé est inscrit
            $myEvents = Event::whereIn('id', $myEventIds)->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'allEvents' => $allEvents,
                    'myEvents' => $myEvents,
                    'employee' => $employee
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('EmployeeController@getEmployeeEvents - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des événements',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function registerForEvent(Request $request, $employeeId, $eventId)
    {
        try {
            $employee = Employee::find($employeeId);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employé non trouvé'
                ], 404);
            }

            $companyId = $employee->company_id;

            // On vérifie que l'événement existe et appartient à l'entreprise
            $event = Event::where('id', $eventId)
                    ->where('company_id', $companyId)
                    ->first();

            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Événement non trouvé ou non autorisé pour votre entreprise'
                ], 404);
            }

            // On vérifie si l'employé est déjà inscrit
            $existingRegistration = EventRegistration::where('event_id', $eventId)
                                    ->where('employee_id', $employeeId)
                                    ->first();

            if ($existingRegistration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous êtes déjà inscrit à cet événement'
                ], 422);
            }

            // On vérifie si l'événement n'est pas déjà complet
            if ($event->registrations >= $event->capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cet événement est complet'
                ], 422);
            }

            // On crée une nouvelle inscription
            $registration = EventRegistration::create([
                'event_id' => $eventId,
                'employee_id' => $employeeId,
                'registration_date' => now(),
                'status' => 'confirmed'
            ]);

            // On met à jour le compteur d'inscriptions de l'événement
            $event->registrations = ($event->registrations ?? 0) + 1;
            $event->save();

            return response()->json([
                'success' => true,
                'message' => 'Inscription à l\'événement réussie',
                'data' => $registration
            ]);

        } catch (\Exception $e) {
            Log::error('EmployeeController@registerForEvent - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'inscription à l\'événement',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cancelEventRegistration($employeeId, $eventId)
    {
        try {
            $employee = Employee::find($employeeId);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employé non trouvé'
                ], 404);
            }

            // On cherche l'inscription
            $registration = EventRegistration::where('event_id', $eventId)
                            ->where('employee_id', $employeeId)
                            ->first();

            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas inscrit à cet événement'
                ], 404);
            }

            // Supprimer l'inscription
            $registration->delete();

            // Mettre à jour le compteur d'inscriptions
            $event = Event::find($eventId);
            if ($event) {
                $event->registrations = max(0, ($event->registrations ?? 0) - 1);
                $event->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Inscription à l\'événement annulée avec succès'
            ]);

        } catch (\Exception $e) {
            Log::error('EmployeeController@cancelEventRegistration - ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation de l\'inscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
