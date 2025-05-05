<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\EventProposal;
use App\Models\Location;
use App\Models\ServiceType;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EventProposalController extends Controller
{
    /**
     * Affiche le formulaire de création de proposition d'activité
     */
    public function create()
    {
        // Récupérer les types d'activités directement depuis l'enum de Provider
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

        return view('dashboards.client.event_proposals.create', [
            'activityTypes' => $activityTypes,
            'locations' => $locations
        ]);
    }

    /**
     * Enregistre une nouvelle proposition d'activité
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'activity_type' => 'required|string',
            'proposed_date' => 'required|date|after:today',
            'location_id' => 'required|exists:location,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Récupérer l'ID de la société connectée depuis la session
        $companyId = session('user_id');

        // Trouver ou créer le ServiceType correspondant au type d'activité
        $serviceType = $this->findOrCreateServiceType($request->activity_type);

        // Créer la proposition d'activité
        $eventProposal = EventProposal::create([
            'company_id' => $companyId,
            'event_type_id' => $serviceType->id,
            'proposed_date' => $request->proposed_date,
            'location_id' => $request->location_id,
            'notes' => $request->notes,
            'status' => 'Pending'
        ]);

        // Envoyer une notification à l'admin
        $this->notifyAdmin($eventProposal);

        return redirect()->route('client.event_proposals.index')
            ->with('success', 'Votre demande d\'activité a été soumise avec succès et est en attente de validation.');
    }

    /**
     * Affiche la liste des propositions d'activités pour une société
     */
    public function index()
    {
        $companyId = session('user_id');
        $eventProposals = EventProposal::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboards.client.event_proposals.index', [
            'eventProposals' => $eventProposals
        ]);
    }

    /**
     * Affiche les détails d'une proposition d'activité
     */
    public function show($id)
    {
        $companyId = session('user_id');
        $eventProposal = EventProposal::where('id', $id)
            ->where('company_id', $companyId)
            ->firstOrFail();

        return view('dashboards.client.event_proposals.show', [
            'eventProposal' => $eventProposal
        ]);
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

    /**
     * Notifie l'administrateur d'une nouvelle proposition d'activité via PHPMailer
     */
    private function notifyAdmin(EventProposal $eventProposal)
    {
        // Créer une notification pour les admins dans la base de données
        $notification = new \App\Models\Notification();
        $notification->recipient_id = 1; // ID de l'admin par défaut
        $notification->recipient_type = 'Company';
        $notification->title = 'Nouvelle demande d\'activité';
        $notification->message = 'Une nouvelle demande d\'activité a été soumise par ' . $eventProposal->company->name;
        $notification->notification_type = 'Email';
        $notification->save();

        // Envoi d'email avec PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Configuration du serveur
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = env('MAIL_PORT', 587);

            // Pour le débogage (désactivé en production)
            // $mail->SMTPDebug = 2;
            // $mail->Debugoutput = function($str, $level) {
            //     \Log::info("PHPMailer Debug: $str");
            // };

            // Destinataire (admin)
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
            $mail->addAddress(env('ADMIN_EMAIL', 'admin@businesscare.fr'), 'Administrateur');

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Nouvelle demande d\'activité';

            // Corps du message
            $mail->Body = "
                <meta charset='UTF-8'>
                <h2>Nouvelle demande d'activité</h2>
                <p><strong>Entreprise :</strong> {$eventProposal->company->name}</p>
                <p><strong>Service demandé :</strong> {$eventProposal->eventType->title}</p>
                <p><strong>Date souhaitée :</strong> {$eventProposal->proposed_date->format('d/m/Y')}</p>
                <p><strong>Lieu :</strong> {$eventProposal->location->name}</p>
                " . ($eventProposal->notes ? "<p><strong>Remarques :</strong> {$eventProposal->notes}</p>" : "") . "
                <p>Veuillez vous connecter au tableau de bord administrateur pour traiter cette demande.</p>
                <p><a href='" . url('/dashboard/gestion_admin/event_proposals/' . $eventProposal->id) . "'>Accéder à la demande</a></p>
            ";

            $mail->AltBody = "Nouvelle demande d'activité - Entreprise: {$eventProposal->company->name}, Service: {$eventProposal->eventType->title}, Date: {$eventProposal->proposed_date->format('d/m/Y')}";

            $mail->send();
            \Log::info("Email de notification envoyé à l'administrateur pour la proposition d'activité #{$eventProposal->id}");
            return true;
        } catch (Exception $e) {
            \Log::error("Erreur d'envoi d'email à l'administrateur: {$mail->ErrorInfo}");
            return false;
        }
    }
}
