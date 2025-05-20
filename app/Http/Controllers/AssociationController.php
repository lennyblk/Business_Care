<?php

namespace App\Http\Controllers;

use App\Models\Association;
use App\Models\Donation;
use App\Models\Employee;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Log;

class AssociationController extends Controller
{
    /**
     * Affiche la liste des associations
     */
    public function index()
    {
        // Récupérer les données via l'API
        $response = app(\App\Http\Controllers\API\AssociationApiController::class)->index();
        $data = json_decode($response->getContent(), true);

        // Vérifier si la requête a réussi
        if (!$data['success']) {
            return redirect()->back()->withErrors(['error' => 'Impossible de récupérer les associations']);
        }

        $associations = $data['data'];

        // La vue attend $associations, pas $invoices
        return view('dashboards.client.associations.index', compact('associations'));
    }

    /**
     * Affiche les détails d'une association
     */
    public function show($id)
    {
        // Récupérer les données via l'API
        $response = app(\App\Http\Controllers\API\AssociationApiController::class)->show($id);
        $data = json_decode($response->getContent(), true);

        // Vérifier si la requête a réussi
        if (!$data['success']) {
            return redirect()->back()->withErrors(['error' => 'Association non trouvée']);
        }

        $association = $data['data'];

        return view('dashboards.client.associations.show', compact('association'));
    }

    /**
     * Traite un don à une association
     */
    public function donate(Request $request, $id)
{
    // Valider les données du formulaire
    $request->validate([
        'amount' => 'required|numeric|min:1'
    ]);

    try {
        $association = Association::findOrFail($id);
        $companyId = session('user_id');
        $amount = $request->amount;
        $userType = session('user_type');

        // Configuration de Stripe avec la clé API
        Stripe::setApiKey('sk_test_51RJaodCBigEWbFDKy1ZUFlMNoljc5GuwMW8vtcAf6CqTqB11Iskm8LJ5IyrLisMpQbifdyl7CG2pv5KSRY4AsB0N00YBpKyVV7');

        Log::info('Création de session Stripe pour don à l\'association #' . $id, [
            'montant' => $amount,
            'utilisateur_id' => $companyId,
            'type_utilisateur' => $userType
        ]);

        $successUrl = route('client.associations.donation.success', ['id' => $id]) . '?session_id={CHECKOUT_SESSION_ID}&amount=' . $amount;
        $cancelUrl = route('client.associations.show', ['id' => $id]);

        $session = Session::create([
            'payment_method_types' => ['card'],

            'mode' => 'payment',

            'payment_method_options' => [
                'card' => [
                    'setup_future_usage' => null,
                    'request_three_d_secure' => 'any',
                ],
            ],

            'customer_creation' => 'always',

            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Don à l\'association ' . $association->name,
                        'description' => 'Soutien à ' . $association->name,
                    ],
                    'unit_amount' => (int)($amount * 100),
                ],
                'quantity' => 1,
            ]],

            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,

            'metadata' => [
                'association_id' => $id,
                'company_id' => $companyId,
                'donation_type' => 'financial',
                'user_type' => $userType
            ]
        ]);

        return redirect($session->url);
    } catch (\Exception $e) {
        Log::error('Erreur Stripe pour don: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return redirect()->route('client.associations.show', $id)
            ->with('error', 'Une erreur est survenue lors de la création du paiement: ' . $e->getMessage());
    }
}

    /**
     * Gestion du succès d'un don
     */
    public function donationSuccess(Request $request, $id)
{
    try {
        $association = Association::findOrFail($id);
        $companyId = session('user_id');
        $userType = session('user_type');
        $amount = $request->get('amount');

        Stripe::setApiKey('sk_test_51RJaodCBigEWbFDKy1ZUFlMNoljc5GuwMW8vtcAf6CqTqB11Iskm8LJ5IyrLisMpQbifdyl7CG2pv5KSRY4AsB0N00YBpKyVV7');

        $sessionId = $request->get('session_id');
        if (!$sessionId) {
            Log::error('Session ID manquant dans la requête de succès du don');
            return redirect()->route('client.associations.show', $id)
                ->with('error', 'Impossible de vérifier le don. Session ID manquant.');
        }

        $session = Session::retrieve($sessionId);

        if ($session->payment_status === 'paid') {
            // Appel au controller API pour traiter le don
            $apiResponse = app(\App\Http\Controllers\API\AssociationApiController::class)->processDonation(
                new Request([
                    'amount' => $amount,
                    'payment_method_id' => $sessionId
                ]),
                $id
            );

            $result = json_decode($apiResponse->getContent(), true);

            if ($result['success']) {
            
                $company = \App\Models\Company::findOrFail($companyId);

                $this->sendDonationConfirmationToCompany($result['data']['donation'], $association, $company);

                return redirect()->route('client.associations.index')
                    ->with('success', 'Votre don a été effectué avec succès. Merci pour votre générosité ! La facture est disponible dans votre espace de facturation.');
            }

            return redirect()->route('client.associations.show', $id)
                ->with('error', 'Erreur lors du traitement du don: ' . ($result['message'] ?? 'Erreur inconnue'));
        }

    } catch (\Exception $e) {
    }
}

private function sendDonationConfirmationToCompany($donation, $association, $company)
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

        $fromAddress = env('MAIL_FROM_ADDRESS');
        if (empty($fromAddress)) {
            $fromAddress = 'noreply@business-care.fr';
            Log::warning('Adresse MAIL_FROM_ADDRESS vide, utilisation de l\'adresse par défaut');
        }

        $mail->setFrom($fromAddress, env('MAIL_FROM_NAME', 'Business-Care'));
        $mail->addAddress($company->email);

        $mail->isHTML(true);
        $mail->Subject = 'Confirmation de don - Business Care';
        $mail->Body = "
            <h2>Don confirmé</h2>
            <p>Bonjour " . $company->name . ",</p>
            <p>Nous avons bien reçu votre don de " . number_format($donation->amount_or_description, 2, ',', ' ') . " € à l'association " . $association->name . ".</p>
            <p>Une facture a été générée et est disponible dans votre espace facturation.</p>
            <p>Merci pour votre générosité et votre soutien !</p>
            <p>Cordialement,<br>L'équipe Business-Care</p>
        ";

        $mail->send();
        Log::info('Email de confirmation de don envoyé à ' . $company->email);
    } catch (\Exception $e) {
        Log::error("Erreur d'envoi d'email de confirmation de don: " . $e->getMessage());
    }
}

    private function sendDonationConfirmationEmail($donation, $association, $employee)
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
            $mail->addAddress($employee->email);

            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de don - Business Care';
            $mail->Body = "
                <h2>Don confirmé</h2>
                <p>Bonjour " . $employee->first_name . ",</p>
                <p>Nous avons bien reçu votre don de " . number_format($donation->amount_or_description, 2, ',', ' ') . " € à l'association " . $association->name . ".</p>
                <p>Une facture a été générée et est disponible dans l'espace facturation de votre entreprise.</p>
                <p>Merci pour votre générosité et votre soutien !</p>
                <p>Cordialement,<br>L'équipe Business-Care</p>
            ";

            $mail->send();
            Log::info('Email de confirmation de don envoyé à ' . $employee->email);
        } catch (\Exception $e) {
            Log::error("Erreur d'envoi d'email de confirmation de don: " . $e->getMessage());
        }
    }
}
