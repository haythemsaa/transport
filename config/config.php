<?php
/**
 * Configuration principale de l'application
 * Teleroute Marketplace
 */

// Configuration de session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Mettre à 1 en production avec HTTPS
session_start();

// Timezone
date_default_timezone_set('Europe/Paris');

// Configuration de l'application
define('APP_NAME', 'Teleroute Marketplace');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/transport');
define('ASSETS_URL', BASE_URL . '/assets');

// Chemins
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Créer les dossiers s'ils n'existent pas
if (!is_dir(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}
if (!is_dir(LOGS_PATH)) {
    mkdir(LOGS_PATH, 0755, true);
}

// Configuration des uploads
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Configuration email (à configurer selon vos besoins)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'votre-email@gmail.com');
define('SMTP_PASSWORD', 'votre-mot-de-passe');
define('SMTP_FROM_EMAIL', 'noreply@teleroute-marketplace.com');
define('SMTP_FROM_NAME', APP_NAME);

// Configuration de la pagination
define('ITEMS_PER_PAGE', 20);

// Configuration des cartes (Google Maps API)
define('GOOGLE_MAPS_API_KEY', 'VOTRE_CLE_API_GOOGLE_MAPS');

// Mode debug
define('DEBUG_MODE', true);

// Gestion des erreurs
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', LOGS_PATH . '/error.log');
}

// Autoloader simple
spl_autoload_register(function ($class) {
    $paths = [
        ROOT_PATH . '/models/',
        ROOT_PATH . '/controllers/',
        ROOT_PATH . '/helpers/',
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Fonctions utilitaires globales
require_once ROOT_PATH . '/helpers/functions.php';
