<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Company;
use Codedge\Fpdf\Fpdf\Fpdf;

class ContractPdfGenerator extends FPDF
{
    protected $contract;
    protected $company;

    public function __construct(Contract $contract)
    {
        parent::__construct();
        $this->contract = $contract;
        $this->company = Company::find($contract->company_id);
        
        $this->SetFont('Arial', '', 11);
    }

    public function Text($x, $y, $txt)
    {
        parent::Text($x, $y, utf8_decode($txt));
    }

    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        parent::Cell($w, $h, utf8_decode($txt), $border, $ln, $align, $fill, $link);
    }

    public function MultiCell($w, $h, $txt, $border = 0, $align = 'J', $fill = false)
    {
        parent::MultiCell($w, $h, utf8_decode($txt), $border, $align, $fill);
    }

    public function generate()
    {
        // Configuration du document
        $this->AddPage();
        $this->SetAutoPageBreak(true, 15);

        // En-tête
        $this->generateHeader();

        // Informations client
        $this->generateClientInfo();

        // Détails du contrat
        $this->generateContractDetails();

        // Services inclus
        $this->generateServices();

        // Tarification
        $this->generatePricing();

        // Conditions
        $this->generateTerms();

        // Signatures
        $this->generateSignatures();

        // Pied de page
        $this->generateFooter();

        return $this;
    }

    protected function generateHeader()
    {
        // Logo et en-tête
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'BUSINESS CARE', 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 10, 'Contrat de Services', 0, 1, 'C');
        $this->Cell(0, 5, 'N°' . $this->contract->id, 0, 1, 'C');
        $this->Ln(10);

        // Date du contrat
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Date du contrat : ' . date('d/m/Y'), 0, 1, 'R');
        $this->Ln(5);
    }

    protected function generateClientInfo()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Informations du client', 0, 1);

        $this->SetFont('Arial', '', 10);
        $this->Cell(50, 5, 'Société :', 0);
        $this->Cell(0, 5, $this->company->name, 0, 1);

        $this->Cell(50, 5, 'Adresse :', 0);
        $this->Cell(0, 5, $this->company->address, 0, 1);

        $this->Cell(50, 5, 'Code postal :', 0);
        $this->Cell(0, 5, $this->company->code_postal, 0, 1);

        $this->Cell(50, 5, 'Ville :', 0);
        $this->Cell(0, 5, $this->company->ville, 0, 1);

        $this->Cell(50, 5, 'Email :', 0);
        $this->Cell(0, 5, $this->company->email, 0, 1);

        $this->Cell(50, 5, 'Téléphone :', 0);
        $this->Cell(0, 5, $this->company->telephone, 0, 1);

        $this->Cell(50, 5, 'SIRET :', 0);
        $this->Cell(0, 5, $this->company->siret ?? 'Non renseigné', 0, 1);

        $this->Ln(10);
    }

    protected function generateContractDetails()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Détails du contrat', 0, 1);

        $this->SetFont('Arial', '', 10);
        $this->Cell(50, 5, 'Formule :', 0);
        $this->Cell(0, 5, $this->contract->formule_abonnement, 0, 1);

        $this->Cell(50, 5, 'Date de début :', 0);
        $this->Cell(0, 5, date('d/m/Y', strtotime($this->contract->start_date)), 0, 1);

        $this->Cell(50, 5, 'Date de fin :', 0);
        $this->Cell(0, 5, date('d/m/Y', strtotime($this->contract->end_date)), 0, 1);

        $this->Cell(50, 5, 'Durée :', 0);
        $duration = \Carbon\Carbon::parse($this->contract->start_date)->diffInMonths($this->contract->end_date);
        $this->Cell(0, 5, $duration . ' mois', 0, 1);

        $this->Cell(50, 5, 'Méthode de paiement :', 0);
        $this->Cell(0, 5, $this->contract->payment_method, 0, 1);

        $this->Cell(50, 5, 'Statut :', 0);
        $status = '';
        switch ($this->contract->payment_status) {
            case 'pending':
                $status = 'En attente d\'approbation';
                break;
            case 'unpaid':
                $status = 'Non payé';
                break;
            case 'processing':
                $status = 'Paiement en cours';
                break;
            case 'active':
                $status = 'Actif';
                break;
            default:
                $status = $this->contract->payment_status;
        }
        $this->Cell(0, 5, $status, 0, 1);

        $this->Ln(10);
    }

    protected function generateServices()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Services inclus', 0, 1);

        $this->SetFont('Arial', '', 10);

        // Traiter le texte multilignes pour l'adapter au PDF
        $services = explode("\n", $this->contract->services);
        foreach ($services as $service) {
            if (!empty(trim($service))) {
                $this->MultiCell(0, 5, trim($service), 0);
            }
        }

        $this->Ln(10);
    }

    protected function generatePricing()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Tarification', 0, 1);

        $this->SetFont('Arial', '', 10);
        $this->Cell(90, 7, 'Montant mensuel :', 1);
        $this->Cell(0, 7, number_format($this->contract->amount, 2, ',', ' ') . ' €', 1, 1, 'R');

        $this->Cell(90, 7, 'Nombre de mois :', 1);
        $duration = \Carbon\Carbon::parse($this->contract->start_date)->diffInMonths($this->contract->end_date);
        $this->Cell(0, 7, $duration, 1, 1, 'R');

        $this->Cell(90, 7, 'Total du contrat :', 1);
        $total = $this->contract->amount * $duration;
        $this->Cell(0, 7, number_format($total, 2, ',', ' ') . ' €', 1, 1, 'R');

        $this->Ln(10);
    }

    protected function generateTerms()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Conditions générales', 0, 1);

        $this->SetFont('Arial', '', 10);
        $terms = "Le présent contrat de services est établi entre Business Care et la société mentionnée ci-dessus. ";
        $terms .= "Il définit les modalités de fourniture des services pour la formule " . $this->contract->formule_abonnement . ". ";
        $terms .= "Le contrat prend effet à la date de début indiquée et se termine à la date de fin, sauf résiliation anticipée conformément aux conditions générales de vente. ";
        $terms .= "Le paiement s'effectue mensuellement selon la méthode de paiement choisie. ";
        $terms .= "Toute résiliation anticipée peut entraîner des frais selon les termes du contrat.";

        $this->MultiCell(0, 5, $terms, 0);
        $this->Ln(10);
    }

    protected function generateSignatures()
    {
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'Signatures', 0, 1);

        $this->SetFont('Arial', '', 10);
        $this->Cell(95, 40, 'Pour Business Care :', 1);
        $this->Cell(0, 40, 'Pour ' . $this->company->name . ' :', 1, 1);

        $this->Ln(10);
    }

    protected function generateFooter()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Business Care - Contrat de services #' . $this->contract->id . ' - Page ' . $this->PageNo(), 0, 0, 'C');
    }
}
