<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Contract;
use Carbon\Carbon;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;

class CheckExpiredContracts extends Command
{
    protected $signature = 'contracts:check-expired';
    protected $description = 'Check for expired contracts and update their status';

    public function handle()
    {
        $today = Carbon::today();

        $expiredContracts = Contract::where('payment_status', 'active')
            ->whereDate('end_date', '<', $today)
            ->with('company')
            ->get();

        foreach ($expiredContracts as $contract) {
            $contract->payment_status = 'unpaid';
            $contract->save();

            $this->sendExpirationEmail($contract);

            $this->info("Contract #{$contract->id} expired and status updated.");
        }

        $this->info("Checked " . $expiredContracts->count() . " expired contracts.");
    }

    private function sendExpirationEmail($contract)
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
            $mail->Subject = 'Votre contrat Business Care a expiré';
            $mail->Body = "
                <h2>Contrat expiré</h2>
                <p>Bonjour,</p>
                <p>Votre contrat Business Care a expiré le " . Carbon::parse($contract->end_date)->format('d/m/Y') . ".</p>
                <p>Pour continuer à bénéficier de nos services, veuillez renouveler votre contrat.</p>
                <p><a href='" . url('/contracts') . "'>Renouveler votre contrat</a></p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            Log::error("Erreur d'envoi d'email d'expiration: " . $e->getMessage());
        }
    }
}
