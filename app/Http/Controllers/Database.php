<?php

namespace App\Http\Controllers;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    //ok
    public static function getConnection()
    {
        if (self::$instance === null) {
            try {
                $host = env('DB_HOST', '127.0.0.1');
                $port = env('DB_PORT', '3308');
                $database = env('DB_DATABASE', 'businesscare');
                $username = env('DB_USERNAME', 'root');
                $password = env('DB_PASSWORD', 'root');

                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                self::$instance = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                throw new PDOException("Erreur de connexion à la base de données : " . $e->getMessage() .
                    "\nVérifiez vos paramètres de connexion dans le fichier .env" .
                    "\nHost: $host, Port: $port, Database: $database");
            }
        }

        return self::$instance;
    }
}

// Ajouter les modèles nécessaires
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', 'address', 'code_postal', 'ville', 'pays', 'phone', 'creation_date', 'email', 'password', 'siret',
        'formule_abonnement', 'statut_compte', 'date_debut_contrat', 'date_fin_contrat', 'mode_paiement_prefere', 'employee_count'
    ];
}

class Contract extends Model
{
    protected $fillable = [
        'company_id', 'start_date', 'end_date', 'services', 'amount', 'payment_method'
    ];
}

class Invoice extends Model
{
    protected $fillable = [
        'company_id', 'contract_id', 'issue_date', 'due_date', 'total_amount', 'payment_status', 'pdf_path', 'details'
    ];
}

class Quote extends Model
{
    protected $fillable = [
        'company_id', 'creation_date', 'expiration_date', 'total_amount', 'status', 'services_details'
    ];
}
