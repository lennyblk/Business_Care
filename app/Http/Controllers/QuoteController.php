<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Service;
use App\Models\Activity;
use App\Models\Invoice;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\QuotePdfGenerator;

class QuoteController extends Controller
{

    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        // user_id est l'ID de la table company pour les sociétés
        $companyId = session('user_id'); // Dans ce cas, user_id est l'id de la company
        
        if (session('user_type') !== 'societe') {
            return redirect()->route('dashboard.' . session('user_type'))
                ->with('error', 'Vous n\'avez pas accès à cette fonctionnalité.');
        }
        
        // Utiliser company_id pour la requête car c'est le nom de la colonne dans la table quote
        $quotes = Quote::where('company_id', $companyId)
                    ->orderBy('creation_date', 'desc')
                    ->paginate(10);
        
        return view('dashboards.client.quotes.index', compact('quotes'));
    }


    public function create()
    {
        return view('dashboards.client.quotes.create');
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'formule_abonnement' => 'required',
            'company_size' => 'required|integer|min:1',
            'price_per_employee' => 'required|numeric|min:0'
        ]);

        // Définir les caractéristiques de chaque formule
        $formulesDetails = [
            'Starter' => [
                'max_employees' => 30,
                'activities_count' => 2,
                'medical_appointments' => 1,
                'extra_appointment_fee' => 75,
                'chatbot_questions' => '6 questions', // Raccourci
                'weekly_advice' => false,
                'personalized_advice' => false,
                'price' => 180,
                'events_access' => false,
                'pratical_guides_access' => true
            ],
            'Basic' => [
                'min_employees' => 31,
                'max_employees' => 250,
                'activities_count' => 3,
                'medical_appointments' => 2,
                'extra_appointment_fee' => 75,
                'chatbot_questions' => '20 questions', // Raccourci
                'weekly_advice' => true,
                'personalized_advice' => false,
                'price' => 150,
                'events_access' => true,
                'pratical_guides_access' => true
            ],
            'Premium' => [
                'min_employees' => 251,
                'activities_count' => 4,
                'medical_appointments' => 3,
                'extra_appointment_fee' => 50,
                'chatbot_questions' => 'illimité', // Raccourci
                'weekly_advice' => true,
                'personalized_advice' => true,
                'price' => 100,
                'events_access' => true,
                'pratical_guides_access' => true
            ]
        ];

        // Récupérer les détails de la formule choisie
        $formuleDetails = $formulesDetails[$request->formule_abonnement];

        // Calculer le montant total
        $totalAmount = $request->company_size * $formuleDetails['price'];

        // Créer le devis avec toutes les informations
        $quote = Quote::create([
            'company_id' => session('user_id'),
            'formule_abonnement' => $request->formule_abonnement,
            'company_size' => $request->company_size,
            'price_per_employee' => $formuleDetails['price'],
            'activities_count' => $formuleDetails['activities_count'],
            'medical_appointments' => $formuleDetails['medical_appointments'],
            'extra_appointment_fee' => $formuleDetails['extra_appointment_fee'],
            'chatbot_questions' => $formuleDetails['chatbot_questions'],
            'weekly_advice' => $formuleDetails['weekly_advice'],
            'personalized_advice' => $formuleDetails['personalized_advice'],
            'events_access' => $formuleDetails['events_access'],
            'pratical_guides_access' => $formuleDetails['pratical_guides_access'],
            'total_amount' => $totalAmount,
            'creation_date' => now(),
            'expiration_date' => now()->addMonth(),
            'status' => 'Pending',
            'services_details' => $request->services_details ?? ''
        ]);

        return redirect()->route('quotes.index')
            ->with('success', 'Devis créé avec succès.');
    }


    public function show(Quote $quote)
    {
        return view('dashboards.client.quotes.show', compact('quote'));
    }


    public function edit(Quote $quote)
    {
        $this->checkQuoteOwnership($quote);
        
        if ($quote->status !== 'Pending') {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'Seuls les devis en attente peuvent être modifiés.');
        }
        
        return view('dashboards.client.quotes.edit', compact('quote'));
    }


    public function update(Request $request, Quote $quote)
    {
        $this->checkQuoteOwnership($quote);
        
        if ($quote->status !== 'Pending') {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'Seuls les devis en attente peuvent être modifiés.');
        }
        
        // Validation des champs du formulaire
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_size' => 'required|integer|min:1',
            'contract_duration' => 'required|integer|min:1|max:5',
        ]);
        
        // Détermination de la formule en fonction du nombre de salariés
        if ($request->company_size <= 30) {
            $formula = 'Starter';
            $pricePerEmployee = 180;
        } elseif ($request->company_size <= 250) {
            $formula = 'Basic';
            $pricePerEmployee = 150;
        } else {
            $formula = 'Premium';
            $pricePerEmployee = 100;
        }
        
        // Mise à jour des caractéristiques de la formule
        $activitiesCount = ($formula == 'Starter') ? 2 : (($formula == 'Basic') ? 3 : 4);
        $medicalAppointments = ($formula == 'Starter') ? 1 : (($formula == 'Basic') ? 2 : 3);
        $extraAppointmentFee = ($formula == 'Premium') ? '50€/rdv' : '75€/rdv';
        $chatbotQuestions = ($formula == 'Starter') ? '6 questions' : (($formula == 'Basic') ? '20 questions' : 'illimité');
        $weeklyAdvice = ($formula == 'Starter') ? 'non' : 'oui';
        $personalizedAdvice = ($formula == 'Premium') ? 'oui' : 'non';
        
        // montants
        $annualAmount = $request->company_size * $pricePerEmployee;
        $totalAmount = $annualAmount * $request->contract_duration;
        $totalAmountTTC = $totalAmount * 1.2;
        
        // Mise à jour du devis
        $quote->company_name = $request->company_name;
        $quote->company_size = $request->company_size;
        $quote->contract_duration = $request->contract_duration;
        $quote->formule_abonnement = $formula;
        $quote->price_per_employee = $pricePerEmployee;
        $quote->activities_count = $activitiesCount;
        $quote->medical_appointments = $medicalAppointments;
        $quote->extra_appointment_fee = $extraAppointmentFee;
        $quote->chatbot_questions = $chatbotQuestions;
        $quote->weekly_advice = $weeklyAdvice;
        $quote->personalized_advice = $personalizedAdvice;
        $quote->annual_amount = $annualAmount;
        $quote->total_amount = $totalAmount;
        $quote->total_amount_ttc = $totalAmountTTC;
        $quote->services_details = $request->services_details ?? $quote->services_details;
        
        $quote->save();
        
        // Enregistrement de l'activité
        if (class_exists('App\Models\Activity')) {
            Activity::create([
                'company_id' => $quote->company_id,
                'user_id' => Auth::id() ?? session('user_id'),
                'title' => 'Devis modifié',
                'description' => 'Devis #' . $quote->reference_number . ' a été modifié',
                'type' => 'quote',
                'subject_type' => Quote::class,
                'subject_id' => $quote->id,
            ]);
        }
        
        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Devis mis à jour avec succès.');
    }

    public function destroy(Quote $quote)
    {
        $this->checkQuoteOwnership($quote);
        
        if ($quote->status !== 'Pending') {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'Seuls les devis en attente peuvent être supprimés.');
        }
        
        $reference = $quote->reference_number;
        $quote->delete();
        
        if (class_exists('App\Models\Activity')) {
            Activity::create([
                'company_id' => Auth::user()->company_id ?? session('user_id'),
                'user_id' => Auth::id() ?? session('user_id'),
                'title' => 'Devis supprimé',
                'description' => 'Devis #' . $reference . ' a été supprimé',
                'type' => 'quote',
                'subject_type' => 'App\Models\Quote',
                'subject_id' => null,
            ]);
        }
        
        return redirect()->route('quotes.index')
            ->with('success', 'Devis supprimé avec succès.');
    }

    public function accept(Quote $quote)
    {
        $this->checkQuoteOwnership($quote);
        
        if ($quote->status !== 'Pending') {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'Ce devis a déjà été traité.');
        }
        
        // Marquer le devis comme accepté
        $quote->status = 'Accepted';
        $quote->save();
        
        // Créer une facture à partir du devis
        $invoiceNumber = 'FACT-' . time() . '-' . $quote->company_id;
        
        $invoice = new Invoice([
            'company_id' => $quote->company_id,
            'quote_id' => $quote->id,
            'invoice_number' => $invoiceNumber,
            'issue_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(30),
            'amount' => $quote->total_amount,
            'tax_amount' => $quote->total_amount * 0.20, // TVA 20%
            'total_amount' => $quote->total_amount_ttc,
            'status' => 'unpaid',
        ]);
        
        $invoice->save();
        
        // Enregistrement des activités
        if (class_exists('App\Models\Activity')) {
            Activity::create([
                'company_id' => $quote->company_id,
                'user_id' => Auth::id() ?? session('user_id'),
                'title' => 'Devis accepté',
                'description' => 'Devis #' . $quote->reference_number . ' a été accepté',
                'type' => 'quote',
                'subject_type' => Quote::class,
                'subject_id' => $quote->id,
            ]);
            
            Activity::create([
                'company_id' => $quote->company_id,
                'user_id' => Auth::id() ?? session('user_id'),
                'title' => 'Facture créée',
                'description' => 'Facture #' . $invoice->invoice_number . ' a été créée à partir du devis #' . $quote->reference_number,
                'type' => 'invoice',
                'subject_type' => Invoice::class,
                'subject_id' => $invoice->id,
            ]);
        }
        
        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Devis accepté avec succès. Une facture a été générée.');
    }


    public function reject(Quote $quote)
    {
        $this->checkQuoteOwnership($quote);
        
        if ($quote->status !== 'Pending') {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'Ce devis a déjà été traité.');
        }
        
        $quote->status = 'Rejected';
        $quote->save();
        
        if (class_exists('App\Models\Activity')) {
            Activity::create([
                'company_id' => $quote->company_id,
                'user_id' => Auth::id() ?? session('user_id'),
                'title' => 'Devis rejeté',
                'description' => 'Devis #' . $quote->reference_number . ' a été rejeté',
                'type' => 'quote',
                'subject_type' => Quote::class,
                'subject_id' => $quote->id,
            ]);
        }
        
        return redirect()->route('quotes.index')
            ->with('info', 'Devis rejeté.');
    }


    public function download(Quote $quote)
    {
        $pdf = new QuotePdfGenerator($quote);
        return $pdf->generate()->Output('Devis-'.$quote->id.'.pdf', 'D');
    }

    public function preview(Quote $quote)
    {
        $pdf = new QuotePdfGenerator($quote);
        return $pdf->generate()->Output('Devis-'.$quote->id.'.pdf', 'I');
    }

    public function convertToContract(Quote $quote)
    {
        // Vérifier que le devis est accepté
        if ($quote->status !== 'Accepted') {
            return redirect()->back()->with('error', 'Le devis doit être accepté pour être converti en contrat.');
        }

        // Créer le contrat
        $contract = Contract::create([
            'company_id' => $quote->company_id,
            'quote_id' => $quote->id,
            'formule_abonnement' => $quote->formule_abonnement,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'amount' => $quote->total_amount,
            'status' => 'Pending',
            // autres champs nécessaires...
        ]);

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Le devis a été converti en contrat avec succès.');
    }


    private function checkQuoteOwnership(Quote $quote)
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (!$user->company || $quote->company_id !== $user->company_id) {
                abort(403, 'Vous n\'êtes pas autorisé à accéder à ce devis.');
            }
        } else {
            // Vérification basée sur la session
            if (!session()->has('user_id') || $quote->company_id !== session('user_id')) {
                abort(403, 'Vous n\'êtes pas autorisé à accéder à ce devis.');
            }
        }
    }
}