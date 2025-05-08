<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Services\ContractPdfGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContractPdfController extends Controller
{
    /**
     * Générer et télécharger le PDF d'un contrat
     *
     * @param int $id ID du contrat
     * @return \Illuminate\Http\Response
     */
    public function download($id)
    {
        try {
            // Vérifier que l'utilisateur est connecté et a le droit d'accéder à ce contrat
            if (session('user_type') !== 'societe' && session('user_type') !== 'admin') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société ou administrateur pour accéder à cette page.');
            }

            // Récupérer le contrat
            $contract = Contract::findOrFail($id);

            // Vérifier que l'utilisateur a accès à ce contrat
            if (session('user_type') === 'societe' && session('user_id') != $contract->company_id) {
                return back()->with('error', 'Vous n\'avez pas accès à ce contrat.');
            }

            // Générer le PDF
            $pdfGenerator = new ContractPdfGenerator($contract);
            $pdf = $pdfGenerator->generate();

            // Nom du fichier à télécharger
            $filename = 'contrat_' . $contract->id . '_' . date('Ymd') . '.pdf';

            // Retourner le PDF
            return $pdf->Output('D', $filename);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF du contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la génération du PDF du contrat.');
        }
    }

    /**
     * Afficher le PDF dans le navigateur
     *
     * @param int $id ID du contrat
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            // Vérifier que l'utilisateur est connecté et a le droit d'accéder à ce contrat
            if (session('user_type') !== 'societe' && session('user_type') !== 'admin') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société ou administrateur pour accéder à cette page.');
            }

            // Récupérer le contrat
            $contract = Contract::findOrFail($id);

            // Vérifier que l'utilisateur a accès à ce contrat
            if (session('user_type') === 'societe' && session('user_id') != $contract->company_id) {
                return back()->with('error', 'Vous n\'avez pas accès à ce contrat.');
            }

            // Générer le PDF
            $pdfGenerator = new ContractPdfGenerator($contract);
            $pdf = $pdfGenerator->generate();

            // Afficher le PDF dans le navigateur
            return $pdf->Output('I', 'contrat_' . $contract->id . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du PDF du contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage du PDF du contrat.');
        }
    }
}
