<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Company;
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

            // Configuration de Stripe avec la clé API
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Log de débogage avec la clé Stripe utilisée (sans montrer la clé complète)
            $stripeKey = env('STRIPE_SECRET');
            if ($stripeKey) {
                Log::info('Mode Stripe: ' . (strpos($stripeKey, 'sk_test_') === 0 ? 'TEST' : 'PRODUCTION'));
            } else {
                Log::error('Clé Stripe non définie dans .env');
            }

            // Ajout d'informations sur le mode de paiement
            Log::info('Création de la session de paiement pour le contrat #' . $contractId, [
                'amount' => $contract->amount,
                'company' => $contract->company_id
            ]);

            // Configuration plus stricte pour la session Stripe
            $sessionParams = [
                // Forcer l'utilisation de la carte uniquement
                'payment_method_types' => ['card'],

                // Information sur le client
                'customer_email' => $contract->company->email,

                // Détails de l'article
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Contrat Business Care - ' . $contract->formule_abonnement,
                            'description' => 'Contrat du ' . \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') .
                                           ' au ' . \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y'),
                        ],
                        'unit_amount' => (int)($contract->amount * 100), // Conversion en centimes
                    ],
                    'quantity' => 1,
                ]],

                // Mode de paiement unique
                'mode' => 'payment',

                // Type de soumission - obligatoire pour forcer l'affichage du formulaire
                'submit_type' => 'pay',

                // Configuration de l'interface
                'billing_address_collection' => 'required', // Demander l'adresse de facturation

                // Configuration technique du paiement
                'payment_intent_data' => [
                    'capture_method' => 'automatic',
                    'receipt_email' => $contract->company->email,
                    'description' => 'Paiement du contrat #' . $contract->id,
                ],

                // URLs de redirection
                'success_url' => route('stripe.success', ['contract' => $contractId]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('stripe.cancel', ['contract' => $contractId]),

                // Métadonnées
                'metadata' => [
                    'contract_id' => $contract->id,
                    'company_id' => $contract->company_id,
                    'formule_abonnement' => $contract->formule_abonnement,
                ],
            ];

            // Création de la session
            $session = Session::create($sessionParams);

            // Log de l'URL de la session créée
            Log::info('Session Stripe créée: ' . $session->id . ', URL: ' . $session->url);

            // Sauvegarder l'ID de la session Stripe
            $contract->stripe_checkout_id = $session->id;
            $contract->save();

            return redirect($session->url);
        } catch (\Exception $e) {
            Log::error('Erreur Stripe: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('contracts.show', $contractId)
                ->with('error', 'Une erreur est survenue lors de la création du paiement: ' . $e->getMessage());
        }
    }

    public function success(Request $request, $contractId)
    {
        try {
            $contract = Contract::with('company')->findOrFail($contractId);

            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Récupérer la session de paiement
            $sessionId = $request->get('session_id');
            if (!$sessionId) {
                Log::error('Session ID manquant dans la requête de succès');
                return redirect()->route('contracts.show', $contractId)
                    ->with('error', 'Impossible de vérifier le paiement. Session ID manquant.');
            }

            $session = Session::retrieve($sessionId);
            Log::info('Session de paiement récupérée', [
                'session_id' => $sessionId,
                'payment_status' => $session->payment_status
            ]);

            if ($session->payment_status === 'paid') {
                // Mettre à jour le statut du contrat
                $contract->payment_status = 'active';
                $contract->save();

                // Mettre à jour directement les informations de l'entreprise
                $company = Company::findOrFail($contract->company_id);
                $company->date_fin_contrat = $contract->end_date;
                $company->formule_abonnement = $contract->formule_abonnement;
                $company->statut_compte = 'Actif'; // Assurer que le compte est actif
                $company->save();

                Log::info('Contrat et entreprise mis à jour après paiement', [
                    'contract_id' => $contract->id,
                    'company_id' => $company->id,
                    'formule' => $company->formule_abonnement,
                    'date_fin' => $company->date_fin_contrat
                ]);

                // Envoyer un email de confirmation
                $this->sendPaymentConfirmationEmail($contract);

                return redirect()->route('contracts.show', $contractId)
                    ->with('success', 'Paiement effectué avec succès. Votre contrat est maintenant actif.');
            }

            Log::warning('Session de paiement non payée', [
                'session_id' => $sessionId,
                'status' => $session->payment_status
            ]);
            return redirect()->route('contracts.show', $contractId)
                ->with('error', 'Le paiement n\'a pas été complété. Statut: ' . $session->payment_status);
        } catch (\Exception $e) {
            Log::error('Erreur de confirmation Stripe: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('contracts.show', $contractId)
                ->with('error', 'Une erreur est survenue lors de la confirmation du paiement: ' . $e->getMessage());
        }
    }

    public function cancel($contractId)
    {
        Log::info('Paiement annulé par l\'utilisateur', ['contract_id' => $contractId]);
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
                <p>Votre contrat est maintenant actif jusqu'au " . \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') . ".</p>
                <p><strong>Détails :</strong></p>
                <ul>
                    <li>Formule : " . $contract->formule_abonnement . "</li>
                    <li>Services : " . $contract->services . "</li>
                    <li>Montant : " . number_format($contract->amount, 2, ',', ' ') . " €</li>
                    <li>Période : du " . \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') .
                       " au " . \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y') . "</li>
                </ul>
                <p><a href='" . url('/contracts/' . $contract->id) . "'>Voir votre contrat</a></p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->send();
            Log::info('Email de confirmation envoyé à ' . $contract->company->email);
        } catch (\Exception $e) {
            Log::error("Erreur d'envoi d'email de confirmation: " . $e->getMessage());
        }
    }
}
