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

            // Log pour le débogage
            Log::info('Création de session Stripe pour le contrat #' . $contractId, [
                'mode_stripe' => strpos(env('STRIPE_SECRET'), 'test') !== false ? 'TEST' : 'PRODUCTION'
            ]);

            // Session Stripe simplifiée mais avec configuration explicite pour la carte
            $session = Session::create([
                // Uniquement carte (pas d'autres méthodes)
                'payment_method_types' => ['card'],

                // Forcer le mode carte
                'mode' => 'payment',

                // Configuration spécifique pour les tests
                'payment_method_options' => [
                    'card' => [
                        // Ces options forcent l'affichage du formulaire de carte
                        'setup_future_usage' => null,
                        'request_three_d_secure' => 'any',
                    ],
                ],

                // Désactiver l'utilisation de cartes enregistrées
                'customer_creation' => 'always',

                // Ligne d'article obligatoire
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Contrat Business Care - ' . $contract->formule_abonnement,
                            'description' => 'Durée: ' . \Carbon\Carbon::parse($contract->start_date)->format('d/m/Y') .
                                           ' - ' . \Carbon\Carbon::parse($contract->end_date)->format('d/m/Y'),
                        ],
                        'unit_amount' => (int)($contract->amount * 100), // Le montant est déjà le total du contrat
                    ],
                    'quantity' => 1,
                ]],

                // URLs de redirection
                'success_url' => route('stripe.success', ['contract' => $contractId]) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('stripe.cancel', ['contract' => $contractId]),
            ]);

            // Sauvegarder l'ID de la session Stripe
            $contract->stripe_checkout_id = $session->id;
            $contract->save();

            // Redirection vers la page de paiement Stripe
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

            // Vérifier la session de paiement
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

                try {
                    // Créer et enregistrer la facture
                    $invoice = new \App\Models\Invoice();
                    $invoice->contract_id = $contract->id;
                    $invoice->company_id = $contract->company_id;
                    $invoice->issue_date = now();
                    $invoice->due_date = now()->addDays(15);
                    $invoice->total_amount = $contract->amount;
                    $invoice->payment_status = 'Paid';
                    $invoice->details = "Paiement Stripe - Session ID: " . $sessionId . "\n" .
                                      "Date de paiement: " . now()->format('d/m/Y H:i:s') . "\n" .
                                      "Méthode: Carte bancaire via Stripe";

                    if (!$invoice->save()) {
                        throw new \Exception('Échec de la sauvegarde de la facture');
                    }

                    Log::info('Facture créée avec succès', [
                        'invoice_id' => $invoice->id,
                        'contract_id' => $contract->id,
                        'company_id' => $contract->company_id
                    ]);

                } catch (\Exception $invoiceError) {
                    Log::error('Erreur critique lors de la création de la facture', [
                        'error' => $invoiceError->getMessage(),
                        'contract_id' => $contract->id,
                        'company_id' => $contract->company_id
                    ]);
                }

                // Log des informations du contrat pour débogage
                Log::info('Informations du contrat avant mise à jour de l\'entreprise', [
                    'contract_id' => $contract->id,
                    'formule_abonnement' => $contract->formule_abonnement,
                    'end_date' => $contract->end_date
                ]);

                // Mettre à jour la date de fin de contrat et la formule dans la table company
                try {
                    $company = Company::findOrFail($contract->company_id);

                    // Log de l'état actuel de l'entreprise
                    Log::info('État actuel de l\'entreprise', [
                        'company_id' => $company->id,
                        'current_formule' => $company->formule_abonnement,
                        'current_date_fin' => $company->date_fin_contrat
                    ]);

                    // Mettre à jour explicitement chaque champ
                    $company->date_fin_contrat = $contract->end_date;
                    $company->statut_compte = 'Actif';

                    // Vérifier que la formule est une valeur autorisée
                    $allowedFormules = ['Starter', 'Basic', 'Premium'];
                    if (in_array($contract->formule_abonnement, $allowedFormules)) {
                        $company->formule_abonnement = $contract->formule_abonnement;
                        Log::info('Formule mise à jour à: ' . $contract->formule_abonnement);
                    } else {
                        Log::warning('Formule non valide: ' . $contract->formule_abonnement);
                    }

                    // Sauvegarder et vérifier le résultat
                    $saved = $company->save();

                    // Log de confirmation de la mise à jour
                    Log::info('Résultat de la mise à jour de l\'entreprise', [
                        'saved' => $saved ? 'Oui' : 'Non',
                        'company_id' => $company->id,
                        'new_formule' => $company->formule_abonnement,
                        'new_date_fin' => $company->date_fin_contrat
                    ]);

                    // Double vérification
                    $refreshedCompany = Company::find($company->id);
                    Log::info('État de l\'entreprise après rafraîchissement', [
                        'formule_abonnement' => $refreshedCompany->formule_abonnement,
                        'date_fin_contrat' => $refreshedCompany->date_fin_contrat,
                        'statut_compte' => $refreshedCompany->statut_compte
                    ]);

                } catch (\Exception $companyError) {
                    Log::error('Erreur lors de la mise à jour de l\'entreprise: ' . $companyError->getMessage(), [
                        'trace' => $companyError->getTraceAsString()
                    ]);
                }

                $this->sendPaymentConfirmationEmail($contract);

                return redirect()->route('contracts.show', $contractId)
                    ->with('success', 'Paiement effectué avec succès. Votre contrat est maintenant actif.');
            }

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