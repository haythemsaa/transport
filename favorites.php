<?php
require_once 'config/config.php';
requireLogin();

$pageTitle = 'Mes favoris';
$user = $_SESSION['user'];

try {
    $db = Database::getInstance()->getConnection();

    // Handle favorite removal
    if (isset($_GET['remove'])) {
        $stmt = $db->prepare("DELETE FROM favorites WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['remove'], $user['id']]);
        setFlash('success', 'Favori supprimé');
        redirect('/favorites.php');
    }

    // Get filter
    $filter = $_GET['filter'] ?? 'all';

    // Get user's favorites
    $where = "f.user_id = ?";
    $params = [$user['id']];

    if ($filter === 'freight') {
        $where .= " AND f.offer_type = 'freight'";
    } elseif ($filter === 'vehicle') {
        $where .= " AND f.offer_type = 'vehicle'";
    }

    $stmt = $db->prepare("
        SELECT
            f.id as favorite_id,
            f.offer_type,
            f.offer_id,
            f.created_at as favorited_at,
            CASE
                WHEN f.offer_type = 'freight' THEN fo.loading_city
                ELSE vo.departure_city
            END as departure_city,
            CASE
                WHEN f.offer_type = 'freight' THEN fo.delivery_city
                ELSE vo.destination_city
            END as destination_city,
            CASE
                WHEN f.offer_type = 'freight' THEN fo.loading_date
                ELSE vo.available_date
            END as offer_date,
            CASE
                WHEN f.offer_type = 'freight' THEN fo.price
                ELSE vo.price_per_km
            END as price,
            CASE
                WHEN f.offer_type = 'freight' THEN fo.weight
                ELSE vo.max_weight
            END as weight,
            CASE
                WHEN f.offer_type = 'freight' THEN fo.status
                ELSE vo.status
            END as status,
            CASE
                WHEN f.offer_type = 'freight' THEN fo.vehicle_type
                ELSE vo.vehicle_type
            END as vehicle_type,
            CASE
                WHEN f.offer_type = 'freight' THEN fo.cargo_description
                ELSE NULL
            END as description,
            u.company_name,
            u.rating
        FROM favorites f
        LEFT JOIN freight_offers fo ON f.offer_type = 'freight' AND f.offer_id = fo.id
        LEFT JOIN vehicle_offers vo ON f.offer_type = 'vehicle' AND f.offer_id = vo.id
        LEFT JOIN users u ON (f.offer_type = 'freight' AND fo.user_id = u.id) OR (f.offer_type = 'vehicle' AND vo.user_id = u.id)
        WHERE $where
        ORDER BY f.created_at DESC
    ");
    $stmt->execute($params);
    $favorites = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Count by type
    $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND offer_type = 'freight'");
    $stmt->execute([$user['id']]);
    $freightCount = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ? AND offer_type = 'vehicle'");
    $stmt->execute([$user['id']]);
    $vehicleCount = $stmt->fetchColumn();

} catch (Exception $e) {
    logError('Favorites error', ['error' => $e->getMessage()]);
    $favorites = [];
    $freightCount = 0;
    $vehicleCount = 0;
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-heart-fill text-danger"></i> Mes favoris</h1>
            <p class="text-muted">Retrouvez rapidement vos offres sauvegardées</p>
        </div>
    </div>

    <!-- Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="btn-group w-100" role="group">
                        <a href="?filter=all" class="btn btn-<?= $filter === 'all' ? 'primary' : 'outline-primary' ?>">
                            <i class="bi bi-star"></i> Tous (<?= $freightCount + $vehicleCount ?>)
                        </a>
                        <a href="?filter=freight" class="btn btn-<?= $filter === 'freight' ? 'primary' : 'outline-primary' ?>">
                            <i class="bi bi-box-seam"></i> Fret (<?= $freightCount ?>)
                        </a>
                        <a href="?filter=vehicle" class="btn btn-<?= $filter === 'vehicle' ? 'success' : 'outline-success' ?>">
                            <i class="bi bi-truck"></i> Véhicules (<?= $vehicleCount ?>)
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Favorites List -->
    <div class="row">
        <div class="col-12">
            <?php if (empty($favorites)): ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-heart text-muted" style="font-size: 5rem;"></i>
                        <h4 class="mt-4">Aucun favori</h4>
                        <p class="text-muted">Ajoutez des offres à vos favoris pour les retrouver facilement</p>
                        <div class="mt-4">
                            <a href="/search-freight.php" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Rechercher du fret
                            </a>
                            <a href="/search-vehicles.php" class="btn btn-success">
                                <i class="bi bi-search"></i> Rechercher des véhicules
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($favorites as $fav): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h6 class="mb-0">
                                            <?php if ($fav['offer_type'] === 'freight'): ?>
                                                <i class="bi bi-box-seam text-primary"></i> Offre de fret
                                            <?php else: ?>
                                                <i class="bi bi-truck text-success"></i> Véhicule
                                            <?php endif; ?>
                                        </h6>
                                        <?= getStatusBadge($fav['status']) ?>
                                    </div>

                                    <?php if ($fav['description']): ?>
                                        <p class="small mb-2"><?= h($fav['description']) ?></p>
                                    <?php endif; ?>

                                    <div class="mb-2 small">
                                        <div class="mb-1">
                                            <i class="bi bi-geo-alt"></i>
                                            <strong><?= h($fav['departure_city']) ?></strong>
                                            <i class="bi bi-arrow-right mx-1"></i>
                                            <strong><?= h($fav['destination_city']) ?></strong>
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-calendar"></i>
                                            <?= formatDate($fav['offer_date']) ?>
                                        </div>
                                        <?php if ($fav['vehicle_type']): ?>
                                            <div class="mb-1">
                                                <i class="bi bi-truck"></i>
                                                <?= h($fav['vehicle_type']) ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mb-1">
                                            <i class="bi bi-box"></i>
                                            <?= number_format($fav['weight']) ?> kg
                                        </div>
                                        <div class="mb-1">
                                            <i class="bi bi-building"></i>
                                            <?= h($fav['company_name']) ?>
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <?= number_format($fav['rating'], 1) ?>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <strong class="<?= $fav['offer_type'] === 'freight' ? 'text-primary' : 'text-success' ?>">
                                            <?php if ($fav['offer_type'] === 'freight'): ?>
                                                <?= formatPrice($fav['price']) ?>
                                            <?php else: ?>
                                                <?= formatPrice($fav['price']) ?>/km
                                            <?php endif; ?>
                                        </strong>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <?php if ($fav['offer_type'] === 'freight'): ?>
                                                <a href="/view-freight.php?id=<?= $fav['offer_id'] ?>"
                                                   class="btn btn-outline-primary">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="/view-vehicle.php?id=<?= $fav['offer_id'] ?>"
                                                   class="btn btn-outline-success">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?remove=<?= $fav['favorite_id'] ?>"
                                               class="btn btn-outline-danger"
                                               onclick="return confirm('Retirer des favoris ?')">
                                                <i class="bi bi-heart-fill"></i>
                                            </a>
                                        </div>
                                    </div>

                                    <small class="text-muted d-block mt-2">
                                        <i class="bi bi-clock"></i> Ajouté <?= timeAgo($fav['favorited_at']) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
