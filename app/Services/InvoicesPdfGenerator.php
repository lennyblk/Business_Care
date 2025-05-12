<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Company;
use Codedge\Fpdf\Fpdf\Fpdf;

class InvoicePdfGenerator
{
    protected $pdf;
    protected $contract;
    protected $company;
    protected $invoiceNumber;
    protected $invoiceDate;

    public function __construct(Contract $contract, $invoiceNumber = null)
    {
        // Utilisation de la classe étendue UTF8 pour FPDF
        $this->pdf = new \App\Services\PDF_UTF8();
        $this->contract = $contract;
        $this->company = Company::find($contract->company_id);
        $this->invoiceNumber = $invoiceNumber ?? 'F' . date('Ym') . '-' . $contract->id;
        $this->invoiceDate = date('Y-m-d');
    }

    public function generate()
    {
        // Configuration du document
        $this->pdf->AddPage();
        $this->pdf->SetAutoPageBreak(true, 15);

        // En-tête
        $this->generateHeader();

        // Informations client
        $this->generateClientInfo();

        // Détails de la facture
        $this->generateInvoiceDetails();

        // Tableau des prestations
        $this->generateServicesTable();

        // Totaux
        $this->generateTotals();

        // Informations de paiement
        $this->generatePaymentInfo();

        // Pied de page
        $this->generateFooter();

        return $this->pdf;
    }

    protected function generateHeader()
    {
        // Logo et en-tête
        $this->pdf->SetFont('Arial', 'B', 16);
        $this->pdf->Cell(0, 10, 'BUSINESS CARE', 0, 1, 'C');
        $this->pdf->SetFont('Arial', 'B', 14);
        $this->pdf->Cell(0, 10, 'FACTURE', 0, 1, 'C');
        $this->pdf->SetFont('Arial', '', 12);
        $this->pdf->Cell(0, 5, 'N°' . $this->invoiceNumber, 0, 1, 'C');
        $this->pdf->Ln(10);

        // Coordonnées de l'entreprise émettrice
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->Cell(0, 5, 'Business Care', 0, 1, 'L');
        $this->pdf->Cell(0, 5, '123 Avenue des Affaires', 0, 1, 'L');
        $this->pdf->Cell(0, 5, '75000 Paris', 0, 1, 'L');
        $this->pdf->Cell(0, 5, 'FRANCE', 0, 1, 'L');
        $this->pdf->Cell(0, 5, 'Tél : 01 23 45 67 89', 0, 1, 'L');
        $this->pdf->Cell(0, 5, 'Email : contact@business-care.fr', 0, 1, 'L');
        $this->pdf->Cell(0, 5, 'SIRET : 123 456 789 00010', 0, 1, 'L');
        $this->pdf->Ln(10);
    }

