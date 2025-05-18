<?php

namespace App\Http\Controllers;

use App\Http\Controllers\API\AdminPendingRegistrationController as APIPendingRegistrationController;
use App\Models\PendingRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminPendingRegistrationController extends Controller
{
    protected $apiController;

    public function __construct()
    {
        $this->apiController = new APIPendingRegistrationController();
    }

    public function index()
    {
        try {
            $pendingRegistrations = PendingRegistration::where('status', 'pending')
                                                      ->orderBy('created_at', 'desc')
                                                      ->get();

            return view('dashboards.gestion_admin.inscriptions.index', compact('pendingRegistrations'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage des inscriptions en attente: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du chargement des inscriptions en attente.');
        }
    }

    public function show($id)
    {
        try {
            $registration = PendingRegistration::findOrFail($id);
            return view('dashboards.gestion_admin.inscriptions.show', compact('registration'));
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de l\'inscription #'.$id.': ' . $e->getMessage());
            return back()->with('error', 'Inscription non trouvée.');
        }
    }

    public function approve($id)
    {
        try {
            Log::info('Web: Tentative d\'approbation pour l\'inscription #'.$id);

            // Vérifier que l'inscription existe et est en attente
            $registration = PendingRegistration::findOrFail($id);

            if ($registration->status !== 'pending') {
                return redirect()->back()->withErrors(['error' => 'Cette demande a déjà été traitée.']);
            }

            // Appel au contrôleur API
            $request = new Request(['id' => $id]);
            $response = $this->apiController->approve($id);

            // Si c'est une réponse JSON (format API), la convertir
            if (is_object($response) && method_exists($response, 'getContent')) {
                $jsonResponse = json_decode($response->getContent(), true);
                if (isset($jsonResponse['success'])) {
                    return redirect()->route('admin.inscriptions.index')
                        ->with('success', $jsonResponse['message'] ?? 'Demande d\'inscription approuvée avec succès.');
                }

                if (isset($jsonResponse['error'])) {
                    return redirect()->back()->withErrors(['error' => $jsonResponse['error']]);
                }
            }

            // Si la réponse est déjà un redirect, le retourner
            if (is_object($response) && is_a($response, 'Illuminate\Http\RedirectResponse')) {
                return $response;
            }

            // Fallback
            return redirect()->route('admin.inscriptions.index')
                ->with('success', 'Demande d\'inscription approuvée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'approbation de l\'inscription #'.$id.': ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue lors de l\'approbation: ' . $e->getMessage()]);
        }
    }

    public function reject($id)
    {
        try {
            Log::info('Web: Tentative de rejet pour l\'inscription #'.$id);

            // Vérifier que l'inscription existe et est en attente
            $registration = PendingRegistration::findOrFail($id);

            if ($registration->status !== 'pending') {
                return redirect()->back()->withErrors(['error' => 'Cette demande a déjà été traitée.']);
            }

            // Appel au contrôleur API
            $request = new Request(['id' => $id]);
            $response = $this->apiController->reject($id);

            // Si c'est une réponse JSON (format API), la convertir
            if (is_object($response) && method_exists($response, 'getContent')) {
                $jsonResponse = json_decode($response->getContent(), true);
                if (isset($jsonResponse['success'])) {
                    return redirect()->route('admin.inscriptions.index')
                        ->with('success', $jsonResponse['message'] ?? 'Demande d\'inscription rejetée avec succès.');
                }

                if (isset($jsonResponse['error'])) {
                    return redirect()->back()->withErrors(['error' => $jsonResponse['error']]);
                }
            }

            // Si la réponse est déjà un redirect, le retourner
            if (is_object($response) && is_a($response, 'Illuminate\Http\RedirectResponse')) {
                return $response;
            }

            // Fallback
            return redirect()->route('admin.inscriptions.index')
                ->with('success', 'Demande d\'inscription rejetée avec succès.');
        } catch (\Exception $e) {
            Log::error('Erreur lors du rejet de l\'inscription #'.$id.': ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue lors du rejet: ' . $e->getMessage()]);
        }
    }
}
