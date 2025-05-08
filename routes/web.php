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
use App\Http\Controllers\ClientEmployeeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\API\AdminPendingRegistrationController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\EventProposalController;
use App\Http\Controllers\AdminEventProposalController;
use App\Http\Controllers\ProviderAssignmentController;

// Pages principales
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [ContactController::class, 'index'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::get('/communities', [PageController::class, 'communities'])->name('communities');
Route::get('/contracts-info', [PageController::class, 'contracts'])->name('contracts-info');
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

// Inscription
Route::middleware(['check.auth'])->group(function () {
    Route::prefix('dashboard/gestion_admin/inscriptions')->group(function () {
        Route::get('/', [AdminPendingRegistrationController::class, 'index'])->name('admin.inscriptions.index');
        Route::get('/{id}', [AdminPendingRegistrationController::class, 'show'])->name('admin.inscriptions.show');
        Route::post('/{id}/approve', [AdminPendingRegistrationController::class, 'approve'])->name('admin.inscriptions.approve');
        Route::post('/{id}/reject', [AdminPendingRegistrationController::class, 'reject'])->name('admin.inscriptions.reject');
    });
});


Route::middleware(['check.auth'])->group(function () {
    Route::prefix('dashboard/gestion_admin/contracts')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminContractController::class, 'index'])->name('admin.contracts.index');
        Route::get('/{id}', [App\Http\Controllers\AdminContractController::class, 'show'])->name('admin.contracts.show');
        Route::post('/{id}/approve', [App\Http\Controllers\AdminContractController::class, 'approve'])->name('admin.contracts.approve');
        Route::post('/{id}/reject', [App\Http\Controllers\AdminContractController::class, 'reject'])->name('admin.contracts.reject');
    });
});

Route::middleware(['check.auth'])->group(function () {
    Route::prefix('dashboard/gestion_admin/advice')->group(function () {
        Route::get('/', [App\Http\Controllers\AdminAdviceController::class, 'index'])->name('admin.advice.index');
        Route::get('/create', [App\Http\Controllers\AdminAdviceController::class, 'create'])->name('admin.advice.create');
        Route::post('/', [App\Http\Controllers\AdminAdviceController::class, 'store'])->name('admin.advice.store');
        Route::get('/{id}/edit', [App\Http\Controllers\AdminAdviceController::class, 'edit'])->name('admin.advice.edit');
        Route::put('/{id}', [App\Http\Controllers\AdminAdviceController::class, 'update'])->name('admin.advice.update');
        Route::delete('/{id}', [App\Http\Controllers\AdminAdviceController::class, 'destroy'])->name('admin.advice.destroy');

        // Nouvelles routes pour les catégories
        Route::get('/categories', [App\Http\Controllers\AdminAdviceCategoryController::class, 'index'])
            ->name('admin.advice-categories.index');
        Route::post('/categories', [App\Http\Controllers\AdminAdviceCategoryController::class, 'store'])
            ->name('admin.advice-categories.store');
        Route::put('/categories/{id}', [App\Http\Controllers\AdminAdviceCategoryController::class, 'update'])
            ->name('admin.advice-categories.update');
        Route::delete('/categories/{id}', [App\Http\Controllers\AdminAdviceCategoryController::class, 'destroy'])
            ->name('admin.advice-categories.destroy');

        // Routes pour les tags
        Route::get('/tags', [App\Http\Controllers\AdminAdviceTagController::class, 'index'])
            ->name('admin.advice-tags.index');
        Route::post('/tags', [App\Http\Controllers\AdminAdviceTagController::class, 'store'])
            ->name('admin.advice-tags.store');
        Route::put('/tags/{id}', [App\Http\Controllers\AdminAdviceTagController::class, 'update'])
            ->name('admin.advice-tags.update');
        Route::delete('/tags/{id}', [App\Http\Controllers\AdminAdviceTagController::class, 'destroy'])
            ->name('admin.advice-tags.destroy');
    });
});

// ============= ESPACE CLIENT

Route::middleware(['auth', 'client'])->group(function () {
    // Dashboard client
    Route::get('/client/dashboard', [DashboardController::class, 'clientDashboard'])->name('client.dashboard');
});

