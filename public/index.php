<?php
/**
 * Point d'entree unique de l'application (Front Controller).
 * Toutes les requetes passent par ce fichier : index.php?c=...&a=...
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Csrf.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/App.php';

$app = new App();
$app->run();
