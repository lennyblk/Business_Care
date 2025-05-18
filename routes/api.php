<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\ProviderController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\ContractController;
use App\Http\Controllers\API\AdminContractController;
use App\Http\Controllers\API\AdminPendingRegistrationController;
use App\Http\Controllers\API\PendingRegistrationController;
use App\Http\Controllers\API\AdviceCategoryController;
use App\Http\Controllers\API\AdviceTagController;
use App\Http\Controllers\API\AdviceFeedbackController;
use App\Http\Controllers\AdminContract2Controller;
use App\Http\Controllers\API\ProviderEvaluationController;
use App\Http\Controllers\AssociationApiController;

Route::get('/test', function() {
    return response()->json(['message' => 'API fonctionne correctement']);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [PendingRegistrationController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
});

// Routes pour les entreprises
Route::apiResource('companies', CompanyController::class);
Route::prefix('companies')->group(function () {
    Route::get('{id}/employees', [CompanyController::class, 'getEmployees']);
    Route::get('{id}/contracts', [CompanyController::class, 'getContracts']);
    Route::get('{id}/events', [EventController::class, 'getByCompany']);
    Route::patch('{id}/update-contract-end-date', [CompanyController::class, 'updateContractEndDate']);
    Route::patch('{id}/update-contract-info', [CompanyController::class, 'updateContractInfo']);
});

// Routes pour les contrats
Route::apiResource('contracts', ContractController::class);
Route::get('contracts/by-company/{companyId}', [ContractController::class, 'getByCompany']);
// Nouvelle route pour résiliation de contrat
Route::post('contracts/{id}/terminate', [ContractController::class, 'terminate']);

// Routes pour les employés
Route::apiResource('employees', EmployeeController::class);
Route::prefix('employees')->group(function () {
    Route::get('by-company/{companyId}', [EmployeeController::class, 'getByCompany']);
    Route::post('{id}/change-password', [EmployeeController::class, 'changePassword']);
});

// Routes pour les prestataires
Route::apiResource('providers', ProviderController::class);
Route::prefix('providers')->group(function () {
    Route::get('{id}/availabilities', [ProviderController::class, 'getAvailabilities']);
    Route::get('{id}/evaluations', [ProviderController::class, 'getEvaluations']);
    Route::get('{id}/invoices', [ProviderController::class, 'getInvoices']);
});

Route::prefix('provider')->group(function () {
    Route::get('/{id}/evaluations', [ProviderEvaluationController::class, 'getProviderEvaluations']);
});

// Routes pour les événements
Route::apiResource('events', EventController::class);
Route::prefix('events')->group(function () {
    Route::get('{id}/employees', [EventController::class, 'getRegisteredEmployees']);
    Route::post('{id}/register', [EventController::class, 'registerEmployee']);
    Route::post('{id}/unregister', [EventController::class, 'unregisterEmployee']);
});

// Routes pour les événements
Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/company/{companyId}', [EventController::class, 'getByCompany']);
    Route::get('/registered/{employeeId}', [EventController::class, 'getRegisteredEmployees']);
    Route::post('/register', [EventController::class, 'store']);
    Route::delete('/{id}', [EventController::class, 'destroy']);
    Route::get('/history/{employeeId}', [EventController::class, 'getHistory']);
});

// Routes d'administration
Route::prefix('admin')->group(function () {
    Route::apiResource('pending-registrations', AdminPendingRegistrationController::class)->only(['index', 'show']);
    Route::post('pending-registrations/{id}/approve', [AdminPendingRegistrationController::class, 'approve']);
    Route::post('pending-registrations/{id}/reject', [AdminPendingRegistrationController::class, 'reject']);

    // Nouvelles routes admin pour la gestion des employés
    Route::get('employees', [EmployeeController::class, 'adminIndex']);
    Route::get('employees/{id}', [EmployeeController::class, 'adminShow']);

    // Routes pour la gestion des contrats
    Route::get('contracts/pending', [AdminContractController::class, 'getPendingContracts']);
    Route::post('contracts/{id}/approve', [AdminContractController::class, 'approveContract']);
    Route::post('contracts/{id}/reject', [AdminContractController::class, 'rejectContract']);
});

// Nouvelle route pour la réinitialisation de mot de passe
Route::post('password/reset', [AuthController::class, 'resetPassword']);
Route::post('password/forgot', [AuthController::class, 'sendResetLinkEmail']);

