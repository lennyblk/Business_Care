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
        // Vérifier le type d'utilisateur connecté
        if (session('user_type') === 'societe') {
            $company_id = session('user_id');
            $invoices = Invoice::where('company_id', $company_id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        } elseif (session('user_type') === 'admin') {
            $invoices = Invoice::orderBy('created_at', 'desc')
                ->paginate(10);
        } else {
            return redirect()->route('login')
                ->with('error', 'Vous n\'avez pas accès à cette page.');
        }

        return view('invoices.index', compact('invoices'));
    }

    public function show($id)
    {
        // Vérifier que l'utilisateur est connecté
        if (!session('user_type')) {
            return redirect()->route('login')
                ->with('error', 'Vous devez être connecté pour accéder à cette page.');
        }

        // Récupérer la facture
        $invoice = Invoice::findOrFail($id);

        // Vérifier les droits d'accès
        if (session('user_type') === 'societe' && session('user_id') != $invoice->company_id) {
            return back()->with('error', 'Vous n\'avez pas accès à cette facture.');
        }

        return view('invoices.show', compact('invoice'));
    }


    public function download($id)
    {
        try {
            // Vérifier que l'utilisateur est connecté
            if (!session('user_type')) {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté pour accéder à cette page.');
            }

            // Récupérer la facture
            $invoice = Invoice::findOrFail($id);

            // Vérifier les droits d'accès
            if (session('user_type') === 'societe' && session('user_id') != $invoice->company_id) {
                return back()->with('error', 'Vous n\'avez pas accès à cette facture.');
            }

            // Récupérer le contrat associé
            $contract = Contract::findOrFail($invoice->contract_id);

            // Générer le PDF
            $pdfGenerator = new InvoicePdfGenerator($contract, $invoice->invoice_number);
            $pdf = $pdfGenerator->generate();

            // Nom du fichier à télécharger
            $filename = 'facture_' . $invoice->invoice_number . '.pdf';

            // Retourner le PDF pour téléchargement
            return $pdf->Output('D', $filename);

        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération du PDF de facture: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la génération du PDF de facture.');
        }
    }

    public function viewPdf($id)
    {
        try {
            // Vérifier que l'utilisateur est connecté
            if (!session('user_type')) {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté pour accéder à cette page.');
            }

            // Récupérer la facture
            $invoice = Invoice::findOrFail($id);

            // Vérifier les droits d'accès
            if (session('user_type') === 'societe' && session('user_id') != $invoice->company_id) {
                return back()->with('error', 'Vous n\'avez pas accès à cette facture.');
            }

            // Récupérer le contrat associé
            $contract = Contract::findOrFail($invoice->contract_id);

            // Générer le PDF
            $pdfGenerator = new InvoicePdfGenerator($contract, $invoice->invoice_number);
            $pdf = $pdfGenerator->generate();

            // Afficher le PDF dans le navigateur
            return $pdf->Output('I', 'facture_' . $invoice->invoice_number . '.pdf');

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage du PDF de facture: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'affichage du PDF de facture.');
        }
    }


    public function pay($id)
    {
        try {
            // Vérifier que l'utilisateur est connecté en tant que société
            if (session('user_type') !== 'societe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société pour effectuer cette action.');
            }

            // Récupérer la facture
            $invoice = Invoice::findOrFail($id);

            // Vérifier les droits d'accès
            if (session('user_id') != $invoice->company_id) {
                return back()->with('error', 'Vous n\'avez pas accès à cette facture.');
            }

            // Vérifier que la facture n'est pas déjà payée
            if ($invoice->status === 'paid') {
                return back()->with('error', 'Cette facture a déjà été payée.');
            }

            // Rediriger vers la page de paiement
            return redirect()->route('payments.process', ['invoice' => $invoice->id]);

        } catch (\Exception $e) {
            Log::error('Erreur lors du paiement de la facture: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du paiement de la facture.');
        }
    }
}