    protected function generateClientInfo()
    {
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 10, 'Facturé à', 0, 1);

        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->Cell(0, 5, $this->company->name, 0, 1);
        $this->pdf->Cell(0, 5, $this->company->address, 0, 1);
        $this->pdf->Cell(0, 5, $this->company->code_postal . ' ' . $this->company->ville, 0, 1);
        $this->pdf->Cell(0, 5, $this->company->pays, 0, 1);
        $this->pdf->Cell(0, 5, 'Email : ' . $this->company->email, 0, 1);
        $this->pdf->Cell(0, 5, 'Tél : ' . $this->company->telephone, 0, 1);
        if ($this->company->siret) {
            $this->pdf->Cell(0, 5, 'SIRET : ' . $this->company->siret, 0, 1);
        }
        $this->pdf->Ln(10);
    }

    protected function generateInvoiceDetails()
    {
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 10, 'Détails de la facture', 0, 1);

        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->Cell(50, 5, 'Numéro de facture :', 0);
        $this->pdf->Cell(0, 5, $this->invoiceNumber, 0, 1);

        $this->pdf->Cell(50, 5, 'Date de facturation :', 0);
        $this->pdf->Cell(0, 5, date('d/m/Y', strtotime($this->invoiceDate)), 0, 1);

        $this->pdf->Cell(50, 5, 'Référence contrat :', 0);
        $this->pdf->Cell(0, 5, 'CONT-' . $this->contract->id, 0, 1);

        $this->pdf->Cell(50, 5, 'Période de facturation :', 0);
        $currentMonth = date('m/Y');
        $this->pdf->Cell(0, 5, 'Mois de ' . $currentMonth, 0, 1);

        $this->pdf->Ln(10);
    }

    protected function generateServicesTable()
    {
        $this->pdf->SetFont('Arial', 'B', 10);

        // En-têtes du tableau
        $this->pdf->Cell(90, 7, 'Description', 1, 0, 'C');
        $this->pdf->Cell(30, 7, 'Quantité', 1, 0, 'C');
        $this->pdf->Cell(30, 7, 'Prix unitaire', 1, 0, 'C');
        $this->pdf->Cell(40, 7, 'Montant HT', 1, 1, 'C');

        // Détails du tableau
        $this->pdf->SetFont('Arial', '', 10);

        // Abonnement
        $this->pdf->Cell(90, 7, 'Abonnement ' . $this->contract->formule_abonnement, 1);
        $this->pdf->Cell(30, 7, '1', 1, 0, 'C');
        $this->pdf->Cell(30, 7, number_format($this->contract->amount * 0.8, 2, ',', ' ') . ' €', 1, 0, 'R');
        $this->pdf->Cell(40, 7, number_format($this->contract->amount * 0.8, 2, ',', ' ') . ' €', 1, 1, 'R');

        // Services inclus (condensés en une ligne pour simplifier)
        $this->pdf->Cell(90, 7, 'Services inclus', 1);
        $this->pdf->Cell(30, 7, '1', 1, 0, 'C');
        $this->pdf->Cell(30, 7, number_format($this->contract->amount * 0.2, 2, ',', ' ') . ' €', 1, 0, 'R');
        $this->pdf->Cell(40, 7, number_format($this->contract->amount * 0.2, 2, ',', ' ') . ' €', 1, 1, 'R');

        $this->pdf->Ln(5);
    }

    protected function generateTotals()
    {
        // TVA à 20%
        $amountHT = $this->contract->amount;
        $amountTVA = $amountHT * 0.2;
        $amountTTC = $amountHT + $amountTVA;

        $this->pdf->SetFont('Arial', 'B', 10);

        // Total HT
        $this->pdf->Cell(150, 7, 'Total HT', 1);
        $this->pdf->Cell(40, 7, number_format($amountHT, 2, ',', ' ') . ' €', 1, 1, 'R');

        // TVA
        $this->pdf->Cell(150, 7, 'TVA (20%)', 1);
        $this->pdf->Cell(40, 7, number_format($amountTVA, 2, ',', ' ') . ' €', 1, 1, 'R');

        // Total TTC
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(150, 8, 'Total TTC', 1);
        $this->pdf->Cell(40, 8, number_format($amountTTC, 2, ',', ' ') . ' €', 1, 1, 'R');

        $this->pdf->Ln(10);
    }

    protected function generatePaymentInfo()
    {
        $this->pdf->SetFont('Arial', 'B', 12);
        $this->pdf->Cell(0, 10, 'Informations de paiement', 0, 1);

        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->Cell(0, 5, 'Méthode de paiement : ' . $this->contract->payment_method, 0, 1);
        $this->pdf->Cell(0, 5, 'Date de paiement : ' . date('d/m/Y'), 0, 1);

        // Condition de paiement
        $this->pdf->Cell(0, 5, 'Conditions de paiement : Paiement à réception de facture', 0, 1);

        // Coordonnées bancaires
        $this->pdf->Ln(5);
        $this->pdf->Cell(0, 5, 'Coordonnées bancaires :', 0, 1);
        $this->pdf->Cell(0, 5, 'IBAN : FR76 1234 5678 9123 4567 8912 345', 0, 1);
        $this->pdf->Cell(0, 5, 'BIC : ABCDEFGHIJK', 0, 1);

        $this->pdf->Ln(10);

        // Remarque
        $this->pdf->SetFont('Arial', 'I', 9);
        $this->pdf->Cell(0, 5, 'Facture acquittée - TVA récupérable sur la base de cette facture', 0, 1);
        $this->pdf->Cell(0, 5, 'En cas de retard de paiement, une pénalité de 3 fois le taux d\'intérêt légal sera appliquée.', 0, 1);
        $this->pdf->Cell(0, 5, 'Une indemnité forfaitaire de 40€ pour frais de recouvrement sera due.', 0, 1);

        $this->pdf->Ln(10);
    }

    protected function generateFooter()
    {
        $this->pdf->SetY(-15);
        $this->pdf->SetFont('Arial', 'I', 8);
        $this->pdf->Cell(0, 10, 'Business Care - SARL au capital de 50 000€ - RCS Paris 123 456 789', 0, 0, 'C');
    }
}
