<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Contract;
use App\Models\Company;
use App\Services\InvoicePdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminInvoiceController extends Controller
{

    public function index()
    {
        try {
            $invoices = Invoice::with(['company', 'contract'])
                ->orderBy('issue_date', 'desc')
                ->paginate(15);

            return view('dashboards.gestion_admin.invoices.index', compact('invoices'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage des factures admin: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage des factures.');
        }
    }


    public function show($id)
    {
        try {

            $invoice = Invoice::with(['company', 'contract'])->findOrFail($id);

            return view('dashboards.gestion_admin.invoices.show', compact('invoice'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de la facture admin: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage de la facture.');
        }
    }


    public function download($id)
    {
        try {
            // Récupérer la facture
            $invoice = Invoice::with(['company', 'contract'])->findOrFail($id);
            $contract = $invoice->contract;
            $company = $invoice->company;

            // Créer un PDF avec support UTF-8
            $pdf = new \FPDF('P', 'mm', 'A4');
            $pdf->AddPage();

            // Fonctions pour gérer l'UTF-8
            function utf8_to_latin($text) {
                $text = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
                return $text ? $text : 'Error encoding text';
            }

            // En-tête avec logo et informations de l'entreprise
            $pdf->SetFont('Arial', 'B', 20);
            $pdf->Cell(0, 15, utf8_to_latin('FACTURE'), 0, 1, 'C');

            // Numéro de facture
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('N° ' . ($invoice->invoice_number ?? 'F-' . $invoice->id)), 0, 1, 'C');
            $pdf->Ln(5);

            // Date et références
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 7, utf8_to_latin('Date d\'émission : ' . date('d/m/Y')), 0, 1, 'R');
            $pdf->Cell(0, 7, utf8_to_latin('Date d\'échéance : ' . date('d/m/Y', strtotime('+30 days'))), 0, 1, 'R');
            $pdf->Cell(0, 7, utf8_to_latin('Référence : CONT-' . $contract->id), 0, 1, 'R');
            $pdf->Ln(5);

            // Bloc Business Care (émetteur)
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(95, 8, utf8_to_latin('ÉMETTEUR'), 0, 0);

            // Bloc Client (destinataire)
            $pdf->Cell(95, 8, utf8_to_latin('DESTINATAIRE'), 0, 1);

            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(95, 7, utf8_to_latin('Business Care'), 0, 0);
            $pdf->Cell(95, 7, utf8_to_latin($company->name), 0, 1);

            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(95, 5, utf8_to_latin('110, rue de Rivoli'), 0, 0);
            $pdf->Cell(95, 5, utf8_to_latin($company->address ?? ''), 0, 1);

            $pdf->Cell(95, 5, utf8_to_latin('75001 Paris'), 0, 0);
            $pdf->Cell(95, 5, utf8_to_latin(($company->code_postal ?? '') . ' ' . ($company->ville ?? '')), 0, 1);

            $pdf->Cell(95, 5, utf8_to_latin('France'), 0, 0);
            $pdf->Cell(95, 5, utf8_to_latin($company->pays ?? 'France'), 0, 1);

            $pdf->Cell(95, 5, utf8_to_latin('Tél : 01 23 45 67 89'), 0, 0);
            $pdf->Cell(95, 5, utf8_to_latin('Tél : ' . ($company->telephone ?? 'N/A')), 0, 1);

            $pdf->Cell(95, 5, utf8_to_latin('Email : contact@business-care.fr'), 0, 0);
            $pdf->Cell(95, 5, utf8_to_latin('Email : ' . ($company->email ?? 'N/A')), 0, 1);

            $pdf->Cell(95, 5, utf8_to_latin('SIRET : 123 456 789 00010'), 0, 0);
            if (isset($company->siret)) {
                $pdf->Cell(95, 5, utf8_to_latin('SIRET : ' . $company->siret), 0, 1);
            } else {
                $pdf->Cell(95, 5, '', 0, 1);
            }

            $pdf->Ln(10);

            // Description du service
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('DESCRIPTION DES SERVICES'), 0, 1);

            // En-têtes du tableau
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(90, 8, utf8_to_latin('Description'), 1, 0, 'C', true);
            $pdf->Cell(25, 8, utf8_to_latin('Quantité'), 1, 0, 'C', true);
            $pdf->Cell(35, 8, utf8_to_latin('Prix unitaire HT'), 1, 0, 'C', true);
            $pdf->Cell(40, 8, utf8_to_latin('Montant HT'), 1, 1, 'C', true);

            // Contenu du tableau
            $pdf->SetFont('Arial', '', 9);

            // Abonnement de base (80% du montant)
            $baseAmount = $contract->amount * 0.8;
            $pdf->Cell(90, 8, utf8_to_latin('Abonnement ' . $contract->formule_abonnement), 1, 0);
            $pdf->Cell(25, 8, '1', 1, 0, 'C');
            $pdf->Cell(35, 8, utf8_to_latin(number_format($baseAmount, 2, ',', ' ') . ' €'), 1, 0, 'R');
            $pdf->Cell(40, 8, utf8_to_latin(number_format($baseAmount, 2, ',', ' ') . ' €'), 1, 1, 'R');

            // Services inclus (20% du montant)
            $servicesAmount = $contract->amount * 0.2;
            $pdf->Cell(90, 8, utf8_to_latin('Services inclus'), 1, 0);
            $pdf->Cell(25, 8, '1', 1, 0, 'C');
            $pdf->Cell(35, 8, utf8_to_latin(number_format($servicesAmount, 2, ',', ' ') . ' €'), 1, 0, 'R');
            $pdf->Cell(40, 8, utf8_to_latin(number_format($servicesAmount, 2, ',', ' ') . ' €'), 1, 1, 'R');

            // Sous-total, TVA et total
            $pdf->Ln(5);
            $pdf->Cell(115, 8, '', 0, 0);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->Cell(35, 8, utf8_to_latin('Total HT'), 1, 0, 'L', true);
            $pdf->Cell(40, 8, utf8_to_latin(number_format($contract->amount, 2, ',', ' ') . ' €'), 1, 1, 'R', true);

            $pdf->Cell(115, 8, '', 0, 0);
            $pdf->Cell(35, 8, utf8_to_latin('TVA (20%)'), 1, 0, 'L', true);
            $pdf->Cell(40, 8, utf8_to_latin(number_format($contract->amount * 0.2, 2, ',', ' ') . ' €'), 1, 1, 'R', true);

            $pdf->Cell(115, 8, '', 0, 0);
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(35, 8, utf8_to_latin('Total TTC'), 1, 0, 'L', true);
            $pdf->Cell(40, 8, utf8_to_latin(number_format($contract->amount * 1.2, 2, ',', ' ') . ' €'), 1, 1, 'R', true);

            $pdf->Ln(10);

            // Informations de paiement
            $pdf->SetFont('Arial', 'B', 11);
            $pdf->Cell(0, 8, utf8_to_latin('INFORMATIONS DE PAIEMENT'), 0, 1);

            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 6, utf8_to_latin('Méthode de paiement : Carte bancaire / Prélèvement automatique'), 0, 1);
            $pdf->Cell(0, 6, utf8_to_latin('Conditions de paiement : Paiement à réception de facture'), 0, 1);

            $pdf->Ln(3);
            $pdf->Cell(0, 6, utf8_to_latin('Coordonnées bancaires :'), 0, 1);
            $pdf->Cell(0, 6, utf8_to_latin('IBAN : FR76 1234 5678 9123 4567 8912 345'), 0, 1);
            $pdf->Cell(0, 6, utf8_to_latin('BIC : ABCDEFGHIJK'), 0, 1);

            $pdf->Ln(10);

            // Notes et conditions
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(0, 5, utf8_to_latin('Facture acquittée - TVA récupérable sur la base de cette facture'), 0, 1);
            $pdf->Cell(0, 5, utf8_to_latin('En cas de retard de paiement, une pénalité de 3 fois le taux d\'intérêt légal sera appliquée.'), 0, 1);
            $pdf->Cell(0, 5, utf8_to_latin('Une indemnité forfaitaire de 40€ pour frais de recouvrement sera due.'), 0, 1);

            // Pied de page
            $pdf->SetY(-15);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(0, 10, utf8_to_latin('Business Care - SARL au capital de 50 000€ - RCS Paris 123 456 789'), 0, 0, 'C');

            // Générer et télécharger le PDF
            return $pdf->Output('D', 'facture_' . $invoice->id . '.pdf');
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du PDF: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Une erreur est survenue: ' . $e->getMessage());
        }
    }

    public function markAsPaid($id)
    {
        try {
            // Récupérer la facture
            $invoice = Invoice::findOrFail($id);

            // Vérifier que la facture n'est pas déjà payée
            if ($invoice->payment_status === 'Paid') {
                return back()->with('info', 'Cette facture est déjà marquée comme payée.');
            }

            // Mettre à jour le statut
            $invoice->payment_status = 'Paid';
            $invoice->save();

            // Mettre à jour le contrat associé si nécessaire
            // ...

            return back()->with('success', 'La facture a été marquée comme payée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors du marquage de la facture comme payée: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du marquage de la facture comme payée.');
        }
    }


    public function generateMonthlyInvoices()
    {
        try {
            // Vérifier que l'utilisateur est admin
            if (session('user_type') !== 'admin') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant qu\'administrateur pour accéder à cette page.');
            }

            // Récupérer tous les contrats actifs
            $contracts = Contract::where('payment_status', 'active')
                ->whereDate('end_date', '>=', now())
                ->get();

            // Vérifier si on a des contrats actifs
            if ($contracts->isEmpty()) {
                return redirect()->route('admin.invoices.index')
                    ->with('info', 'Aucun contrat actif trouvé pour générer des factures.');
            }

            Log::info('Génération des factures mensuelles en cours', [
                'nombre_contrats_actifs' => $contracts->count()
            ]);

            $count = 0;

            foreach ($contracts as $contract) {
                try {
                    // Vérifier si une facture a déjà été générée ce mois-ci
                    $existingInvoice = Invoice::where('contract_id', $contract->id)
                        ->whereYear('issue_date', now()->year)
                        ->whereMonth('issue_date', now()->month)
                        ->exists();

                    if (!$existingInvoice) {
                        $invoice = new Invoice();
                        $invoice->contract_id = $contract->id;
                        $invoice->company_id = $contract->company_id;
                        $invoice->issue_date = now();
                        $invoice->due_date = now()->addDays(15);
                        $invoice->total_amount = $contract->amount; // Assurez-vous d'utiliser total_amount partout
                        $invoice->payment_status = 'Pending';

                        // Générer les détails de la facture
                        $details = "Abonnement " . $contract->formule_abonnement . " pour la période du "
                                . now()->startOfMonth()->format('d/m/Y') . " au "
                                . now()->endOfMonth()->format('d/m/Y') . ".\n\n"
                                . "- Abonnement de base : " . number_format($contract->amount * 0.8, 2, ',', ' ') . " €\n"
                                . "- Services inclus : " . number_format($contract->amount * 0.2, 2, ',', ' ') . " €\n"
                                . "\nTotal HT : " . number_format($contract->amount, 2, ',', ' ') . " €\n"
                                . "TVA (20%) : " . number_format($contract->amount * 0.2, 2, ',', ' ') . " €\n"
                                . "Total TTC : " . number_format($contract->amount * 1.2, 2, ',', ' ') . " €";

                        $invoice->details = $details;
                        $invoice->pdf_path = null;

                        $invoice->save();

                        Log::info('Facture générée avec succès', [
                            'contract_id' => $contract->id,
                            'invoice_id' => $invoice->id
                        ]);

                        $count++;
                    }
                } catch (\Exception $contractError) {
                    // Log l'erreur mais continue avec les autres contrats
                    Log::error('Erreur lors de la génération de la facture pour le contrat #' . $contract->id . ': ' . $contractError->getMessage());
                }
            }

            if ($count > 0) {
                return redirect()->route('admin.invoices.index')
                    ->with('success', $count . ' nouvelles factures ont été générées avec succès.');
            } else {
                return redirect()->route('admin.invoices.index')
                    ->with('info', 'Aucune nouvelle facture n\'a été générée. Les factures pour ce mois existent peut-être déjà.');
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération des factures mensuelles: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Une erreur est survenue lors de la génération des factures mensuelles: ' . $e->getMessage());
        }
    }

    public function getByCompany($companyId)
    {
        try {

            // Récupérer l'entreprise
            $company = Company::findOrFail($companyId);

            // Récupérer toutes les factures de cette entreprise
            $invoices = Invoice::where('company_id', $companyId)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return view('dashboards.gestion_admin.invoices.company', compact('invoices', 'company'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage des factures par entreprise: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage des factures.');
        }
    }
}
