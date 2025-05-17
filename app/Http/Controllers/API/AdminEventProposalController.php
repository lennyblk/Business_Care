<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EventProposal;
use App\Models\Provider;
use App\Models\Company;
use App\Models\Location;
use App\Models\ServiceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AdminEventProposalController extends Controller
{
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

            $companies = Company::all();
            $locations = Location::all();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'activityTypes' => $activityTypes,
                    'companies' => $companies,
                    'locations' => $locations
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('API Error in AdminEventProposalController@getFormData: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $eventProposal = EventProposal::create([
                'company_id' => $request->company_id,
                'event_type_id' => $this->findOrCreateServiceType($request->activity_type)->id,
                'proposed_date' => $request->proposed_date,
                'location_id' => $request->location_id,
                'duration' => $request->duration,
                'notes' => $request->notes,
                'status' => 'Pending',
                'created_by_admin' => true
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Proposition d\'activité créée avec succès',
                'data' => $eventProposal
            ]);
        } catch (\Exception $e) {
            Log::error('API Error in AdminEventProposalController@store: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur est survenue'
            ], 500);
        }
    }

    private function findOrCreateServiceType($activityType)
    {
        $serviceType = ServiceType::where('title', 'LIKE', "%$activityType%")->first();

        if (!$serviceType) {
            $provider = Provider::where('statut_prestataire', 'Validé')->first();
            if (!$provider) {
                $provider = Provider::first();
            }

            $title = ucfirst($activityType);
            $serviceType = ServiceType::create([
                'provider_id' => $provider ? $provider->id : 1,
                'title' => $title,
                'description' => "Prestation : $title",
                'price' => 100.00,
                'duration' => 60
            ]);
        }

        return $serviceType;
    }

    public function assignProvider(Request $request, $id)
    {
        $request->validate([
            'provider_id' => 'required|exists:provider,id',
            'payment_amount' => 'required|numeric|min:0'
        ]);

        $eventProposal = EventProposal::findOrFail($id);
        $provider = Provider::findOrFail($request->provider_id);

        $assignment = ProviderAssignment::create([
            'event_proposal_id' => $eventProposal->id,
            'provider_id' => $request->provider_id,
            'status' => 'Proposed',
            'payment_amount' => $request->payment_amount,
            'proposed_at' => now()
        ]);

        $eventProposal->status = 'Assigned';
        $eventProposal->save();

        $this->notifyProvider($assignment);
        $this->notifyCompany($eventProposal, $provider);

        return response()->json([
            'status' => 'success',
            'message' => 'Le prestataire a été assigné avec succès à cette activité.'
        ]);
    }

    public function rejectProposal($id)
    {
        $eventProposal = EventProposal::findOrFail($id);
        $eventProposal->status = 'Rejected';
        $eventProposal->save();

        $this->notifyCompanyRejection($eventProposal);

        return response()->json([
            'status' => 'success',
            'message' => 'La demande d\'activité a été refusée.'
        ]);
    }

    private function notifyProvider(ProviderAssignment $assignment)
    {
        $provider = $assignment->provider;
        $eventProposal = $assignment->eventProposal;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = env('MAIL_PORT', 587);

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
            $mail->addAddress($provider->email);

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

    private function notifyCompany(EventProposal $eventProposal, Provider $provider)
    {
        $company = $eventProposal->company;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = env('MAIL_PORT', 587);

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
            $mail->addAddress($company->email);

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

    private function notifyCompanyRejection(EventProposal $eventProposal)
    {
        $company = $eventProposal->company;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = env('MAIL_PORT', 587);

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
            $mail->addAddress($company->email);

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