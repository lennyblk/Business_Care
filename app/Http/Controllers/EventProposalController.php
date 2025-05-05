<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EventProposal;
use App\Models\Location;
use App\Models\ServiceType;
use App\Models\Provider;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EventProposalController extends Controller
{
    /**
     * Affiche la liste des propositions d'activités pour la société connectée
     */
    public function index()
    {
        // Récupérer l'ID de la société à partir de la session
        $companyId = session('user_id');

        // Récupérer les propositions pour cette entreprise
        $eventProposals = EventProposal::with(['eventType', 'location'])
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Notez le changement de chemin de vue ici
        return view('dashboards.client.event_proposals.index', compact('eventProposals'));
    }

    /**
     * Affiche le formulaire de création d'une nouvelle proposition d'activité
     */
    public function create()
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

        // Récupérer les emplacements
        $locations = Location::whereIn('city', ['Paris', 'Troyes', 'Biarritz', 'Nice'])->get();

        // Notez le changement de chemin de vue ici
        return view('dashboards.client.event_proposals.create', compact('activityTypes', 'locations'));
    }

    /**
     * Enregistre une nouvelle proposition d'activité
     */
    public function store(Request $request)
    {
        // Valider les données
        $validated = $request->validate([
            'activity_type' => 'required|string',
            'proposed_date' => 'required|date|after:today',
            'location_id' => 'required|exists:location,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        // Récupérer l'ID de la société à partir de la session
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

        // Notifier l'admin de la nouvelle demande
        $this->notifyAdmin($eventProposal);

        return redirect()->route('client.event_proposals.index')
            ->with('success', 'Votre demande d\'activité a été soumise avec succès.');
    }
    /**
     * Affiche les détails d'une proposition d'activité
     */
    public function show($id)
    {
        $eventProposal = EventProposal::with(['eventType', 'location', 'company', 'event'])
            ->findOrFail($id);

        // Récupérer l'ID de la société à partir de la session
        $companyId = session('user_id');

        // Vérifier que la proposition appartient à l'entreprise de l'utilisateur connecté
        if ($eventProposal->company_id != $companyId && session('user_type') !== 'admin') {
            return redirect()->route('client.event_proposals.index')
                ->withErrors(['error' => 'Vous n\'êtes pas autorisé à voir cette proposition d\'activité.']);
        }

        // Notez le changement de chemin de vue ici
        return view('dashboards.client.event_proposals.show', compact('eventProposal'));
    }

    /**
     * Affiche le formulaire de modification d'une proposition d'activité
     */
    public function edit($id)
    {
        $eventProposal = EventProposal::findOrFail($id);

        // Récupérer l'ID de la société à partir de la session
        $companyId = session('user_id');

        // Vérifier que la proposition appartient à l'entreprise de l'utilisateur connecté
        if ($eventProposal->company_id != $companyId) {
            return redirect()->route('client.event_proposals.index')
                ->withErrors(['error' => 'Vous n\'êtes pas autorisé à modifier cette proposition d\'activité.']);
        }

        // Si la proposition n'est plus en attente, elle ne peut plus être modifiée
        if ($eventProposal->status !== 'Pending') {
            return redirect()->route('client.event_proposals.show', $id)
                ->withErrors(['error' => 'Cette proposition d\'activité ne peut plus être modifiée car elle a déjà été traitée.']);
        }

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

        // Récupérer les emplacements
        $locations = Location::whereIn('city', ['Paris', 'Troyes', 'Biarritz', 'Nice'])->get();

        // Notez le changement de chemin de vue ici
        return view('dashboards.client.event_proposals.edit', compact('eventProposal', 'activityTypes', 'locations'));
    }

    /**
     * Met à jour une proposition d'activité
     */
    public function update(Request $request, $id)
    {
        // Valider les données
        $validated = $request->validate([
            'proposed_date' => 'required|date|after:today',
            'location_id' => 'required|exists:location,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        $eventProposal = EventProposal::findOrFail($id);

        // Récupérer l'ID de la société à partir de la session
        $companyId = session('user_id');

        // Vérifier que la proposition appartient à l'entreprise de l'utilisateur connecté
        if ($eventProposal->company_id != $companyId) {
            return redirect()->route('client.event_proposals.index')
                ->withErrors(['error' => 'Vous n\'êtes pas autorisé à modifier cette proposition d\'activité.']);
        }

        // Si la proposition n'est plus en attente, elle ne peut plus être modifiée
        if ($eventProposal->status !== 'Pending') {
            return redirect()->route('client.event_proposals.show', $id)
                ->withErrors(['error' => 'Cette proposition d\'activité ne peut plus être modifiée car elle a déjà été traitée.']);
        }

        // Mettre à jour la proposition d'activité
        $eventProposal->update([
            'proposed_date' => $request->proposed_date,
            'location_id' => $request->location_id,
            'notes' => $request->notes
        ]);

        return redirect()->route('client.event_proposals.show', $id)
            ->with('success', 'Votre demande d\'activité a été mise à jour avec succès.');
    }

    /**
     * Supprime une proposition d'activité
     */
    public function destroy($id)
    {
        $eventProposal = EventProposal::findOrFail($id);

        // Récupérer l'ID de la société à partir de la session
        $companyId = session('user_id');

        // Vérifier que la proposition appartient à l'entreprise de l'utilisateur connecté
        if ($eventProposal->company_id != $companyId) {
            return redirect()->route('client.event_proposals.index')
                ->withErrors(['error' => 'Vous n\'êtes pas autorisé à supprimer cette proposition d\'activité.']);
        }

        // Si la proposition n'est plus en attente, elle ne peut plus être supprimée
        if ($eventProposal->status !== 'Pending') {
            return redirect()->route('client.event_proposals.show', $id)
                ->withErrors(['error' => 'Cette proposition d\'activité ne peut plus être supprimée car elle a déjà été traitée.']);
        }

        // Supprimer la proposition d'activité
        $eventProposal->delete();

        return redirect()->route('client.event_proposals.index')
            ->with('success', 'Votre demande d\'activité a été supprimée avec succès.');
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

    private function notifyAdmin(EventProposal $eventProposal)
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Configuration du serveur
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = env('MAIL_PORT', 587);

            // Destinataire (admin)
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
            $mail->addAddress(env('ADMIN_EMAIL', 'admin@businesscare.fr'), 'Administrateur');

            // Chargement des relations nécessaires
            $eventProposal->load(['company', 'eventType', 'location']);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Nouvelle demande d\'activité';

            $mail->Body = "
                <meta charset='UTF-8'>
                <h2>Nouvelle demande d'activité</h2>
                <p><strong>Entreprise :</strong> {$eventProposal->company->name}</p>
                <p><strong>Type d'activité :</strong> {$eventProposal->eventType->title}</p>
                <p><strong>Date souhaitée :</strong> {$eventProposal->proposed_date->format('d/m/Y')}</p>
                <p><strong>Lieu :</strong> {$eventProposal->location->name} ({$eventProposal->location->city})</p>
                <p>Veuillez vous connecter au tableau de bord administrateur pour examiner cette demande et assigner un prestataire.</p>
                <p><a href='" . url('/dashboard/gestion_admin/event_proposals/' . $eventProposal->id) . "'>Voir les détails de la demande</a></p>
            ";

            $mail->AltBody = "Nouvelle demande d'activité - Entreprise: {$eventProposal->company->name}, Type: {$eventProposal->eventType->title}, Date: {$eventProposal->proposed_date->format('d/m/Y')}, Lieu: {$eventProposal->location->name} ({$eventProposal->location->city})";

            $mail->send();

            // Créer également une notification en base de données
            $notification = new \App\Models\Notification();
            $notification->recipient_id = 1; // ID de l'admin par défaut
            $notification->recipient_type = 'Admin';
            $notification->title = 'Nouvelle demande d\'activité';
            $notification->message = 'Une nouvelle demande d\'activité a été soumise par ' .
                $eventProposal->company->name . ' pour ' . $eventProposal->eventType->title .
                ' le ' . $eventProposal->proposed_date->format('d/m/Y');
            $notification->notification_type = 'Email';
            $notification->save();

            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            \Log::error("Erreur d'envoi d'email à l'administrateur: {$mail->ErrorInfo}");
            return false;
        }
    }


}
