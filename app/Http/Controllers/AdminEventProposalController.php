<?php

namespace App\Http\Controllers;

use App\Models\EventProposal;
use App\Models\Provider;
use App\Models\ProviderAssignment;
use App\Models\Event;
use App\Models\Notification;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AdminEventProposalController extends Controller
{
    /**
     * Affiche la liste des propositions d'activités
     */
    public function index()
    {
        $pendingProposals = EventProposal::where('status', 'Pending')
            ->with(['company', 'eventType', 'location'])
            ->orderBy('created_at', 'asc')
            ->get();

        $assignedProposals = EventProposal::whereIn('status', ['Assigned', 'Accepted'])
            ->with(['company', 'eventType', 'location'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboards.gestion_admin.event_proposals.index', [
            'pendingProposals' => $pendingProposals,
            'assignedProposals' => $assignedProposals
        ]);
    }

    /**
     * Affiche les détails d'une proposition d'activité
     */
    public function show($id)
    {
        $eventProposal = EventProposal::with(['company', 'eventType', 'location', 'providerAssignments.provider'])
            ->findOrFail($id);

        // Trouver des prestataires correspondant aux critères (même ville et même type d'activité)
        $recommendations = Provider::where('statut_prestataire', 'Validé')
            ->where('ville', $eventProposal->location->city)
            ->where('activity_type', $eventProposal->eventType->title)
            ->orderBy('rating', 'desc')
            ->get();

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
        $provider = Provider::findOrFail($request->provider_id);

        // Créer l'assignation du prestataire
        $assignment = ProviderAssignment::create([
            'event_proposal_id' => $eventProposal->id,
            'provider_id' => $request->provider_id,
            'status' => 'Proposed',
            'payment_amount' => $request->payment_amount,
            'proposed_at' => now()
        ]);

        // Mettre à jour le statut de la proposition
        $eventProposal->status = 'Assigned';
        $eventProposal->save();

        // Envoyer une notification au prestataire
        $this->notifyProvider($assignment);

        // Envoyer une notification à l'entreprise
        $this->notifyCompany($eventProposal, $provider);

        return redirect()->route('admin.event_proposals.index')
            ->with('success', 'Le prestataire a été assigné avec succès à cette activité.');
    }

    /**
     * Refuse une proposition d'activité
     */
    public function rejectProposal($id)
    {
        $eventProposal = EventProposal::findOrFail($id);
        $eventProposal->status = 'Rejected';
        $eventProposal->save();

        // Envoyer une notification à l'entreprise
        $this->notifyCompanyRejection($eventProposal);

        return redirect()->route('admin.event_proposals.index')
            ->with('success', 'La demande d\'activité a été refusée.');
    }

    /**
     * Notifie un prestataire d'une nouvelle assignation
     */
    private function notifyProvider(ProviderAssignment $assignment)
    {
        $provider = $assignment->provider;
        $eventProposal = $assignment->eventProposal;

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

        } catch (Exception $e) {
            \Log::error("Erreur d'envoi d'email au prestataire: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Notifie l'entreprise qu'un prestataire a été assigné
     */
    private function notifyCompany(EventProposal $eventProposal, Provider $provider)
    {
        $company = $eventProposal->company;

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

            // Destinataire (company)
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
            $mail->addAddress($company->email);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Demande d\'activité en cours de traitement';

            $mail->Body = "
                <meta charset='UTF-8'>
                <h2>Votre demande d'activité est en cours de traitement</h2>
                <p>Cher client {$company->name},</p>
                <p>Nous avons le plaisir de vous informer que votre demande d'activité <strong>{$eventProposal->eventType->title}</strong> prévue le <strong>{$eventProposal->proposed_date->format('d/m/Y')}</strong> a été traitée.</p>
                <p>Nous avons sélectionné un prestataire pour animer cette activité : <strong>{$provider->first_name} {$provider->last_name}</strong>.</p>
                <p>Le prestataire doit maintenant confirmer sa disponibilité. Vous serez informé dès qu'il aura accepté l'activité.</p>
                <p>Vous pouvez suivre l'avancement de votre demande dans votre espace client.</p>
                <p><a href='" . url('/dashboard/client/event_proposals/' . $eventProposal->id) . "'>Accéder à votre espace</a></p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->AltBody = "Votre demande d'activité est en cours de traitement - Nous avons sélectionné un prestataire pour animer votre {$eventProposal->eventType->title} du {$eventProposal->proposed_date->format('d/m/Y')}.";

            $mail->send();


            return true;
        } catch (Exception $e) {
            \Log::error("Erreur d'envoi d'email à l'entreprise: {$mail->ErrorInfo}");
            return false;
        }
    }

    /**
     * Notifie l'entreprise que sa demande a été refusée
     */
    private function notifyCompanyRejection(EventProposal $eventProposal)
    {
        $company = $eventProposal->company;

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

            // Destinataire (company)
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
            $mail->addAddress($company->email);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Demande d\'activité refusée';

            $mail->Body = "
                <meta charset='UTF-8'>
                <h2>Votre demande d'activité a été refusée</h2>
                <p>Cher client {$company->name},</p>
                <p>Nous sommes désolés de vous informer que votre demande d'activité <strong>{$eventProposal->eventType->title}</strong> prévue le <strong>{$eventProposal->proposed_date->format('d/m/Y')}</strong> a été refusée.</p>
                <p>Cela peut être dû à plusieurs raisons, notamment :</p>
                <ul>
                    <li>Aucun prestataire n'est disponible à cette date</li>
                    <li>L'activité ne correspond pas aux services que nous proposons actuellement</li>
                    <li>L'activité ne répond pas à nos critères de qualité ou de sécurité</li>
                </ul>
                <p>Nous vous invitons à nous contacter pour discuter de cette décision ou pour soumettre une nouvelle demande à une date différente.</p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->AltBody = "Votre demande d'activité a été refusée - Nous sommes désolés de vous informer que votre demande pour l'activité {$eventProposal->eventType->title} du {$eventProposal->proposed_date->format('d/m/Y')} a été refusée.";

            $mail->send();
        } catch (Exception $e) {
            \Log::error("Erreur d'envoi d'email à l'entreprise: {$mail->ErrorInfo}");
            return false;
        }
    }
}
