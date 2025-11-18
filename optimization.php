<?php
require_once 'config/config.php';
requireLogin();

if (!isTransporter() && $user['user_type'] !== 'both') {
    setFlash('warning', 'Cette fonctionnalité est réservée aux transporteurs');
    redirect('/dashboard.php');
}

$pageTitle = 'Optimisation de trajets';
$user = $_SESSION['user'];

try {
    $db = Database::getInstance()->getConnection();

    // Get user's available vehicles
    $stmt = $db->prepare("
        SELECT * FROM vehicle_offers
        WHERE user_id = ?
            AND status = 'available'
            AND deleted_at IS NULL
        ORDER BY available_date ASC
    ");
    $stmt->execute([$user['id']]);
    $userVehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Find optimization opportunities
    $opportunities = [];

    foreach ($userVehicles as $vehicle) {
        // Find freight offers near the vehicle's destination
        $stmt = $db->prepare("
            SELECT
                fo.*,
                u.company_name,
                u.rating,
                (6371 * acos(
                    cos(radians(?)) * cos(radians(fo.loading_lat)) *
                    cos(radians(fo.loading_lng) - radians(?)) +
                    sin(radians(?)) * sin(radians(fo.loading_lat))
                )) AS distance_km
            FROM freight_offers fo
            JOIN users u ON fo.user_id = u.id
            WHERE fo.status = 'active'
                AND fo.deleted_at IS NULL
                AND fo.user_id != ?
                AND fo.weight <= ?
                AND fo.loading_lat IS NOT NULL
                AND fo.loading_lng IS NOT NULL
            HAVING distance_km <= 100
            ORDER BY distance_km ASC
            LIMIT 5
        ");

        $stmt->execute([
            $vehicle['destination_lat'],
            $vehicle['destination_lng'],
            $vehicle['destination_lat'],
            $user['id'],
            $vehicle['max_weight']
        ]);

        $nearbyFreight = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($nearbyFreight)) {
            $opportunities[] = [
                'vehicle' => $vehicle,
                'freight_offers' => $nearbyFreight
            ];
        }
    }

} catch (Exception $e) {
    logError('Optimization error', ['error' => $e->getMessage()]);
    $userVehicles = [];
    $opportunities = [];
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-shuffle"></i> Optimisation de trajets</h1>
            <p class="text-muted">Réduisez vos kilomètres à vide et maximisez votre rentabilité</p>
        </div>
    </div>

    <!-- Benefits -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-success h-100">
                <div class="card-body text-center">
                    <i class="bi bi-piggy-bank text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Rentabilité +30%</h5>
                    <p class="text-muted small mb-0">Réduisez vos trajets à vide grâce à notre algorithme</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-warning h-100">
                <div class="card-body text-center">
                    <i class="bi bi-fuel-pump text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Économies de carburant</h5>
                    <p class="text-muted small mb-0">Moins de kilomètres = moins de carburant consommé</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-info h-100">
                <div class="card-body text-center">
                    <i class="bi bi-tree text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Impact écologique</h5>
                    <p class="text-muted small mb-0">Réduisez votre empreinte carbone</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Optimization Opportunities -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb"></i>
                        Opportunités d'optimisation (<?= count($opportunities) ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (empty($opportunities)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-search text-muted" style="font-size: 5rem;"></i>
                            <h4 class="mt-4">Aucune opportunité trouvée</h4>
                            <p class="text-muted">Publiez des offres de véhicules pour trouver des opportunités d'optimisation</p>
                            <a href="/post-vehicle.php" class="btn btn-primary mt-3">
                                <i class="bi bi-plus-circle"></i> Publier un véhicule
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($opportunities as $opp): ?>
                            <div class="mb-4 pb-4 border-bottom">
                                <!-- Vehicle Info -->
                                <div class="row mb-3">
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <h6 class="mb-2">
                                                <i class="bi bi-truck"></i> Votre véhicule
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <strong>Type:</strong> <?= h($opp['vehicle']['vehicle_type']) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>Trajet:</strong>
                                                    <?= h($opp['vehicle']['departure_city']) ?> →
                                                    <?= h($opp['vehicle']['destination_city']) ?>
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>Date:</strong> <?= formatDate($opp['vehicle']['available_date']) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Freight Opportunities -->
                                <h6 class="mb-3">
                                    <i class="bi bi-box-seam text-success"></i>
                                    Fret disponible au retour (<?= count($opp['freight_offers']) ?>)
                                </h6>

                                <div class="row g-3">
                                    <?php foreach ($opp['freight_offers'] as $freight): ?>
                                        <div class="col-md-6">
                                            <div class="card border-success">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                                        <h6 class="mb-0"><?= h($freight['cargo_description']) ?></h6>
                                                        <span class="badge bg-success">
                                                            <?= round($freight['distance_km']) ?> km
                                                        </span>
                                                    </div>

                                                    <div class="small mb-2">
                                                        <div><i class="bi bi-geo-alt"></i>
                                                            <?= h($freight['loading_city']) ?> →
                                                            <?= h($freight['delivery_city']) ?>
                                                        </div>
                                                        <div><i class="bi bi-calendar"></i>
                                                            <?= formatDate($freight['loading_date']) ?>
                                                        </div>
                                                        <div><i class="bi bi-box"></i>
                                                            <?= number_format($freight['weight']) ?> kg
                                                        </div>
                                                        <div><i class="bi bi-building"></i>
                                                            <?= h($freight['company_name']) ?>
                                                            <i class="bi bi-star-fill text-warning"></i>
                                                            <?= number_format($freight['rating'], 1) ?>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <strong class="text-success"><?= formatPrice($freight['price']) ?></strong>
                                                        <a href="/view-freight.php?id=<?= $freight['id'] ?>"
                                                           class="btn btn-sm btn-success">
                                                            <i class="bi bi-eye"></i> Voir
                                                        </a>
                                                    </div>

                                                    <!-- Savings Calculation -->
                                                    <?php
                                                    $emptyKm = 100; // Estimation
                                                    $fuelSavings = ($emptyKm * 0.30 * 1.80); // 30L/100km * 1.80€/L
                                                    ?>
                                                    <div class="alert alert-success mt-2 mb-0 py-2">
                                                        <small>
                                                            <i class="bi bi-piggy-bank"></i>
                                                            Économie estimée: ~<?= round($fuelSavings) ?>€ en carburant
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Tips Section -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="mb-3"><i class="bi bi-lightbulb-fill text-warning"></i> Conseils d'optimisation</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <h6><i class="bi bi-1-circle text-primary"></i> Planifiez à l'avance</h6>
                            <p class="small text-muted">Publiez vos véhicules plusieurs jours à l'avance pour maximiser les opportunités</p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="bi bi-2-circle text-primary"></i> Soyez flexible</h6>
                            <p class="small text-muted">Acceptez les trajets dans un rayon de 100km pour plus d'options</p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="bi bi-3-circle text-primary"></i> Utilisez les alertes</h6>
                            <p class="small text-muted">Configurez des alertes pour être notifié des nouvelles opportunités</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <h6><i class="bi bi-4-circle text-primary"></i> Groupez vos chargements</h6>
                            <p class="small text-muted">Combinez plusieurs petits chargements sur un même trajet</p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="bi bi-5-circle text-primary"></i> Évaluez la rentabilité</h6>
                            <p class="small text-muted">Vérifiez que le détour est compensé par le prix du fret</p>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="bi bi-6-circle text-primary"></i> Maintenez votre réputation</h6>
                            <p class="small text-muted">Une bonne note attire plus de clients et d'opportunités</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
