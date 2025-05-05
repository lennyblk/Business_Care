<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\EventProposal;
use App\Models\Location;
use App\Models\ServiceType;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EventProposalController extends Controller
{
    /**
     * Récupère les types d'activités disponibles et les emplacements
     */
    public function getFormData()
    {
        // Récupérer les types d'activités
        $activityTypes = [
            'rencontre sportive' => 'Rencontre sportive',
            'conférence' => 'Conférence',
            'webinar' => 'Webinar',
            'yoga' => 'Yoga',
            'pot' => 'Pot',
            'séance d\'art plastiques' => 'Séance d\'art plastiques',
            'session jeu vidéo' => 'Session jeu vidéo',
            'autre' => 'Autre'
        ];

        // Récupérer les emplacements des villes Business Care
        $locations = Location::whereIn('city', ['Paris', 'Troyes', 'Biarritz', 'Nice'])->get();

        // S'il n'y a pas d'emplacements, les créer
        if ($locations->isEmpty()) {
            $this->createDefaultLocations();
            $locations = Location::whereIn('city', ['Paris', 'Troyes', 'Biarritz', 'Nice'])->get();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'activityTypes' => $activityTypes,
                'locations' => $locations
            ]
        ]);
    }

    /**
     * Liste toutes les propositions d'événements
     */
    public function index()
    {
        $eventProposals = EventProposal::with(['eventType', 'location', 'company'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $eventProposals
        ]);
    }

    /**
     * Enregistre une nouvelle proposition d'activité
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:company,id',
            'activity_type' => 'required|string',
            'proposed_date' => 'required|date|after:today',
            'location_id' => 'required|exists:location,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Trouver ou créer le ServiceType correspondant au type d'activité
        $serviceType = $this->findOrCreateServiceType($request->activity_type);

        try {
            // Créer la proposition d'activité
            $eventProposal = EventProposal::create([
                'company_id' => $request->company_id,
                'event_type_id' => $serviceType->id,
                'proposed_date' => $request->proposed_date,
                'location_id' => $request->location_id,
                'notes' => $request->notes,
                'status' => 'Pending'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Demande d\'activité créée avec succès',
                'data' => $eventProposal
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère les détails d'une proposition d'activité
     */
    public function show($id)
    {
        $eventProposal = EventProposal::with(['eventType', 'location', 'company', 'event'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $eventProposal
        ]);
    }

    /**
     * Récupère la liste des propositions d'activités pour une société
     */
    public function getByCompany($companyId)
    {
        $eventProposals = EventProposal::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->with(['eventType', 'location'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $eventProposals
        ]);
    }

    /**
     * Met à jour une proposition d'activité
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'proposed_date' => 'sometimes|required|date|after:today',
            'location_id' => 'sometimes|required|exists:location,id',
            'notes' => 'nullable|string|max:1000',
            'status' => 'sometimes|required|in:Pending,Assigned,Accepted,Rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $eventProposal = EventProposal::findOrFail($id);
            $eventProposal->update($request->only(['proposed_date', 'location_id', 'notes', 'status']));

            return response()->json([
                'success' => true,
                'message' => 'Demande d\'activité mise à jour avec succès',
                'data' => $eventProposal
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la mise à jour de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime une proposition d'activité
     */
    public function destroy($id)
    {
        try {
            $eventProposal = EventProposal::findOrFail($id);
            $eventProposal->delete();

            return response()->json([
                'success' => true,
                'message' => 'Demande d\'activité supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trouve ou crée un ServiceType à partir du type d'activité
     */
    private function findOrCreateServiceType($activityType)
    {
        // Chercher un prestataire par défaut
        $provider = Provider::where('statut_prestataire', 'Validé')->first();

        // Si aucun prestataire n'est disponible, prendre le premier
        if (!$provider) {
            $provider = Provider::first();
        }

        // Si toujours pas de prestataire, en créer un par défaut
        if (!$provider) {
            $provider = Provider::create([
                'first_name' => 'System',
                'last_name' => 'Default',
                'email' => 'system@businesscare.fr',
                'password' => bcrypt('password'),
                'description' => 'Prestataire système par défaut',
                'domains' => 'Toutes activités',
                'statut_prestataire' => 'Validé',
                'activity_type' => $activityType,
                'tarif_horaire' => 0
            ]);
        }

        // Chercher un ServiceType existant pour cette activité
        $serviceType = ServiceType::where('title', 'LIKE', "%$activityType%")->first();

        // Si aucun service type n'existe, en créer un
        if (!$serviceType) {
            $title = ucfirst($activityType);

            $serviceType = ServiceType::create([
                'provider_id' => $provider->id,
                'title' => $title,
                'description' => "Prestation : $title",
                'price' => 100.00, // Prix par défaut
                'duration' => 60   // Durée par défaut en minutes
            ]);
        }

        return $serviceType;
    }

    /**
     * Crée les emplacements par défaut
     */
    private function createDefaultLocations()
    {
        $defaultLocations = [
            [
                'name' => 'Business Care Paris (1er)',
                'address' => '110, rue de Rivoli',
                'postal_code' => '75001',
                'city' => 'Paris',
                'is_active' => true
            ],
            [
                'name' => 'Business Care Troyes',
                'address' => '13 rue Antoine Parmentier',
                'postal_code' => '10000',
                'city' => 'Troyes',
                'is_active' => true
            ],
            [
                'name' => 'Business Care Biarritz',
                'address' => '47 rue Lisboa',
                'postal_code' => '64200',
                'city' => 'Biarritz',
                'is_active' => true
            ],
            [
                'name' => 'Business Care Nice',
                'address' => '8 rue Beaumont',
                'postal_code' => '06000',
                'city' => 'Nice',
                'is_active' => true
            ]
        ];

        foreach ($defaultLocations as $locationData) {
            Location::create($locationData);
        }
    }
}
