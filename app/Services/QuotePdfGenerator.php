<?php

namespace App\Services;

use App\Models\Quote;
use Codedge\Fpdf\Fpdf\Fpdf;

class QuotePdfGenerator extends Fpdf
{
    protected $quote;

    public function __construct(Quote $quote)
    {
        parent::__construct();
        $this->quote = $quote;
    }

    public function generate()
    {
        // Configuration du document
        $this->AddPage();
        $this->SetAutoPageBreak(true, 15);

        // En-tête
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'BUSINESS CARE', 0, 1, 'C');
        $this->Cell(0, 10, 'DEVIS', 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 5, 'N° ' . ($this->quote->reference_number ?? 'DEVIS-' . $this->quote->id), 0, 1, 'C');
        $this->Ln(10);

        // Informations client
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Informations du client', 0, 1);
        $this->SetFont('Arial', '', 10);
        $this->Cell(50, 5, 'Entreprise:', 0);
        $this->Cell(0, 5, $this->quote->company->name ?? '-', 0, 1);
        $this->Cell(50, 5, 'Date de création:', 0);
        $this->Cell(0, 5, $this->quote->creation_date->format('d/m/Y'), 0, 1);
        $this->Ln(5);

        // Détails de la formule
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Détails de la formule ' . $this->quote->formule_abonnement, 0, 1);
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, '• Activités par mois: ' . $this->quote->activities_count, 0, 1);
        $this->Cell(0, 6, '• RDV médicaux par mois: ' . $this->quote->medical_appointments, 0, 1);
        $this->Cell(0, 6, '• Questions chatbot: ' . $this->quote->chatbot_questions, 0, 1);
        $this->Cell(0, 6, '• RDV supplémentaires: ' . $this->quote->extra_appointment_fee . '€', 0, 1);
        $this->Cell(0, 6, '• Conseils hebdomadaires: ' . ($this->quote->weekly_advice ? 'Oui' : 'Non'), 0, 1);
        $this->Ln(5);

        // Tarification
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Tarification', 0, 1);
        $this->SetFont('Arial', '', 10);
        $this->Cell(90, 7, 'Prix par salarié:', 1);
        $this->Cell(0, 7, number_format($this->quote->price_per_employee, 2, ',', ' ') . ' €', 1, 1, 'R');
        $this->Cell(90, 7, 'Nombre de salariés:', 1);
        $this->Cell(0, 7, $this->quote->company_size, 1, 1, 'R');
        $this->Cell(90, 7, 'Total HT:', 1);
        $this->Cell(0, 7, number_format($this->quote->total_amount, 2, ',', ' ') . ' €', 1, 1, 'R');
        $this->Cell(90, 7, 'TVA (20%):', 1);
        $this->Cell(0, 7, number_format($this->quote->total_amount * 0.2, 2, ',', ' ') . ' €', 1, 1, 'R');
        $this->Cell(90, 7, 'Total TTC:', 1);
        $this->Cell(0, 7, number_format($this->quote->total_amount * 1.2, 2, ',', ' ') . ' €', 1, 1, 'R');

        return $this;
    }
}