Route::prefix('employees')->group(function () {
    // Routes existantes
    Route::get('/', [EmployeeController::class, 'index']);
    Route::post('/', [EmployeeController::class, 'store']);
    Route::get('/{id}', [EmployeeController::class, 'show']);
    Route::put('/{id}', [EmployeeController::class, 'update']);
    Route::delete('/{id}', [EmployeeController::class, 'destroy']);
    Route::get('/company/{companyId}', [EmployeeController::class, 'getByCompany']);

    // Nouvelles routes
    Route::get('/current', [EmployeeController::class, 'getCurrentEmployee']);
    Route::get('/{id}/events', [EmployeeController::class, 'getEmployeeEvents']);
    Route::post('/{employeeId}/events/{eventId}/register', [EmployeeController::class, 'registerForEvent']);
    Route::delete('/{employeeId}/events/{eventId}/cancel', [EmployeeController::class, 'cancelEventRegistration']);
});

// Routes pour les propositions d'activités
Route::prefix('event-proposals')->group(function () {
    Route::get('/', [App\Http\Controllers\API\EventProposalController::class, 'index']);
    Route::post('/', [App\Http\Controllers\API\EventProposalController::class, 'store']);
    Route::get('/form-data', [App\Http\Controllers\API\EventProposalController::class, 'getFormData']);
    Route::get('/company/{companyId}', [App\Http\Controllers\API\EventProposalController::class, 'getByCompany']);
    Route::get('/{id}', [App\Http\Controllers\API\EventProposalController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\API\EventProposalController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\API\EventProposalController::class, 'destroy']);
});

// Routes pour les assignations de prestataires
Route::prefix('provider-assignments')->group(function () {
    Route::get('/', [App\Http\Controllers\API\ProviderAssignmentController::class, 'index']);
    Route::post('/', [App\Http\Controllers\API\ProviderAssignmentController::class, 'store']);
    Route::get('/{id}', [App\Http\Controllers\API\ProviderAssignmentController::class, 'show']);
    Route::put('/{id}', [App\Http\Controllers\API\ProviderAssignmentController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\API\ProviderAssignmentController::class, 'destroy']);
    Route::get('/provider/{providerId}/status/{status}', [App\Http\Controllers\API\ProviderAssignmentController::class, 'getByProviderAndStatus']);
    Route::get('/provider/{providerId}/assignment/{id}', [App\Http\Controllers\API\ProviderAssignmentController::class, 'getByIdAndProvider']);
    Route::post('/{id}/accept/{providerId}', [App\Http\Controllers\API\ProviderAssignmentController::class, 'acceptAssignment']);
    Route::post('/{id}/reject/{providerId}', [App\Http\Controllers\API\ProviderAssignmentController::class, 'rejectAssignment']);
    Route::get('/event-proposal/{eventProposalId}', [App\Http\Controllers\API\ProviderAssignmentController::class, 'getByEventProposal']);
});

Route::prefix('profile')->group(function () {
    Route::get('/{id}/{userType}', [App\Http\Controllers\API\ProfileController::class, 'getProfile']);
    Route::put('/{id}/{userType}', [App\Http\Controllers\API\ProfileController::class, 'updateProfile']);
    Route::put('/{id}/{userType}/password', [App\Http\Controllers\API\ProfileController::class, 'updatePassword']);
});

Route::prefix('admin/contracts2')->group(function () {
    Route::get('/', [App\Http\Controllers\API\AdminContract2Controller::class, 'getAllContracts']);
    Route::get('/{id}', [App\Http\Controllers\API\AdminContract2Controller::class, 'getContract']);
});
Route::post('/admin/contracts2/{id}/mark-as-paid', [App\Http\Controllers\API\AdminContract2Controller::class, 'markAsPaid']);

// Association cote entreprises
Route::prefix('associations')->group(function () {
    Route::get('/', [App\Http\Controllers\API\AssociationApiController::class, 'index']);
    Route::get('/{id}', [App\Http\Controllers\API\AssociationApiController::class, 'show']);
    Route::post('/{id}/donate', [App\Http\Controllers\API\AssociationApiController::class, 'processDonation']);
});

// ROUTES POUR APP MOBILE ==========================================================================================================================

Route::get('/events', [EventController::class, 'index']);

// Route pour obtenir les événements de l'employé connecté
Route::get('/employee/events', function (Request $request) {
    // Pour le développement, utiliser un ID d'employé par défaut
    // Dans une version de production, extraire l'ID de l'employé du token
    $employeeId = $request->header('Employee-Id', 1);

    // Récupérer les événements de l'employé via le contrôleur existant
    $employee = \App\Models\Employee::find($employeeId);
    if (!$employee) {
        return response()->json([]);
    }

    $registeredEvents = \App\Models\EventRegistration::where('employee_id', $employeeId)
        ->with('event')
        ->get()
        ->map(function($registration) {
            $event = $registration->event;
            return [
                'id' => $event->id,
                'name' => $event->title ?? $event->name,
                'description' => $event->description,
                'date' => $event->date,
                'location' => $event->location
            ];
        });

    return response()->json($registeredEvents);
});

