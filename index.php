<?php
require_once 'config/config.php';
require_once 'config/database.php';

$pageTitle = 'Accueil';

// Obtenir les statistiques
$freightModel = new FreightOffer();
$vehicleModel = new VehicleOffer();
$freightStats = $freightModel->getStats();
$vehicleStats = $vehicleModel->getStats();

// Obtenir les dernières offres
$latestFreight = $freightModel->search([], 1, 6);
$latestVehicles = $vehicleModel->search([], 1, 6);

include 'includes/header.php';
?>

<!-- Hero Section -->
<section class="bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">
                    Trouvez du fret et des véhicules en quelques clics
                </h1>
                <p class="lead mb-4">
                    La plateforme leader de mise en relation pour le transport routier.
                    Plus de <strong><?= number_format(($freightStats['active_offers'] ?? 0) + ($vehicleStats['available_vehicles'] ?? 0)) ?> offres</strong> disponibles aujourd'hui.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="/register.php" class="btn btn-light btn-lg">
                        <i class="bi bi-person-plus"></i> S'inscrire gratuitement
                    </a>
                    <a href="/search-freight.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-search"></i> Rechercher
                    </a>
                </div>
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0">
                <div class="bg-white rounded-3 p-4 shadow">
                    <h4 class="text-dark mb-3">Recherche rapide</h4>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#freight-tab" type="button">
                                <i class="bi bi-box-seam"></i> Fret
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#vehicle-tab" type="button">
                                <i class="bi bi-truck-front"></i> Véhicules
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content">
                        <!-- Freight Tab -->
                        <div class="tab-pane fade show active" id="freight-tab">
                            <form action="/search-freight.php" method="GET">
                                <div class="mb-3">
                                    <label class="form-label text-dark">Ville de chargement</label>
                                    <input type="text" name="loading_city" class="form-control" placeholder="Ex: Paris">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-dark">Ville de livraison</label>
                                    <input type="text" name="delivery_city" class="form-control" placeholder="Ex: Lyon">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-dark">Date de chargement</label>
                                    <input type="date" name="loading_date_from" class="form-control">
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Rechercher du fret
                                </button>
                            </form>
                        </div>

                        <!-- Vehicle Tab -->
                        <div class="tab-pane fade" id="vehicle-tab">
                            <form action="/search-vehicles.php" method="GET">
                                <div class="mb-3">
                                    <label class="form-label text-dark">Ville de départ</label>
                                    <input type="text" name="departure_city" class="form-control" placeholder="Ex: Marseille">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-dark">Destination souhaitée</label>
                                    <input type="text" name="destination_city" class="form-control" placeholder="Ex: Bordeaux">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-dark">Type de véhicule</label>
                                    <select name="vehicle_type" class="form-select">
                                        <option value="">Tous les types</option>
                                        <option value="van">Fourgon</option>
                                        <option value="truck">Camion</option>
                                        <option value="semi_trailer">Semi-remorque</option>
                                        <option value="refrigerated_truck">Frigorifique</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Rechercher des véhicules
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">Pourquoi choisir <?= APP_NAME ?> ?</h2>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="text-center">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-graph-up-arrow fs-1"></i>
                    </div>
                    <h5>Des milliers d'offres</h5>
                    <p class="text-muted">Accédez à <?= number_format(($freightStats['active_offers'] ?? 0) + ($vehicleStats['available_vehicles'] ?? 0)) ?>+ offres mises à jour quotidiennement</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-shield-check fs-1"></i>
                    </div>
                    <h5>Utilisateurs vérifiés</h5>
                    <p class="text-muted">Tous nos membres sont vérifiés pour votre sécurité</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-chat-dots fs-1"></i>
                    </div>
                    <h5>Messagerie instantanée</h5>
                    <p class="text-muted">Communiquez directement avec vos partenaires en temps réel</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="text-center">
                    <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="bi bi-geo-alt fs-1"></i>
                    </div>
                    <h5>Géolocalisation</h5>
                    <p class="text-muted">Trouvez facilement du fret sur vos itinéraires</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Latest Freight Offers -->
