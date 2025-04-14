<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PendingRegistrationController extends Controller
{
    public function register(Request $request)
    {
        \Log::info('Données reçues dans PendingRegistrationController', $request->all());
        \Log::info('Début de la demande d\'inscription', $request->all());

        // Validation commune pour tous les types d'utilisateurs
        $commonRules = [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'user_type' => 'required|in:societe,employe,prestataire',
        ];

        // Règles par type d'utilisateur
        $typeRules = [
            'societe' => [
                'company_name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'code_postal' => 'required|string|max:10',
                'ville' => 'required|string|max:100',
                'telephone' => 'required|string|max:20',
                'siret' => 'nullable|string|max:14',
            ],
            'employe' => [
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'company_name' => 'required|string',
                'position' => 'required|string|max:100',
                'departement' => 'nullable|string|max:100',
                'telephone' => 'nullable|string|max:20',
            ],
            'prestataire' => [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'domains' => 'required|string',
                'telephone' => 'required|string|max:20',
                'description' => 'nullable|string',
                'tarif_horaire' => 'nullable|numeric|min:0',
            ],
        ];

        // on récupère les règles spécifiques au type d'utilisateur
        $userType = $request->input('user_type');
        $validationRules = array_merge($commonRules, $typeRules[$userType] ?? []);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            \Log::warning('Validation échouée', ['errors' => $validator->errors()]);
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // Hashage du mot de passe
            $data = $request->all();
            $data['password'] = Hash::make($data['password']);
            $data['status'] = 'pending';
            \Log::info('Données préparées pour insertion', ['data' => array_diff_key($data, ['password' => ''])]);

            // Stockage de la demande d'inscription en attente
            $pendingRegistration = PendingRegistration::create($data);

            \Log::info('Inscription en attente créée', ['id' => $pendingRegistration->id]);


            // Envoi d'email à l'administrateur et aux utilisateurs
            $this->sendAdminNotification($pendingRegistration);
            $this->sendUserConfirmation($pendingRegistration);

            return response()->json([
                'success' => true,
                'message' => 'Demande d\'inscription envoyée avec succès. Veuillez attendre l\'approbation de l\'administrateur.',
                'data' => $pendingRegistration
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la création de l\'inscription', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage(),
                'error_details' => $e->getTraceAsString()
            ], 500);
        }
    }

    private function sendAdminNotification($pendingRegistration)
    {
        $mail = new PHPMailer(true);

        try {

            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function($str, $level) {
            \Log::info("PHPMailer Debug: $str");
            };

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
            $mail->addAddress(env('ADMIN_EMAIL', 'len06blackett@gmail.com'), 'Administrateur');

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Nouvelle demande d\'inscription';

            // Corps du message selon le type d'utilisateur
            $userType = $pendingRegistration->user_type;
            $userName = '';

            if ($userType === 'societe') {
                $userName = $pendingRegistration->company_name;
            } else {
                $userName = $pendingRegistration->first_name . ' ' . $pendingRegistration->last_name;
            }

            $mail->Body = "
                <meta charset='UTF-8'>
                <h2>Nouvelle demande d'inscription</h2>
                <p><strong>Type d'utilisateur :</strong> {$userType}</p>
                <p><strong>Nom :</strong> {$userName}</p>
                <p><strong>Email :</strong> {$pendingRegistration->email}</p>
                <p>Veuillez vous connecter au tableau de bord administrateur pour approuver ou rejeter cette demande.</p>
                <p><a href='" . url('/dashboard/gestion_admin/inscriptions') . "'>Accéder au tableau de bord</a></p>
            ";

            $mail->AltBody = "Nouvelle demande d'inscription - Type: {$userType}, Nom: {$userName}, Email: {$pendingRegistration->email}";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log l'erreur mais ne pas échouer le processus d'inscription
            \Log::error("Erreur d'envoi d'email: {$mail->ErrorInfo}");
            return false;
        }
    }

    private function sendUserConfirmation($pendingRegistration)
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
            $mail->addAddress($pendingRegistration->email);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de votre demande d\'inscription';

            $userName = '';
            if ($pendingRegistration->user_type === 'societe') {
                $userName = $pendingRegistration->company_name;
            } else {
                $userName = $pendingRegistration->first_name . ' ' . $pendingRegistration->last_name;
            }

            $mail->Body = "
                <meta charset='UTF-8'>
                <h2>Merci pour votre inscription!</h2>
                <p>Nous avons bien reçu votre demande d'inscription et notre équipe administrative va l'examiner sous peu.</p>
                <p>Vous serez informé(e) par email dès que votre compte sera validé.</p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->AltBody = "Merci pour votre inscription! Nous avons bien reçu votre demande et notre équipe administrative va l'examiner sous peu.";

            $mail->send();
            return true;
        } catch (Exception $e) {
            \Log::error("Erreur d'envoi d'email utilisateur: {$mail->ErrorInfo}");
            return false;
        }
    }

}
