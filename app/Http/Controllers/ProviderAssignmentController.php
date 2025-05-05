<?php

namespace App\Http\Controllers;

use App\Models\ProviderAssignment;
use App\Models\Notification;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ProviderAssignmentController extends Controller
{
    /**
     * Affiche la liste des propositions d'activités pour le prestataire
     */
    public function index()
    {
        $providerId = Auth::guard('provider')->id();

        $pendingAssignments = ProviderAssignment::where('provider_id', $providerId)
            ->where('status', 'Proposed')
            ->orderBy('proposed_at', 'desc')
            ->get();

        $acceptedAssignments = ProviderAssignment::where('provider_id', $providerId)
            ->where('status', 'Accepted')
            ->orderBy('proposed_at', 'desc')
            ->get();

        return view('dashboards.provider.assignments.index', [
            'pendingAssignments' => $pendingAssignments,
            'acceptedAssignments' => $acceptedAssignments
        ]);
    }

    /**
     * Affiche les détails d'une assignation
     */
    public function show($id)
    {
        $providerId = Auth::guard('provider')->id();

        $assignment = ProviderAssignment::where('id', $id)
            ->where('provider_id', $providerId)
            ->firstOrFail();

        return view('dashboards.provider.assignments.show', [
            'assignment' => $assignment
        ]);
    }

    /**
     * Accepte une assignation
     */
    public function accept($id)
    {
        $providerId = Auth::guard('provider')->id();

        $assignment = ProviderAssignment::where('id', $id)
            ->where('provider_id', $providerId)
            ->where('status', 'Proposed')
            ->firstOrFail();

        // Accepter l'assignation
        $assignment->status = 'Accepted';
        $assignment->response_at = now();
        $assignment->save();

        // Mettre à jour le statut de la proposition
        $eventProposal = $assignment->eventProposal;
        $eventProposal->status = 'Accepted';
        $eventProposal->save();

        // Créer un événement basé sur la proposition
        $serviceType = $eventProposal->eventType;

        $event = Event::create([
            'name' => $serviceType->title,
            'description' => $serviceType->description,
            'date' => $eventProposal->proposed_date,
            'event_type' => 'Workshop', // Type par défaut, à modifier par l'admin si nécessaire
            'capacity' => 30, // Capacité par défaut, peut être ajustée
            'location' => $eventProposal->location->name,
            'company_id' => $eventProposal->company_id,
            'event_proposal_id' => $eventProposal->id
        ]);

        // Notifier l'entreprise
        $this->notifyCompany($assignment, $event);

        return redirect()->route('provider.assignments.index')
            ->with('success', 'Vous avez accepté cette activité avec succès.');
    }

    /**
     * Refuse une assignation
     */
    public function reject($id)
    {
        $providerId = Auth::guard('provider')->id();

        $assignment = ProviderAssignment::where('id', $id)
            ->where('provider_id', $providerId)
            ->where('status', 'Proposed')
            ->firstOrFail();

        // Rejeter l'assignation
        $assignment->status = 'Rejected';
        $assignment->response_at = now();
        $assignment->save();

        // Notifier l'admin
        $this->notifyAdmin($assignment);

        return redirect()->route('provider.assignments.index')
            ->with('success', 'Vous avez refusé cette activité.');
    }

    /**
     * Notifie l'entreprise que l'activité a été acceptée via PHPMailer
     */
    private function notifyCompany(ProviderAssignment $assignment, $event)
    {
        $eventProposal = $assignment->eventProposal;
        $company = $eventProposal->company;
        $provider = $assignment->provider;

        // Créer une notification en base de données
        $notification = new Notification();
        $notification->recipient_id = $company->id;
        $notification->recipient_type = 'Company';
        $notification->title = 'Activité confirmée';
        $notification->message = 'L\'activité ' . $event->name . ' prévue le ' .
            $event->date->format('d/m/Y') . ' a été confirmée. Vos employés peuvent maintenant s\'y inscrire.';
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

            // Destinataire (entreprise)
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
            $mail->addAddress($company->email);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Activité confirmée - ' . $event->name;

            $mail->Body = "
                <meta charset='UTF-8'>
                <h2>Confirmation d'activité</h2>
                <p>Cher client {$company->name},</p>
                <p>Nous avons le plaisir de vous confirmer que votre activité <strong>{$event->name}</strong> a été acceptée par notre prestataire.</p>
                <p><strong>Date:</strong> {$event->date->format('d/m/Y')}</p>
                <p><strong>Lieu:</strong> {$event->location}</p>
                <p><strong>Prestataire:</strong> {$provider->first_name} {$provider->last_name}</p>
                <p>Vos employés peuvent maintenant s'inscrire à cette activité via leur espace personnel.</p>
                <p><a href='" . url('/dashboard/client') . "'>Accéder à votre espace</a></p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->AltBody = "Confirmation d'activité - Votre activité {$event->name} prévue le {$event->date->format('d/m/Y')} a été confirmée. Vos employés peuvent maintenant s'y inscrire.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            \Log::error("Erreur d'envoi d'email à l'entreprise: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Notifie l'administrateur qu'une activité a été refusée via PHPMailer
     */
    private function notifyAdmin(ProviderAssignment $assignment)
    {
        // Créer une notification en base de données
        $notification = new Notification();
        $notification->recipient_id = 1; // ID de l'admin par défaut
        $notification->recipient_type = 'Company';
        $notification->title = 'Activité refusée par un prestataire';
        $notification->message = 'Le prestataire ' . $assignment->provider->first_name . ' ' .
            $assignment->provider->last_name . ' a refusé l\'activité proposée pour ' .
            $assignment->eventProposal->company->name;
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

            // Destinataire (admin)
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
            $mail->addAddress(env('ADMIN_EMAIL', 'admin@businesscare.fr'));

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Activité refusée par un prestataire';

            $mail->Body = "
                <meta charset='UTF-8'>
                <h2>Refus d'une activité</h2>
                <p>Le prestataire <strong>{$assignment->provider->first_name} {$assignment->provider->last_name}</strong> a refusé l'activité proposée.</p>
                <p><strong>Entreprise:</strong> {$assignment->eventProposal->company->name}</p>
                <p><strong>Activité:</strong> {$assignment->eventProposal->eventType->title}</p>
                <p><strong>Date prévue:</strong> {$assignment->eventProposal->proposed_date->format('d/m/Y')}</p>
                <p>Veuillez vous connecter à la plateforme pour assigner un autre prestataire à cette activité.</p>
                <p><a href='" . url('/dashboard/gestion_admin/event_proposals/' . $assignment->eventProposal->id) . "'>Gérer cette activité</a></p>
            ";

            $mail->AltBody = "Refus d'activité - Le prestataire {$assignment->provider->first_name} {$assignment->provider->last_name} a refusé l'activité {$assignment->eventProposal->eventType->title} pour {$assignment->eventProposal->company->name}.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            \Log::error("Erreur d'envoi d'email à l'admin: {$mail->ErrorInfo}");
            return false;
        }
    }
}
