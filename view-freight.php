<?php
require_once 'config/config.php';
require_once 'config/database.php';

$offerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$offerId) {
    setFlash('danger', 'Offre introuvable');
    redirect('/search-freight.php');
}

$freightModel = new FreightOffer();
$offer = $freightModel->getById($offerId);

if (!$offer) {
    setFlash('danger', 'Offre introuvable');
    redirect('/search-freight.php');
}

$pageTitle = $offer['title'];

// Vérifier si l'utilisateur possède cette offre
$isOwner = isLoggedIn() && $_SESSION['user_id'] == $offer['user_id'];

include 'includes/header.php';
?>

<div class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/search-freight.php">Recherche de fret</a></li>
            <li class="breadcrumb-item active"><?= h($offer['title']) ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Colonne principale -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h2 class="mb-2"><?= h($offer['title']) ?></h2>
                            <?= getStatusBadge($offer['status']) ?>
                        </div>
                        <?php if ($isOwner): ?>
                            <div class="btn-group">
                                <a href="/edit-freight.php?id=<?= $offer['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Modifier
                                </a>
                                <button onclick="deleteOffer()" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash"></i> Supprimer
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Informations principales -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-success mb-2">
                                        <i class="bi bi-geo-alt"></i> Point de chargement
                                    </h6>
                                    <p class="mb-1"><strong><?= h($offer['loading_city']) ?>, <?= h($offer['loading_postal_code']) ?></strong></p>
                                    <p class="mb-1 text-muted small"><?= h($offer['loading_country']) ?></p>
                                    <?php if ($offer['loading_address']): ?>
                                        <p class="mb-1 small"><?= h($offer['loading_address']) ?></p>
                                    <?php endif; ?>
                                    <p class="mb-0 mt-2">
                                        <i class="bi bi-calendar"></i>
                                        <strong><?= formatDate($offer['loading_date']) ?></strong>
                                        <?php if ($offer['loading_time_start']): ?>
                                            <br><small class="text-muted">
                                                <?= substr($offer['loading_time_start'], 0, 5) ?>
                                                <?= $offer['loading_time_end'] ? ' - ' . substr($offer['loading_time_end'], 0, 5) : '' ?>
                                            </small>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="text-danger mb-2">
                                        <i class="bi bi-geo-alt-fill"></i> Point de livraison
                                    </h6>
                                    <p class="mb-1"><strong><?= h($offer['delivery_city']) ?>, <?= h($offer['delivery_postal_code']) ?></strong></p>
                                    <p class="mb-1 text-muted small"><?= h($offer['delivery_country']) ?></p>
                                    <?php if ($offer['delivery_address']): ?>
                                        <p class="mb-1 small"><?= h($offer['delivery_address']) ?></p>
                                    <?php endif; ?>
                                    <p class="mb-0 mt-2">
                                        <i class="bi bi-calendar"></i>
                                        <strong><?= formatDate($offer['delivery_date']) ?></strong>
                                        <?php if ($offer['delivery_time_start']): ?>
                                            <br><small class="text-muted">
                                                <?= substr($offer['delivery_time_start'], 0, 5) ?>
                                                <?= $offer['delivery_time_end'] ? ' - ' . substr($offer['delivery_time_end'], 0, 5) : '' ?>
                                            </small>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Détails du fret -->
                    <h5 class="mb-3"><i class="bi bi-box-seam"></i> Détails du fret</h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-box fs-4 text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Type de cargo</small>
                                    <strong><?= getCargoTypeLabel($offer['cargo_type']) ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-boxes fs-4 text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Poids</small>
                                    <strong><?= formatWeight($offer['weight']) ?></strong>
                                </div>
                            </div>
                        </div>
                        <?php if ($offer['volume']): ?>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-box fs-4 text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Volume</small>
                                    <strong><?= formatVolume($offer['volume']) ?></strong>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($offer['quantity']): ?>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-stack fs-4 text-primary me-2"></i>
                                <div>
                                    <small class="text-muted d-block">Quantité</small>
                                    <strong><?= $offer['quantity'] ?> unité(s)</strong>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($offer['cargo_description']): ?>
                        <div class="mb-4">
                            <h6>Description du cargo</h6>
                            <p><?= nl2br(h($offer['cargo_description'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Véhicule requis -->
                    <h5 class="mb-3"><i class="bi bi-truck"></i> Véhicule requis</h5>
                    <div class="mb-4">
                        <span class="badge bg-primary fs-6">
                            <?= getVehicleTypeLabel($offer['vehicle_type']) ?>
                        </span>
                    </div>

                    <?php if ($offer['special_requirements']): ?>
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Exigences particulières</h6>
                            <p class="mb-0"><?= nl2br(h($offer['special_requirements'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($offer['description']): ?>
                        <hr>
                        <h5 class="mb-3">Description</h5>
                        <p><?= nl2br(h($offer['description'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Carte (si Google Maps configuré) -->
            <?php if (defined('GOOGLE_MAPS_API_KEY') && GOOGLE_MAPS_API_KEY !== 'VOTRE_CLE_API_GOOGLE_MAPS'): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-map"></i> Itinéraire</h5>
                </div>
                <div class="card-body p-0">
                    <div id="map" class="map-container"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Prix et contact -->
            <div class="card shadow-sm sticky-top mb-4" style="top: 20px;">
                <div class="card-body">
                    <?php if ($offer['price']): ?>
                        <div class="text-center mb-3">
                            <h3 class="text-primary mb-1"><?= formatPrice($offer['price']) ?></h3>
                            <?php if ($offer['price_negotiable']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Prix négociable
                                </span>
                            <?php endif; ?>
                            <?php if ($offer['payment_terms']): ?>
                                <p class="text-muted small mt-2">
                                    <?= h($offer['payment_terms']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <hr>
                    <?php endif; ?>

                    <?php if (!$isOwner && isLoggedIn()): ?>
                        <a href="/messages.php?offer_type=freight&offer_id=<?= $offer['id'] ?>&user_id=<?= $offer['user_id'] ?>"
                           class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-chat-dots"></i> Contacter le chargeur
                        </a>
                        <button class="btn btn-outline-primary w-100" onclick="saveOffer()">
                            <i class="bi bi-bookmark"></i> Sauvegarder l'offre
                        </button>
                    <?php elseif (!isLoggedIn()): ?>
                        <a href="/login.php" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Connectez-vous pour contacter
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informations chargeur -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-building"></i> Chargeur</h6>
                </div>
                <div class="card-body">
                    <h5 class="mb-2"><?= h($offer['company_name']) ?></h5>

                    <?php if ($offer['rating'] > 0): ?>
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= round($offer['rating'])): ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php else: ?>
                                            <i class="bi bi-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <strong><?= number_format($offer['rating'], 1) ?></strong>
                            </div>
                            <small class="text-muted"><?= $offer['total_ratings'] ?> évaluation(s)</small>
                        </div>
                    <?php endif; ?>

                    <?php if (isLoggedIn()): ?>
                        <div class="mb-2">
                            <i class="bi bi-telephone"></i>
                            <a href="tel:<?= h($offer['phone']) ?>"><?= h($offer['phone']) ?></a>
                        </div>
                        <div class="mb-2">
                            <i class="bi bi-envelope"></i>
                            <a href="mailto:<?= h($offer['email']) ?>"><?= h($offer['email']) ?></a>
                        </div>
                    <?php endif; ?>

                    <a href="/directory.php?user=<?= $offer['user_id'] ?>" class="btn btn-outline-primary btn-sm w-100 mt-3">
                        <i class="bi bi-eye"></i> Voir le profil
                    </a>
                </div>
            </div>

            <!-- Statistiques -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-graph-up"></i> Statistiques</h6>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Vues</span>
                        <strong><?= number_format($offer['views']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Publiée</span>
                        <strong><?= timeAgo($offer['created_at']) ?></strong>
                    </div>
                    <?php if ($offer['expires_at']): ?>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Expire</span>
                            <strong><?= formatDate($offer['expires_at']) ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function deleteOffer() {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette offre ?')) {
        showLoading();
        window.location.href = '/delete-freight.php?id=<?= $offer['id'] ?>';
    }
}

function saveOffer() {
    alert('Offre sauvegardée dans vos favoris !');
    // TODO: Implémenter la sauvegarde via AJAX
}
</script>

<?php include 'includes/footer.php'; ?>
