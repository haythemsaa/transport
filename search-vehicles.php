<?php
require_once 'config/config.php';
require_once 'config/database.php';

$pageTitle = 'Recherche de véhicules';

// Récupérer les filtres
$filters = [
    'departure_city' => $_GET['departure_city'] ?? '',
    'destination_city' => $_GET['destination_city'] ?? '',
    'departure_country' => $_GET['departure_country'] ?? '',
    'destination_country' => $_GET['destination_country'] ?? '',
    'available_from' => $_GET['available_from'] ?? '',
    'vehicle_type' => $_GET['vehicle_type'] ?? '',
    'min_weight' => $_GET['min_weight'] ?? '',
    'has_refrigeration' => isset($_GET['has_refrigeration']) ? 1 : 0,
    'has_tail_lift' => isset($_GET['has_tail_lift']) ? 1 : 0,
    'has_adr' => isset($_GET['has_adr']) ? 1 : 0,
    'sort' => $_GET['sort'] ?? 'date_desc',
];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Rechercher les véhicules
$vehicleModel = new VehicleOffer();
$results = $vehicleModel->search($filters, $page);

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <!-- Sidebar Filtres -->
        <div class="col-lg-3">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel"></i> Filtres de recherche
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="" id="filterForm">

                        <!-- Localisation -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-geo-alt"></i> Départ
                            </label>
                            <input type="text" class="form-control form-control-sm mb-2"
                                   name="departure_city" placeholder="Ville de départ"
                                   value="<?= h($filters['departure_city']) ?>">
                            <select class="form-select form-select-sm" name="departure_country">
                                <option value="">Tous les pays</option>
                                <option value="France" <?= $filters['departure_country'] === 'France' ? 'selected' : '' ?>>France</option>
                                <option value="Belgique" <?= $filters['departure_country'] === 'Belgique' ? 'selected' : '' ?>>Belgique</option>
                                <option value="Allemagne" <?= $filters['departure_country'] === 'Allemagne' ? 'selected' : '' ?>>Allemagne</option>
                                <option value="Espagne" <?= $filters['departure_country'] === 'Espagne' ? 'selected' : '' ?>>Espagne</option>
                                <option value="Italie" <?= $filters['departure_country'] === 'Italie' ? 'selected' : '' ?>>Italie</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-geo-alt-fill"></i> Destination
                            </label>
                            <input type="text" class="form-control form-control-sm mb-2"
                                   name="destination_city" placeholder="Ville de destination"
                                   value="<?= h($filters['destination_city']) ?>">
                            <select class="form-select form-select-sm" name="destination_country">
                                <option value="">Tous les pays</option>
                                <option value="France" <?= $filters['destination_country'] === 'France' ? 'selected' : '' ?>>France</option>
                                <option value="Belgique" <?= $filters['destination_country'] === 'Belgique' ? 'selected' : '' ?>>Belgique</option>
                                <option value="Allemagne" <?= $filters['destination_country'] === 'Allemagne' ? 'selected' : '' ?>>Allemagne</option>
                                <option value="Espagne" <?= $filters['destination_country'] === 'Espagne' ? 'selected' : '' ?>>Espagne</option>
                                <option value="Italie" <?= $filters['destination_country'] === 'Italie' ? 'selected' : '' ?>>Italie</option>
                            </select>
                        </div>

                        <hr>

                        <!-- Date disponibilité -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-calendar"></i> Disponible à partir du
                            </label>
                            <input type="date" class="form-control form-control-sm"
                                   name="available_from"
                                   value="<?= h($filters['available_from']) ?>">
                        </div>

                        <hr>

                        <!-- Type de véhicule -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-truck"></i> Type de véhicule
                            </label>
                            <select class="form-select form-select-sm" name="vehicle_type">
                                <option value="">Tous les types</option>
                                <option value="van" <?= $filters['vehicle_type'] === 'van' ? 'selected' : '' ?>>Fourgon</option>
                                <option value="truck" <?= $filters['vehicle_type'] === 'truck' ? 'selected' : '' ?>>Camion</option>
                                <option value="semi_trailer" <?= $filters['vehicle_type'] === 'semi_trailer' ? 'selected' : '' ?>>Semi-remorque</option>
                                <option value="container_truck" <?= $filters['vehicle_type'] === 'container_truck' ? 'selected' : '' ?>>Porte-conteneur</option>
                                <option value="refrigerated_truck" <?= $filters['vehicle_type'] === 'refrigerated_truck' ? 'selected' : '' ?>>Frigorifique</option>
                                <option value="flatbed" <?= $filters['vehicle_type'] === 'flatbed' ? 'selected' : '' ?>>Plateau</option>
                                <option value="tanker" <?= $filters['vehicle_type'] === 'tanker' ? 'selected' : '' ?>>Citerne</option>
                            </select>
                        </div>

                        <!-- Capacité minimale -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-boxes"></i> Capacité min. (tonnes)
                            </label>
                            <input type="number" class="form-control form-control-sm"
                                   name="min_weight" placeholder="Ex: 10"
                                   value="<?= h($filters['min_weight']) ?>" step="0.1">
                        </div>

                        <hr>

                        <!-- Équipements -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-gear"></i> Équipements
                            </label>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       id="has_refrigeration" name="has_refrigeration"
                                       <?= $filters['has_refrigeration'] ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="has_refrigeration">
                                    <i class="bi bi-snow"></i> Réfrigération
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       id="has_tail_lift" name="has_tail_lift"
                                       <?= $filters['has_tail_lift'] ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="has_tail_lift">
                                    <i class="bi bi-arrow-down-up"></i> Hayon élévateur
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       id="has_adr" name="has_adr"
                                       <?= $filters['has_adr'] ? 'checked' : '' ?>>
                                <label class="form-check-label small" for="has_adr">
                                    <i class="bi bi-exclamation-triangle"></i> ADR (matières dangereuses)
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i class="bi bi-search"></i> Rechercher
                        </button>
                        <a href="/search-vehicles.php" class="btn btn-outline-secondary w-100 btn-sm">
                            <i class="bi bi-arrow-clockwise"></i> Réinitialiser
                        </a>
                    </form>
                </div>
            </div>
        </div>

        <!-- Résultats -->
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3>
                    <i class="bi bi-truck-front text-success"></i>
                    Véhicules disponibles
                </h3>

                <div class="d-flex gap-2 align-items-center">
                    <?php if ($results['success']): ?>
                        <span class="text-muted">
                            <?= number_format($results['total']) ?> véhicule(s) trouvé(s)
                        </span>
                    <?php endif; ?>

                    <select class="form-select form-select-sm" style="width: auto;"
                            onchange="window.location.href=updateUrlParameter('sort', this.value)">
                        <option value="date_desc" <?= $filters['sort'] === 'date_desc' ? 'selected' : '' ?>>Plus récents</option>
                        <option value="date_asc" <?= $filters['sort'] === 'date_asc' ? 'selected' : '' ?>>Disponibilité proche</option>
                        <option value="price_asc" <?= $filters['sort'] === 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
                        <option value="price_desc" <?= $filters['sort'] === 'price_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                    </select>
                </div>
            </div>

            <?php if ($results['success'] && !empty($results['data'])): ?>
                <div class="row g-3">
                    <?php foreach ($results['data'] as $offer): ?>
                        <div class="col-md-6">
                            <div class="card h-100 offer-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h5 class="card-title mb-0"><?= h($offer['title']) ?></h5>
                                        <?= getStatusBadge($offer['status']) ?>
                                    </div>

                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="bi bi-building"></i>
                                            <?= h($offer['company_name']) ?>
                                            <?php if ($offer['rating'] > 0): ?>
                                                <span class="ms-2">
                                                    <i class="bi bi-star-fill text-warning"></i>
                                                    <?= number_format($offer['rating'], 1) ?>
                                                    <small>(<?= $offer['total_ratings'] ?>)</small>
                                                </span>
                                            <?php endif; ?>
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <div class="badge bg-primary mb-2">
                                            <i class="bi bi-truck"></i>
                                            <?= getVehicleTypeLabel($offer['vehicle_type']) ?>
                                        </div>
                                        <?php if ($offer['vehicle_brand']): ?>
                                            <div class="small text-muted">
                                                <?= h($offer['vehicle_brand']) ?>
                                                <?= $offer['vehicle_model'] ? ' - ' . h($offer['vehicle_model']) : '' ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-2">
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="bi bi-geo-alt text-success"></i>
                                            <strong class="mx-1"><?= h($offer['departure_city']) ?></strong>
                                            <span class="text-muted small"><?= h($offer['departure_country']) ?></span>
                                        </div>
                                        <?php if ($offer['destination_city']): ?>
                                            <div class="text-center my-1">
                                                <i class="bi bi-arrow-down"></i>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <i class="bi bi-geo-alt-fill text-danger"></i>
                                                <strong class="mx-1"><?= h($offer['destination_city']) ?></strong>
                                                <span class="text-muted small"><?= h($offer['destination_country']) ?></span>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-muted small">
                                                <i class="bi bi-arrow-right"></i> Destination flexible
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mb-2">
                                        <small>
                                            <i class="bi bi-calendar"></i>
                                            Disponible: <?= formatDate($offer['available_from']) ?>
                                            <?php if ($offer['available_until']): ?>
                                                - <?= formatDate($offer['available_until']) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>

                                    <div class="mb-2">
                                        <span class="badge bg-dark">
                                            <i class="bi bi-boxes"></i>
                                            Max: <?= formatWeight($offer['max_weight']) ?>
                                        </span>
                                        <?php if ($offer['max_volume']): ?>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-box"></i>
                                                <?= formatVolume($offer['max_volume']) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($offer['max_pallets']): ?>
                                            <span class="badge bg-info">
                                                <?= $offer['max_pallets'] ?> palettes
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($offer['has_gps'] || $offer['has_refrigeration'] || $offer['has_tail_lift'] || $offer['has_adr']): ?>
                                        <div class="mb-2">
                                            <?php if ($offer['has_gps']): ?>
                                                <span class="badge bg-primary"><i class="bi bi-geo"></i> GPS</span>
                                            <?php endif; ?>
                                            <?php if ($offer['has_refrigeration']): ?>
                                                <span class="badge bg-info"><i class="bi bi-snow"></i> Frigo</span>
                                            <?php endif; ?>
                                            <?php if ($offer['has_tail_lift']): ?>
                                                <span class="badge bg-success"><i class="bi bi-arrow-down-up"></i> Hayon</span>
                                            <?php endif; ?>
                                            <?php if ($offer['has_adr']): ?>
                                                <span class="badge bg-warning"><i class="bi bi-exclamation-triangle"></i> ADR</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($offer['price_per_km'] || $offer['min_price']): ?>
                                        <div class="mb-3">
                                            <?php if ($offer['price_per_km']): ?>
                                                <h5 class="text-success mb-0">
                                                    <?= formatPrice($offer['price_per_km']) ?>/km
                                                </h5>
                                            <?php endif; ?>
                                            <?php if ($offer['min_price']): ?>
                                                <small class="text-muted">
                                                    Prix min: <?= formatPrice($offer['min_price']) ?>
                                                </small>
                                            <?php endif; ?>
                                            <?php if ($offer['price_negotiable']): ?>
                                                <small class="text-success d-block">
                                                    <i class="bi bi-check-circle"></i> Négociable
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($offer['description']): ?>
                                        <p class="card-text small text-muted mb-2">
                                            <?= h(substr($offer['description'], 0, 100)) ?>
                                            <?= strlen($offer['description']) > 100 ? '...' : '' ?>
                                        </p>
                                    <?php endif; ?>

                                    <div class="d-flex gap-2">
                                        <a href="/view-vehicle.php?id=<?= $offer['id'] ?>" class="btn btn-success btn-sm flex-grow-1">
                                            <i class="bi bi-eye"></i> Voir les détails
                                        </a>
                                        <?php if (isLoggedIn()): ?>
                                            <a href="/messages.php?offer_type=vehicle&offer_id=<?= $offer['id'] ?>&user_id=<?= $offer['user_id'] ?>"
                                               class="btn btn-outline-success btn-sm">
                                                <i class="bi bi-chat-dots"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mt-2 text-end">
                                        <small class="text-muted">
                                            <i class="bi bi-eye"></i> <?= number_format($offer['views']) ?> vues
                                            • <?= timeAgo($offer['created_at']) ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($results['pages'] > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $results['pages']; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= paginationUrl($i) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php elseif ($results['success']): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                        <h5>Aucun véhicule trouvé</h5>
                        <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                        <a href="/search-vehicles.php" class="btn btn-success">
                            <i class="bi bi-arrow-clockwise"></i> Réinitialiser la recherche
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Erreur lors de la recherche. Veuillez réessayer.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function updateUrlParameter(key, value) {
    const url = new URL(window.location);
    url.searchParams.set(key, value);
    return url.toString();
}
</script>

<?php include 'includes/footer.php'; ?>
