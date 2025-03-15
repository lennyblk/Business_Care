<?php

namespace App\Http\Controllers;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;

    public static function getConnection()
    {
        if (self::$instance === null) {
            try {
                $host = env('DB_HOST', '127.0.0.1');
                $port = env('DB_PORT', '3306');
                $database = env('DB_DATABASE', 'businesscare');
                $username = env('DB_USERNAME', 'root');
                $password = env('DB_PASSWORD', '');

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
