<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\Company;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AdminPendingRegistrationController extends Controller
{
    public function index()
    {
        try {
            $pendingRegistrations = PendingRegistration::where('status', 'pending')
                                                      ->orderBy('created_at', 'desc')
                                                      ->get();

            return response()->json(['data' => $pendingRegistrations]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erreur lors de la récupération des inscriptions: ' . $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $registration = PendingRegistration::findOrFail($id);
            return response()->json(['data' => $registration]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Inscription non trouvée'], 404);
        }
    }

    public function approve($id)
    {
        \Log::info('API: Approbation de l\'inscription #'.$id);

        try {
            $pendingRegistration = PendingRegistration::findOrFail($id);

            if ($pendingRegistration->status !== 'pending') {
                return response()->json(['error' => 'Cette demande a déjà été traitée.'], 400);
            }

            $additionalData = json_decode($pendingRegistration->additional_data, true) ?? [];

            switch ($pendingRegistration->user_type) {
                case 'societe':
                    $company = new Company();
                    $company->name = $pendingRegistration->company_name;
                    $company->email = $pendingRegistration->email;
                    $company->password = $pendingRegistration->password; // deja hashé
                    $company->address = $pendingRegistration->address;
                    $company->code_postal = $pendingRegistration->code_postal;
                    $company->ville = $pendingRegistration->ville;
                    $company->telephone = $pendingRegistration->telephone;
                    $company->creation_date = now();
                    $company->siret = $pendingRegistration->siret;
                    $company->formule_abonnement = 'Starter';
                    $company->statut_compte = 'Actif';
                    $company->date_debut_contrat = now();
                    $company->date_fin_contrat = now()->addYear();
                    $company->save();
                    break;

                case 'prestataire':
                    $activityType = $pendingRegistration->activity_type;

                    $provider = new Provider();
                    $provider->first_name = $pendingRegistration->first_name;
                    $provider->last_name = $pendingRegistration->last_name;
                    $provider->email = $pendingRegistration->email;
                    $provider->password = $pendingRegistration->password; // Déjà hashé
                    $provider->description = $pendingRegistration->description ?? 'Pas de description';
                    $provider->domains = $pendingRegistration->domains;
                    $provider->telephone = $pendingRegistration->telephone;
                    $provider->adresse = $pendingRegistration->adresse;
                    $provider->code_postal = $pendingRegistration->code_postal;
                    $provider->ville = $pendingRegistration->ville;
                    $provider->siret = $pendingRegistration->siret;
                    $provider->tarif_horaire = $pendingRegistration->tarif_horaire;
                    $provider->activity_type = $pendingRegistration->activity_type;

                    if ($activityType === 'autre' && isset($additionalData['custom_activity'])) {
                        $provider->other_activity = $additionalData['custom_activity'];
                    }


                    $provider->statut_prestataire = 'Validé';
                    $provider->date_validation = now();
                    $provider->save();
                    break;

                default:
                    \Log::info('Type d\'utilisateur non géré, uniquement mise à jour du statut', [
                        'type' => $pendingRegistration->user_type
                    ]);
                    break;
            }

            $pendingRegistration->status = 'approved';
            $pendingRegistration->save();

            $this->sendUserNotification($pendingRegistration, true);

            return response()->json([
                'success' => true,
                'message' => 'Demande d\'inscription approuvée avec succès.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur approbation: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Une erreur est survenue: ' . $e->getMessage()
            ], 500);
        }
    }

    public function reject($id)
    {
        try {
            $registration = PendingRegistration::findOrFail($id);

            if ($registration->status !== 'pending') {
                return response()->json(['error' => 'Cette demande a déjà été traitée.'], 400);
            }

            $additionalData = json_decode($registration->additional_data, true) ?? [];

            if (isset($additionalData['document_justificatif'])) {
                Storage::disk('public')->delete($additionalData['document_justificatif']);
                \Log::info('Document justificatif supprimé', ['path' => $additionalData['document_justificatif']]);
            }

            $registration->status = 'rejected';
            $registration->save();

            $this->sendUserNotification($registration, false);

            return response()->json([
                'success' => true,
                'message' => 'Inscription rejetée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Une erreur est survenue: ' . $e->getMessage()
            ], 500);
        }
    }

    private function sendUserNotification($registration, $approved)
    {
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
            $mail->addAddress($registration->email);

            $mail->isHTML(true);

            if ($approved) {
                $mail->Subject = 'Votre inscription a été approuvée';
                $mail->Body = "
                    <meta charset='UTF-8'>
                    <h2>Félicitations !</h2>
                    <p>Votre demande d'inscription a été approuvée. Vous pouvez maintenant vous connecter à votre compte.</p>
                    <p><a href='" . url('/login') . "'>Se connecter</a></p>
                ";
                $mail->AltBody = "Votre demande d'inscription a été approuvée. Vous pouvez maintenant vous connecter à votre compte.";
            } else {
                $mail->Subject = 'Votre inscription a été rejetée';
                $mail->Body = "
                    <h2>Nous sommes désolés</h2>
                    <p>Votre demande d'inscription a été rejetée. Pour plus d'informations, veuillez nous contacter.</p>
                ";
                $mail->AltBody = "Votre demande d'inscription a été rejetée. Pour plus d'informations, veuillez nous contacter.";
            }

            $mail->send();
            return true;
        } catch (Exception $e) {
            \Log::error("Erreur d'envoi d'email: {$mail->ErrorInfo}");
            return false;
        }
    }
}
