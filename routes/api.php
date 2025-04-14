<?php
// routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\ProviderController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\ContractController;
use App\Http\Controllers\API\AdminPendingRegistrationController;
use App\Http\Controllers\API\PendingRegistrationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Route de test pour s'assurer que l'API fonctionne
Route::get('/test', function() {
    return response()->json(['message' => 'API fonctionne correctement']);
});

// Routes d'authentification
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [PendingRegistrationController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
});

// Routes pour les entreprises
Route::apiResource('companies', CompanyController::class);
Route::get('companies', [CompanyController::class, 'index']);
Route::get('companies/{id}', [CompanyController::class, 'show']);
Route::get('companies/{id}/employees', [CompanyController::class, 'getEmployees']);
Route::get('companies/{id}/contracts', [CompanyController::class, 'getContracts']);
Route::put('companies/{id}', [CompanyController::class, 'update']);
Route::delete('companies/{id}', [CompanyController::class, 'destroy']);

// Routes pour les salariés
Route::apiResource('employees', EmployeeController::class);
Route::get('employees/by-company/{companyId}', [EmployeeController::class, 'getByCompany']);

// Routes pour les prestataires
Route::apiResource('providers', ProviderController::class);
Route::get('providers/{id}/availabilities', [ProviderController::class, 'getAvailabilities']);
Route::get('providers/{id}/evaluations', [ProviderController::class, 'getEvaluations']);
Route::get('providers/{id}/invoices', [ProviderController::class, 'getInvoices']);

// Routes pour les événements/activités
Route::apiResource('events', EventController::class);

// Routes pour les contrats
Route::apiResource('contracts', ContractController::class);
Route::get('contracts/by-company/{companyId}', [ContractController::class, 'getByCompany']);


