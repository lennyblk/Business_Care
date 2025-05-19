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
            $employeeId = session('user_id');
            $amount = $request->amount;

            // Récupérer l'employé et sa société
            $employee = Employee::findOrFail($employeeId);
            $companyId = $employee->company_id;

            // Configuration de Stripe avec la clé API
            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Log pour le débogage
            Log::info('Création de session Stripe pour don à l\'association #' . $id, [
                'mode_stripe' => strpos(env('STRIPE_SECRET'), 'test') !== false ? 'TEST' : 'PRODUCTION',
                'montant' => $amount,
                'employé' => $employeeId,
                'société' => $companyId
            ]);

            // Session Stripe pour le don
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
                            'name' => 'Don à l\'association ' . $association->name,
                            'description' => 'Soutien à ' . $association->name,
                        ],
                        'unit_amount' => (int)($amount * 100), // Conversion en centimes
                    ],
                    'quantity' => 1,
                ]],

                // URLs de redirection
                'success_url' => route('client.associations.donation.success', ['id' => $id]) . '?session_id={CHECKOUT_SESSION_ID}&amount=' . $amount,
                'cancel_url' => route('client.associations.show', ['id' => $id]),

                // Métadonnées pour le suivi
                'metadata' => [
                    'association_id' => $id,
                    'employee_id' => $employeeId,
                    'donation_type' => 'financial',
                    'company_id' => $companyId
                ]
            ]);

            // Redirection vers la page de paiement Stripe
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
            $employeeId = session('user_id');
            $amount = $request->get('amount');

            Stripe::setApiKey(env('STRIPE_SECRET'));

            // Vérifier la session de paiement
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
                    $this->sendDonationConfirmationEmail($result['data']['donation'], $association, Employee::findOrFail($employeeId));

                    return redirect()->route('client.associations.index')
                        ->with('success', 'Votre don a été effectué avec succès. Merci pour votre générosité ! La facture est disponible dans votre espace de facturation.');
                }

                return redirect()->route('client.associations.show', $id)
                    ->with('error', 'Erreur lors du traitement du don: ' . ($result['message'] ?? 'Erreur inconnue'));
            }

            return redirect()->route('client.associations.show', $id)
                ->with('error', 'Le don n\'a pas été complété. Statut: ' . $session->payment_status);

        } catch (\Exception $e) {
            Log::error('Erreur de confirmation don Stripe: ' . $e->getMessage());
            return redirect()->route('client.associations.show', $id)
                ->with('error', 'Une erreur est survenue lors de la confirmation du don: ' . $e->getMessage());
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
