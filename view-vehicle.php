<?php
require_once 'config/config.php';
require_once 'config/database.php';

$offerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$offerId) {
    setFlash('danger', 'Offre introuvable');
    redirect('/search-vehicles.php');
}

$vehicleModel = new VehicleOffer();
$offer = $vehicleModel->getById($offerId);

if (!$offer) {
    setFlash('danger', 'Offre introuvable');
    redirect('/search-vehicles.php');
}

$pageTitle = $offer['title'];
$isOwner = isLoggedIn() && $_SESSION['user_id'] == $offer['user_id'];

include 'includes/header.php';
?>

<div class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Accueil</a></li>
            <li class="breadcrumb-item"><a href="/search-vehicles.php">Recherche de véhicules</a></li>
            <li class="breadcrumb-item active"><?= h($offer['title']) ?></li>
        </ol>
    </nav>

    <div class="row">
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
                                <a href="/edit-vehicle.php?id=<?= $offer['id'] ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Modifier
                                </a>
                                <button onclick="deleteOffer()" class="btn btn-outline-danger btn-sm">
                                    <i class="bi bi-trash"></i> Supprimer
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Informations véhicule -->
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h5 class="mb-3"><i class="bi bi-truck"></i> Informations du véhicule</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <strong>Type:</strong><br>
                                    <span class="badge bg-primary fs-6"><?= getVehicleTypeLabel($offer['vehicle_type']) ?></span>
                                </div>
                                <?php if ($offer['vehicle_brand']): ?>
                                <div class="col-md-6">
                                    <strong>Marque/Modèle:</strong><br>
                                    <?= h($offer['vehicle_brand']) ?>
                                    <?= $offer['vehicle_model'] ? ' - ' . h($offer['vehicle_model']) : '' ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Itinéraire -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="text-success mb-2">
                                        <i class="bi bi-geo-alt"></i> Point de départ
                                    </h6>
                                    <p class="mb-1"><strong><?= h($offer['departure_city']) ?>, <?= h($offer['departure_postal_code']) ?></strong></p>
                                    <p class="mb-1 text-muted small"><?= h($offer['departure_country']) ?></p>
                                    <?php if ($offer['departure_address']): ?>
                                        <p class="mb-1 small"><?= h($offer['departure_address']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="text-danger mb-2">
                                        <i class="bi bi-geo-alt-fill"></i> Destination
                                    </h6>
                                    <?php if ($offer['destination_city']): ?>
                                        <p class="mb-1"><strong><?= h($offer['destination_city']) ?>, <?= h($offer['destination_postal_code']) ?></strong></p>
                                        <p class="mb-1 text-muted small"><?= h($offer['destination_country']) ?></p>
                                        <?php if ($offer['destination_address']): ?>
                                            <p class="mb-1 small"><?= h($offer['destination_address']) ?></p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="text-muted">
                                            <i class="bi bi-arrow-right-circle"></i> Destination flexible
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Disponibilité -->
                    <div class="mb-4">
                        <h5 class="mb-3"><i class="bi bi-calendar"></i> Disponibilité</h5>
                        <p>
                            <strong>Du:</strong> <?= formatDate($offer['available_from']) ?>
                            <?php if ($offer['available_until']): ?>
                                <br><strong>Au:</strong> <?= formatDate($offer['available_until']) ?>
                            <?php endif; ?>
                        </p>
                    </div>

                    <!-- Capacités -->
                    <h5 class="mb-3"><i class="bi bi-boxes"></i> Capacités</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="bi bi-boxes fs-2 text-primary d-block mb-2"></i>
                                <strong><?= formatWeight($offer['max_weight']) ?></strong>
                                <br><small class="text-muted">Poids max</small>
                            </div>
                        </div>
                        <?php if ($offer['max_volume']): ?>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="bi bi-box fs-2 text-primary d-block mb-2"></i>
                                <strong><?= formatVolume($offer['max_volume']) ?></strong>
                                <br><small class="text-muted">Volume max</small>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php if ($offer['max_pallets']): ?>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="bi bi-stack fs-2 text-primary d-block mb-2"></i>
                                <strong><?= $offer['max_pallets'] ?></strong>
                                <br><small class="text-muted">Palettes max</small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Équipements -->
                    <h5 class="mb-3"><i class="bi bi-gear"></i> Équipements</h5>
                    <div class="row g-2 mb-4">
                        <div class="col-md-6">
                            <?= $offer['has_gps'] ? '<span class="badge bg-success mb-2"><i class="bi bi-check-circle"></i> GPS installé</span>' : '<span class="badge bg-secondary mb-2"><i class="bi bi-x-circle"></i> Pas de GPS</span>' ?>
                        </div>
                        <div class="col-md-6">
                            <?= $offer['has_refrigeration'] ? '<span class="badge bg-info mb-2"><i class="bi bi-check-circle"></i> Réfrigération</span>' : '<span class="badge bg-secondary mb-2"><i class="bi bi-x-circle"></i> Pas de réfrigération</span>' ?>
                        </div>
                        <div class="col-md-6">
                            <?= $offer['has_tail_lift'] ? '<span class="badge bg-success mb-2"><i class="bi bi-check-circle"></i> Hayon élévateur</span>' : '<span class="badge bg-secondary mb-2"><i class="bi bi-x-circle"></i> Pas de hayon</span>' ?>
                        </div>
                        <div class="col-md-6">
                            <?= $offer['has_adr'] ? '<span class="badge bg-warning mb-2"><i class="bi bi-check-circle"></i> ADR (mat. dangereuses)</span>' : '<span class="badge bg-secondary mb-2"><i class="bi bi-x-circle"></i> Pas d\'ADR</span>' ?>
                        </div>
                    </div>

                    <?php if ($offer['equipment_details']): ?>
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Détails supplémentaires</h6>
                            <p class="mb-0"><?= nl2br(h($offer['equipment_details'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($offer['description']): ?>
                        <hr>
                        <h5 class="mb-3">Description</h5>
                        <p><?= nl2br(h($offer['description'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Prix et contact -->
            <div class="card shadow-sm sticky-top mb-4" style="top: 20px;">
                <div class="card-body">
                    <?php if ($offer['price_per_km'] || $offer['min_price']): ?>
                        <div class="text-center mb-3">
                            <?php if ($offer['price_per_km']): ?>
                                <h3 class="text-success mb-1"><?= formatPrice($offer['price_per_km']) ?>/km</h3>
                            <?php endif; ?>
                            <?php if ($offer['min_price']): ?>
                                <p class="text-muted">Prix minimum: <?= formatPrice($offer['min_price']) ?></p>
                            <?php endif; ?>
                            <?php if ($offer['price_negotiable']): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Prix négociable
                                </span>
                            <?php endif; ?>
                        </div>
                        <hr>
                    <?php endif; ?>

                    <?php if (!$isOwner && isLoggedIn()): ?>
                        <a href="/messages.php?offer_type=vehicle&offer_id=<?= $offer['id'] ?>&user_id=<?= $offer['user_id'] ?>"
                           class="btn btn-success w-100 mb-2">
                            <i class="bi bi-chat-dots"></i> Contacter le transporteur
                        </a>
                        <button class="btn btn-outline-success w-100" onclick="saveOffer()">
                            <i class="bi bi-bookmark"></i> Sauvegarder l'offre
                        </button>
                    <?php elseif (!isLoggedIn()): ?>
                        <a href="/login.php" class="btn btn-success w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Connectez-vous pour contacter
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Informations transporteur -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-building"></i> Transporteur</h6>
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

                    <a href="/directory.php?user=<?= $offer['user_id'] ?>" class="btn btn-outline-success btn-sm w-100 mt-3">
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
        window.location.href = '/delete-vehicle.php?id=<?= $offer['id'] ?>';
    }
}

function saveOffer() {
    alert('Offre sauvegardée dans vos favoris !');
}
</script>

<?php include 'includes/footer.php'; ?>
