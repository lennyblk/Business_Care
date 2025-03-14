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
            if ($user && password_verify($password, $user['password'])) {
                return $user;
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
        // Validation des données
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'user_type' => 'required|in:societe,employe,prestataire,admin',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');
        $userType = $request->input('user_type');

        try {
            $user = $this->validateCredentials($email, $password, $userType);

            if ($user) {
                // Stockez les informations utilisateur en session
                session([
                    'user_id' => $user['id'],
                    'user_email' => $user['email'],
                    'user_type' => $userType,
                    'is_logged_in' => true
                ]);

                // Redirection selon le type d'utilisateur
                switch ($userType) {
                    case 'societe':
                        return redirect()->route('dashboard.client');
                    case 'employe':
                        return redirect()->route('dashboard.employee');
                    case 'prestataire':
                        return redirect()->route('dashboard.provider');
                    case 'admin':
                        return redirect()->route('dashboard.admin');
                }
            }

            // Authentification échouée
            return back()->withErrors(['email' => 'Identifiants invalides'])->withInput();

        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Une erreur est survenue: ' . $e->getMessage()])->withInput();
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
        // Validation des données améliorée
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'user_type' => 'required|in:societe,employe,prestataire',
            // Pour les sociétés
            'company_name' => 'required_if:user_type,societe',
            'address' => 'required_if:user_type,societe',
            'phone' => 'required_if:user_type,societe',
            // Pour les employés
            'first_name' => 'required_if:user_type,employe',
            'last_name' => 'required_if:user_type,employe',
            'position' => 'required_if:user_type,employe',
            'company_id' => 'required_if:user_type,employe|numeric',
            // Pour les prestataires
            'name' => 'required_if:user_type,prestataire',
            'description' => 'required_if:user_type,prestataire',
            'domains' => 'required_if:user_type,prestataire',
        ]);

        try {
            $pdo = Database::getConnection();

            // Vérifiez si l'email existe déjà
            $checkEmail = $pdo->prepare("SELECT email FROM employee WHERE email = :email
                                        UNION SELECT email FROM company WHERE email = :email
                                        UNION SELECT email FROM provider WHERE email = :email
                                        UNION SELECT email FROM admin WHERE email = :email");
            $checkEmail->execute(['email' => $request->email]);

            if ($checkEmail->rowCount() > 0) {
                return back()->withErrors(['email' => 'Cet email est déjà utilisé'])->withInput();
            }

            // Hashage du mot de passe
            $hashedPassword = password_hash($request->password, PASSWORD_DEFAULT);

            // Insertion selon le type d'utilisateur
            switch ($request->user_type) {
                case 'societe':
                    $stmt = $pdo->prepare("INSERT INTO company (name, address, phone, email, password, plan, creation_date)
                                        VALUES (:name, :address, :phone, :email, :password, 'Starter', CURDATE())");
                    $stmt->execute([
                        'name' => $request->company_name,
                        'address' => $request->address,
                        'phone' => $request->phone,
                        'email' => $request->email,
                        'password' => $hashedPassword
                    ]);
                    break;

                case 'employe':
                    $stmt = $pdo->prepare("INSERT INTO employee (company_id, first_name, last_name, email, password, position, hire_date, subscription)
                                        VALUES (:company_id, :first_name, :last_name, :email, :password, :position, CURDATE(), 'Starter')");
                    $stmt->execute([
                        'company_id' => $request->company_id,
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'password' => $hashedPassword,
                        'position' => $request->position
                    ]);
                    break;

                case 'prestataire':
                    $stmt = $pdo->prepare("INSERT INTO provider (name, email, password, description, rating, domains)
                                        VALUES (:name, :email, :password, :description, 0, :domains)");
                    $stmt->execute([
                        'name' => $request->name,
                        'email' => $request->email,
                        'password' => $hashedPassword,
                        'description' => $request->description,
                        'domains' => $request->domains
                    ]);
                    break;
            }

            // Vérifiez si l'insertion a réussi
            if ($stmt->rowCount() > 0) {
                // Stockez les informations utilisateur en session
                session([
                    'user_email' => $request->email,
                    'user_type' => $request->user_type,
                    'is_logged_in' => true
                ]);

                // Redirection selon le type d'utilisateur
                switch ($request->user_type) {
                    case 'societe':
                        return redirect()->route('dashboard.client');
                    case 'employe':
                        return redirect()->route('dashboard.employee');
                    case 'prestataire':
                        return redirect()->route('dashboard.provider');
                }
            } else {
                return back()->withErrors(['error' => 'Une erreur est survenue lors de l\'inscription. Veuillez réessayer.'])->withInput();
            }

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Une erreur est survenue lors de l\'inscription: ' . $e->getMessage()])->withInput();
        }
    }
}
