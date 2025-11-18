<?php
require_once 'config/config.php';
$pageTitle = 'Page introuvable - Erreur 404';
http_response_code(404);
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 text-center">
            <div class="mb-4">
                <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 8rem;"></i>
            </div>
            <h1 class="display-1 fw-bold">404</h1>
            <h2 class="mb-4">Page introuvable</h2>
            <p class="lead text-muted mb-4">
                Désolé, la page que vous recherchez n'existe pas ou a été déplacée.
            </p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="/" class="btn btn-primary">
                    <i class="bi bi-house"></i> Retour à l'accueil
                </a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Page précédente
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
