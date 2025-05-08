<?php

namespace App\Http\Controllers;

use App\Models\ProviderAssignment;
use App\Models\EventProposal;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ProviderAssignmentController extends Controller
{
    /**
     * Affiche la liste des propositions d'activités pour le prestataire
     */
    public function index(Request $request)
    {
        try {
            // Pour les tests, utilisons un ID fixe (à remplacer par la vraie source d'ID)
            $providerId = $request->session()->get('provider_id', 1); // Fallback à ID 1 pour les tests

            Log::info('Provider ID récupéré: ' . $providerId);

            // Requêtes directes sur les modèles
            $pendingAssignments = ProviderAssignment::with(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location'])
                ->where('provider_id', $providerId)
                ->where('status', 'Proposed')
                ->orderBy('proposed_at', 'desc')
                ->get();

            $acceptedAssignments = ProviderAssignment::with(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location'])
                ->where('provider_id', $providerId)
                ->where('status', 'Accepted')
                ->orderBy('proposed_at', 'desc')
                ->get();

            Log::info('Nombre d\'assignations en attente: ' . count($pendingAssignments));
            Log::info('Nombre d\'assignations acceptées: ' . count($acceptedAssignments));

            return view('dashboards.provider.assignments.index', [
                'pendingAssignments' => $pendingAssignments,
                'acceptedAssignments' => $acceptedAssignments
            ]);
        } catch (\Exception $e) {
            Log::error('ProviderAssignmentController@index error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Erreur lors de la récupération des assignations: ' . $e->getMessage());
        }
    }

    /**
     * Affiche les détails d'une assignation
     */
    public function show(Request $request, $id)
    {
        try {
            $providerId = $request->session()->get('provider_id', 1); // Fallback à ID 1 pour les tests

            // Récupérer directement l'assignation
            $assignment = ProviderAssignment::with(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location'])
                ->where('id', $id)
                ->where('provider_id', $providerId)
                ->firstOrFail();

            return view('dashboards.provider.assignments.show', [
                'assignment' => $assignment
            ]);
        } catch (\Exception $e) {
            Log::error('ProviderAssignmentController@show error: ' . $e->getMessage());
            return back()->with('error', 'Assignation non trouvée');
        }
    }

    /**
     * Accepte une assignation
     */
    public function accept(Request $request, $id)
    {
        try {
            $providerId = $request->session()->get('provider_id', 1); // Fallback à ID 1 pour les tests

            // Récupérer l'assignation
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
            $eventProposal = $assignment->eventProposal;
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
                'event_proposal_id' => $eventProposal->id
            ]);

            $event->save();

            // Notifier l'entreprise
            $this->notifyCompany($assignment, $event);

            return redirect()->route('provider.assignments.index')
                ->with('success', 'Vous avez accepté cette activité avec succès.');
        } catch (\Exception $e) {
            Log::error('ProviderAssignmentController@accept error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return back()->with('error', 'Erreur lors de l\'acceptation de l\'assignation: ' . $e->getMessage());
        }
    }

    /**
     * Refuse une assignation
     */
    public function reject(Request $request, $id)
    {
        try {
            $providerId = $request->session()->get('provider_id', 1); // Fallback à ID 1 pour les tests

            // Récupérer l'assignation
            $assignment = ProviderAssignment::with(['provider', 'eventProposal.company', 'eventProposal.eventType', 'eventProposal.location'])
                ->where('id', $id)
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
        } catch (\Exception $e) {
            Log::error('ProviderAssignmentController@reject error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du refus de l\'assignation: ' . $e->getMessage());
        }
    }

    /**
     * Notifie l'entreprise que l'activité a été acceptée via PHPMailer
     */
    private function notifyCompany($assignment, $event)
    {
        try {
            $company = $assignment->eventProposal->company;
            $provider = $assignment->provider;

            $mail = new PHPMailer(true);

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

            $eventDate = date('d/m/Y', strtotime($event->date));

            $mail->Body = "
                <meta charset='UTF-8'>
                <h2>Confirmation d'activité</h2>
                <p>Cher client {$company->name},</p>
                <p>Nous avons le plaisir de vous confirmer que votre activité <strong>{$event->name}</strong> a été acceptée par notre prestataire.</p>
                <p><strong>Date:</strong> {$eventDate}</p>
                <p><strong>Lieu:</strong> {$event->location}</p>
                <p><strong>Prestataire:</strong> {$provider->first_name} {$provider->last_name}</p>
                <p>Vos employés peuvent maintenant s'inscrire à cette activité via leur espace personnel.</p>
                <p><a href='" . url('/dashboard/client') . "'>Accéder à votre espace</a></p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->AltBody = "Confirmation d'activité - Votre activité {$event->name} prévue le {$eventDate} a été confirmée. Vos employés peuvent maintenant s'y inscrire.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            Log::error("Erreur d'envoi d'email à l'entreprise: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Notifie l'administrateur qu'une activité a été refusée via PHPMailer
     */
    private function notifyAdmin($assignment)
    {
        try {
            $eventProposal = $assignment->eventProposal;
            $company = $eventProposal->company;
            $provider = $assignment->provider;

            $mail = new PHPMailer(true);

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

            $eventProposalDate = date('d/m/Y', strtotime($eventProposal->proposed_date));

            $mail->Body = "
                <meta charset='UTF-8'>
                <h2>Refus d'une activité</h2>
                <p>Le prestataire <strong>{$provider->first_name} {$provider->last_name}</strong> a refusé l'activité proposée.</p>
                <p><strong>Entreprise:</strong> {$company->name}</p>
                <p><strong>Activité:</strong> {$eventProposal->eventType->title}</p>
                <p><strong>Date prévue:</strong> {$eventProposalDate}</p>
                <p>Veuillez vous connecter à la plateforme pour assigner un autre prestataire à cette activité.</p>
                <p><a href='" . url('/dashboard/gestion_admin/event_proposals/' . $eventProposal->id) . "'>Gérer cette activité</a></p>
            ";

            $mail->AltBody = "Refus d'activité - Le prestataire {$provider->first_name} {$provider->last_name} a refusé l'activité {$eventProposal->eventType->title} pour {$company->name}.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            Log::error("Erreur d'envoi d'email à l'admin: {$mail->ErrorInfo}");
            return false;
        }
    }
}
