<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class AdminPendingRegistrationController extends Controller
{
    public function index()
    {

        $pendingRegistrations = PendingRegistration::where('status', 'pending')
                                                  ->orderBy('created_at', 'desc')
                                                  ->get();

        return view('dashboards.gestion_admin.inscriptions.index', compact('pendingRegistrations'));
    }

    public function show($id)
    {
        $registration = PendingRegistration::findOrFail($id);
        return view('dashboards.gestion_admin.inscriptions.show', compact('registration'));
    }

    public function approve($id)
    {
        \Log::info('Début de la méthode approve', ['id' => $id]);

        try {
            $pendingRegistration = PendingRegistration::findOrFail($id);

            \Log::info('PendingRegistration trouvé', [
                'id' => $id,
                'status' => $pendingRegistration->status,
                'type' => $pendingRegistration->user_type
            ]);

            // Vérifier que la demande est toujours en attente
            if ($pendingRegistration->status !== 'pending') {
                return redirect()->back()->withErrors(['error' => 'Cette demande a déjà été traitée.']);
            }

            // Récupérer les données supplémentaires stockées en JSON
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

                    \Log::info('Société créée avec succès', ['company_id' => $company->id]);
                    break;

                case 'prestataire':
                    // Déterminer le type d'activité à partir des données additionnelles
                    $activityType = $pendingRegistration->activity_type ?? 'yoga';

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
                    $provider->activity_type = $activityType;

                    // Si l'activité est "autre", enregistrer l'activité personnalisée
                    if ($activityType === 'autre' && isset($additionalData['custom_activity'])) {
                        $provider->other_activity = $additionalData['custom_activity'];
                    }

                    $provider->statut_prestataire = 'Validé';
                    $provider->date_validation = now();
                    $provider->save();

                    \Log::info('Prestataire créé avec succès', ['provider_id' => $provider->id]);
                    break;

                default:
                    // Pour les autres types, simplement mettre à jour le statut
                    \Log::info('Type d\'utilisateur non géré, uniquement mise à jour du statut', [
                        'type' => $pendingRegistration->user_type
                    ]);
                    break;
            }

            $pendingRegistration->status = 'approved';
            $pendingRegistration->save();

            \Log::info('Statut de la demande mis à jour avec succès', [
                'id' => $id,
                'nouveau_status' => 'approved'
            ]);

            $this->sendUserNotification($pendingRegistration, true);

            return redirect()->route('admin.inscriptions.index')
                ->with('success', 'Demande d\'inscription approuvée avec succès.');

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'approbation de l\'inscription', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->withErrors(['error' => 'Une erreur est survenue: ' . $e->getMessage()]);
        }
    }

    public function reject($id)
    {
        $registration = PendingRegistration::findOrFail($id);

        $registration->status = 'rejected';
        $registration->save();

        $this->sendUserNotification($registration, false);

        return redirect()->route('admin.inscriptions.index')
                       ->with('success', 'Inscription rejetée');
    }

    private function sendUserNotification($registration, $approved)
    {
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

            // Destinataire (utilisateur)
            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
            $mail->addAddress($registration->email);

            // Contenu
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
