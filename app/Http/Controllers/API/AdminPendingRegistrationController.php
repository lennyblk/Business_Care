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
        $registration = PendingRegistration::findOrFail($id);

        try {
            switch ($registration->user_type) {
                case 'societe':
                    $company = Company::create([
                        'name' => $registration->company_name,
                        'address' => $registration->address,
                        'code_postal' => $registration->code_postal,
                        'ville' => $registration->ville,
                        'pays' => 'France',
                        'phone' => $registration->telephone,
                        'email' => $registration->email,
                        'siret' => $registration->siret,
                        'password' => $registration->password, // déjà hashé
                        'creation_date' => now(),
                        'formule_abonnement' => 'Starter',
                        'statut_compte' => 'Actif',
                        'date_debut_contrat' => now()
                    ]);
                    $userData = [
                        'id' => $company->id,
                        'email' => $company->email,
                        'name' => $company->name
                    ];
                    break;

                case 'employe':
                    $company = Company::where('name', $registration->company_name)->first();

                    if (!$company) {
                        return redirect()->back()->with('error', 'Entreprise non trouvée');
                    }

                    $employee = Employee::create([
                        'company_id' => $company->id,
                        'first_name' => $registration->first_name,
                        'last_name' => $registration->last_name,
                        'email' => $registration->email,
                        'telephone' => $registration->telephone,
                        'position' => $registration->position,
                        'departement' => $registration->departement,
                        'date_creation_compte' => now(),
                        'password' => $registration->password, // déjà hashé
                        'preferences_langue' => 'fr'
                    ]);
                    $userData = [
                        'id' => $employee->id,
                        'email' => $employee->email,
                        'name' => $employee->first_name . ' ' . $employee->last_name
                    ];
                    break;

                case 'prestataire':
                    $provider = Provider::create([
                        'last_name' => $registration->last_name,
                        'first_name' => $registration->first_name,
                        'description' => $registration->description ?? 'Pas de description',
                        'domains' => $registration->domains,
                        'email' => $registration->email,
                        'telephone' => $registration->telephone,
                        'password' => $registration->password, // déjà hashé
                        'statut_prestataire' => 'Validé',
                        'tarif_horaire' => $registration->tarif_horaire
                    ]);
                    $userData = [
                        'id' => $provider->id,
                        'email' => $provider->email,
                        'name' => $provider->first_name . ' ' . $provider->last_name
                    ];
                    break;
            }

            // Mettre à jour le statut de la demande
            $registration->status = 'approved';
            $registration->save();

            // Envoyer un email à l'utilisateur pour l'informer de l'approbation
            $this->sendUserNotification($registration, true);

            return redirect()->route('admin.pending-registrations.index')
                           ->with('success', 'Inscription approuvée avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                          ->with('error', 'Une erreur est survenue : ' . $e->getMessage());
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

        return redirect()->route('admin.pending-registrations.index')
                       ->with('success', 'Inscription rejetée');
    }

    private function sendUserNotification($registration, $approved)
    {
        $mail = new PHPMailer(true);

        try {
            // Configuration du serveur
            $mail->isSMTP();
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
