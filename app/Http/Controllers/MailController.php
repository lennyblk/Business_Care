<?php

namespace App\Http\Controllers;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\Http\Controllers\MailController;

class MailController extends Controller
{
    public function envoyerEmail()
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

        // Destinataires
        $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
        $mail->addAddress('len06blackett@gmail.com', 'Destinataire');

        // Contenu
        $mail->isHTML(true);
        $mail->Subject = 'Test d\'envoi d\'email';
        $mail->Body = 'Contenu <b>HTML</b> de l\'email';
        $mail->AltBody = 'Contenu texte simple de l\'email';

        $mail->send();
        return 'Email envoyé avec succès';
    } catch (Exception $e) {
        return "Erreur d'envoi: {$mail->ErrorInfo}";
    }
}
}
