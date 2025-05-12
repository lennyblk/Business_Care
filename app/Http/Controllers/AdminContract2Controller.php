<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\AdminContract2Controller as ApiAdminContract2Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Contract;

class AdminContract2Controller extends Controller
{
    protected $apiController;

    public function __construct()
    {
        $this->apiController = new ApiAdminContract2Controller();
    }

    public function index()
    {
        try {
            $response = $this->apiController->getAllContracts();
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return back()->with('error', 'Erreur lors de la récupération des contrats');
            }

            $contracts = $this->arrayToObjects($data['data'] ?? []);

            return view('dashboards.gestion_admin.contracts2.index', compact('contracts'));
        } catch (\Exception $e) {
            Log::error('Exception lors de la récupération des contrats: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function show($id)
    {
        try {
            $response = $this->apiController->getContract($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return back()->with('error', 'Erreur lors de la récupération du contrat');
            }

            $contract = $this->arrayToObject($data['data'] ?? []);

            return view('dashboards.gestion_admin.contracts2.show', compact('contract'));
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'affichage du contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    private function arrayToObject($array)
    {
        if (!is_array($array)) {
            return $array;
        }

        $object = new \stdClass();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $object->$key = $this->arrayToObject($value);
            } else {
                $object->$key = $value;
            }
        }
        return $object;
    }

    private function arrayToObjects($arrayOfArrays)
    {
        $objects = [];
        foreach ($arrayOfArrays as $array) {
            $objects[] = $this->arrayToObject($array);
        }
        return $objects;
    }

    public function markAsPaid($id)
    {
        try {
            $response = $this->apiController->markAsPaid($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                return back()->with('error', $data['message'] ?? 'Erreur lors du marquage du contrat comme payé');
            }

            return redirect()->route('admin.contracts2.show', $id)
                        ->with('success', 'Contrat marqué comme payé avec succès');
        } catch (\Exception $e) {
            Log::error('Exception lors du marquage du contrat comme payé: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue');
        }
    }

    public function download($id)
{
    try {
        $contract = Contract::with('company')->findOrFail($id);

        // Créer le PDF directement
        $pdf = new \Codedge\Fpdf\Fpdf\Fpdf();
        $pdf->AddPage();

        // En-tête
        $pdf->SetFont('Arial', 'B', 16);
        $pdf->Cell(0, 10, utf8_decode('BUSINESS CARE'), 0, 1, 'C');
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, utf8_decode('Contrat de Services'), 0, 1, 'C');
        $pdf->Cell(0, 5, utf8_decode('N°' . $contract->id), 0, 1, 'C');
        $pdf->Ln(10);

        // Date du contrat
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(0, 5, utf8_decode('Date du contrat : ' . date('d/m/Y')), 0, 1, 'R');
        $pdf->Ln(5);

        // Informations du client
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Informations du client'), 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 5, utf8_decode('Société :'), 0);
        $pdf->Cell(0, 5, utf8_decode($contract->company->name ?? 'N/A'), 0, 1);

        $pdf->Cell(50, 5, utf8_decode('Adresse :'), 0);
        $pdf->Cell(0, 5, utf8_decode($contract->company->address ?? 'N/A'), 0, 1);

        $pdf->Cell(50, 5, utf8_decode('Email :'), 0);
        $pdf->Cell(0, 5, utf8_decode($contract->company->email ?? 'N/A'), 0, 1);

        $pdf->Cell(50, 5, utf8_decode('Téléphone :'), 0);
        $pdf->Cell(0, 5, utf8_decode($contract->company->telephone ?? 'N/A'), 0, 1);

        $pdf->Ln(10);

        // Détails du contrat
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Détails du contrat'), 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(50, 5, utf8_decode('Formule :'), 0);
        $pdf->Cell(0, 5, utf8_decode($contract->formule_abonnement ?? 'N/A'), 0, 1);

        $pdf->Cell(50, 5, utf8_decode('Date de début :'), 0);
        $pdf->Cell(0, 5, utf8_decode(date('d/m/Y', strtotime($contract->start_date))), 0, 1);

        $pdf->Cell(50, 5, utf8_decode('Date de fin :'), 0);
        $pdf->Cell(0, 5, utf8_decode(date('d/m/Y', strtotime($contract->end_date))), 0, 1);

        $pdf->Cell(50, 5, utf8_decode('Durée :'), 0);
        $duration = \Carbon\Carbon::parse($contract->start_date)->diffInMonths($contract->end_date);
        $pdf->Cell(0, 5, utf8_decode($duration . ' mois'), 0, 1);

        $pdf->Cell(50, 5, utf8_decode('Méthode de paiement :'), 0);
        $pdf->Cell(0, 5, utf8_decode($contract->payment_method ?? 'N/A'), 0, 1);

        $pdf->Cell(50, 5, utf8_decode('Statut :'), 0);
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
        $pdf->Cell(0, 5, utf8_decode($status), 0, 1);

        $pdf->Ln(10);

        // Services inclus
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Services inclus'), 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $services = $contract->services ?? 'Aucun service spécifié';
        $pdf->MultiCell(0, 5, utf8_decode($services), 0, 'L');

        $pdf->Ln(10);

        // Tarification
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Tarification'), 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(90, 7, utf8_decode('Montant mensuel :'), 1);
        $pdf->Cell(0, 7, utf8_decode(number_format($contract->amount, 2, ',', ' ') . ' ') . chr(128), 1, 1, 'R');

        $pdf->Cell(90, 7, utf8_decode('Nombre de mois :'), 1);
        $pdf->Cell(0, 7, utf8_decode($duration), 1, 1, 'R');

        $pdf->Cell(90, 7, utf8_decode('Total du contrat :'), 1);
        $total = $contract->amount * $duration;
        $pdf->Cell(0, 7, utf8_decode(number_format($contract->amount, 2, ',', ' ') . ' ') . chr(128), 1, 1, 'R');

        $pdf->Ln(10);

        // Conditions générales
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Conditions générales'), 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $terms = "Le présent contrat de services est établi entre Business Care et la société mentionnée ci-dessus. ";
        $terms .= "Il définit les modalités de fourniture des services pour la formule " . $contract->formule_abonnement . ". ";
        $terms .= "Le contrat prend effet à la date de début indiquée et se termine à la date de fin, sauf résiliation anticipée conformément aux conditions générales de vente. ";
        $terms .= "Le paiement s'effectue mensuellement selon la méthode de paiement choisie. ";
        $terms .= "Toute résiliation anticipée peut entraîner des frais selon les termes du contrat.";

        $pdf->MultiCell(0, 5, utf8_decode($terms), 0, 'L');
        $pdf->Ln(10);

        // Signatures
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 10, utf8_decode('Signatures'), 0, 1);

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(95, 40, utf8_decode('Pour Business Care :'), 1);
        $pdf->Cell(0, 40, utf8_decode('Pour ' . ($contract->company->name ?? 'N/A') . ' :'), 1, 1);

        $pdf->Ln(10);

        // Pied de page
        $pdf->SetY(-15);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 10, utf8_decode('Business Care - Contrat de services #' . $contract->id . ' - Page ' . $pdf->PageNo()), 0, 0, 'C');

        // Télécharger le PDF
        $filename = 'contrat_' . $contract->id . '_' . date('Ymd') . '.pdf';
        return $pdf->Output('D', $filename);

    } catch (\Exception $e) {
        \Log::error('Erreur lors de la génération du PDF: ' . $e->getMessage());
        return back()->with('error', 'Impossible de générer le PDF: ' . $e->getMessage());
    }
}

}
