<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PendingRegistrationController extends Controller
{
    public function register(Request $request)
    {
        \Log::info('Données reçues dans PendingRegistrationController', $request->all());
        \Log::info('Début de la demande d\'inscription', $request->all());

        $commonRules = [
            'email' => 'required|email',
            'password' => 'required|min:6',
            'user_type' => 'required|in:societe,employe,prestataire',
        ];

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
                'name' => 'required|string|max:100',
                'prenom' => 'required|string|max:100',
                'specialite' => 'required|string',
                'telephone_provider' => 'required|string|max:20',
                'bio' => 'nullable|string',
                'tarif_horaire' => 'required|numeric|min:0',
                'activity_type' => 'required|string',
                'other_activity' => 'required_if:activity_type,autre|nullable|string',
                'adresse' => 'nullable|string|max:255',
                'code_postal_provider' => 'nullable|string|max:10',
                'ville_provider' => 'nullable|string|max:100',
                'siret_provider' => 'nullable|string|max:14',
                'document_justificatif' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            ],
        ];

        $userType = $request->input('user_type');
        $validationRules = array_merge($commonRules, $typeRules[$userType] ?? []);

        $validator = Validator::make($request->all(), $validationRules);

        if ($validator->fails()) {
            \Log::warning('Validation échouée', ['errors' => $validator->errors()]);
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->all();
            $data['password'] = Hash::make($data['password']);
            $data['status'] = 'pending';

            if ($userType === 'prestataire') {
                $data['first_name'] = $data['prenom'];
                $data['last_name'] = $data['name'];
                $data['domains'] = $data['specialite'];
                $data['description'] = $data['bio'] ?? 'Pas de description';
                $data['telephone'] = $data['telephone_provider'];
                $data['code_postal'] = $data['code_postal_provider'];
                $data['ville'] = $data['ville_provider'];
                $data['siret'] = $data['siret_provider'];

                if ($request->hasFile('document_justificatif')) {
                    $file = $request->file('document_justificatif');
                    $fileName = 'justificatif_' . Str::random(10) . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $filePath = $file->storeAs('justificatifs', $fileName, 'public');

                    $additionalData = isset($data['additional_data']) ? json_decode($data['additional_data'], true) : [];

                    if (isset($data['activity_type']) && $data['activity_type'] === 'autre' && !empty($data['other_activity'])) {
                        $additionalData['custom_activity'] = $data['other_activity'];
                    }

                    // Ajout du chemin du fichier
                    $additionalData['document_justificatif'] = $filePath;

                    $data['additional_data'] = json_encode($additionalData);
                }
            }

            \Log::info('Données préparées pour insertion', ['data' => array_diff_key($data, ['password' => ''])]);

            $pendingRegistration = PendingRegistration::create($data);

            \Log::info('Inscription en attente créée', ['id' => $pendingRegistration->id]);

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

            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = env('MAIL_PORT', 587);

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
            $mail->addAddress(env('ADMIN_EMAIL', 'len06blackett@gmail.com'), 'Administrateur');

            $mail->isHTML(true);
            $mail->Subject = 'Nouvelle demande d\'inscription';

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
            \Log::error("Erreur d'envoi d'email: {$mail->ErrorInfo}");
            return false;
        }
    }

    private function sendUserConfirmation($pendingRegistration)
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
            $mail->addAddress($pendingRegistration->email);

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
