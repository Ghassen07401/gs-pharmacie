<?php
/**
 * Configuration generale de l'application
 */

// --- Parametres de connexion a la base de donnees ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'pharmacie_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// --- Parametres generaux de l'application ---
define('APP_NAME', 'Systeme de Gestion de Pharmacie');
define('BASE_URL', '/'); // adapter selon le sous-dossier de deploiement, ex: '/pharmacie-app/public/'
define('UPLOAD_DIR', __DIR__ . '/../public/assets/uploads/');

// --- Session ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Affichage des erreurs en developpement ---
error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('Africa/Tunis');
