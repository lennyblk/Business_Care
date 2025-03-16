<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AdminCompanyController;
use App\Http\Controllers\AdminProviderController;
use App\Http\Controllers\AdminEmployeeController; // Ajouter l'importation du contrôleur AdminEmployeeController

// Pages principales
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::get('/communities', [PageController::class, 'communities'])->name('communities');
Route::get('/contracts', [PageController::class, 'contracts'])->name('contracts');
Route::get('/events', [PageController::class, 'events'])->name('events');
Route::get('/medical', [PageController::class, 'medical'])->name('medical');
Route::get('/services', function () {
    return view('services');
})->name('services');
Route::get('/service/{id}', [ServiceController::class, 'show'])->name('service.show');

// Auth routes
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Dashboards
Route::middleware(['check.auth'])->group(function () {
    Route::get('/dashboard/client', [DashboardController::class, 'client'])->name('dashboard.client');
    Route::get('/dashboard/employee', [DashboardController::class, 'employee'])->name('dashboard.employee');
    Route::get('/dashboard/provider', [DashboardController::class, 'provider'])->name('dashboard.provider');
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
});

// Admin routes ---------------------------------------------------------
// Company
Route::middleware(['check.auth'])->group(function () {
    Route::get('/dashboard/gestion_admin/entreprises', [AdminCompanyController::class, 'index'])->name('admin.company');
    Route::get('/dashboard/gestion_admin/entreprises/create', [AdminCompanyController::class, 'create'])->name('admin.company.create');
    Route::post('/dashboard/gestion_admin/entreprises', [AdminCompanyController::class, 'store'])->name('admin.company.store');
    Route::get('/dashboard/gestion_admin/entreprises/{id}', [AdminCompanyController::class, 'show'])->name('admin.company.show');
    Route::get('/dashboard/gestion_admin/entreprises/{id}/edit', [AdminCompanyController::class, 'edit'])->name('admin.company.edit');
    Route::put('/dashboard/gestion_admin/entreprises/{id}', [AdminCompanyController::class, 'update'])->name('admin.company.update');
    Route::delete('/dashboard/gestion_admin/entreprises/{id}', [AdminCompanyController::class, 'destroy'])->name('admin.company.destroy');
    Route::get('/dashboard/gestion_admin/entreprises/{id}/contracts', [AdminCompanyController::class, 'contracts'])->name('admin.company.contracts');
});

// Prestaires
Route::middleware(['check.auth'])->group(function () {
    Route::prefix('dashboard/gestion_admin/prestataires')->group(function () {
        Route::get('/', [AdminProviderController::class, 'index'])->name('admin.prestataires.index');
        Route::get('/create', [AdminProviderController::class, 'create'])->name('admin.prestataires.create');
        Route::post('/', [AdminProviderController::class, 'store'])->name('admin.prestataires.store');
        Route::get('/{id}', [AdminProviderController::class, 'show'])->name('admin.prestataires.show');
        Route::get('/{id}/edit', [AdminProviderController::class, 'edit'])->name('admin.prestataires.edit');
        Route::put('/{id}', [AdminProviderController::class, 'update'])->name('admin.prestataires.update');
        Route::delete('/{id}', [AdminProviderController::class, 'destroy'])->name('admin.prestataires.destroy');
    });
});

// Employees
Route::middleware(['check.auth'])->group(function () {
    Route::prefix('dashboard/gestion_admin/salaries')->group(function () {
        Route::get('/', [AdminEmployeeController::class, 'index'])->name('admin.salaries.index');
        Route::get('/create', [AdminEmployeeController::class, 'create'])->name('admin.salaries.create');
        Route::post('/', [AdminEmployeeController::class, 'store'])->name('admin.salaries.store');
        Route::get('/{id}', [AdminEmployeeController::class, 'show'])->name('admin.salaries.show');
        Route::get('/{id}/edit', [AdminEmployeeController::class, 'edit'])->name('admin.salaries.edit');
        Route::put('/{id}', [AdminEmployeeController::class, 'update'])->name('admin.salaries.update');
        Route::delete('/{id}', [AdminEmployeeController::class, 'destroy'])->name('admin.salaries.destroy');
    });
});
