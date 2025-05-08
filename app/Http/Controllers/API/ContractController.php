<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


class ContractController extends Controller
{
    public function index()
    {
        try {
            $contracts = Contract::all();
            return response()->json(['data' => $contracts]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des contrats: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue'], 500);
        }
    }

    public function store(Request $request)
{
    try {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|exists:company,id',
            'services' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'formule_abonnement' => 'required|in:Starter,Basic,Premium'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Créer le contrat avec le statut 'pending'
        $contractData = $request->all();
        $contractData['payment_status'] = 'pending';
        $contract = Contract::create($contractData);

        // Envoyer les emails de notification
        $this->sendContractPendingNotifications($contract);

        return response()->json([
            'message' => 'Contrat créé avec succès',
            'data' => $contract
        ], 201);
    } catch (\Exception $e) {
        Log::error('API: Erreur lors de la création du contrat: ' . $e->getMessage());
        return response()->json(['message' => 'Une erreur est survenue lors de la création du contrat'], 500);
    }
}

private function sendContractPendingNotifications($contract)
{
    // Email à l'entreprise
    $this->sendCompanyNotification($contract);

    // Email à l'admin
    $this->sendAdminNotification($contract);
}

private function sendCompanyNotification($contract)
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
        $mail->Subject = 'Votre demande de contrat a été reçue';
        $mail->Body = "
            <h2>Demande de contrat en cours d'examen</h2>
            <p>Bonjour,</p>
            <p>Nous avons bien reçu votre demande de contrat et elle est actuellement en cours d'examen par notre équipe.</p>
            <p>Vous serez notifié par email dès qu'une décision sera prise.</p>
            <p>Cordialement,<br>L'équipe Business-Care</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        Log::error("Erreur d'envoi d'email: {$mail->ErrorInfo}");
    }
}

private function sendAdminNotification($contract)
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
        $mail->addAddress(env('MAIL_FROM_ADDRESS'));

        $mail->isHTML(true);
        $mail->Subject = 'Nouvelle demande de contrat à valider';
        $mail->Body = "
            <h2>Nouvelle demande de contrat</h2>
            <p>Une nouvelle demande de contrat a été soumise.</p>
            <p><strong>Entreprise :</strong> {$contract->company->name}</p>
            <p><strong>Montant :</strong> {$contract->amount} €</p>
            <p><a href='" . url('/dashboard/gestion_admin/contracts') . "'>Voir les contrats en attente</a></p>
        ";

        $mail->send();
    } catch (Exception $e) {
        Log::error("Erreur d'envoi d'email admin: {$mail->ErrorInfo}");
    }
}

    // GET /api/contracts/{id}
    public function show($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            return response()->json(['data' => $contract]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération du contrat: ' . $e->getMessage());
            return response()->json(['message' => 'Contrat non trouvé'], 404);
        }
    }

    // PUT /api/contracts/{id}
    public function update(Request $request, $id)
    {
        try {
            $contract = Contract::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'services' => 'required|string',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'amount' => 'required|numeric',
                'payment_method' => 'required|string',
                'formule_abonnement' => 'required|in:Starter,Basic,Premium'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $contract->update($request->all());

            return response()->json([
                'message' => 'Contrat mis à jour avec succès',
                'data' => $contract
            ]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la mise à jour du contrat: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la mise à jour du contrat'], 500);
        }
    }

    // DELETE /api/contracts/{id}
    public function destroy($id)
    {
        try {
            $contract = Contract::findOrFail($id);
            $contract->delete();

            return response()->json(['message' => 'Contrat supprimé avec succès']);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la suppression du contrat: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la suppression du contrat'], 500);
        }
    }

    // GET /api/companies/{companyId}/contracts
    public function getByCompany($companyId)
    {
        try {
            $company = Company::find($companyId);

            if (!$company) {
                return response()->json(['message' => 'Entreprise non trouvée'], 404);
            }

            $contracts = Contract::where('company_id', $companyId)->get();

            return response()->json(['data' => $contracts]);
        } catch (\Exception $e) {
            Log::error('API: Erreur lors de la récupération des contrats par entreprise: ' . $e->getMessage());
            return response()->json(['message' => 'Une erreur est survenue lors de la récupération des contrats'], 500);
        }
    }

    public function edit($id)
    {
        try {
            if (session('user_type') !== 'societe') {
                return redirect()->route('login')
                    ->with('error', 'Vous devez être connecté en tant que société pour accéder à cette page.');
            }

            // Appel à l'API
            $response = $this->apiContractController->show($id);
            $data = json_decode($response->getContent(), true);

            if ($response->getStatusCode() !== 200) {
                Log::error('Erreur lors de la récupération du contrat', [
                    'status' => $response->getStatusCode(),
                    'response' => $data
                ]);
                return back()->with('error', 'Contrat non trouvé');
            }

            // Convertir en objet
            $contract = $this->arrayToObject($data['data'] ?? []);

            // Récupérer le nombre d'employés pour les calculs
            $employeeCount = \App\Models\Employee::where('company_id', session('user_id'))->count();

            return view('dashboards.client.contracts.edit', compact('contract', 'employeeCount'));
        } catch (\Exception $e) {
            Log::error('Exception lors de l\'édition d\'un contrat: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de l\'édition du contrat');
        }
    }

     public function terminate($id)
    {
        try {
            $contract = Contract::with('company')->findOrFail($id);

            // Utiliser 'pending' ou 'unpaid' au lieu de 'terminated'
            // 'pending' a du sens car ça peut indiquer "en attente de traitement de résiliation"
            $contract->payment_status = 'pending';
            $contract->save();

            // Mettre à jour le statut du compte de l'entreprise
            $company = Company::findOrFail($contract->company_id);
            $company->statut_compte = 'Non Actif';
            $company->save();

            Log::info('Contrat résilié et compte désactivé', [
                'contract_id' => $id,
                'company_id' => $contract->company_id,
                'new_account_status' => $company->statut_compte,
                'contract_status' => $contract->payment_status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contrat résilié avec succès',
                'data' => $contract
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la résiliation du contrat: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la résiliation du contrat: ' . $e->getMessage()
            ], 500);
        }
    }

}
