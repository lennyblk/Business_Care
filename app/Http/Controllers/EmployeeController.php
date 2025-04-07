<?php
namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Employee;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class EmployeeController extends Controller

{
    public function index()
    {
        $userType = session('user_type');

        if ($userType === 'employe') {
            // Récupérer l'ID de l'employé depuis la session
            $userId = (int)session('user_id');

            // Récupérer l'employé par son ID
            $employee = Employee::find($userId);

            // Si l'employé n'est pas trouvé par son ID, forcer un employé de company_id = 2
            if (!$employee) {
                $employee = Employee::where('company_id', 2)->first();
            }

            if ($employee) {
                $companyId = $employee->company_id;

                // Récupérer les événements auxquels l'employé est inscrit
                $myEventIds = EventRegistration::where('employee_id', $employee->id)
                            ->pluck('event_id')
                            ->toArray();

                // Récupérer uniquement les événements pour CETTE entreprise
                $allEvents = Event::where('company_id', $companyId)
                            ->whereNotIn('id', $myEventIds)
                            ->get();

                // Récupérer les événements auxquels l'employé est inscrit
                $myEvents = Event::whereIn('id', $myEventIds)->get();

                return view('dashboards.employee.events.index', compact('allEvents', 'myEvents', 'employee'));
            }
        }

        return redirect()->route('login')->withErrors(['error' => 'Impossible de déterminer l\'employé connecté.']);
    }

    public function register(Request $request, $id)
    {
        $userType = session('user_type');

        if ($userType === 'employe') {
            // Récupérer l'ID de l'employé depuis la session
            $userId = (int)session('user_id');

            // Récupérer l'employé par son ID
            $employee = Employee::find($userId);

            // Si l'employé n'est pas trouvé par son ID, forcer un employé de company_id = 2
            if (!$employee) {
                $employee = Employee::where('company_id', 2)->first();
            }

            if ($employee) {
                $companyId = $employee->company_id;

                // Vérifier que l'événement existe et appartient à l'entreprise
                $event = Event::where('id', $id)
                        ->where('company_id', $companyId)
                        ->first();

                if (!$event) {
                    return redirect()->route('employee.events.index')
                        ->withErrors(['error' => 'Événement non trouvé.']);
                }

                // Vérifier si l'employé est déjà inscrit
                $existingRegistration = EventRegistration::where('event_id', $id)
                                        ->where('employee_id', $employee->id)
                                        ->first();

                if ($existingRegistration) {
                    return redirect()->route('employee.events.index')
                        ->with('warning', 'Vous êtes déjà inscrit à cet événement.');
                }

                // Vérifier si l'événement n'est pas déjà complet
                if ($event->registrations >= $event->capacity) {
                    return redirect()->route('employee.events.index')
                        ->with('warning', 'Cet événement est complet.');
                }

                // Créer une nouvelle inscription
                EventRegistration::create([
                    'event_id' => $id,
                    'employee_id' => $employee->id,
                    'registration_date' => now(),
                    'status' => 'confirmed'
                ]);

                // Mettre à jour le compteur d'inscriptions de l'événement
                $event->registrations = ($event->registrations ?? 0) + 1;
                $event->save();

                return redirect()->route('employee.events.index')
                    ->with('success', 'Vous êtes maintenant inscrit à cet événement.');
            }
        }

        return redirect()->route('login')
            ->withErrors(['error' => 'Impossible de déterminer l\'employé connecté.']);
    }

    public function cancelRegistration($id)
    {
        $userType = session('user_type');

        if ($userType === 'employe') {
            // Récupérer l'ID de l'employé depuis la session
            $userId = (int)session('user_id');

            // Récupérer l'employé par son ID
            $employee = Employee::find($userId);

            // Si l'employé n'est pas trouvé par son ID, forcer un employé de company_id = 2
            if (!$employee) {
                $employee = Employee::where('company_id', 2)->first();
            }

            if ($employee) {
                // Chercher l'inscription
                $registration = EventRegistration::where('event_id', $id)
                                ->where('employee_id', $employee->id)
                                ->first();

                if (!$registration) {
                    return redirect()->route('employee.events.index')
                        ->with('warning', 'Vous n\'êtes pas inscrit à cet événement.');
                }

                // Supprimer l'inscription
                $registration->delete();

                // Mettre à jour le compteur d'inscriptions
                $event = Event::find($id);
                if ($event) {
                    $event->registrations = max(0, ($event->registrations ?? 0) - 1);
                    $event->save();
                }

                return redirect()->route('employee.events.index')
                    ->with('success', 'Votre inscription a été annulée.');
            }
        }

        return redirect()->route('login')
            ->withErrors(['error' => 'Impossible de déterminer l\'employé connecté.']);
    }
}
