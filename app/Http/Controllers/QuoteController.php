<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Service;
use App\Models\Activity;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class QuoteController extends Controller
{
    /**
     * Affiche la liste des devis de la société
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Vérifiez si l'utilisateur est authentifié avec session
        if (!session()->has('user_id')) {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        // Récupérer l'ID de la société depuis la session
        $companyId = session('user_id');
        
        // Vérifier que l'utilisateur est bien du type 'societe'
        if (session('user_type') !== 'societe') {
            return redirect()->route('dashboard.' . session('user_type'))
                ->with('error', 'Vous n\'avez pas accès à cette fonctionnalité.');
        }
        
        // Récupérer les devis de la société
        $quotes = Quote::where('company_id', $companyId)
                    ->latest()
                    ->paginate(10);
        
        return view('dashboards.client.quotes.index', compact('quotes'));
    }

    /**
     * Affiche le formulaire de création de devis
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('dashboards.client.quotes.create');
    }

    /**
     * Enregistre un nouveau devis
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
 * Enregistre un nouveau devis
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\RedirectResponse
 */
    public function store(Request $request)
    {
        // Validation des champs du nouveau formulaire
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_size' => 'required|integer|min:1',
            'contract_duration' => 'required|integer|min:1|max:5',
            'formule_abonnement' => 'required|string|in:Starter,Basic,Premium',
            'price_per_employee' => 'required|numeric',
            'total_amount' => 'required|numeric',
        ]);
        
        // Récupération des informations utilisateur et société
        if (Auth::check()) {
            $user = Auth::user();
            $companyId = $user->company_id;
        } else {
            // Utilisation de l'ID de session si l'authentification n'est pas basée sur Auth
            $companyId = session('user_id');
        }
        
        if (!$companyId) {
            return redirect()->back()
                ->with('error', 'Vous devez être associé à une société pour créer un devis.')
                ->withInput();
        }
        
        // Déterminer les dates
        $creationDate = Carbon::now();
        $expirationDate = Carbon::now()->addDays(30); // Validité standard de 30 jours
        
        // Générer le numéro de référence unique
        $referenceNumber = 'DEVIS-' . strtoupper(substr($request->formule_abonnement, 0, 3)) . '-' . time() . '-' . $companyId;
        
        // Création du devis
        $quote = new Quote([
            'company_id' => $companyId,
            'company_name' => $request->company_name,
            'company_size' => $request->company_size,
            'contract_duration' => $request->contract_duration,
            'formule_abonnement' => $request->formule_abonnement,
            'price_per_employee' => $request->price_per_employee,
            'activities_count' => $request->activities_count,
            'medical_appointments' => $request->medical_appointments,
            'extra_appointment_fee' => $request->extra_appointment_fee,
            'chatbot_questions' => $request->chatbot_questions,
            'weekly_advice' => $request->weekly_advice,
            'personalized_advice' => $request->personalized_advice,
            'annual_amount' => $request->annual_amount,
            'total_amount' => $request->total_amount,
            'total_amount_ttc' => $request->total_amount_ttc,
            'reference_number' => $referenceNumber,
            'creation_date' => $creationDate,
            'expiration_date' => $expirationDate,
            'status' => 'Pending',
            'services_details' => $request->services_details ?? null,
        ]);
        
        $quote->save();
        
        // Enregistrement de l'activité si le modèle Activity existe
        if (class_exists('App\Models\Activity')) {
            Activity::create([
                'company_id' => $companyId,
                'user_id' => $user->id ?? session('user_id'),
                'title' => 'Nouveau devis créé',
                'description' => 'Devis pour ' . $request->company_size . ' salariés avec formule ' . $request->formule_abonnement,
                'type' => 'quote',
                'subject_type' => Quote::class,
                'subject_id' => $quote->id,
            ]);
        }
        
        return redirect()->route('quotes.index')
            ->with('success', 'Devis créé avec succès.');
    }

    /**
     * Affiche les détails d'un devis
     *
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\View\View
     */
    public function show(Quote $quote)
    {
        $this->checkQuoteOwnership($quote);
        
        return view('dashboards.client.quotes.show', compact('quote'));
    }

    /**
     * Affiche le formulaire d'édition d'un devis (uniquement si en attente)
     *
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit(Quote $quote)
    {
        $this->checkQuoteOwnership($quote);
        
        if ($quote->status !== 'Pending') {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'Seuls les devis en attente peuvent être modifiés.');
        }
        
        return view('dashboards.client.quotes.edit', compact('quote'));
    }

    /**
     * Met à jour un devis
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Quote $quote)
    {
        $this->checkQuoteOwnership($quote);
        
        if ($quote->status !== 'Pending') {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'Seuls les devis en attente peuvent être modifiés.');
        }
        
        // Validation des champs du nouveau formulaire
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
        
        // Calcul des montants
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

    /**
     * Supprimer un devis (uniquement si en attente)
     *
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Accepter un devis et le transformer en facture
     *
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Rejeter un devis
     *
     * @param  \App\Models\Quote  $quote
     * @return \Illuminate\Http\RedirectResponse
     */
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

    /**
     * Vérifie que le devis appartient bien à la société de l'utilisateur connecté
     *
     * @param  \App\Models\Quote  $quote
     * @return void
     */
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