Route::post('/events/{id}/register', function (Request $request, $id) {
    $employeeId = $request->header('Employee-Id', 1);

    $event = \App\Models\Event::find($id);
    if (!$event) {
        return response()->json([
            'success' => false,
            'message' => 'Événement non trouvé'
        ], 404);
    }

    $existingRegistration = \App\Models\EventRegistration::where('event_id', $id)
        ->where('employee_id', $employeeId)
        ->first();

    if ($existingRegistration) {
        return response()->json([
            'success' => false,
            'message' => 'Vous êtes déjà inscrit à cet événement'
        ], 422);
    }

    \App\Models\EventRegistration::create([
        'event_id' => $id,
        'employee_id' => $employeeId,
        'registration_date' => now(),
        'status' => 'confirmed'
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Inscription réussie'
    ]);
});

Route::delete('/events/{id}/unregister', function (Request $request, $id) {
    $employeeId = $request->header('Employee-Id', 1);

    $registration = \App\Models\EventRegistration::where('event_id', $id)
        ->where('employee_id', $employeeId)
        ->first();

    if (!$registration) {
        return response()->json([
            'success' => false,
            'message' => 'Vous n\'êtes pas inscrit à cet événement'
        ], 404);
    }

    $registration->delete();

    return response()->json([
        'success' => true,
        'message' => 'Désinscription réussie'
    ]);
});

Route::get('/company/events', function (Request $request) {
    // Récupérer l'employé connecté via le token
    $employee = $request->user();

    if (!$employee) {
        return response()->json([]);
    }

    // Récupérer les événements de la compagnie de l'employé
    $events = \App\Models\Event::where('company_id', $employee->company_id)
        ->where('date', '>=', now()) // Seulement les événements futurs
        ->orderBy('date')
        ->get()
        ->map(function($event) {
            return [
                'id' => $event->id,
                'name' => $event->title ?? $event->name,
                'description' => $event->description,
                'date' => $event->date,
                'event_type' => $event->event_type,
                'location' => $event->location,
                'company_id' => $event->company_id,
                'registrations' => $event->registrations()->count()
            ];
        });

    return response()->json($events);
})->middleware('auth:api');

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Routes pour les conseils
Route::get('/advices', [\App\Http\Controllers\API\AdviceController::class, 'index'])->name('api.advices.index');
Route::get('/advices/{id}', [\App\Http\Controllers\API\AdviceController::class, 'show'])->name('api.advices.show');

Route::prefix('advices')->group(function () {
    Route::get('/', [\App\Http\Controllers\API\AdviceController::class, 'index']);
    Route::get('/formule/{formule}', [\App\Http\Controllers\API\AdviceController::class, 'getByFormule']);
    Route::get('/personalized', [\App\Http\Controllers\API\AdviceController::class, 'getPersonalizedAdvices']);
    Route::get('/categories', [\App\Http\Controllers\API\AdviceController::class, 'categories']);
    Route::get('/tags', [\App\Http\Controllers\API\AdviceController::class, 'tags']);
    Route::get('/{id}', [\App\Http\Controllers\API\AdviceController::class, 'show']);
    Route::get('/scheduled', [AdviceController::class, 'scheduled']);
    Route::post('/{id}/schedule', [AdviceController::class, 'saveSchedule']);
    Route::patch('/schedule/{id}/toggle', [AdviceController::class, 'toggleSchedule']);
});

// Routes pour les catégories de conseils
Route::prefix('advice-categories')->group(function () {
    Route::get('/', [AdviceCategoryController::class, 'index']);
    Route::post('/', [AdviceCategoryController::class, 'store']);
    Route::put('/{id}', [AdviceCategoryController::class, 'update']);
    Route::delete('/{id}', [AdviceCategoryController::class, 'destroy']);
});

// Routes pour les tags de conseils
Route::prefix('advice-tags')->group(function () {
    Route::get('/', [AdviceTagController::class, 'index']);
    Route::post('/', [AdviceTagController::class, 'store']);
    Route::delete('/{id}', [AdviceTagController::class, 'destroy']);
});

// Routes pour les feedbacks des conseils
Route::prefix('advice-feedback')->group(function () {
    Route::get('/advice/{id}', [AdviceFeedbackController::class, 'getByAdvice']);
    Route::post('/', [AdviceFeedbackController::class, 'store']);
});

// Routes pour les évaluations de service
Route::post('/service-evaluations/{event_id}', [App\Http\Controllers\API\ServiceEvaluationController::class, 'store']);
