<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProviderAssignment;
use App\Models\EventProposal;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProviderAssignmentController extends Controller
{
    /**
     * Récupère toutes les assignations
     */
    public function index()
    {
        try {
            $assignments = ProviderAssignment::with(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location'])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Liste des assignations récupérée avec succès',
                'data' => $assignments
            ]);
        } catch (\Exception $e) {
            Log::error('API ProviderAssignmentController@index error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des assignations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère une assignation par son ID
     */
    public function show($id)
    {
        try {
            $assignment = ProviderAssignment::with(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Assignation récupérée avec succès',
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            Log::error('API ProviderAssignmentController@show error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Assignation non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Crée une nouvelle assignation
     */
    public function store(Request $request)
    {
        try {
            Log::info('Données reçues dans store', $request->all());

            $validator = Validator::make($request->all(), [
                'provider_id' => 'required|exists:provider,id',
                'event_proposal_id' => 'required|exists:event_proposal,id',
                'status' => 'required|in:Proposed,Accepted,Rejected',
                'payment_amount' => 'required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                Log::warning('Validation échouée', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $assignment = new ProviderAssignment();
            $assignment->provider_id = $request->provider_id;
            $assignment->event_proposal_id = $request->event_proposal_id;
            $assignment->status = $request->status;
            $assignment->payment_amount = $request->payment_amount;
            $assignment->proposed_at = now();

            if ($request->status !== 'Proposed') {
                $assignment->response_at = now();
            }

            $assignment->save();

            // Charger les relations pour la réponse
            $assignment->load(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location']);

            return response()->json([
                'success' => true,
                'message' => 'Assignation créée avec succès',
                'data' => $assignment
            ], 201);
        } catch (\Exception $e) {
            Log::error('API ProviderAssignmentController@store error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'assignation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Met à jour une assignation
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'sometimes|required|in:Proposed,Accepted,Rejected',
                'payment_amount' => 'sometimes|required|numeric|min:0'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données invalides',
                    'errors' => $validator->errors()
                ], 422);
            }

            $assignment = ProviderAssignment::findOrFail($id);

            // Si le statut change de Proposed à autre chose, enregistrer la date de réponse
            if ($assignment->status === 'Proposed' && $request->has('status') && $request->status !== 'Proposed') {
                $assignment->response_at = now();
            }

            // Mettre à jour les champs si présents dans la requête
            if ($request->has('status')) {
                $assignment->status = $request->status;
            }

            if ($request->has('payment_amount')) {
                $assignment->payment_amount = $request->payment_amount;
            }

            $assignment->save();

            // Charger les relations pour la réponse
            $assignment->load(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location']);

            return response()->json([
                'success' => true,
                'message' => 'Assignation mise à jour avec succès',
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            Log::error('API ProviderAssignmentController@update error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de l\'assignation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime une assignation
     */
    public function destroy($id)
    {
        try {
            $assignment = ProviderAssignment::findOrFail($id);
            $assignment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Assignation supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('API ProviderAssignmentController@destroy error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de l\'assignation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les assignations par prestataire et statut
     */
    public function getByProviderAndStatus($providerId, $status)
    {
        try {
            Log::info("Récupération des assignations pour provider_id=$providerId avec status=$status");

            $assignments = ProviderAssignment::with(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location'])
                ->where('provider_id', $providerId)
                ->where('status', $status)
                ->orderBy('proposed_at', 'desc')
                ->get();

            Log::info("Nombre d'assignations trouvées: " . count($assignments));

            return response()->json([
                'success' => true,
                'message' => 'Assignations récupérées avec succès',
                'data' => $assignments
            ]);
        } catch (\Exception $e) {
            Log::error('API ProviderAssignmentController@getByProviderAndStatus error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des assignations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère une assignation par ID et prestataire
     */
    public function getByIdAndProvider($id, $providerId)
    {
        try {
            $assignment = ProviderAssignment::with(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location'])
                ->where('id', $id)
                ->where('provider_id', $providerId)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Assignation récupérée avec succès',
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            Log::error('API ProviderAssignmentController@getByIdAndProvider error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Assignation non trouvée',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Accepte une assignation
     */
    public function acceptAssignment($id, $providerId)
    {
        try {
            $assignment = ProviderAssignment::with(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location'])
                ->where('id', $id)
                ->where('provider_id', $providerId)
                ->where('status', 'Proposed')
                ->firstOrFail();

            // Accepter l'assignation
            $assignment->status = 'Accepted';
            $assignment->response_at = now();
            $assignment->save();

            // Mettre à jour le statut de la proposition
            $eventProposal = EventProposal::findOrFail($assignment->event_proposal_id);
            $eventProposal->status = 'Accepted';
            $eventProposal->save();

            // Créer un événement basé sur la proposition
            $serviceType = $eventProposal->eventType;

            $event = new Event([
                'name' => $serviceType->title,
                'description' => $serviceType->description,
                'date' => $eventProposal->proposed_date,
                'event_type' => 'Workshop', // Type par défaut
                'capacity' => 30, // Capacité par défaut
                'location' => $eventProposal->location->name,
                'company_id' => $eventProposal->company_id,
                'event_proposal_id' => $eventProposal->id,
                'duration' => $eventProposal->duration ?? 60 // Utiliser la durée avec fallback à 60 minutes
            ]);

            $event->save();

            // Recharger les relations pour la réponse
            $assignment->load(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location']);

            return response()->json([
                'success' => true,
                'message' => 'Assignation acceptée avec succès',
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            Log::error('API ProviderAssignmentController@acceptAssignment error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'acceptation de l\'assignation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Rejette une assignation
     */
    public function rejectAssignment($id, $providerId)
    {
        try {
            $assignment = ProviderAssignment::with(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location'])
                ->where('id', $id)
                ->where('provider_id', $providerId)
                ->where('status', 'Proposed')
                ->firstOrFail();

            // Rejeter l'assignation
            $assignment->status = 'Rejected';
            $assignment->response_at = now();
            $assignment->save();

            // Recharger les relations pour la réponse
            $assignment->load(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location']);

            return response()->json([
                'success' => true,
                'message' => 'Assignation rejetée avec succès',
                'data' => $assignment
            ]);
        } catch (\Exception $e) {
            Log::error('API ProviderAssignmentController@rejectAssignment error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rejet de l\'assignation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les assignations par proposition d'événement
     */
    public function getByEventProposal($eventProposalId)
    {
        try {
            $assignments = ProviderAssignment::with(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location'])
                ->where('event_proposal_id', $eventProposalId)
                ->orderBy('proposed_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Assignations récupérées avec succès',
                'data' => $assignments
            ]);
        } catch (\Exception $e) {
            Log::error('API ProviderAssignmentController@getByEventProposal error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des assignations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
