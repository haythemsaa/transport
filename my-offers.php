<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Veuillez vous connecter');
    redirect('/login.php');
}

$pageTitle = 'Mes offres';
$userId = $_SESSION['user_id'];
$type = $_GET['type'] ?? 'freight';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

if ($type === 'freight') {
    $freightModel = new FreightOffer();
    $results = $freightModel->getByUser($userId, $page);
} else {
    $vehicleModel = new VehicleOffer();
    $results = $vehicleModel->getByUser($userId, $page);
}

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-list-ul"></i> Mes offres</h2>
        <div>
            <?php if ($type === 'freight' && isShipper()): ?>
                <a href="/exports/freight-csv.php" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-download"></i> Exporter CSV
                </a>
                <a href="/post-freight.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Nouvelle offre de fret
                </a>
            <?php endif; ?>
            <?php if ($type === 'vehicle' && isTransporter()): ?>
                <a href="/exports/vehicles-csv.php" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-download"></i> Exporter CSV
                </a>
                <a href="/post-vehicle.php" class="btn btn-success">
                    <i class="bi bi-plus-circle"></i> Nouveau véhicule
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4">
        <?php if (isShipper()): ?>
        <li class="nav-item">
            <a class="nav-link <?= $type === 'freight' ? 'active' : '' ?>" href="?type=freight">
                <i class="bi bi-box-seam"></i> Offres de fret (<?= $results['total'] ?? 0 ?>)
            </a>
        </li>
        <?php endif; ?>
        <?php if (isTransporter()): ?>
        <li class="nav-item">
            <a class="nav-link <?= $type === 'vehicle' ? 'active' : '' ?>" href="?type=vehicle">
                <i class="bi bi-truck"></i> Offres de véhicules (<?= $results['total'] ?? 0 ?>)
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- Liste des offres -->
    <?php if ($results['success'] && !empty($results['data'])): ?>
        <div class="row g-3">
            <?php foreach ($results['data'] as $offer): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?= h($offer['title']) ?></h5>
                                <?= getStatusBadge($offer['status']) ?>
                            </div>

                            <?php if ($type === 'freight'): ?>
                                <p class="mb-2">
                                    <i class="bi bi-geo-alt"></i>
                                    <?= h($offer['loading_city']) ?> → <?= h($offer['delivery_city']) ?>
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-calendar"></i>
                                    <?= formatDate($offer['loading_date']) ?>
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-box"></i>
                                    <?= getCargoTypeLabel($offer['cargo_type']) ?>
                                    • <?= formatWeight($offer['weight']) ?>
                                </p>
                            <?php else: ?>
                                <p class="mb-2">
                                    <i class="bi bi-truck"></i>
                                    <?= getVehicleTypeLabel($offer['vehicle_type']) ?>
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-geo-alt"></i>
                                    <?= h($offer['departure_city']) ?>
                                    <?= $offer['destination_city'] ? ' → ' . h($offer['destination_city']) : ' (flexible)' ?>
                                </p>
                                <p class="mb-2">
                                    <i class="bi bi-calendar"></i>
                                    Dispo: <?= formatDate($offer['available_from']) ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($offer['price'] ?? $offer['price_per_km'] ?? false): ?>
                                <p class="text-primary mb-2">
                                    <strong>
                                        <?php if (isset($offer['price'])): ?>
                                            <?= formatPrice($offer['price']) ?>
                                        <?php else: ?>
                                            <?= formatPrice($offer['price_per_km']) ?>/km
                                        <?php endif; ?>
                                    </strong>
                                </p>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <small class="text-muted">
                                    <i class="bi bi-eye"></i> <?= $offer['views'] ?> vues
                                </small>
                                <div class="btn-group btn-group-sm">
                                    <a href="/view-<?= $type ?>.php?id=<?= $offer['id'] ?>" class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="/edit-<?= $type ?>.php?id=<?= $offer['id'] ?>" class="btn btn-outline-secondary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button onclick="confirmDelete(<?= $offer['id'] ?>, '<?= $type ?>')" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>

                            <small class="text-muted d-block mt-2">
                                Créée: <?= timeAgo($offer['created_at']) ?>
                            </small>
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
                            <a class="page-link" href="?type=<?= $type ?>&page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

    <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                <h5>Aucune offre</h5>
                <p class="text-muted mb-4">Vous n'avez pas encore publié d'offre</p>
                <?php if ($type === 'freight' && isShipper()): ?>
                    <a href="/post-freight.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Publier une offre de fret
                    </a>
                <?php elseif ($type === 'vehicle' && isTransporter()): ?>
                    <a href="/post-vehicle.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Publier un véhicule
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function confirmDelete(id, type) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette offre ?')) {
        window.location.href = `/delete-${type}.php?id=${id}`;
    }
}
</script>

<?php include 'includes/footer.php'; ?>
