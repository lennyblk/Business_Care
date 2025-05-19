<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Company;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class AdminContractController extends Controller
{
    public function getPendingContracts()
    {
        try {
            $contracts = Contract::where('payment_status', 'pending')
                                ->with('company')
                                ->orderBy('id', 'desc')
                                ->get();

            return response()->json(['data' => $contracts], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des contrats en attente: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function approveContract($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $contract->payment_status = 'unpaid';
            $contract->save();

            $this->sendApprovalEmail($contract);

            return response()->json([
                'message' => 'Contrat approuvé avec succès',
                'data' => $contract
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'approbation du contrat: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function rejectContract($id)
    {
        try {
            $contract = Contract::with('company')->findOrFail($id);

            $this->sendRejectionEmail($contract);

            $contract->delete();

            return response()->json([
                'message' => 'Contrat rejeté avec succès'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors du rejet du contrat: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function approveTermination($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $contract->payment_status = 'terminated';
            $contract->is_termination_request = 0;
            $contract->save();

            $company = $contract->company;
            $company->statut_compte = 'Inactif';
            $company->save();

            $this->sendTerminationApprovalEmail($contract);

            return response()->json([
                'message' => 'Résiliation approuvée avec succès',
                'data' => $contract
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'approbation de la résiliation: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function rejectTermination($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $contract->payment_status = 'active';
            $contract->is_termination_request = 0;
            $contract->save();

            $this->sendTerminationRejectionEmail($contract);

            return response()->json([
                'message' => 'Demande de résiliation rejetée',
                'data' => $contract
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors du rejet de la résiliation: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    private function sendApprovalEmail($contract)
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
            $mail->addAddress($contract->company->email);

            $mail->isHTML(true);
            $mail->Subject = 'Votre contrat a été approuvé';
            $mail->Body = "
                <h2>Contrat approuvé</h2>
                <p>Bonjour,</p>
                <p>Votre contrat a été approuvé par notre équipe.</p>
                <p>Vous pouvez maintenant procéder au paiement pour activer votre contrat.</p>
                <p><a href='" . url('/contracts') . "'>Accéder à vos contrats</a></p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            Log::error("Erreur d'envoi d'email: {$mail->ErrorInfo}");
        }
    }

    private function sendRejectionEmail($contract)
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
            $mail->addAddress($contract->company->email);

            $mail->isHTML(true);
            $mail->Subject = 'Votre contrat a été rejeté';
            $mail->Body = "
                <h2>Contrat rejeté</h2>
                <p>Bonjour,</p>
                <p>Nous sommes désolés de vous informer que votre contrat a été rejeté.</p>
                <p>Pour plus d'informations, veuillez nous contacter.</p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            Log::error("Erreur d'envoi d'email: {$mail->ErrorInfo}");
        }
    }

    private function sendTerminationApprovalEmail($contract)
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
            $mail->addAddress($contract->company->email);

            $mail->isHTML(true);
            $mail->Subject = 'Votre demande de résiliation a été approuvée';
            $mail->Body = "
                <h2>Résiliation approuvée</h2>
                <p>Bonjour,</p>
                <p>Nous vous informons que votre demande de résiliation a été approuvée.</p>
                <p>Votre contrat est désormais marqué comme résilié.</p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            Log::error("Erreur d'envoi d'email: {$mail->ErrorInfo}");
        }
    }

    private function sendTerminationRejectionEmail($contract)
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
            $mail->addAddress($contract->company->email);

            $mail->isHTML(true);
            $mail->Subject = 'Votre demande de résiliation a été rejetée';
            $mail->Body = "
                <h2>Résiliation rejetée</h2>
                <p>Bonjour,</p>
                <p>Nous vous informons que votre demande de résiliation a été rejetée.</p>
                <p>Votre contrat est toujours actif.</p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            Log::error("Erreur d'envoi d'email: {$mail->ErrorInfo}");
        }
    }
}
