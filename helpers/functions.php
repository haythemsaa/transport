<?php
/**
 * Fonctions utilitaires globales
 * Teleroute Marketplace
 */

/**
 * Sécuriser les sorties HTML
 */
function h($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Rediriger vers une URL
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Vérifier si l'utilisateur est connecté
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Obtenir l'utilisateur connecté
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    return $_SESSION['user_data'] ?? null;
}

/**
 * Vérifier le type d'utilisateur
 */
function isTransporter() {
    $user = getCurrentUser();
    return $user && in_array($user['user_type'], ['transporter', 'both']);
}

function isShipper() {
    $user = getCurrentUser();
    return $user && in_array($user['user_type'], ['shipper', 'both']);
}

/**
 * Générer un token aléatoire
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Formater une date en français
 */
function formatDate($date, $format = 'd/m/Y') {
    if (empty($date)) return '';
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formater une date et heure en français
 */
function formatDateTime($datetime, $format = 'd/m/Y H:i') {
    if (empty($datetime)) return '';
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    return date($format, $timestamp);
}

/**
 * Temps écoulé depuis une date (ex: "il y a 2 heures")
 */
function timeAgo($datetime) {
    $timestamp = is_numeric($datetime) ? $datetime : strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return "à l'instant";
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return "il y a " . $mins . " minute" . ($mins > 1 ? 's' : '');
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "il y a " . $hours . " heure" . ($hours > 1 ? 's' : '');
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return "il y a " . $days . " jour" . ($days > 1 ? 's' : '');
    } else {
        return formatDate($datetime);
    }
}

/**
 * Formater un prix
 */
function formatPrice($price, $currency = '€') {
    if (empty($price)) return 'N/A';
    return number_format($price, 2, ',', ' ') . ' ' . $currency;
}

/**
 * Formater un poids
 */
function formatWeight($weight) {
    if (empty($weight)) return 'N/A';
    return number_format($weight, 2, ',', ' ') . ' t';
}

/**
 * Formater un volume
 */
function formatVolume($volume) {
    if (empty($volume)) return 'N/A';
    return number_format($volume, 2, ',', ' ') . ' m³';
}

/**
 * Calculer la distance entre deux points (formule de Haversine)
 */
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Rayon de la Terre en km

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));

    return $earthRadius * $c;
}

/**
 * Générer une URL de pagination
 */
function paginationUrl($page) {
    $params = $_GET;
    $params['page'] = $page;
    return '?' . http_build_query($params);
}

/**
 * Sécuriser un upload de fichier
 */
function uploadFile($file, $allowedTypes = ALLOWED_EXTENSIONS, $uploadDir = UPLOAD_PATH) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Erreur lors de l\'upload du fichier'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'Le fichier est trop volumineux'];
    }

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedTypes)) {
        return ['success' => false, 'error' => 'Type de fichier non autorisé'];
    }

    $filename = uniqid() . '_' . time() . '.' . $extension;
    $destination = $uploadDir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $filename, 'path' => $destination];
    }

    return ['success' => false, 'error' => 'Erreur lors de la sauvegarde du fichier'];
}

/**
 * Envoyer une notification flash
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Récupérer et supprimer une notification flash
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Obtenir le label d'un type de véhicule
 */
function getVehicleTypeLabel($type) {
    $labels = [
        'van' => 'Fourgon',
        'truck' => 'Camion',
        'semi_trailer' => 'Semi-remorque',
        'container_truck' => 'Porte-conteneur',
        'refrigerated_truck' => 'Camion frigorifique',
        'flatbed' => 'Plateau',
        'tanker' => 'Citerne'
    ];
    return $labels[$type] ?? $type;
}

/**
 * Obtenir le label d'un type de cargo
 */
function getCargoTypeLabel($type) {
    $labels = [
        'pallets' => 'Palettes',
        'containers' => 'Conteneurs',
        'bulk' => 'Vrac',
        'vehicles' => 'Véhicules',
        'refrigerated' => 'Réfrigéré',
        'dangerous' => 'Matières dangereuses',
        'other' => 'Autre'
    ];
    return $labels[$type] ?? $type;
}

/**
 * Obtenir le badge de statut
 */
function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="badge bg-success">Actif</span>',
        'pending' => '<span class="badge bg-warning">En attente</span>',
        'assigned' => '<span class="badge bg-info">Assigné</span>',
        'in_transit' => '<span class="badge bg-primary">En transit</span>',
        'completed' => '<span class="badge bg-success">Terminé</span>',
        'delivered' => '<span class="badge bg-success">Livré</span>',
        'cancelled' => '<span class="badge bg-danger">Annulé</span>',
        'suspended' => '<span class="badge bg-danger">Suspendu</span>',
        'available' => '<span class="badge bg-success">Disponible</span>',
        'unavailable' => '<span class="badge bg-secondary">Non disponible</span>',
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . h($status) . '</span>';
}

/**
 * Logger une erreur
 */
function logError($message, $context = []) {
    $log = date('[Y-m-d H:i:s] ') . $message;
    if (!empty($context)) {
        $log .= ' | Context: ' . json_encode($context);
    }
    $log .= PHP_EOL;
    file_put_contents(LOGS_PATH . '/error.log', $log, FILE_APPEND);
}

/**
 * Logger une activité
 */
function logActivity($message, $context = []) {
    $log = date('[Y-m-d H:i:s] ') . $message;
    if (!empty($context)) {
        $log .= ' | ' . json_encode($context);
    }
    $log .= PHP_EOL;
    file_put_contents(LOGS_PATH . '/activity.log', $log, FILE_APPEND);
}

/**
 * Valider un email
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Générer un mot de passe aléatoire
 */
function generatePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle(str_repeat($chars, $length)), 0, $length);
}

/**
 * Vérifier la force d'un mot de passe
 */
function isStrongPassword($password) {
    return strlen($password) >= 8 &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[0-9]/', $password);
}

/**
 * Nettoyer une chaîne pour URL
 */
function slugify($string) {
    $string = transliterator_transliterate('Any-Latin; Latin-ASCII', $string);
    $string = preg_replace('/[^a-z0-9]+/i', '-', strtolower($string));
    return trim($string, '-');
}

/**
 * Obtenir l'adresse IP du client
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Obtenir le User Agent
 */
function getUserAgent() {
    return $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
}

/**
 * Envoyer une réponse JSON
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

/**
 * Vérifier un token CSRF
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Générer un token CSRF
 */
function getCsrfToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = generateToken();
    }
    return $_SESSION['csrf_token'];
}
