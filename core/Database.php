<?php
/**
 * Classe Database
 * Fournit une instance unique (singleton) de connexion PDO.
 * Contrainte du projet : uniquement PDO, pas de MySQLi.
 */
class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
        // constructeur prive : empeche l'instanciation directe
    }

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                die('Erreur de connexion a la base de donnees : ' . $e->getMessage());
            }
        }

        return self::$instance;
    }
}
