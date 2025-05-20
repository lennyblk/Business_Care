<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Contract;
use App\Services\InvoicePdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{

    public function index()
    {
        if (session('user_type') === 'societe') {
            $company_id = session('user_id');
            $invoices = Invoice::where('company_id', $company_id)
                ->orderBy('issue_date', 'desc')
                ->paginate(10);
        } elseif (session('user_type') === 'admin') {
            $invoices = Invoice::orderBy('issue_date', 'desc')
                ->paginate(10);
        } else {
            return redirect()->route('login')
                ->with('error', 'Vous n\'avez pas accès à cette page.');
        }

        return view('dashboards.client.invoices.index', compact('invoices'));
    }

    public function show($id)
    {

        $invoice = Invoice::findOrFail($id);

        if (session('user_type') === 'societe' && session('user_id') != $invoice->company_id) {
            return back()->with('error', 'Vous n\'avez pas accès à cette facture.');
        }

        return view('dashboards.client.invoices.show', compact('invoice'));
    }


    public function download($id)
    {
        try {
            $invoice = Invoice::with(['company', 'contract'])->findOrFail($id);
            $company = $invoice->company;

            if (!$invoice->is_donation && !$invoice->contract) {
                throw new \Exception('Contrat non trouvé pour cette facture');
            }

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
            if (!$invoice->is_donation) {
                $pdf->Cell(0, 7, utf8_to_latin('Référence : CONT-' . $invoice->contract_id), 0, 1, 'R');
            }
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
            if ($invoice->is_donation) {
                $pdf->Cell(0, 10, utf8_to_latin('DÉTAILS DU DON'), 0, 1);
            } else {
                $pdf->Cell(0, 10, utf8_to_latin('DESCRIPTION DES SERVICES'), 0, 1);
            }

            // En-têtes du tableau
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetFillColor(240, 240, 240);
            $pdf->Cell(90, 8, utf8_to_latin('Description'), 1, 0, 'C', true);
            $pdf->Cell(25, 8, utf8_to_latin('Quantité'), 1, 0, 'C', true);
            $pdf->Cell(35, 8, utf8_to_latin('Prix unitaire'), 1, 0, 'C', true);
            $pdf->Cell(40, 8, utf8_to_latin('Montant'), 1, 1, 'C', true);

            // Contenu du tableau
            $pdf->SetFont('Arial', '', 9);

            if ($invoice->is_donation) {
                // Cas d'un don
                $pdf->Cell(90, 8, utf8_to_latin($invoice->details), 1, 0);
                $pdf->Cell(25, 8, '1', 1, 0, 'C');
                $pdf->Cell(35, 8, utf8_to_latin(number_format($invoice->total_amount, 2, ',', ' ') . ' €'), 1, 0, 'R');
                $pdf->Cell(40, 8, utf8_to_latin(number_format($invoice->total_amount, 2, ',', ' ') . ' €'), 1, 1, 'R');

                // Total pour un don
                $pdf->Ln(5);
                $pdf->Cell(115, 8, '', 0, 0);
                $pdf->SetFont('Arial', 'B', 11);
                $pdf->Cell(35, 8, utf8_to_latin('Total'), 1, 0, 'L', true);
                $pdf->Cell(40, 8, utf8_to_latin(number_format($invoice->total_amount, 2, ',', ' ') . ' €'), 1, 1, 'R', true);
            } else {
                // Cas d'une facture normale
                $contract = $invoice->contract;

                // Un seul service avec le montant total
                $pdf->Cell(90, 8, utf8_to_latin('Contrat ' . $contract->formule_abonnement . ' (Annuel)'), 1, 0);
                $pdf->Cell(25, 8, '1', 1, 0, 'C');
                $pdf->Cell(35, 8, utf8_to_latin(number_format($contract->amount, 2, ',', ' ') . ' €'), 1, 0, 'R');
                $pdf->Cell(40, 8, utf8_to_latin(number_format($contract->amount, 2, ',', ' ') . ' €'), 1, 1, 'R');

                // Total
                $pdf->Ln(5);
                $pdf->SetFont('Arial', 'B', 11);
                $pdf->Cell(115, 8, '', 0, 0);
                $pdf->Cell(35, 8, utf8_to_latin('Total'), 1, 0, 'L', true);
                $pdf->Cell(40, 8, utf8_to_latin(number_format($contract->amount, 2, ',', ' ') . ' €'), 1, 1, 'R', true);
            }

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

    public function viewPdf($id)
    {
        try {

            $invoice = Invoice::findOrFail($id);

            if (session('user_type') === 'societe' && session('user_id') != $invoice->company_id) {
                return back()->with('error', 'Vous n\'avez pas accès à cette facture.');
            }

            $contract = Contract::findOrFail($invoice->contract_id);

            $pdfGenerator = new InvoicePdfGenerator($contract, $invoice->invoice_number);
            $pdf = $pdfGenerator->generate();

            return $pdf->Output('I', 'facture_' . $invoice->invoice_number . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du PDF de facture: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage du PDF de facture.');
        }
    }


    public function pay($id)
    {
        try {
            if (session('user_type') !== 'societe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société pour effectuer cette action.');
            }

            $invoice = Invoice::findOrFail($id);

            if (session('user_id') != $invoice->company_id) {
                return back()->with('error', 'Vous n\'avez pas accès à cette facture.');
            }

            if ($invoice->status === 'paid') {
                return back()->with('error', 'Cette facture a déjà été payée.');
            }

            return redirect()->route('payments.process', ['invoice' => $invoice->id]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du paiement de la facture: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du paiement de la facture.');
        }
    }
}
