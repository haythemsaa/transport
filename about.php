<?php
require_once 'config/config.php';
$pageTitle = 'À propos';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="mb-4">À propos de <?= APP_NAME ?></h1>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">Notre Mission</h3>
                    <p class="lead">
                        <?= APP_NAME ?> est la plateforme leader de mise en relation entre transporteurs et chargeurs en Europe.
                        Notre mission est de faciliter le transport routier en connectant efficacement l'offre et la demande.
                    </p>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-truck text-primary" style="font-size: 3rem;"></i>
                            <h4 class="mt-3">85,000+</h4>
                            <p class="text-muted">Professionnels inscrits</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-box-seam text-success" style="font-size: 3rem;"></i>
                            <h4 class="mt-3">350,000+</h4>
                            <p class="text-muted">Offres de fret par jour</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <i class="bi bi-geo-alt text-info" style="font-size: 3rem;"></i>
                            <h4 class="mt-3">25+</h4>
                            <p class="text-muted">Pays couverts</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">Nos Valeurs</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h5><i class="bi bi-shield-check text-success"></i> Confiance</h5>
                            <p>Tous nos membres sont vérifiés pour garantir des transactions sécurisées.</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h5><i class="bi bi-lightning text-warning"></i> Rapidité</h5>
                            <p>Accédez instantanément à des milliers d'offres en temps réel.</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h5><i class="bi bi-people text-primary"></i> Communauté</h5>
                            <p>Rejoignez une communauté de professionnels engagés.</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h5><i class="bi bi-graph-up text-info"></i> Innovation</h5>
                            <p>Des outils technologiques pour optimiser votre activité.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="mb-3">Nos Services</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Bourse de fret en temps réel</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Recherche avancée de véhicules</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Messagerie instantanée</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Système de notation et évaluations</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Géolocalisation et cartographie</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Gestion des transactions</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Support client dédié</li>
                    </ul>
                </div>
            </div>

            <div class="text-center mt-5">
                <h3 class="mb-4">Rejoignez-nous dès aujourd'hui !</h3>
                <a href="/register.php" class="btn btn-primary btn-lg">
                    <i class="bi bi-person-plus"></i> Créer un compte gratuitement
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
