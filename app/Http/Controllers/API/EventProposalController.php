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
use Illuminate\Support\Facades\Log;

class EventProposalController extends Controller
{
    public function index()
    {
        try {
            $companyId = session('user_id');
            $eventProposals = EventProposal::with(['eventType', 'location'])
                ->where('company_id', $companyId)
                ->where('created_by_admin', 0)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $eventProposals
            ]);
        } catch (\Exception $e) {
            Log::error('API Error in EventProposalController@index: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }

    public function getFormData()
    {
        try {
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

            $defaultDurations = [
                30 => '30 minutes',
                45 => '45 minutes',
                60 => '1 heure',
                90 => '1 heure 30',
                120 => '2 heures',
                180 => '3 heures',
                240 => '4 heures',
                360 => '6 heures',
                480 => '8 heures'
            ];

            $locations = Location::whereIn('city', ['Paris', 'Troyes', 'Biarritz', 'Nice'])->get();

            return response()->json([
                'status' => 'success',
                'activityTypes' => $activityTypes,
                'locations' => $locations,
                'defaultDurations' => $defaultDurations
            ]);
        } catch (\Exception $e) {
            Log::error('API Error in EventProposalController@getFormData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activity_type' => 'required|string',
            'proposed_date' => 'required|date|after:today',
            'location_id' => 'required|exists:location,id',
            'duration' => 'required|integer|min:30|max:480',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $serviceType = $this->findOrCreateServiceType($request->activity_type);
            
            $eventProposal = EventProposal::create([
                'company_id' => session('user_id'),
                'event_type_id' => $serviceType->id,
                'proposed_date' => $request->proposed_date,
                'location_id' => $request->location_id,
                'duration' => $request->duration,
                'notes' => $request->notes,
                'status' => 'Pending',
                'created_by_admin' => session('user_type') === 'admin' ? 1 : 0
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Demande d\'activité créée avec succès',
                'data' => $eventProposal
            ], 201);
        } catch (\Exception $e) {
            Log::error('API Error in EventProposalController@store: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la création de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $eventProposal = EventProposal::with(['eventType', 'location', 'company', 'event'])
                ->findOrFail($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'eventProposal' => $eventProposal
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('API Error in EventProposalController@show: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des détails'
            ], 500);
        }
    }

    public function getByCompany($companyId)
    {
        try {
            $eventProposals = EventProposal::where('company_id', $companyId)
                ->orderBy('created_at', 'desc')
                ->with(['eventType', 'location'])
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $eventProposals
            ]);
        } catch (\Exception $e) {
            Log::error('API Error in EventProposalController@getByCompany: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la récupération des propositions'
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'proposed_date' => 'sometimes|required|date|after:today',
            'location_id' => 'sometimes|required|exists:location,id',
            'duration' => 'sometimes|required|integer|min:30|max:480',
            'notes' => 'nullable|string|max:1000',
            'status' => 'sometimes|required|in:Pending,Assigned,Accepted,Rejected'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $eventProposal = EventProposal::findOrFail($id);
            $eventProposal->update($request->only(['proposed_date', 'location_id', 'duration', 'notes', 'status']));

            return response()->json([
                'status' => 'success',
                'message' => 'Demande d\'activité mise à jour avec succès',
                'data' => $eventProposal
            ]);
        } catch (\Exception $e) {
            Log::error('API Error in EventProposalController@update: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la mise à jour de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $eventProposal = EventProposal::findOrFail($id);
            $eventProposal->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Demande d\'activité supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            Log::error('API Error in EventProposalController@destroy: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue lors de la suppression de la demande',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function findOrCreateServiceType($activityType)
    {
        $provider = Provider::where('statut_prestataire', 'Validé')->first();

        if (!$provider) {
            $provider = Provider::first();
        }

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

        $serviceType = ServiceType::where('title', 'LIKE', "%$activityType%")->first();

        if (!$serviceType) {
            $title = ucfirst($activityType);

            $serviceType = ServiceType::create([
                'provider_id' => $provider->id,
                'title' => $title,
                'description' => "Prestation : $title",
                'price' => 100.00,
                'duration' => 60
            ]);
        }

        return $serviceType;
    }

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