// Routes pour les contrats client
Route::middleware(['check.auth'])->group(function () {
    Route::get('/contracts', [ContractController::class, 'index'])->name('contracts.index');
    Route::get('/contracts/create', [ContractController::class, 'create'])->name('contracts.create');
    Route::post('/contracts', [ContractController::class, 'store'])->name('contracts.store');
    Route::get('/contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
    Route::get('/contracts/{contract}/edit', [ContractController::class, 'edit'])->name('contracts.edit');
    Route::put('/contracts/{contract}', [ContractController::class, 'update'])->name('contracts.update');
    Route::delete('/contracts/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');
    Route::get('/contracts/{contract}/change', [ContractController::class, 'requestChange'])
        ->name('contracts.request-change');
    Route::post('/contracts/{contract}/change', [ContractController::class, 'submitChange'])
        ->name('contracts.submit-change');
    // Nouvelle route pour la résiliation de contrat
    Route::post('/contracts/{contract}/terminate', [ContractController::class, 'terminate'])
        ->name('contracts.terminate');
});

// Routes pour les devis
Route::middleware(['check.auth'])->group(function () {
    Route::resource('quotes', QuoteController::class);
    Route::post('/quotes/calculate', [QuoteController::class, 'calculate'])->name('quotes.calculate');
    Route::post('/quotes/{quote}/accept', [QuoteController::class, 'accept'])->name('quotes.accept');
    Route::post('/quotes/{quote}/reject', [QuoteController::class, 'reject'])->name('quotes.reject');
});

// Gestion Employee en tant que client
Route::middleware(['check.auth'])->group(function () {
    Route::get('/employees', [ClientEmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [ClientEmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [ClientEmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{id}', [ClientEmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{id}/edit', [ClientEmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{id}', [ClientEmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{id}', [ClientEmployeeController::class, 'destroy'])->name('employees.destroy');
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

// Routes pour les conseils des salariés
Route::middleware(['check.auth'])->group(function () {
    Route::prefix('dashboard/employee/advice')->group(function () {
        Route::get('/', [\App\Http\Controllers\EmployeeAdviceController::class, 'index'])->name('employee.advice.index');
        Route::get('/{id}', [\App\Http\Controllers\EmployeeAdviceController::class, 'show'])->name('employee.advice.show');
        Route::post('/{id}/feedback', [\App\Http\Controllers\EmployeeAdviceController::class, 'storeFeedback'])->name('employee.advice.feedback');
    });
});

Route::get('/test-email', [MailController::class, 'envoyerEmail'])->name('test.email');
Route::post('/register/pending', [AuthController::class, 'registerPending'])->name('register.pending');

// Routes Stripe
Route::middleware(['check.auth'])->group(function () {
    Route::get('/contracts/{contract}/payment', [StripePaymentController::class, 'createCheckoutSession'])
        ->name('contracts.payment.create');
    Route::get('/stripe/success/{contract}', [StripePaymentController::class, 'success'])
        ->name('stripe.success');
    Route::get('/stripe/cancel/{contract}', [StripePaymentController::class, 'cancel'])
        ->name('stripe.cancel');
});

// Routes pour les propositions d'activités (côté client/company)
Route::middleware(['check.auth'])->group(function () {
    Route::get('/dashboard/client/event_proposals', [App\Http\Controllers\EventProposalController::class, 'index'])
        ->name('client.event_proposals.index');
    Route::get('/dashboard/client/event_proposals/create', [App\Http\Controllers\EventProposalController::class, 'create'])
        ->name('client.event_proposals.create');
    Route::post('/dashboard/client/event_proposals', [App\Http\Controllers\EventProposalController::class, 'store'])
        ->name('client.event_proposals.store');
    Route::get('/dashboard/client/event_proposals/{id}', [App\Http\Controllers\EventProposalController::class, 'show'])
        ->name('client.event_proposals.show');
    Route::get('/dashboard/client/event_proposals/{id}/edit', [App\Http\Controllers\EventProposalController::class, 'edit'])
        ->name('client.event_proposals.edit');
    Route::put('/dashboard/client/event_proposals/{id}', [App\Http\Controllers\EventProposalController::class, 'update'])
        ->name('client.event_proposals.update');
    Route::delete('/dashboard/client/event_proposals/{id}', [App\Http\Controllers\EventProposalController::class, 'destroy'])
        ->name('client.event_proposals.destroy');
});

// Routes pour la gestion des propositions (côté admin)
Route::middleware(['check.auth'])->group(function () {
    Route::get('/dashboard/gestion_admin/event_proposals', [AdminEventProposalController::class, 'index'])->name('admin.event_proposals.index');
    Route::get('/dashboard/gestion_admin/event_proposals/{id}', [AdminEventProposalController::class, 'show'])->name('admin.event_proposals.show');
    Route::post('/dashboard/gestion_admin/event_proposals/{id}/assign', [AdminEventProposalController::class, 'assignProvider'])->name('admin.event_proposals.assign');
    Route::post('/dashboard/gestion_admin/event_proposals/{id}/reject', [AdminEventProposalController::class, 'rejectProposal'])->name('admin.event_proposals.reject');
});

// Routes pour les assignations des prestataires
Route::prefix('dashboard/provider/assignments')->name('provider.assignments.')->group(function () {
    Route::get('/', [App\Http\Controllers\ProviderAssignmentController::class, 'index'])->name('index');
    Route::get('/{id}', [App\Http\Controllers\ProviderAssignmentController::class, 'show'])->name('show');
    Route::post('/{id}/accept', [App\Http\Controllers\ProviderAssignmentController::class, 'accept'])->name('accept');
    Route::post('/{id}/reject', [App\Http\Controllers\ProviderAssignmentController::class, 'reject'])->name('reject');
});
