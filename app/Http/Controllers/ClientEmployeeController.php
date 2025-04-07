<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\NewEmployeeNotification;

class EmployeeController extends Controller
{
    /**
     * Affiche la liste des collaborateurs de la société
     */
    public function index()
    {
        $user = Auth::user();
        $company = $user->company;
        
        if (!$company) {
            return redirect()->route('dashboard.client')
                ->with('error', 'Vous devez être associé à une société pour accéder à cette page.');
        }
        
        $employees = $company->employees()->paginate(10);
        
        return view('dashboards.client.employees.index', compact('employees'));
    }

    /**
     * Affiche le formulaire de création de collaborateur
     */
    public function create()
    {
        return view('dashboards.client.employees.create');
    }

    /**
     * Enregistre un nouveau collaborateur
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);
        
        $user = Auth::user();
        $company = $user->company;
        
        if (!$company) {
            return redirect()->route('dashboard.client')
                ->with('error', 'Vous devez être associé à une société pour ajouter un collaborateur.');
        }
        
        $employee = new Employee([
            'name' => $request->name,
            'email' => $request->email,
            'position' => $request->position,
            'phone' => $request->phone,
            'is_active' => true,
        ]);
        
        $company->employees()->save($employee);
        
        // Enregistrement de l'activité
        Activity::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'title' => 'Nouveau collaborateur',
            'description' => 'Collaborateur ' . $employee->name . ' a été ajouté',
            'type' => 'employee',
            'subject_type' => Employee::class,
            'subject_id' => $employee->id,
        ]);
        
        // Notification aux administrateurs
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new NewEmployeeNotification($employee, $company));
        }
        
        return redirect()->route('employees.index')
            ->with('success', 'Collaborateur ajouté avec succès.');
    }

    /**
     * Affiche les détails d'un collaborateur
     */
    public function show(Employee $employee)
    {
        $this->checkEmployeeOwnership($employee);
        
        return view('dashboards.client.employees.show', compact('employee'));
    }

    /**
     * Affiche le formulaire d'édition d'un collaborateur
     */
    public function edit(Employee $employee)
    {
        $this->checkEmployeeOwnership($employee);
        
        return view('dashboards.client.employees.edit', compact('employee'));
    }

    /**
     * Met à jour les informations d'un collaborateur
     */
    public function update(Request $request, Employee $employee)
    {
        $this->checkEmployeeOwnership($employee);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email,' . $employee->id,
            'position' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);
        
        $employee->name = $request->name;
        $employee->email = $request->email;
        $employee->position = $request->position;
        $employee->phone = $request->phone;
        $employee->save();
        
        // Enregistrement de l'activité
        Activity::create([
            'company_id' => $employee->company_id,
            'user_id' => Auth::id(),
            'title' => 'Collaborateur modifié',
            'description' => 'Les informations du collaborateur ' . $employee->name . ' ont été mises à jour',
            'type' => 'employee',
            'subject_type' => Employee::class,
            'subject_id' => $employee->id,
        ]);
        
        return redirect()->route('employees.show', $employee)
            ->with('success', 'Collaborateur mis à jour avec succès.');
    }

    /**
     * Désactive ou supprime un collaborateur
     */
    public function destroy(Employee $employee)
    {
        $this->checkEmployeeOwnership($employee);
        
        // Au lieu de supprimer, on désactive le collaborateur
        $employee->is_active = false;
        $employee->save();
        
        // Enregistrement de l'activité
        Activity::create([
            'company_id' => $employee->company_id,
            'user_id' => Auth::id(),
            'title' => 'Collaborateur désactivé',
            'description' => 'Le collaborateur ' . $employee->name . ' a été désactivé',
            'type' => 'employee',
            'subject_type' => Employee::class,
            'subject_id' => $employee->id,
        ]);
        
        return redirect()->route('employees.index')
            ->with('success', 'Collaborateur désactivé avec succès.');
    }

    /**
     * Vérifie que le collaborateur appartient bien à la société de l'utilisateur connecté
     */
    private function checkEmployeeOwnership(Employee $employee)
    {
        $user = Auth::user();
        
        if (!$user->company || $employee->company_id !== $user->company_id) {
            abort(403, 'Vous n\'êtes pas autorisé à accéder à ce collaborateur.');
        }
    }
}