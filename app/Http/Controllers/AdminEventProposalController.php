<?php

namespace App\Http\Controllers;

use App\Models\EventProposal;
use App\Models\Provider;
use App\Models\ProviderAssignment;
use App\Models\ProviderRecommendationLog;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AdminEventProposalController extends Controller
{
    /**
     * Affiche la liste des propositions d'activités en attente
     */
    public function index()
    {
        $pendingProposals = EventProposal::where('status', 'Pending')
            ->orderBy('created_at', 'asc')
            ->get();

        $assignedProposals = EventProposal::where('status', 'Assigned')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboards.gestion_admin.event_proposals.index', [
            'pendingProposals' => $pendingProposals,
            'assignedProposals' => $assignedProposals
        ]);
    }

    /**
     * Affiche les détails d'une proposition d'activité avec des recommandations de prestataires
     */
    public function show($id)
    {
        $eventProposal = EventProposal::findOrFail($id);

        // Si le statut est 'Pending', générer des recommandations
        if ($eventProposal->status === 'Pending') {
            $recommendations = $this->generateRecommendations($eventProposal);
        } else {
            // Sinon, récupérer les prestataires déjà assignés
            $recommendations = Provider::whereHas('assignments', function ($query) use ($eventProposal) {
                $query->where('event_proposal_id', $eventProposal->id);
            })->get();
        }

        return view('dashboards.gestion_admin.event_proposals.show', [
            'eventProposal' => $eventProposal,
            'recommendations' => $recommendations
        ]);
    }

    /**
     * Assigne un prestataire à une proposition d'activité
     */
    public function assignProvider(Request $request, $id)
    {
        $request->validate([
            'provider_id' => 'required|exists:provider,id',
            'payment_amount' => 'required|numeric|min:0'
        ]);

        $eventProposal = EventProposal::findOrFail($id);

        // Créer l'assignation du prestataire
        $assignment = ProviderAssignment::create([
            'event_proposal_id' => $eventProposal->id,
            'provider_id' => $request->provider_id,
            'status' => 'Proposed',
            'payment_amount' => $request->payment_amount
        ]);

        // Mettre à jour le statut de la proposition
        $eventProposal->status = 'Assigned';
        $eventProposal->save();

        // Envoyer une notification au prestataire
        $this->notifyProvider($assignment);

        return redirect()->route('admin.event_proposals.index')
            ->with('success', 'Le prestataire a été assigné avec succès à cette activité.');
    }

    /**
     * Génère des recommandations de prestataires pour une proposition d'activité
     */
    private function generateRecommendations(EventProposal $eventProposal)
    {
        // Récupérer l'emplacement de la proposition
        $location = $eventProposal->location;

        // Récupérer le type de service demandé
        $serviceType = $eventProposal->eventType;

        // Trouver des prestataires correspondant aux critères
        $providers = Provider::where('statut_prestataire', 'Validé')
            ->where(function($query) use ($location, $serviceType) {
                // Correspondance géographique
                $query->where('ville', $location->city);

                // Correspondance de compétences (simplifié)
                // Dans un cas réel, vous voudriez peut-être comparer avec domains ou une autre table de compétences
            })
            ->orderBy('rating', 'desc')
            ->take(3)
            ->get();

        // Enregistrer les recommandations pour chaque prestataire
        foreach ($providers as $provider) {
            // Calculer les scores
            $geographicMatch = $provider->ville === $location->city;

            // Simplification: vérifier si les domaines du prestataire contiennent le titre du service
            $skillMatch = str_contains(strtolower($provider->domains), strtolower($serviceType->title));

            // Calculer le score de tarif (inversement proportionnel)
            $priceScore = 5 - min(5, max(0, $provider->tarif_horaire / 50));

            // Score total
            $totalScore = ($geographicMatch ? 5 : 0) +
                          ($skillMatch ? 5 : 0) +
                          $provider->rating +
                          $priceScore;

            // Recommandé si score total > 10
            $recommended = $totalScore > 10;

            // Enregistrer la recommandation
            ProviderRecommendationLog::create([
                'event_proposal_id' => $eventProposal->id,
                'provider_id' => $provider->id,
                'geographic_match' => $geographicMatch,
                'skill_match' => $skillMatch,
                'rating_score' => $provider->rating,
                'price_score' => $priceScore,
                'total_score' => $totalScore,
                'recommended' => $recommended
            ]);
        }

        return $providers;
    }

    /**
     * Notifie un prestataire d'une nouvelle assignation via PHPMailer
     */
    private function notifyProvider(ProviderAssignment $assignment)
    {
        $provider = $assignment->provider;
        $eventProposal = $assignment->eventProposal;

        // Créer une notification pour le prestataire
        $notification = new \App\Models\Notification();
        $notification->recipient_id = $provider->id;
        $notification->recipient_type = 'Provider';
        $notification->title = 'Nouvelle proposition d\'activité';
        $notification->message = 'Vous avez été sélectionné pour animer une activité le ' .
            $eventProposal->proposed_date->format('d/m/Y') .
            ' à ' . $eventProposal->location->name;
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

            // Destinataire (prestataire)
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
            $mail->addAddress($provider->email);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Nouvelle proposition d\'activité';

            $mail->Body = "
                <meta charset='UTF-8'>
                <h2>Vous avez été sélectionné pour une activité!</h2>
                <p>Cher/Chère {$provider->first_name} {$provider->last_name},</p>
                <p>Vous avez été sélectionné(e) pour animer une activité <strong>{$eventProposal->eventType->title}</strong>.</p>
                <p><strong>Date:</strong> {$eventProposal->proposed_date->format('d/m/Y')}</p>
                <p><strong>Lieu:</strong> {$eventProposal->location->name}</p>
                <p><strong>Entreprise:</strong> {$eventProposal->company->name}</p>
                <p><strong>Rémunération proposée:</strong> {$assignment->payment_amount}€</p>
                <p>Veuillez vous connecter à votre espace prestataire pour accepter ou refuser cette proposition.</p>
                <p><a href='" . url('/dashboard/provider/assignments/' . $assignment->id) . "'>Accéder à votre espace</a></p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->AltBody = "Nouvelle proposition d'activité - Vous avez été sélectionné pour animer une activité {$eventProposal->eventType->title} le {$eventProposal->proposed_date->format('d/m/Y')} à {$eventProposal->location->name}.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            \Log::error("Erreur d'envoi d'email au prestataire: {$mail->ErrorInfo}");
            return false;
        }
    }
}
