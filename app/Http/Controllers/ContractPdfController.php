<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContractPdfController extends Controller
{
    public function download($id)
    {
        try {
            if (session('user_type') !== 'societe' && session('user_type') !== 'admin') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société ou administrateur pour accéder à cette page.');
            }

            $contract = Contract::with('company')->findOrFail($id);

            // Vérifier que l'utilisateur a accès à ce contrat
            if (session('user_type') === 'societe' && session('user_id') != $contract->company_id) {
                return back()->with('error', 'Vous n\'avez pas accès à ce contrat.');
            }

            // Récupérer la société associée au contrat
            $company = $contract->company;

            // Créer un PDF avec support UTF-8
            $pdf = new \FPDF('P', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetAutoPageBreak(true, 15);

            // Fonction pour gérer l'UTF-8
            function utf8_to_latin($text) {
                $text = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
                return $text ? $text : 'Error encoding text';
            }

            // En-tête
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, utf8_to_latin('BUSINESS CARE'), 0, 1, 'C');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Contrat de Services'), 0, 1, 'C');
            $pdf->Cell(0, 5, utf8_to_latin('N°' . $contract->id), 0, 1, 'C');
            $pdf->Ln(10);

            // Date du contrat
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 5, utf8_to_latin('Date du contrat : ' . date('d/m/Y')), 0, 1, 'R');
            $pdf->Ln(5);

            // Informations client
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Informations du client'), 0, 1);

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(50, 5, utf8_to_latin('Société :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->name), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Adresse :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->address), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Code postal :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->code_postal), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Ville :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->ville), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Email :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->email), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Téléphone :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->telephone), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('SIRET :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->siret ?? 'Non renseigné'), 0, 1);

            $pdf->Ln(10);

            // Détails du contrat
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Détails du contrat'), 0, 1);

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(50, 5, utf8_to_latin('Formule :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($contract->formule_abonnement), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Date de début :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin(date('d/m/Y', strtotime($contract->start_date))), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Date de fin :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin(date('d/m/Y', strtotime($contract->end_date))), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Durée :'), 0);
            $duration = \Carbon\Carbon::parse($contract->start_date)->diffInMonths($contract->end_date);
            $pdf->Cell(0, 5, utf8_to_latin($duration . ' mois'), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Méthode de paiement :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($contract->payment_method), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Statut :'), 0);
            $status = '';
            switch ($contract->payment_status) {
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
                    $status = $contract->payment_status;
            }
            $pdf->Cell(0, 5, utf8_to_latin($status), 0, 1);

            $pdf->Ln(10);

            // Services inclus
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Services inclus'), 0, 1);

            $pdf->SetFont('Arial', '', 10);

            // Traiter le texte multilignes pour l'adapter au PDF
            $services = explode("\n", $contract->services);
            foreach ($services as $service) {
                if (!empty(trim($service))) {
                    $pdf->MultiCell(0, 5, utf8_to_latin(trim($service)), 0);
                }
            }

            $pdf->Ln(10);

            // Tarification
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Tarification'), 0, 1);

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(90, 7, utf8_to_latin('Montant mensuel :'), 1);
            $pdf->Cell(0, 7, utf8_to_latin(number_format($contract->amount, 2, ',', ' ') . ' €'), 1, 1, 'R');

            $pdf->Cell(90, 7, utf8_to_latin('Nombre de mois :'), 1);
            $pdf->Cell(0, 7, utf8_to_latin($duration), 1, 1, 'R');

            $pdf->Cell(90, 7, utf8_to_latin('Total du contrat :'), 1);
            $total = $contract->amount * $duration;
            $pdf->Cell(0, 7, utf8_to_latin(number_format($total, 2, ',', ' ') . ' €'), 1, 1, 'R');

            $pdf->Ln(10);

            // Conditions générales
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Conditions générales'), 0, 1);

            $pdf->SetFont('Arial', '', 10);
            $terms = "Le présent contrat de services est établi entre Business Care et la société mentionnée ci-dessus. ";
            $terms .= "Il définit les modalités de fourniture des services pour la formule " . $contract->formule_abonnement . ". ";
            $terms .= "Le contrat prend effet à la date de début indiquée et se termine à la date de fin, sauf résiliation anticipée conformément aux conditions générales de vente. ";
            $terms .= "Le paiement s'effectue mensuellement selon la méthode de paiement choisie. ";
            $terms .= "Toute résiliation anticipée peut entraîner des frais selon les termes du contrat.";

            $pdf->MultiCell(0, 5, utf8_to_latin($terms), 0);
            $pdf->Ln(10);

            // Signatures
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Signatures'), 0, 1);

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(95, 40, utf8_to_latin('Pour Business Care :'), 1);
            $pdf->Cell(0, 40, utf8_to_latin('Pour ' . $company->name . ' :'), 1, 1);

            $pdf->Ln(10);

            // Pied de page
            $pdf->SetY(-15);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(0, 10, utf8_to_latin('Business Care - Contrat de services #' . $contract->id . ' - Page ' . $pdf->PageNo()), 0, 0, 'C');

            // Nom du fichier à télécharger
            $filename = 'contrat_' . $contract->id . '_' . date('Ymd') . '.pdf';

            // Télécharger le PDF
            return $pdf->Output('D', $filename);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF du contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la génération du PDF du contrat.');
        }
    }

    public function show($id)
    {
        try {
            if (session('user_type') !== 'societe' && session('user_type') !== 'admin') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société ou administrateur pour accéder à cette page.');
            }

            $contract = Contract::with('company')->findOrFail($id);

            // Vérifier que l'utilisateur a accès à ce contrat
            if (session('user_type') === 'societe' && session('user_id') != $contract->company_id) {
                return back()->with('error', 'Vous n\'avez pas accès à ce contrat.');
            }

            // Récupérer la société associée au contrat
            $company = $contract->company;

            // Créer un PDF avec support UTF-8
            $pdf = new \FPDF('P', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetAutoPageBreak(true, 15);

            // Fonction pour gérer l'UTF-8
            function utf8_to_latin($text) {
                $text = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', $text);
                return $text ? $text : 'Error encoding text';
            }

            // En-tête
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 10, utf8_to_latin('BUSINESS CARE'), 0, 1, 'C');
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Contrat de Services'), 0, 1, 'C');
            $pdf->Cell(0, 5, utf8_to_latin('N°' . $contract->id), 0, 1, 'C');
            $pdf->Ln(10);

            // Date du contrat
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 5, utf8_to_latin('Date du contrat : ' . date('d/m/Y')), 0, 1, 'R');
            $pdf->Ln(5);

            // Informations client
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Informations du client'), 0, 1);

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(50, 5, utf8_to_latin('Société :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->name), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Adresse :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->address), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Code postal :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->code_postal), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Ville :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->ville), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Email :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->email), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Téléphone :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->telephone), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('SIRET :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($company->siret ?? 'Non renseigné'), 0, 1);

            $pdf->Ln(10);

            // Détails du contrat
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Détails du contrat'), 0, 1);

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(50, 5, utf8_to_latin('Formule :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($contract->formule_abonnement), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Date de début :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin(date('d/m/Y', strtotime($contract->start_date))), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Date de fin :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin(date('d/m/Y', strtotime($contract->end_date))), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Durée :'), 0);
            $duration = \Carbon\Carbon::parse($contract->start_date)->diffInMonths($contract->end_date);
            $pdf->Cell(0, 5, utf8_to_latin($duration . ' mois'), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Méthode de paiement :'), 0);
            $pdf->Cell(0, 5, utf8_to_latin($contract->payment_method), 0, 1);

            $pdf->Cell(50, 5, utf8_to_latin('Statut :'), 0);
            $status = '';
            switch ($contract->payment_status) {
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
                    $status = $contract->payment_status;
            }
            $pdf->Cell(0, 5, utf8_to_latin($status), 0, 1);

            $pdf->Ln(10);

            // Services inclus
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Services inclus'), 0, 1);

            $pdf->SetFont('Arial', '', 10);

            // Traiter le texte multilignes pour l'adapter au PDF
            $services = explode("\n", $contract->services);
            foreach ($services as $service) {
                if (!empty(trim($service))) {
                    $pdf->MultiCell(0, 5, utf8_to_latin(trim($service)), 0);
                }
            }

            $pdf->Ln(10);

            // Tarification
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Tarification'), 0, 1);

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(90, 7, utf8_to_latin('Montant mensuel :'), 1);
            $pdf->Cell(0, 7, utf8_to_latin(number_format($contract->amount, 2, ',', ' ') . ' €'), 1, 1, 'R');

            $pdf->Cell(90, 7, utf8_to_latin('Nombre de mois :'), 1);
            $pdf->Cell(0, 7, utf8_to_latin($duration), 1, 1, 'R');

            $pdf->Cell(90, 7, utf8_to_latin('Total du contrat :'), 1);
            $total = $contract->amount * $duration;
            $pdf->Cell(0, 7, utf8_to_latin(number_format($total, 2, ',', ' ') . ' €'), 1, 1, 'R');

            $pdf->Ln(10);

            // Conditions générales
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Conditions générales'), 0, 1);

            $pdf->SetFont('Arial', '', 10);
            $terms = "Le présent contrat de services est établi entre Business Care et la société mentionnée ci-dessus. ";
            $terms .= "Il définit les modalités de fourniture des services pour la formule " . $contract->formule_abonnement . ". ";
            $terms .= "Le contrat prend effet à la date de début indiquée et se termine à la date de fin, sauf résiliation anticipée conformément aux conditions générales de vente. ";
            $terms .= "Le paiement s'effectue mensuellement selon la méthode de paiement choisie. ";
            $terms .= "Toute résiliation anticipée peut entraîner des frais selon les termes du contrat.";

            $pdf->MultiCell(0, 5, utf8_to_latin($terms), 0);
            $pdf->Ln(10);

            // Signatures
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(0, 10, utf8_to_latin('Signatures'), 0, 1);

            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(95, 40, utf8_to_latin('Pour Business Care :'), 1);
            $pdf->Cell(0, 40, utf8_to_latin('Pour ' . $company->name . ' :'), 1, 1);

            $pdf->Ln(10);

            // Pied de page
            $pdf->SetY(-15);
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(0, 10, utf8_to_latin('Business Care - Contrat de services #' . $contract->id . ' - Page ' . $pdf->PageNo()), 0, 0, 'C');

            // Afficher le PDF dans le navigateur
            return $pdf->Output('I', 'contrat_' . $contract->id . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du PDF du contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage du PDF du contrat.');
        }
    }
}
