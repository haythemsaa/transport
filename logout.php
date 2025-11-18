<?php
require_once 'config/config.php';

// Logger l'activité avant la déconnexion
if (isLoggedIn()) {
    logActivity("Déconnexion", ['user_id' => $_SESSION['user_id'], 'ip' => getClientIP()]);
}

// Détruire la session
session_unset();
session_destroy();

// Rediriger vers la page d'accueil
setFlash('success', 'Vous avez été déconnecté avec succès');
redirect('/');
