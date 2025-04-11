<?php

namespace App\Http\Controllers;

use PDO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Database;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    private function validateCredentials($email, $password, $userType)
    {
        try {
            $pdo = Database::getConnection();

            // Mapping pour les noms de nos tables
            $tableMap = [
                'societe' => 'company',
                'employe' => 'employee',
                'prestataire' => 'provider',
                'admin' => 'admin'
            ];

            $table = $tableMap[$userType] ?? null;
            if (!$table) {
                return null;
            }

            // Requête pour récupérer l'utilisateur
            $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            // Vérifiez si l'utilisateur existe et si le mot de passe correspond
            if ($user) {
                if ($userType === 'admin') {
                    if ($password === $user['password']) {
                        return $user;
                    }
                } else {
                    if (password_verify($password, $user['password'])) {
                        return $user;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Erreur de validation des identifiants: ' . $e->getMessage());
            return null;
        }
    }

    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'company_name' => 'required_if:user_type,societe,employe'
            ]);

            $pdo = Database::getConnection();

            if ($request->user_type === 'admin') {
                $stmt = $pdo->prepare("SELECT id, email, password, name, 'admin' as type FROM admin WHERE email = :email");
                $stmt->execute(['email' => $validatedData['email']]);
                $user = $stmt->fetch();
            }
            else if ($request->user_type === 'societe') {
                $stmt = $pdo->prepare("
                    SELECT id, email, password, name, 'societe' as type
                    FROM company
                    WHERE email = :email AND name = :company_name
                ");
                $stmt->execute([
                    'email' => $validatedData['email'],
                    'company_name' => $validatedData['company_name']
                ]);
                $user = $stmt->fetch();

                if ($user) {
                    if (password_verify($validatedData['password'], $user['password'])) {
                        session([
                            'user_id' => $user['id'],
                            'user_email' => $user['email'],
                            'user_name' => $user['name'],
                            'user_type' => $user['type']
                        ]);

                        \Log::info('Connexion réussie pour une société', [
                            'user_id' => $user['id'],
                            'user_email' => $user['email'],
                            'company_name' => $user['name']
                        ]);

                        return redirect()->route('dashboard.client');
                    } else {
                        \Log::warning('Mot de passe incorrect pour la société', [
                            'email' => $validatedData['email'],
                            'company_name' => $validatedData['company_name']
                        ]);
                    }
                } else {
                    \Log::warning('Société non trouvée ou informations incorrectes', [
                        'email' => $validatedData['email'],
                        'company_name' => $validatedData['company_name']
                    ]);
                }
            }
            // Vérifier dans la table employee
            else if ($request->user_type === 'employe') {
                $stmt = $pdo->prepare("
                    SELECT e.id, e.email, e.password, e.first_name, e.last_name, 'employe' as type, e.company_id
                    FROM employee e
                    JOIN company c ON e.company_id = c.id
                    WHERE e.email = :email AND c.name = :company_name
                ");
                $stmt->execute([
                    'email' => $validatedData['email'],
                    'company_name' => $validatedData['company_name']
                ]);
                $user = $stmt->fetch();
            }
            // Vérifier dans la table provider
            else {
                $stmt = $pdo->prepare("SELECT id, email, password, first_name, last_name, 'prestataire' as type FROM provider WHERE email = :email");
                $stmt->execute(['email' => $validatedData['email']]);
                $user = $stmt->fetch();
            }

            if ($user) {
                if ($request->user_type === 'admin') {
                    if ($validatedData['password'] === $user['password']) {
                        session([
                            'user_id' => $user['id'],
                            'user_email' => $user['email'],
                            'user_name' => $user['name'],
                            'user_type' => $user['type'],
                            'company_id' => $user['company_id'] ?? null
                        ]);

                        \Log::info('Connexion réussie', [
                            'user_id' => $user['id'],
                            'user_type' => $user['type'],
                            'company_id' => $user['company_id'] ?? null
                        ]);

                        return redirect()->route('dashboard.admin');
                    }
                } else {
                    if (password_verify($validatedData['password'], $user['password'])) {
                        session([
                            'user_id' => $user['id'],
                            'user_email' => $user['email'],
                            'user_name' => $user['first_name'] . ' ' . $user['last_name'],
                            'user_type' => $user['type'],
                            'company_id' => $user['company_id'] ?? null
                        ]);

                        \Log::info('Connexion réussie', [
                            'user_id' => $user['id'],
                            'user_type' => $user['type'],
                            'company_id' => $user['company_id'] ?? null
                        ]);

                        switch ($user['type']) {
                            case 'societe':
                                return redirect()->route('dashboard.client');
                            case 'employe':
                                return redirect()->route('dashboard.employee');
                            case 'prestataire':
                                return redirect()->route('dashboard.provider');
                            default:
                                return redirect()->route('home');
                        }
                    }
                }
            }

            return back()->withErrors(['email' => 'Identifiants invalides'])->withInput();

        } catch (\Exception $e) {
            \Log::error('Erreur de connexion', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['email' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('home');
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
       try {

           \Log::info('Données d\'inscription reçues', [
               'all_data' => $request->all()
           ]);

           // Validation des données
           $validatedData = $request->validate([
               'email' => 'required|email',
               'password' => 'required|min:6',
               'user_type' => 'required|in:societe,employe,prestataire',

               // Validation pour société
               'company_name' => 'required_if:user_type,societe',
               'address' => 'required_if:user_type,societe',
               'code_postal' => 'required_if:user_type,societe',
               'ville' => 'required_if:user_type,societe',
               'phone' => 'required_if:user_type,societe',
               'siret' => 'nullable|digits:14',

               // Validation pour employé
               'first_name' => 'required_if:user_type,employe',
               'last_name' => 'required_if:user_type,employe',
               'position' => 'required_if:user_type,employe',
               'departement' => 'nullable',
               'telephone' => 'nullable',

               // Validation pour prestataire
               'name' => 'required_if:user_type,prestataire',
               'prenom' => 'required_if:user_type,prestataire',
               'specialite' => 'required_if:user_type,prestataire',
               'bio' => 'nullable',
               'tarif_horaire' => 'nullable|numeric|min:0'
           ], [
               'siret.digits' => 'Le numéro SIRET doit contenir exactement 14 chiffres',
           ]);

           // Adaptation des champs pour les prestataires
           if ($request->user_type === 'prestataire') {
               $request->merge([
                   'first_name' => $request->prenom,
                   'last_name' => $request->name,
                   'domains' => $request->specialite,
               ]);
           }

           // Utilisation du contrôleur PendingRegistrationController pour enregistrer la demande
           $pendingController = new \App\Http\Controllers\API\PendingRegistrationController();
           $response = $pendingController->register($request);
           $responseData = json_decode($response->getContent(), true);

           if ($response->getStatusCode() === 201) {
               // Succès : redirection vers la page d'accueil avec message
               return redirect()->route('home')->with('success',
                   'Votre demande d\'inscription a été envoyée avec succès. ' .
                   'Un administrateur l\'examinera prochainement et vous recevrez ' .
                   'une notification par email lorsqu\'elle sera traitée.');
           }

           // Échec : retour au formulaire avec erreurs
           \Log::error('Échec de l\'inscription en attente', $responseData);
           return back()->withErrors(['error' => $responseData['message'] ?? 'Échec de l\'inscription'])
                        ->withInput();

       } catch (\Exception $e) {
           \Log::error('Erreur d\'inscription', [
               'message' => $e->getMessage(),
               'trace' => $e->getTraceAsString()
           ]);
           return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])
                       ->withInput();
       }
    }

    public function registerPending(Request $request)
    {
        // Rediriger vers la méthode register
        return $this->register($request);
    }
}
