<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Log;

class StripePaymentController extends Controller
{
    public function createCheckoutSession($contractId)
    {
        try {
            $contract = Contract::with('company')->findOrFail($contractId);

            // Vérifier que le contrat est en statut 'unpaid'
            if ($contract->payment_status !== 'unpaid') {
                return redirect()->route('contracts.show', $contractId)
                    ->with('error', 'Ce contrat ne peut pas être payé actuellement.');
            }

            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Créer la session de paiement Stripe
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Contrat Business Care - ' . $contract->services,
                            'description' => 'Contrat du ' . \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') .
                                           ' au ' . \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y'),
                        ],
                        'unit_amount' => $contract->amount * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('stripe.success', ['contract' => $contractId]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('stripe.cancel', ['contract' => $contractId]),
                'metadata' => [
                    'contract_id' => $contract->id,
                    'company_id' => $contract->company_id,
                ],
            ]);

            // Sauvegarder l'ID de la session Stripe
            $contract->stripe_checkout_id = $session->id;
            $contract->save();

            return redirect($session->url);
        } catch (\Exception $e) {
            Log::error('Erreur Stripe: ' . $e->getMessage());
            return redirect()->route('contracts.show', $contractId)
                ->with('error', 'Une erreur est survenue lors de la création du paiement.');
        }
    }

    public function success(Request $request, $contractId)
    {
        try {
            $contract = Contract::findOrFail($contractId);

            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Récupérer la session de paiement
            $session = Session::retrieve($request->get('session_id'));

            if ($session->payment_status === 'paid') {
                // Mettre à jour le statut du contrat
                $contract->payment_status = 'active';
                $contract->save();

                // Envoyer un email de confirmation
                $this->sendPaymentConfirmationEmail($contract);

                return redirect()->route('contracts.show', $contractId)
                    ->with('success', 'Paiement effectué avec succès. Votre contrat est maintenant actif.');
            }

            return redirect()->route('contracts.show', $contractId)
                ->with('error', 'Le paiement n\'a pas été complété.');
        } catch (\Exception $e) {
            Log::error('Erreur de confirmation Stripe: ' . $e->getMessage());
            return redirect()->route('contracts.show', $contractId)
                ->with('error', 'Une erreur est survenue lors de la confirmation du paiement.');
        }
    }

    public function cancel($contractId)
    {
        return redirect()->route('contracts.show', $contractId)
            ->with('info', 'Paiement annulé. Vous pouvez réessayer à tout moment.');
    }

    private function sendPaymentConfirmationEmail($contract)
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = env('MAIL_PORT', 587);

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME', 'Business-Care'));
            $mail->addAddress($contract->company->email);

            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de paiement - Business Care';
            $mail->Body = "
                <h2>Paiement confirmé</h2>
                <p>Bonjour,</p>
                <p>Nous avons bien reçu votre paiement pour le contrat #" . $contract->id . ".</p>
                <p>Votre contrat est maintenant actif.</p>
                <p><strong>Détails :</strong></p>
                <ul>
                    <li>Services : " . $contract->services . "</li>
                    <li>Montant : " . number_format($contract->amount, 2, ',', ' ') . " €</li>
                    <li>Période : du " . \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') .
                       " au " . \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') . "</li>
                </ul>
                <p><a href='" . url('/contracts/' . $contract->id) . "'>Voir votre contrat</a></p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->send();
        } catch (\Exception $e) {
            Log::error("Erreur d'envoi d'email de confirmation: " . $e->getMessage());
        }
    }
}
