<?php
require_once 'config/config.php';
requireLogin();

$pageTitle = 'Matching automatique';
$user = $_SESSION['user'];

try {
    $db = Database::getInstance()->getConnection();

    // Get user's offers to find matches
    $matches = [];

    if (isShipper() || ($user['user_type'] === 'both')) {
        // Find vehicles matching user's freight offers
        $stmt = $db->prepare("
            SELECT
                fo.id as freight_id,
                fo.loading_city,
                fo.loading_country,
                fo.delivery_city,
                fo.delivery_country,
                fo.loading_date,
                fo.weight,
                fo.vehicle_type as required_vehicle,
                fo.price as freight_price,
                vo.id as vehicle_id,
                vo.vehicle_type,
                vo.departure_city,
                vo.departure_country,
                vo.destination_city,
                vo.destination_country,
                vo.available_date,
                vo.max_weight,
                vo.price_per_km,
                u.id as transporter_id,
                u.company_name as transporter_company,
                u.rating as transporter_rating
            FROM freight_offers fo
            CROSS JOIN vehicle_offers vo
            JOIN users u ON vo.user_id = u.id
            WHERE fo.user_id = ?
                AND fo.status = 'active'
                AND fo.deleted_at IS NULL
                AND vo.status = 'available'
                AND vo.deleted_at IS NULL
                AND vo.user_id != ?
                AND (
                    (fo.loading_city = vo.departure_city OR fo.loading_city LIKE CONCAT(vo.departure_city, '%'))
                    OR (fo.delivery_city = vo.destination_city OR fo.delivery_city LIKE CONCAT(vo.destination_city, '%'))
                )
                AND vo.max_weight >= fo.weight
                AND (fo.vehicle_type = vo.vehicle_type OR fo.vehicle_type IS NULL)
            ORDER BY u.rating DESC, vo.available_date ASC
            LIMIT 20
        ");
        $stmt->execute([$user['id'], $user['id']]);
        $freightMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($freightMatches as $match) {
            $match['match_type'] = 'freight';
            $match['compatibility_score'] = calculateCompatibilityScore($match, 'freight');
            $matches[] = $match;
        }
    }

    if (isTransporter() || ($user['user_type'] === 'both')) {
        // Find freight matching user's vehicle offers
        $stmt = $db->prepare("
            SELECT
                vo.id as vehicle_id,
                vo.vehicle_type,
                vo.departure_city,
                vo.departure_country,
                vo.destination_city,
                vo.destination_country,
                vo.available_date,
                vo.max_weight,
                vo.price_per_km,
                fo.id as freight_id,
                fo.loading_city,
                fo.loading_country,
                fo.delivery_city,
                fo.delivery_country,
                fo.loading_date,
                fo.weight,
                fo.vehicle_type as required_vehicle,
                fo.price as freight_price,
                u.id as shipper_id,
                u.company_name as shipper_company,
                u.rating as shipper_rating
            FROM vehicle_offers vo
            CROSS JOIN freight_offers fo
            JOIN users u ON fo.user_id = u.id
            WHERE vo.user_id = ?
                AND vo.status = 'available'
                AND vo.deleted_at IS NULL
                AND fo.status = 'active'
                AND fo.deleted_at IS NULL
                AND fo.user_id != ?
                AND (
                    (vo.departure_city = fo.loading_city OR vo.departure_city LIKE CONCAT(fo.loading_city, '%'))
                    OR (vo.destination_city = fo.delivery_city OR vo.destination_city LIKE CONCAT(fo.delivery_city, '%'))
                )
                AND vo.max_weight >= fo.weight
                AND (fo.vehicle_type = vo.vehicle_type OR fo.vehicle_type IS NULL)
            ORDER BY u.rating DESC, fo.loading_date ASC
            LIMIT 20
        ");
        $stmt->execute([$user['id'], $user['id']]);
        $vehicleMatches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($vehicleMatches as $match) {
            $match['match_type'] = 'vehicle';
            $match['compatibility_score'] = calculateCompatibilityScore($match, 'vehicle');
            $matches[] = $match;
        }
    }

    // Sort by compatibility score
    usort($matches, function($a, $b) {
        return $b['compatibility_score'] - $a['compatibility_score'];
    });

} catch (Exception $e) {
    logError('Matching error', ['error' => $e->getMessage()]);
    $matches = [];
}

function calculateCompatibilityScore($match, $type) {
    $score = 0;

    // Perfect city match: +40 points
    if ($type === 'freight') {
        if ($match['loading_city'] === $match['departure_city']) $score += 20;
        if ($match['delivery_city'] === $match['destination_city']) $score += 20;
    } else {
        if ($match['departure_city'] === $match['loading_city']) $score += 20;
        if ($match['destination_city'] === $match['delivery_city']) $score += 20;
    }

    // Vehicle type match: +20 points
    if ($match['vehicle_type'] === $match['required_vehicle']) $score += 20;

    // Date proximity: up to +20 points
    $dateField = $type === 'freight' ? 'available_date' : 'loading_date';
    $targetDate = $type === 'freight' ? 'loading_date' : 'available_date';
    if (isset($match[$dateField]) && isset($match[$targetDate])) {
        $daysDiff = abs((strtotime($match[$dateField]) - strtotime($match[$targetDate])) / 86400);
        if ($daysDiff <= 1) $score += 20;
        elseif ($daysDiff <= 3) $score += 15;
        elseif ($daysDiff <= 7) $score += 10;
    }

    // Rating: up to +20 points
    $ratingField = $type === 'freight' ? 'transporter_rating' : 'shipper_rating';
    if (isset($match[$ratingField])) {
        $score += ($match[$ratingField] / 5) * 20;
    }

    return $score;
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-stars"></i> Matching automatique</h1>
            <p class="text-muted">Découvrez les offres qui correspondent parfaitement à vos besoins</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                    <h3 class="mt-2 mb-0"><?= count($matches) ?></h3>
                    <p class="text-muted mb-0">Correspondances trouvées</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-lightning-fill text-warning" style="font-size: 3rem;"></i>
                    <h3 class="mt-2 mb-0">Instantané</h3>
                    <p class="text-muted mb-0">Analyse en temps réel</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-info">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up text-info" style="font-size: 3rem;"></i>
                    <h3 class="mt-2 mb-0">Intelligent</h3>
                    <p class="text-muted mb-0">Algorithme de compatibilité</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Matches List -->
    <div class="row">
        <div class="col-12">
            <?php if (empty($matches)): ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-search text-muted" style="font-size: 5rem;"></i>
                        <h4 class="mt-4">Aucune correspondance trouvée</h4>
                        <p class="text-muted">Publiez des offres pour que notre algorithme trouve des correspondances</p>
                        <div class="mt-4">
                            <?php if (isShipper()): ?>
                                <a href="/post-freight.php" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Publier une offre de fret
                                </a>
                            <?php endif; ?>
                            <?php if (isTransporter()): ?>
                                <a href="/post-vehicle.php" class="btn btn-success">
                                    <i class="bi bi-plus-circle"></i> Publier un véhicule
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($matches as $match): ?>
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-1 text-center">
                                    <!-- Compatibility Score -->
                                    <div class="position-relative" style="width: 60px; height: 60px;">
                                        <svg width="60" height="60">
                                            <circle cx="30" cy="30" r="25" fill="none" stroke="#e9ecef" stroke-width="5"/>
                                            <circle cx="30" cy="30" r="25" fill="none"
                                                    stroke="<?= $match['compatibility_score'] >= 80 ? '#28a745' : ($match['compatibility_score'] >= 60 ? '#ffc107' : '#6c757d') ?>"
                                                    stroke-width="5"
                                                    stroke-dasharray="<?= ($match['compatibility_score'] / 100) * 157 ?>, 157"
                                                    transform="rotate(-90 30 30)"/>
                                        </svg>
                                        <div class="position-absolute top-50 start-50 translate-middle">
                                            <strong><?= round($match['compatibility_score']) ?>%</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-7">
                                    <?php if ($match['match_type'] === 'freight'): ?>
                                        <!-- Your freight → Matched vehicle -->
                                        <h6 class="mb-2">
                                            <i class="bi bi-box-seam text-primary"></i> Votre fret
                                            <i class="bi bi-arrow-right mx-2"></i>
                                            <i class="bi bi-truck text-success"></i> Véhicule disponible
                                        </h6>
                                        <div class="row small">
                                            <div class="col-md-6">
                                                <strong>Votre offre:</strong><br>
                                                <i class="bi bi-geo-alt"></i> <?= h($match['loading_city']) ?> → <?= h($match['delivery_city']) ?><br>
                                                <i class="bi bi-calendar"></i> <?= formatDate($match['loading_date']) ?><br>
                                                <i class="bi bi-box"></i> <?= number_format($match['weight']) ?> kg
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Véhicule correspondant:</strong><br>
                                                <i class="bi bi-truck"></i> <?= h($match['vehicle_type']) ?><br>
                                                <i class="bi bi-geo-alt"></i> <?= h($match['departure_city']) ?> → <?= h($match['destination_city']) ?><br>
                                                <i class="bi bi-calendar"></i> Dispo: <?= formatDate($match['available_date']) ?><br>
                                                <i class="bi bi-building"></i> <?= h($match['transporter_company']) ?>
                                                <i class="bi bi-star-fill text-warning"></i> <?= number_format($match['transporter_rating'], 1) ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Your vehicle → Matched freight -->
                                        <h6 class="mb-2">
                                            <i class="bi bi-truck text-success"></i> Votre véhicule
                                            <i class="bi bi-arrow-right mx-2"></i>
                                            <i class="bi bi-box-seam text-primary"></i> Fret disponible
                                        </h6>
                                        <div class="row small">
                                            <div class="col-md-6">
                                                <strong>Votre véhicule:</strong><br>
                                                <i class="bi bi-truck"></i> <?= h($match['vehicle_type']) ?><br>
                                                <i class="bi bi-geo-alt"></i> <?= h($match['departure_city']) ?> → <?= h($match['destination_city']) ?><br>
                                                <i class="bi bi-calendar"></i> Dispo: <?= formatDate($match['available_date']) ?><br>
                                                <i class="bi bi-box"></i> Max: <?= number_format($match['max_weight']) ?> kg
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Fret correspondant:</strong><br>
                                                <i class="bi bi-geo-alt"></i> <?= h($match['loading_city']) ?> → <?= h($match['delivery_city']) ?><br>
                                                <i class="bi bi-calendar"></i> <?= formatDate($match['loading_date']) ?><br>
                                                <i class="bi bi-box"></i> <?= number_format($match['weight']) ?> kg<br>
                                                <i class="bi bi-building"></i> <?= h($match['shipper_company']) ?>
                                                <i class="bi bi-star-fill text-warning"></i> <?= number_format($match['shipper_rating'], 1) ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-4 text-end">
                                    <?php if ($match['match_type'] === 'freight'): ?>
                                        <a href="/view-vehicle.php?id=<?= $match['vehicle_id'] ?>" class="btn btn-success">
                                            <i class="bi bi-eye"></i> Voir le véhicule
                                        </a>
                                    <?php else: ?>
                                        <a href="/view-freight.php?id=<?= $match['freight_id'] ?>" class="btn btn-primary">
                                            <i class="bi bi-eye"></i> Voir le fret
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- How it works -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h5 class="mb-3"><i class="bi bi-info-circle"></i> Comment fonctionne le matching ?</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="bi bi-geo-alt-fill text-primary" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">Géolocalisation</h6>
                                <small class="text-muted">Correspondance des villes de départ et d'arrivée</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="bi bi-calendar-check text-success" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">Dates</h6>
                                <small class="text-muted">Proximité des dates de disponibilité</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="bi bi-truck text-info" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">Véhicule</h6>
                                <small class="text-muted">Type de véhicule et capacité</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <i class="bi bi-star-fill text-warning" style="font-size: 2rem;"></i>
                                <h6 class="mt-2">Réputation</h6>
                                <small class="text-muted">Note et fiabilité du partenaire</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
