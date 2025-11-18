<?php
require_once 'config/config.php';
require_once 'config/database.php';

$pageTitle = 'Recherche de fret';

// Récupérer les filtres
$filters = [
    'loading_city' => $_GET['loading_city'] ?? '',
    'delivery_city' => $_GET['delivery_city'] ?? '',
    'loading_country' => $_GET['loading_country'] ?? '',
    'delivery_country' => $_GET['delivery_country'] ?? '',
    'loading_date_from' => $_GET['loading_date_from'] ?? '',
    'loading_date_to' => $_GET['loading_date_to'] ?? '',
    'cargo_type' => $_GET['cargo_type'] ?? '',
    'vehicle_type' => $_GET['vehicle_type'] ?? '',
    'min_weight' => $_GET['min_weight'] ?? '',
    'max_weight' => $_GET['max_weight'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'sort' => $_GET['sort'] ?? 'date_desc',
];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Rechercher les offres
$freightModel = new FreightOffer();
$results = $freightModel->search($filters, $page);

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <!-- Sidebar Filtres -->
        <div class="col-lg-3">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-funnel"></i> Filtres de recherche
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="" id="filterForm">

                        <!-- Localisation -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-geo-alt"></i> Chargement
                            </label>
                            <input type="text" class="form-control form-control-sm mb-2"
                                   name="loading_city" placeholder="Ville de chargement"
                                   value="<?= h($filters['loading_city']) ?>">
                            <select class="form-select form-select-sm" name="loading_country">
                                <option value="">Tous les pays</option>
                                <option value="France" <?= $filters['loading_country'] === 'France' ? 'selected' : '' ?>>France</option>
                                <option value="Belgique" <?= $filters['loading_country'] === 'Belgique' ? 'selected' : '' ?>>Belgique</option>
                                <option value="Allemagne" <?= $filters['loading_country'] === 'Allemagne' ? 'selected' : '' ?>>Allemagne</option>
                                <option value="Espagne" <?= $filters['loading_country'] === 'Espagne' ? 'selected' : '' ?>>Espagne</option>
                                <option value="Italie" <?= $filters['loading_country'] === 'Italie' ? 'selected' : '' ?>>Italie</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-geo-alt-fill"></i> Livraison
                            </label>
                            <input type="text" class="form-control form-control-sm mb-2"
                                   name="delivery_city" placeholder="Ville de livraison"
                                   value="<?= h($filters['delivery_city']) ?>">
                            <select class="form-select form-select-sm" name="delivery_country">
                                <option value="">Tous les pays</option>
                                <option value="France" <?= $filters['delivery_country'] === 'France' ? 'selected' : '' ?>>France</option>
                                <option value="Belgique" <?= $filters['delivery_country'] === 'Belgique' ? 'selected' : '' ?>>Belgique</option>
                                <option value="Allemagne" <?= $filters['delivery_country'] === 'Allemagne' ? 'selected' : '' ?>>Allemagne</option>
                                <option value="Espagne" <?= $filters['delivery_country'] === 'Espagne' ? 'selected' : '' ?>>Espagne</option>
                                <option value="Italie" <?= $filters['delivery_country'] === 'Italie' ? 'selected' : '' ?>>Italie</option>
                            </select>
                        </div>

                        <hr>

                        <!-- Dates -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-calendar"></i> Date de chargement
                            </label>
                            <input type="date" class="form-control form-control-sm mb-2"
                                   name="loading_date_from" placeholder="Du"
                                   value="<?= h($filters['loading_date_from']) ?>">
                            <input type="date" class="form-control form-control-sm"
                                   name="loading_date_to" placeholder="Au"
                                   value="<?= h($filters['loading_date_to']) ?>">
                        </div>

                        <hr>

                        <!-- Type de cargo -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-box"></i> Type de cargo
                            </label>
                            <select class="form-select form-select-sm" name="cargo_type">
                                <option value="">Tous les types</option>
                                <option value="pallets" <?= $filters['cargo_type'] === 'pallets' ? 'selected' : '' ?>>Palettes</option>
                                <option value="containers" <?= $filters['cargo_type'] === 'containers' ? 'selected' : '' ?>>Conteneurs</option>
                                <option value="bulk" <?= $filters['cargo_type'] === 'bulk' ? 'selected' : '' ?>>Vrac</option>
                                <option value="vehicles" <?= $filters['cargo_type'] === 'vehicles' ? 'selected' : '' ?>>Véhicules</option>
                                <option value="refrigerated" <?= $filters['cargo_type'] === 'refrigerated' ? 'selected' : '' ?>>Réfrigéré</option>
                                <option value="dangerous" <?= $filters['cargo_type'] === 'dangerous' ? 'selected' : '' ?>>Matières dangereuses</option>
                            </select>
                        </div>

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
                                <option value="refrigerated_truck" <?= $filters['vehicle_type'] === 'refrigerated_truck' ? 'selected' : '' ?>>Frigorifique</option>
                                <option value="flatbed" <?= $filters['vehicle_type'] === 'flatbed' ? 'selected' : '' ?>>Plateau</option>
                            </select>
                        </div>

                        <hr>

                        <!-- Poids -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-boxes"></i> Poids (tonnes)
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm"
                                           name="min_weight" placeholder="Min"
                                           value="<?= h($filters['min_weight']) ?>" step="0.1">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm"
                                           name="max_weight" placeholder="Max"
                                           value="<?= h($filters['max_weight']) ?>" step="0.1">
                                </div>
                            </div>
                        </div>

                        <!-- Prix -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                <i class="bi bi-currency-euro"></i> Prix (€)
                            </label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm"
                                           name="min_price" placeholder="Min"
                                           value="<?= h($filters['min_price']) ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" class="form-control form-control-sm"
                                           name="max_price" placeholder="Max"
                                           value="<?= h($filters['max_price']) ?>">
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-search"></i> Rechercher
                        </button>
                        <a href="/search-freight.php" class="btn btn-outline-secondary w-100 btn-sm">
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
                    <i class="bi bi-box-seam text-primary"></i>
                    Offres de fret
                </h3>

                <div class="d-flex gap-2 align-items-center">
                    <?php if ($results['success']): ?>
                        <span class="text-muted">
                            <?= number_format($results['total']) ?> offre(s) trouvée(s)
                        </span>
                    <?php endif; ?>

                    <select class="form-select form-select-sm" style="width: auto;"
                            onchange="window.location.href=updateUrlParameter('sort', this.value)">
                        <option value="date_desc" <?= $filters['sort'] === 'date_desc' ? 'selected' : '' ?>>Plus récentes</option>
                        <option value="date_asc" <?= $filters['sort'] === 'date_asc' ? 'selected' : '' ?>>Plus anciennes</option>
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

                                    <div class="mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-geo-alt text-success"></i>
                                            <strong class="mx-1"><?= h($offer['loading_city']) ?></strong>
                                            <span class="text-muted small"><?= h($offer['loading_country']) ?></span>
                                        </div>
                                        <div class="text-center my-1">
                                            <i class="bi bi-arrow-down"></i>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-geo-alt-fill text-danger"></i>
                                            <strong class="mx-1"><?= h($offer['delivery_city']) ?></strong>
                                            <span class="text-muted small"><?= h($offer['delivery_country']) ?></span>
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <small>
                                                <i class="bi bi-calendar"></i>
                                                <?= formatDate($offer['loading_date']) ?>
                                            </small>
                                        </div>
                                        <div class="col-6 text-end">
                                            <small>
                                                <i class="bi bi-arrow-right"></i>
                                                <?= formatDate($offer['delivery_date']) ?>
                                            </small>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="badge bg-info">
                                            <i class="bi bi-box"></i>
                                            <?= getCargoTypeLabel($offer['cargo_type']) ?>
                                        </span>
                                        <span class="badge bg-secondary">
                                            <i class="bi bi-truck"></i>
                                            <?= getVehicleTypeLabel($offer['vehicle_type']) ?>
                                        </span>
                                        <span class="badge bg-dark">
                                            <i class="bi bi-boxes"></i>
                                            <?= formatWeight($offer['weight']) ?>
                                        </span>
                                    </div>

                                    <?php if ($offer['price']): ?>
                                        <div class="mb-3">
                                            <h4 class="text-primary mb-0"><?= formatPrice($offer['price']) ?></h4>
                                            <?php if ($offer['price_negotiable']): ?>
                                                <small class="text-success">
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
                                        <a href="/view-freight.php?id=<?= $offer['id'] ?>" class="btn btn-primary btn-sm flex-grow-1">
                                            <i class="bi bi-eye"></i> Voir les détails
                                        </a>
                                        <?php if (isLoggedIn()): ?>
                                            <a href="/messages.php?offer_type=freight&offer_id=<?= $offer['id'] ?>&user_id=<?= $offer['user_id'] ?>"
                                               class="btn btn-outline-primary btn-sm">
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
                        <h5>Aucune offre trouvée</h5>
                        <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                        <a href="/search-freight.php" class="btn btn-primary">
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
