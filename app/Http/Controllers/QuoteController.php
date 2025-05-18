<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\Contract;
use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class QuoteController extends Controller
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        $companyId = session('user_id');

        if (session('user_type') !== 'societe') {
            return redirect()->route('dashboard.' . session('user_type'))
                ->with('error', 'Vous n\'avez pas accès à cette fonctionnalité.');
        }

        $quotes = Quote::where('company_id', $companyId)
                    ->orderBy('creation_date', 'desc')
                    ->paginate(10);

        return view('dashboards.client.quotes.index', compact('quotes'));
    }

    public function show(Quote $quote)
    {
        return view('dashboards.client.quotes.show', compact('quote'));
    }

    public function destroy(Quote $quote)
    {
        try {
            $this->checkQuoteOwnership($quote);

            if ($quote->status !== 'Pending') {
                return redirect()->route('quotes.show', $quote)
                    ->with('error', 'Seuls les devis en attente peuvent être supprimés.');
            }

            $quote->delete();

            return redirect()->route('quotes.index')
                ->with('success', 'Devis supprimé avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du devis: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la suppression du devis.');
        }
    }

    public function accept(Quote $quote)
    {
        try {
            $this->checkQuoteOwnership($quote);

            if ($quote->status !== 'Pending') {
                return redirect()->route('quotes.show', $quote)
                    ->with('error', 'Ce devis a déjà été traité.');
            }

            // Marquer le devis comme accepté
            $quote->status = 'Accepted';
            $quote->save();

            // Générer la description des services
            $services = $this->generateServicesDescription($quote);

            // Créer un contrat en attente d'approbation avec une valeur d'enum valide
            $contract = new Contract([
                'company_id' => $quote->company_id,
                'quote_id' => $quote->id,
                'formule_abonnement' => $quote->formule_abonnement,
                'services' => $services,
                'start_date' => now(),
                'end_date' => now()->addYear(),
                'amount' => $quote->total_amount / 12, // Montant mensuel
                'payment_method' => 'Direct Debit', // Utiliser une valeur d'enum valide
                'payment_status' => 'pending' // En attente d'approbation
            ]);

            $contract->save();

            // Enregistrement des activités si la classe existe
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
                    'title' => 'Demande de contrat créée',
                    'description' => 'Demande de contrat #' . $contract->id . ' créée à partir du devis #' . $quote->reference_number,
                    'type' => 'contract',
                    'subject_type' => Contract::class,
                    'subject_id' => $contract->id,
                ]);
            }

            return redirect()->route('contracts.show', $contract)
                ->with('success', 'Devis accepté avec succès. Une demande de contrat a été créée et est en attente de validation par notre équipe.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'acceptation du devis: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'acceptation du devis: ' . $e->getMessage());
        }
    }

    public function reject(Quote $quote)
    {
        try {
            $this->checkQuoteOwnership($quote);

            if ($quote->status !== 'Pending') {
                return redirect()->route('quotes.show', $quote)
                    ->with('error', 'Ce devis a déjà été traité.');
            }

            $quote->status = 'Rejected';
            $quote->save();

            return redirect()->route('quotes.index')
                ->with('info', 'Devis rejeté avec succès.');

        } catch (\Exception $e) {
            Log::error('Erreur lors du rejet du devis: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du rejet du devis.');
        }
    }

    public function download($id)
    {
        try {
            $quote = Quote::with('company')->findOrFail($id);

            // Créer un PDF avec support UTF-8
            $pdf = new \FPDF('P', 'mm', 'A4');
            $pdf->AddPage();

            // Fonction pour gérer l'UTF-8
            function utf8_to_latin($text) {
                $text = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
                return $text ? $text : 'Error encoding text';
            }

            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, utf8_to_latin('DEVIS'), 0, 1, 'C');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 5, utf8_to_latin('N° ' . $quote->id), 0, 1, 'C');

            // Contenu simplifié du PDF
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 10, utf8_to_latin('Formule: ' . $quote->formule_abonnement), 0, 1);
            $pdf->Cell(0, 10, utf8_to_latin('Salariés: ' . $quote->company_size), 0, 1);
            $pdf->Cell(0, 10, utf8_to_latin('Total: ' . $quote->total_amount . ' €'), 0, 1);

            // Télécharger le PDF
            return $pdf->Output('D', 'Devis-' . $quote->id . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la génération du PDF.');
        }
    }

        public function store(Request $request)
    {
        $request->validate([
            'formule_abonnement' => 'required',
            'company_size' => 'required|integer|min:1',
            'price_per_employee' => 'required|numeric|min:0'
        ]);

        try {
            // Définir les caractéristiques de chaque formule
            $formulesDetails = [
                'Starter' => [
                    'max_employees' => 30,
                    'activities_count' => 2,
                    'medical_appointments' => 1,
                    'extra_appointment_fee' => 75,
                    'chatbot_questions' => '6 questions',
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
                    'chatbot_questions' => '20 questions',
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
                    'chatbot_questions' => 'illimité',
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
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du devis: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la création du devis.')->withInput();
        }
    }

    public function create()
    {
        return view('dashboards.client.quotes.create');
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

    private function generateServicesDescription(Quote $quote)
    {
        $services = "Formule " . $quote->formule_abonnement . " pour " . $quote->company_size . " salariés.\n\n";

        // Activités
        if ($quote->formule_abonnement == 'Starter') {
            $services .= "- 2 activités avec participation des prestataires\n";
            $services .= "- 1 RDV médical (présentiel/visio)\n";
            $services .= "- RDV médicaux supplémentaires : 75€/RDV\n";
            $services .= "- Accès au chatbot : 6 questions\n";
            $services .= "- Accès aux fiches pratiques BC : Illimité\n";
            $services .= "- Conseils hebdomadaires : Non\n";
            $services .= "- Événements / Communautés : Accès illimité\n";
        } elseif ($quote->formule_abonnement == 'Basic') {
            $services .= "- 3 activités avec participation des prestataires\n";
            $services .= "- 2 RDV médicaux (présentiel/visio)\n";
            $services .= "- RDV médicaux supplémentaires : 75€/RDV\n";
            $services .= "- Accès au chatbot : 20 questions\n";
            $services .= "- Accès aux fiches pratiques BC : Illimité\n";
            $services .= "- Conseils hebdomadaires : Oui (non personnalisés)\n";
            $services .= "- Événements / Communautés : Accès illimité\n";
        } else { // Premium
            $services .= "- 4 activités avec participation des prestataires\n";
            $services .= "- 3 RDV médicaux (présentiel/visio)\n";
            $services .= "- RDV médicaux supplémentaires : 50€/RDV\n";
            $services .= "- Accès au chatbot : Illimité\n";
            $services .= "- Accès aux fiches pratiques BC : Illimité\n";
            $services .= "- Conseils hebdomadaires : Oui personnalisés (suggestion d'activités)\n";
            $services .= "- Événements / Communautés : Accès illimité\n";
        }

        // Ajouter les détails supplémentaires s'ils existent
        if (!empty($quote->services_details)) {
            $services .= "\nDétails complémentaires :\n" . $quote->services_details;
        }

        return $services;
    }
}
