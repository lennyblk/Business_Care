<?php
// test branche lenny

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\AdminCompanyController;
use App\Http\Controllers\AdminProviderController;
use App\Http\Controllers\AdminEmployeeController;
use App\Http\Controllers\AdminActivityController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractPaymentController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MailController;

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

// Activities
Route::middleware(['check.auth'])->group(function () {
    Route::prefix('dashboard/gestion_admin/activites')->group(function () {
        Route::get('/', [AdminActivityController::class, 'index'])->name('admin.activities.index');
        Route::get('/create', [AdminActivityController::class, 'create'])->name('admin.activities.create');
        Route::post('/', [AdminActivityController::class, 'store'])->name('admin.activities.store');
        Route::get('/{id}', [AdminActivityController::class, 'show'])->name('admin.activities.show');
        Route::get('/{id}/edit', [AdminActivityController::class, 'edit'])->name('admin.activities.edit');
        Route::put('/{id}', [AdminActivityController::class, 'update'])->name('admin.activities.update');
        Route::delete('/{id}', [AdminActivityController::class, 'destroy'])->name('admin.activities.destroy');
    });
});

// ============= ESPACE CLIENT - NOUVELLES ROUTES =============

// Routes pour le tableau de bord client (utilisation de la syntaxe de classe)
Route::middleware(['auth', 'client'])->group(function () {
    // Dashboard client
    Route::get('/client/dashboard', [DashboardController::class, 'clientDashboard'])->name('client.dashboard');
});

// Routes pour les contrats (mise à jour avec la syntaxe de classe)
Route::prefix('contracts')->name('contracts.')->middleware(['check.auth'])->group(function () {
    Route::get('/', [ContractController::class, 'index'])->name('index');
    Route::get('/create', [ContractController::class, 'create'])->name('create');
    Route::post('/', [ContractController::class, 'store'])->name('store');
    Route::get('/{contract}', [ContractController::class, 'show'])->name('show');
    Route::get('/{contract}/edit', [ContractController::class, 'edit'])->name('edit');
    Route::put('/{contract}', [ContractController::class, 'update'])->name('update');
    Route::delete('/{contract}', [ContractController::class, 'destroy'])->name('destroy');

    // Paiements liés aux contrats
    Route::get('/{contract}/payment', [ContractPaymentController::class, 'create'])->name('payment.create');
    Route::post('/{contract}/payment', [ContractPaymentController::class, 'process'])->name('payment.process');
    Route::get('/{contract}/showPayment', [ContractController::class, 'showPayment'])->name('showPayment');
    Route::post('/{contract}/processPayment', [ContractController::class, 'processPayment'])->name('processPayment');
});

// Routes pour les devis
Route::middleware(['check.auth'])->group(function () {
    Route::resource('quotes', QuoteController::class);
    Route::post('/quotes/calculate', [QuoteController::class, 'calculate'])->name('quotes.calculate');
    Route::post('/quotes/{quote}/accept', [QuoteController::class, 'accept'])->name('quotes.accept');
    Route::post('/quotes/{quote}/reject', [QuoteController::class, 'reject'])->name('quotes.reject');
});
// Routes pour les collaborateurs de l'entreprise
Route::middleware(['check.auth'])->group(function () {
    Route::resource('employees', ClientEmployeeController::class);
});

// Routes pour les paiements
Route::middleware(['check.auth'])->group(function () {
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    Route::get('/payments/process/{invoice}', [PaymentController::class, 'process'])->name('payments.process');
    Route::get('/payments/success', [PaymentController::class, 'success'])->name('payments.success');
    Route::get('/payments/cancel', [PaymentController::class, 'cancel'])->name('payments.cancel');
});

// Routes pour les factures
Route::middleware(['check.auth'])->group(function () {
    Route::resource('invoices', InvoiceController::class)->only(['index', 'show']);
    Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])->name('invoices.pay');
});

Route::middleware(['check.auth'])->group(function () {
    Route::get('/dashboard/employee/events', [EmployeeController::class, 'index'])->name('employee.events.index');
    Route::post('/dashboard/employee/events/{id}/register', [EmployeeController::class, 'register'])->name('employee.events.register');
    Route::post('/dashboard/employee/events/{id}/cancel', [EmployeeController::class, 'cancelRegistration'])->name('employee.events.cancel');
});

Route::get('/test-email', [MailController::class, 'envoyerEmail'])->name('test.email');


// Test branche merge lenny --- retest conflit