<?php if ($latestFreight['success'] && !empty($latestFreight['data'])): ?>
<section class="py-5 bg-light">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Dernières offres de fret</h2>
            <a href="/search-freight.php" class="btn btn-primary">
                Voir toutes les offres <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($latestFreight['data'] as $offer): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?= h($offer['title']) ?></h5>
                                <?= getStatusBadge($offer['status']) ?>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-building"></i> <?= h($offer['company_name']) ?>
                                    <?php if ($offer['rating'] > 0): ?>
                                        <span class="ms-2">
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <?= number_format($offer['rating'], 1) ?>
                                        </span>
                                    <?php endif; ?>
                                </small>
                            </div>

                            <div class="mb-2">
                                <i class="bi bi-geo-alt text-primary"></i>
                                <strong><?= h($offer['loading_city']) ?></strong>
                                <i class="bi bi-arrow-right mx-2"></i>
                                <strong><?= h($offer['delivery_city']) ?></strong>
                            </div>

                            <div class="mb-2">
                                <i class="bi bi-calendar"></i>
                                <small><?= formatDate($offer['loading_date']) ?></small>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span><i class="bi bi-box"></i> <?= getCargoTypeLabel($offer['cargo_type']) ?></span>
                                <span><i class="bi bi-boxes"></i> <?= formatWeight($offer['weight']) ?></span>
                            </div>

                            <?php if ($offer['price']): ?>
                                <div class="mb-3">
                                    <h4 class="text-primary mb-0"><?= formatPrice($offer['price']) ?></h4>
                                    <?php if ($offer['price_negotiable']): ?>
                                        <small class="text-muted">Négociable</small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <a href="/view-freight.php?id=<?= $offer['id'] ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-eye"></i> Voir les détails
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Latest Vehicle Offers -->
<?php if ($latestVehicles['success'] && !empty($latestVehicles['data'])): ?>
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Véhicules disponibles</h2>
            <a href="/search-vehicles.php" class="btn btn-primary">
                Voir tous les véhicules <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php foreach ($latestVehicles['data'] as $offer): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?= h($offer['title']) ?></h5>
                                <?= getStatusBadge($offer['status']) ?>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="bi bi-building"></i> <?= h($offer['company_name']) ?>
                                    <?php if ($offer['rating'] > 0): ?>
                                        <span class="ms-2">
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <?= number_format($offer['rating'], 1) ?>
                                        </span>
                                    <?php endif; ?>
                                </small>
                            </div>

                            <div class="mb-2">
                                <i class="bi bi-truck text-primary"></i>
                                <strong><?= getVehicleTypeLabel($offer['vehicle_type']) ?></strong>
                            </div>

                            <div class="mb-2">
                                <i class="bi bi-geo-alt text-success"></i>
                                <strong><?= h($offer['departure_city']) ?></strong>
                                <?php if ($offer['destination_city']): ?>
                                    <i class="bi bi-arrow-right mx-2"></i>
                                    <strong><?= h($offer['destination_city']) ?></strong>
                                <?php endif; ?>
                            </div>

                            <div class="mb-2">
                                <i class="bi bi-calendar"></i>
                                <small>Dispo: <?= formatDate($offer['available_from']) ?></small>
                            </div>

                            <div class="mb-3">
                                <span class="badge bg-secondary"><i class="bi bi-boxes"></i> Max: <?= formatWeight($offer['max_weight']) ?></span>
                                <?php if ($offer['has_refrigeration']): ?>
                                    <span class="badge bg-info"><i class="bi bi-snow"></i> Frigo</span>
                                <?php endif; ?>
                                <?php if ($offer['has_gps']): ?>
                                    <span class="badge bg-primary"><i class="bi bi-geo"></i> GPS</span>
                                <?php endif; ?>
                            </div>

                            <a href="/view-vehicle.php?id=<?= $offer['id'] ?>" class="btn btn-outline-primary w-100">
                                <i class="bi bi-eye"></i> Voir les détails
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- CTA Section -->
<section class="bg-primary text-white py-5">
    <div class="container text-center">
        <h2 class="mb-4">Prêt à développer votre activité de transport ?</h2>
        <p class="lead mb-4">Rejoignez des milliers de professionnels du transport qui font confiance à <?= APP_NAME ?></p>
        <a href="/register.php" class="btn btn-light btn-lg">
            <i class="bi bi-person-plus"></i> Créer un compte gratuitement
        </a>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
