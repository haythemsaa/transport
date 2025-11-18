<?php
require_once 'config/config.php';
requireLogin();

$pageTitle = 'Analytics & Statistiques';
$user = $_SESSION['user'];

try {
    $db = Database::getInstance()->getConnection();

    // Monthly stats for charts
    $monthlyStats = [];
    for ($i = 11; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $monthlyStats[$month] = [
            'offers' => 0,
            'transactions' => 0,
            'revenue' => 0
        ];
    }

    // Get monthly offers count
    if (isShipper()) {
        $stmt = $db->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
            FROM freight_offers
            WHERE user_id = ? AND deleted_at IS NULL
                AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY month
        ");
        $stmt->execute([$user['id']]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (isset($monthlyStats[$row['month']])) {
                $monthlyStats[$row['month']]['offers'] = (int)$row['count'];
            }
        }
    }

    if (isTransporter()) {
        $stmt = $db->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as count
            FROM vehicle_offers
            WHERE user_id = ? AND deleted_at IS NULL
                AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY month
        ");
        $stmt->execute([$user['id']]);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (isset($monthlyStats[$row['month']])) {
                $monthlyStats[$row['month']]['offers'] = (int)$row['count'];
            }
        }
    }

    // Get monthly transactions
    $stmt = $db->prepare("
        SELECT DATE_FORMAT(created_at, '%Y-%m') as month,
               COUNT(*) as count,
               SUM(amount) as revenue
        FROM transactions
        WHERE (shipper_id = ? OR transporter_id = ?)
            AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
        GROUP BY month
    ");
    $stmt->execute([$user['id'], $user['id']]);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($monthlyStats[$row['month']])) {
            $monthlyStats[$row['month']]['transactions'] = (int)$row['count'];
            $monthlyStats[$row['month']]['revenue'] = (float)$row['revenue'];
        }
    }

    // Current stats
    $stats = [];

    // Total offers
    if (isShipper()) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM freight_offers WHERE user_id = ? AND deleted_at IS NULL");
        $stmt->execute([$user['id']]);
        $stats['total_offers'] = $stmt->fetchColumn();
    } elseif (isTransporter()) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM vehicle_offers WHERE user_id = ? AND deleted_at IS NULL");
        $stmt->execute([$user['id']]);
        $stats['total_offers'] = $stmt->fetchColumn();
    }

    // Active offers
    if (isShipper()) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM freight_offers WHERE user_id = ? AND status = 'active' AND deleted_at IS NULL");
        $stmt->execute([$user['id']]);
        $stats['active_offers'] = $stmt->fetchColumn();
    } elseif (isTransporter()) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM vehicle_offers WHERE user_id = ? AND status = 'available' AND deleted_at IS NULL");
        $stmt->execute([$user['id']]);
        $stats['active_offers'] = $stmt->fetchColumn();
    }

    // Total transactions
    $stmt = $db->prepare("SELECT COUNT(*) FROM transactions WHERE shipper_id = ? OR transporter_id = ?");
    $stmt->execute([$user['id'], $user['id']]);
    $stats['total_transactions'] = $stmt->fetchColumn();

    // Total revenue
    $stmt = $db->prepare("SELECT SUM(amount) FROM transactions WHERE (shipper_id = ? OR transporter_id = ?) AND status = 'completed'");
    $stmt->execute([$user['id'], $user['id']]);
    $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;

    // Average rating
    $stats['rating'] = $user['rating'];
    $stats['total_ratings'] = $user['total_ratings'];

    // Top routes
    if (isShipper()) {
        $stmt = $db->prepare("
            SELECT
                CONCAT(loading_city, ' → ', delivery_city) as route,
                COUNT(*) as count
            FROM freight_offers
            WHERE user_id = ? AND deleted_at IS NULL
            GROUP BY loading_city, delivery_city
            ORDER BY count DESC
            LIMIT 5
        ");
        $stmt->execute([$user['id']]);
        $topRoutes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $db->prepare("
            SELECT
                CONCAT(departure_city, ' → ', destination_city) as route,
                COUNT(*) as count
            FROM vehicle_offers
            WHERE user_id = ? AND deleted_at IS NULL
            GROUP BY departure_city, destination_city
            ORDER BY count DESC
            LIMIT 5
        ");
        $stmt->execute([$user['id']]);
        $topRoutes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    logError('Analytics error', ['error' => $e->getMessage()]);
    $monthlyStats = [];
    $stats = [];
    $topRoutes = [];
}

include 'includes/header.php';
?>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-graph-up"></i> Analytics & Statistiques</h1>
            <p class="text-muted">Suivez vos performances et analysez votre activité</p>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-list-ul text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-3 mb-0"><?= number_format($stats['total_offers']) ?></h3>
                    <p class="text-muted mb-0">Total offres</p>
                    <small class="text-success"><?= $stats['active_offers'] ?> actives</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center">
                    <i class="bi bi-receipt text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-3 mb-0"><?= number_format($stats['total_transactions']) ?></h3>
                    <p class="text-muted mb-0">Transactions</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-currency-euro text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-3 mb-0"><?= formatPrice($stats['total_revenue']) ?></h3>
                    <p class="text-muted mb-0">Chiffre d'affaires</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-info">
                <div class="card-body text-center">
                    <i class="bi bi-star-fill text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-3 mb-0"><?= number_format($stats['rating'], 2) ?></h3>
                    <p class="text-muted mb-0">Note moyenne</p>
                    <small class="text-muted"><?= $stats['total_ratings'] ?> évaluations</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Offers Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Évolution des offres (12 mois)</h5>
                </div>
                <div class="card-body">
                    <canvas id="offersChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Revenue Chart -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-currency-euro"></i> Chiffre d'affaires (12 mois)</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Routes & Transactions -->
    <div class="row">
        <!-- Top Routes -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-signpost-2"></i> Top 5 trajets</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($topRoutes)): ?>
                        <p class="text-muted text-center my-4">Aucun trajet enregistré</p>
                    <?php else: ?>
                        <canvas id="routesChart" height="250"></canvas>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Activity Summary -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Activité récente</h5>
                </div>
                <div class="card-body">
                    <?php
                    $stmt = $db->prepare("
                        SELECT action, details, created_at
                        FROM activity_logs
                        WHERE user_id = ?
                        ORDER BY created_at DESC
                        LIMIT 10
                    ");
                    $stmt->execute([$user['id']]);
                    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php if (empty($activities)): ?>
                        <p class="text-muted text-center my-4">Aucune activité récente</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($activities as $activity): ?>
                                <div class="list-group-item px-0 py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <small><strong><?= h($activity['action']) ?></strong></small>
                                        </div>
                                        <small class="text-muted"><?= timeAgo($activity['created_at']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Prepare data
const months = <?= json_encode(array_keys($monthlyStats)) ?>;
const monthLabels = months.map(m => {
    const [year, month] = m.split('-');
    return new Date(year, month - 1).toLocaleDateString('fr-FR', { month: 'short', year: 'numeric' });
});
const offersData = <?= json_encode(array_column($monthlyStats, 'offers')) ?>;
const transactionsData = <?= json_encode(array_column($monthlyStats, 'transactions')) ?>;
const revenueData = <?= json_encode(array_column($monthlyStats, 'revenue')) ?>;

// Offers Chart
new Chart(document.getElementById('offersChart'), {
    type: 'line',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Offres publiées',
            data: offersData,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    }
});

// Revenue Chart
new Chart(document.getElementById('revenueChart'), {
    type: 'bar',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Chiffre d\'affaires (€)',
            data: revenueData,
            backgroundColor: '#198754',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

<?php if (!empty($topRoutes)): ?>
// Routes Chart
new Chart(document.getElementById('routesChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($topRoutes, 'route')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($topRoutes, 'count')) ?>,
            backgroundColor: [
                '#0d6efd',
                '#198754',
                '#ffc107',
                '#dc3545',
                '#6c757d'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>
