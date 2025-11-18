<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Veuillez vous connecter');
    redirect('/login.php');
}

$offerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = $_SESSION['user_id'];

if (!$offerId) {
    setFlash('danger', 'Offre introuvable');
    redirect('/my-offers.php?type=vehicle');
}

$vehicleModel = new VehicleOffer();
$result = $vehicleModel->delete($offerId, $userId);

if ($result['success']) {
    setFlash('success', 'Offre supprimée avec succès');
} else {
    setFlash('danger', $result['error']);
}

redirect('/my-offers.php?type=vehicle');
