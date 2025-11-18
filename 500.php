<?php
require_once 'config/config.php';
$pageTitle = 'Erreur serveur - Erreur 500';
http_response_code(500);
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="mb-4">
                <i class="bi bi-server text-danger" style="font-size: 8rem;"></i>
            </div>
            <h1 class="display-1 fw-bold">500</h1>
            <h2 class="mb-4">Erreur serveur</h2>
            <p class="lead text-muted mb-4">
                Une erreur interne s'est produite. Nos équipes ont été notifiées et travaillent à résoudre le problème.
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="/" class="btn btn-primary">
                    <i class="bi bi-house"></i> Retour à l'accueil
                </a>
                <button onclick="location.reload()" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-clockwise"></i> Réessayer
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
