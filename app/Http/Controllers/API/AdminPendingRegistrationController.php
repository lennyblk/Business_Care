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
        try {
            $pendingRegistration = PendingRegistration::findOrFail($id);

            if ($pendingRegistration->status !== 'pending') {
                return redirect()->back()->withErrors(['error' => 'Cette demande a déjà été traitée.']);
            }

            // Récupérer les données supplémentaires stockées en JSON
            $additionalData = json_decode($pendingRegistration->additional_data, true) ?? [];

            // Log des données pour le débogage
            \Log::info('Données de l\'inscription', [
                'pendingId' => $pendingRegistration->id,
                'additionalData' => $additionalData
            ]);

            // Créer l'utilisateur en fonction du type
            switch ($pendingRegistration->user_type) {
                case 'societe':
                    // Code existant pour les sociétés
                    break;

                case 'employe':
                    // Code existant pour les employés
                    break;

                case 'prestataire':
                    // Déterminer le type d'activité à partir des données additionnelles
                    $activityType = 'yoga'; // Valeur par défaut

                    // Vérifier si le type d'activité est dans additionalData
                    if (!empty($additionalData) && isset($additionalData['activity_type'])) {
                        $activityType = $additionalData['activity_type'];
                    }

                    // Créer le prestataire
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
                    $provider->activity_type = $activityType; // Utiliser la valeur déterminée

                    // Si l'activité est "autre", enregistrer l'activité personnalisée
                    if ($activityType === 'autre' && isset($additionalData['custom_activity'])) {
                        $provider->other_activity = $additionalData['custom_activity'];
                    }

                    $provider->statut_prestataire = 'Validé';
                    $provider->date_validation = now();
                    $provider->save();
                    break;
            }

            // Mettre à jour le statut de l'inscription en attente
            $pendingRegistration->status = 'approved';
            $pendingRegistration->save();

            // Envoyer un e-mail de confirmation
            $this->sendApprovalEmail($pendingRegistration);

            return redirect()->route('admin.inscriptions.index')
                ->with('success', 'Demande d\'inscription approuvée avec succès.');

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'approbation de l\'inscription', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()]);
        }
    }

    public function reject($id)
    {
        $registration = PendingRegistration::findOrFail($id);

        // Mettre à jour le statut de la demande
        $registration->status = 'rejected';
        $registration->save();

        // Envoyer un email à l'utilisateur pour l'informer du rejet
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
            // Log l'erreur
            \Log::error("Erreur d'envoi d'email: {$mail->ErrorInfo}");
            return false;
        }
    }
}
