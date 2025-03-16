<?php

namespace App\Http\Controllers;

use PDO;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Database;

class AuthController extends Controller
{
    // Affichage du formulaire de connexion
    public function loginForm()
    {
        return view('auth.login');
    }

    // Fonction de validation des identifiants
    private function validateCredentials($email, $password, $userType)
    {
        try {
            $pdo = Database::getConnection();

            // Mapping sécurisé pour les noms de tables
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
                    // Vérification du mot de passe en clair pour l'administrateur
                    if ($password === $user['password']) {
                        return $user;
                    }
                } else {
                    // Vérification du mot de passe haché pour les autres utilisateurs
                    if (password_verify($password, $user['password'])) {
                        return $user;
                    }
                }
            }

            return null;
        } catch (\Exception $e) {
            // Log l'erreur
            \Log::error('Erreur de validation des identifiants: ' . $e->getMessage());
            return null;
        }
    }

    // Traitement de la connexion
    public function login(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'company_name' => 'required_if:user_type,societe,employe'
            ]);

            $pdo = Database::getConnection();

            // Vérifier d'abord dans la table admin
            if ($request->user_type === 'admin') {
                $stmt = $pdo->prepare("SELECT id, email, password, name, 'admin' as type FROM admin WHERE email = :email");
                $stmt->execute(['email' => $validatedData['email']]);
                $user = $stmt->fetch();
            }
            // Vérifier dans la table company
            else if ($request->user_type === 'societe') {
                $stmt = $pdo->prepare("SELECT id, email, password, name, 'societe' as type FROM company WHERE email = :email AND name = :company_name");
                $stmt->execute([
                    'email' => $validatedData['email'],
                    'company_name' => $validatedData['company_name']
                ]);
                $user = $stmt->fetch();
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
                    // Vérification du mot de passe en clair pour l'administrateur
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
                    // Vérification du mot de passe haché pour les autres utilisateurs
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

            // Authentification échouée
            return back()->withErrors(['email' => 'Identifiants invalides'])->withInput();

        } catch (\Exception $e) {
            \Log::error('Erreur de connexion', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['email' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    // Déconnexion
    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('home');
    }

    // Affichage du formulaire d'inscription
    public function registerForm()
    {
        return view('auth.register');
    }

    // Traitement de l'inscription
    public function register(Request $request)
    {
        try {
            \Log::info('Données brutes reçues', [
                'all_data' => $request->all()
            ]);

            // Validation des données
            $validatedData = $request->validate([
                'email' => 'required|email',
                'password' => 'required|min:6',
                'user_type' => 'required|in:societe,employe,prestataire',

                // Validation société
                'company_name' => 'required_if:user_type,societe',
                'address' => 'required_if:user_type,societe',
                'code_postal' => 'required_if:user_type,societe',
                'ville' => 'required_if:user_type,societe',
                'phone' => 'required_if:user_type,societe',
                'siret' => 'nullable|digits:14',  // Exactement 14 chiffres

                // Validation employé
                'first_name' => 'required_if:user_type,employe',
                'last_name' => 'required_if:user_type,employe',
                'position' => 'required_if:user_type,employe',
                'departement' => 'nullable',
                'telephone' => 'nullable',

                // Validation prestataire
                'name' => 'required_if:user_type,prestataire',
                'prenom' => 'required_if:user_type,prestataire',
                'specialite' => 'required_if:user_type,prestataire',
                'bio' => 'nullable',
                'tarif_horaire' => 'nullable|numeric|min:0'
            ], [
                'siret.digits' => 'Le numéro SIRET doit contenir exactement 14 chiffres',
            ]);

            $pdo = Database::getConnection();
            $pdo->beginTransaction();

            try {
                $hashedPassword = password_hash($request->password, PASSWORD_DEFAULT);

                switch ($request->user_type) {
                    case 'societe':
                        $stmt = $pdo->prepare("
                            INSERT INTO company (
                                name,
                                address,
                                code_postal,
                                ville,
                                pays,
                                phone,
                                email,
                                siret,
                                password,
                                creation_date,
                                formule_abonnement,
                                statut_compte,
                                date_debut_contrat
                            )
                            VALUES (
                                :name,
                                :address,
                                :code_postal,
                                :ville,
                                'France',
                                :phone,
                                :email,
                                :siret,
                                :password,
                                CURDATE(),
                                'Starter',
                                'Actif',
                                CURDATE()
                            )
                        ");

                        $params = [
                            'name' => $validatedData['company_name'],
                            'address' => $validatedData['address'],
                            'code_postal' => $validatedData['code_postal'],
                            'ville' => $validatedData['ville'],
                            'phone' => $validatedData['phone'],
                            'email' => $validatedData['email'],
                            'siret' => $validatedData['siret'] ?? null,
                            'password' => $hashedPassword
                        ];

                        break;

                    case 'employe':
                        $stmt = $pdo->prepare("
                            INSERT INTO employee (
                                company_id,
                                first_name,
                                last_name,
                                email,
                                telephone,
                                position,
                                departement,
                                date_creation_compte,
                                password,
                                preferences_langue
                            )
                            VALUES (
                                :company_id,
                                :first_name,
                                :last_name,
                                :email,
                                :telephone,
                                :position,
                                :departement,
                                CURDATE(),
                                :password,
                                'fr'
                            )
                        ");

                        // Récupérer l'ID de la société
                        $companyStmt = $pdo->prepare("SELECT id FROM company WHERE name = :company_name LIMIT 1");
                        $companyStmt->execute(['company_name' => $validatedData['company_name']]);
                        $company = $companyStmt->fetch();

                        if (!$company) {
                            throw new \Exception('Entreprise non trouvée');
                        }

                        $params = [
                            'company_id' => $company['id'],
                            'first_name' => $validatedData['first_name'],
                            'last_name' => $validatedData['last_name'],
                            'email' => $validatedData['email'],
                            'telephone' => $validatedData['telephone'] ?? null,
                            'position' => $validatedData['position'],
                            'departement' => $validatedData['departement'] ?? null,
                            'password' => $hashedPassword
                        ];

                        break;

                    case 'prestataire':
                        $stmt = $pdo->prepare("
                            INSERT INTO provider (
                                last_name,
                                first_name,
                                domains,
                                email,
                                telephone,
                                password,
                                description,
                                tarif_horaire
                            )
                            VALUES (
                                :last_name,
                                :first_name,
                                :domains,
                                :email,
                                :telephone,
                                :password,
                                :description,
                                :tarif_horaire
                            )
                        ");

                        $params = [
                            'last_name' => $validatedData['name'],
                            'first_name' => $validatedData['prenom'],
                            'domains' => $validatedData['specialite'],
                            'email' => $validatedData['email'],
                            'telephone' => $validatedData['telephone'],
                            'password' => $hashedPassword,
                            'description' => $validatedData['bio'] ?? null,
                            'tarif_horaire' => $validatedData['tarif_horaire'] ?? null
                        ];

                        break;
                }

                $success = $stmt->execute($params);

                if ($success) {
                    $pdo->commit();
                    $userId = $pdo->lastInsertId();

                    // Stocker les informations en session
                    session([
                        'user_id' => $userId,
                        'user_email' => $validatedData['email'],
                        'user_name' => $params['first_name'] . ' ' . $params['last_name'] ?? $params['name'],
                        'user_type' => $request->user_type
                    ]);

                    \Log::info('Inscription réussie, session créée', [
                        'user_id' => $userId,
                        'user_type' => $request->user_type
                    ]);

                    // Redirection selon le type d'utilisateur
                    switch ($request->user_type) {
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

                \Log::error('Échec de l\'insertion', ['success' => $success]);
                $pdo->rollBack();
                return back()->withErrors(['error' => 'Échec de l\'inscription'])->withInput();

            } catch (\PDOException $e) {
                $pdo->rollBack();
                \Log::error('Erreur PDO', [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
                throw $e;
            }
        } catch (\Exception $e) {
            \Log::error('Erreur de traitement de l\'inscription', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }
}
