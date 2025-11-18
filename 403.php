<?php
require_once 'config/config.php';
$pageTitle = 'Accès refusé - Erreur 403';
http_response_code(403);
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="mb-4">
                <i class="bi bi-shield-x text-danger" style="font-size: 8rem;"></i>
            </div>
            <h1 class="display-1 fw-bold">403</h1>
            <h2 class="mb-4">Accès refusé</h2>
            <p class="lead text-muted mb-4">
                Vous n'avez pas les permissions nécessaires pour accéder à cette page.
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="/" class="btn btn-primary">
                    <i class="bi bi-house"></i> Retour à l'accueil
                </a>
                <a href="/login.php" class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Se connecter
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
