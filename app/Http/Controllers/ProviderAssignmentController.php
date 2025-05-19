<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\API\ProviderAssignmentController as ApiController;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class ProviderAssignmentController extends Controller
{
    protected $apiController;

    public function __construct()
    {
        $this->apiController = new ApiController();
    }

    /**
     * Affiche la liste des propositions d'activités pour le prestataire
     */
    public function index(Request $request)
    {
        try {
            // Récupérer l'ID du prestataire depuis la session
            $providerId = $request->session()->get('user_id', 1);

            $pendingResponse = $this->apiController->getByProviderAndStatus($providerId, 'Proposed');
            $acceptedResponse = $this->apiController->getByProviderAndStatus($providerId, 'Accepted');

            $pendingData = json_decode($pendingResponse->getContent(), true);
            $acceptedData = json_decode($acceptedResponse->getContent(), true);

            if (!$pendingData['success'] || !$acceptedData['success']) {
                throw new \Exception('Erreur lors de la récupération des données');
            }

            return view('dashboards.provider.assignments.index', [
                'pendingAssignments' => $pendingData['data'],
                'acceptedAssignments' => $acceptedData['data']
            ]);
        } catch (\Exception $e) {
            Log::error('ProviderAssignmentController@index error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la récupération des assignations');
        }
    }

    /**
     * Affiche les détails d'une assignation
     */
    public function show(Request $request, $id)
    {
        try {
            // Récupérer l'ID du prestataire depuis la session de la même façon que dans index()
            $providerId = $request->session()->get('user_id', 1);

            $response = $this->apiController->getByIdAndProvider($id, $providerId);
            $data = json_decode($response->getContent(), true);

            if (!$data['success']) {
                throw new \Exception($data['message']);
            }

            return view('dashboards.provider.assignments.show', [
                'assignment' => $data['data']
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
            $providerId = $request->session()->get('user_id', 1);
            $response = $this->apiController->acceptAssignment($id, $providerId);
            $data = json_decode($response->getContent(), true);

            if ($data['success']) {
                $this->notifyCompany($data['data']['provider'], $data['data']['eventProposal']);
                return redirect()->route('provider.assignments.index')
                    ->with('success', 'Vous avez accepté cette activité avec succès.');
            }

            throw new \Exception($data['message']);
        } catch (\Exception $e) {
            Log::error('ProviderAssignmentController@accept error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'acceptation');
        }
    }

    /**
     * Refuse une assignation
     */
    public function reject(Request $request, $id)
    {
        try {
            $providerId = $request->session()->get('user_id', 1);
            $response = $this->apiController->rejectAssignment($id, $providerId);
            $data = json_decode($response->getContent(), true);

            if ($data['success']) {
                $this->notifyAdmin($data['data']);
                return redirect()->route('provider.assignments.index')
                    ->with('success', 'Vous avez refusé cette activité.');
            }

            throw new \Exception($data['message']);
        } catch (\Exception $e) {
            Log::error('ProviderAssignmentController@reject error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du refus');
        }
    }

    private function notifyCompany($provider, $eventProposal)
{
    try {
        $company = $eventProposal['company'];
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
        $mail->addAddress($company['email']);

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Activité confirmée - ' . $eventProposal['name'];

        $eventDate = date('d/m/Y', strtotime($eventProposal['proposed_date']));

        // Formater la durée pour l'affichage
        $durationText = '';
        if ($eventProposal['duration'] >= 60) {
            $hours = floor($eventProposal['duration'] / 60);
            $minutes = $eventProposal['duration'] % 60;
            $durationText = $hours . ' heure' . ($hours > 1 ? 's' : '');
            if ($minutes > 0) {
                $durationText .= ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            }
        } else {
            $durationText = $eventProposal['duration'] . ' minutes';
        }

        $mail->Body = "
            <meta charset='UTF-8'>
            <h2>Confirmation d'activité</h2>
            <p>Cher client {$company['name']},</p>
            <p>Nous avons le plaisir de vous confirmer que votre activité <strong>{$eventProposal['name']}</strong> a été acceptée par notre prestataire.</p>
            <p><strong>Date:</strong> {$eventDate}</p>
            <p><strong>Durée:</strong> {$durationText}</p>
            <p><strong>Lieu:</strong> {$eventProposal['location']['name']}</p>
            <p><strong>Prestataire:</strong> {$provider['first_name']} {$provider['last_name']}</p>
            <p>Vos employés peuvent maintenant s'inscrire à cette activité via leur espace personnel.</p>
            <p><a href='" . url('/dashboard/client') . "'>Accéder à votre espace</a></p>
            <p>Cordialement,<br>L'équipe Business-Care</p>
        ";

        $mail->AltBody = "Confirmation d'activité - Votre activité {$eventProposal['name']} prévue le {$eventDate} pour une durée de {$durationText} a été confirmée. Vos employés peuvent maintenant s'y inscrire.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        Log::error("Erreur d'envoi d'email à l'entreprise: {$mail->ErrorInfo}");
        return false;
    }
}

private function notifyAdmin($assignment)
{
    try {
        $eventProposal = $assignment['event_proposal'];
        $company = $eventProposal['company'];
        $provider = $assignment['provider'];

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

        $eventProposalDate = date('d/m/Y', strtotime($eventProposal['proposed_date']));

        // Formater la durée pour l'affichage
        $durationText = '';
        if ($eventProposal['duration'] >= 60) {
            $hours = floor($eventProposal['duration'] / 60);
            $minutes = $eventProposal['duration'] % 60;
            $durationText = $hours . ' heure' . ($hours > 1 ? 's' : '');
            if ($minutes > 0) {
                $durationText .= ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            }
        } else {
            $durationText = $eventProposal['duration'] . ' minutes';
        }

        $mail->Body = "
            <meta charset='UTF-8'>
            <h2>Refus d'une activité</h2>
            <p>Le prestataire <strong>{$provider['first_name']} {$provider['last_name']}</strong> a refusé l'activité proposée.</p>
            <p><strong>Entreprise:</strong> {$company['name']}</p>
            <p><strong>Activité:</strong> {$eventProposal['event_type']['title']}</p>
            <p><strong>Date prévue:</strong> {$eventProposalDate}</p>
            <p><strong>Durée prévue:</strong> {$durationText}</p>
            <p>Veuillez vous connecter à la plateforme pour assigner un autre prestataire à cette activité.</p>
            <p><a href='" . url('/dashboard/gestion_admin/event_proposals/' . $eventProposal['id']) . "'>Gérer cette activité</a></p>
        ";

        $mail->AltBody = "Refus d'activité - Le prestataire {$provider['first_name']} {$provider['last_name']} a refusé l'activité {$eventProposal['event_type']['title']} (durée: {$durationText}) pour {$company['name']}.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        Log::error("Erreur d'envoi d'email à l'admin: {$mail->ErrorInfo}");
        return false;
    }
}
}
