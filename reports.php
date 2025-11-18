<?php
require_once 'config/config.php';
requireLogin();

$pageTitle = 'Rapports et Exports';
$user = $_SESSION['user'];

try {
    $db = Database::getInstance()->getConnection();

    // Get date range
    $startDate = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
    $endDate = $_GET['end_date'] ?? date('Y-m-d'); // Today

    // Get stats for the period
    $stats = [];

    // Total offers
    if (isShipper() || $user['user_type'] === 'both') {
        $stmt = $db->prepare("
            SELECT COUNT(*) as total, SUM(price) as total_value
            FROM freight_offers
            WHERE user_id = ?
                AND created_at BETWEEN ? AND ?
                AND deleted_at IS NULL
        ");
        $stmt->execute([$user['id'], $startDate, $endDate]);
        $stats['freight'] = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (isTransporter() || $user['user_type'] === 'both') {
        $stmt = $db->prepare("
            SELECT COUNT(*) as total
            FROM vehicle_offers
            WHERE user_id = ?
                AND created_at BETWEEN ? AND ?
                AND deleted_at IS NULL
        ");
        $stmt->execute([$user['id'], $startDate, $endDate]);
        $stats['vehicles'] = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Transactions
    $stmt = $db->prepare("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as completed_value,
            SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_value
        FROM transactions
        WHERE (shipper_id = ? OR transporter_id = ?)
            AND created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$user['id'], $user['id'], $startDate, $endDate]);
    $stats['transactions'] = $stmt->fetch(PDO::FETCH_ASSOC);

    // Messages sent/received
    $stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM messages
        WHERE (sender_id = ? OR receiver_id = ?)
            AND created_at BETWEEN ? AND ?
    ");
    $stmt->execute([$user['id'], $user['id'], $startDate, $endDate]);
    $stats['messages'] = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    logError('Reports error', ['error' => $e->getMessage()]);
    $stats = [];
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-file-earmark-bar-graph"></i> Rapports et Exports</h1>
            <p class="text-muted">Générez des rapports détaillés de votre activité</p>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Date de début</label>
                            <input type="date" class="form-control" name="start_date"
                                   value="<?= h($startDate) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date de fin</label>
                            <input type="date" class="form-control" name="end_date"
                                   value="<?= h($endDate) ?>">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-funnel"></i> Filtrer
                            </button>
                        </div>
                    </form>

                    <div class="btn-group mt-3" role="group">
                        <a href="?start_date=<?= date('Y-m-d') ?>&end_date=<?= date('Y-m-d') ?>"
                           class="btn btn-sm btn-outline-secondary">Aujourd'hui</a>
                        <a href="?start_date=<?= date('Y-m-01') ?>&end_date=<?= date('Y-m-d') ?>"
                           class="btn btn-sm btn-outline-secondary">Ce mois</a>
                        <a href="?start_date=<?= date('Y-01-01') ?>&end_date=<?= date('Y-m-d') ?>"
                           class="btn btn-sm btn-outline-secondary">Cette année</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row g-4 mb-4">
        <?php if (isset($stats['freight'])): ?>
        <div class="col-md-3">
            <div class="card shadow-sm border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= $stats['freight']['total'] ?></h3>
                    <p class="text-muted mb-1">Offres de fret</p>
                    <small class="text-success">
                        <?= formatPrice($stats['freight']['total_value'] ?? 0) ?>
                    </small>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isset($stats['vehicles'])): ?>
        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center">
                    <i class="bi bi-truck text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= $stats['vehicles']['total'] ?></h3>
                    <p class="text-muted mb-0">Véhicules publiés</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="col-md-3">
            <div class="card shadow-sm border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-receipt text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= $stats['transactions']['total'] ?? 0 ?></h3>
                    <p class="text-muted mb-1">Transactions</p>
                    <small class="text-success">
                        <?= formatPrice($stats['transactions']['completed_value'] ?? 0) ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-info">
                <div class="card-body text-center">
                    <i class="bi bi-chat-dots text-info" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= $stats['messages']['total'] ?? 0 ?></h3>
                    <p class="text-muted mb-0">Messages échangés</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-spreadsheet"></i> Exports CSV</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Téléchargez vos données au format CSV pour Excel/Google Sheets</p>

                    <div class="list-group">
                        <?php if (isShipper() || $user['user_type'] === 'both'): ?>
                        <a href="/exports/freight-csv.php?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-box-seam text-primary"></i>
                                    <strong>Mes offres de fret</strong>
                                    <div class="text-muted small">Toutes vos offres de fret</div>
                                </div>
                                <i class="bi bi-download"></i>
                            </div>
                        </a>
                        <?php endif; ?>

                        <?php if (isTransporter() || $user['user_type'] === 'both'): ?>
                        <a href="/exports/vehicles-csv.php?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-truck text-success"></i>
                                    <strong>Mes véhicules</strong>
                                    <div class="text-muted small">Toutes vos offres de véhicules</div>
                                </div>
                                <i class="bi bi-download"></i>
                            </div>
                        </a>
                        <?php endif; ?>

                        <a href="/exports/transactions-csv.php?start_date=<?= $startDate ?>&end_date=<?= $endDate ?>"
                           class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-receipt text-warning"></i>
                                    <strong>Mes transactions</strong>
                                    <div class="text-muted small">Historique complet des transactions</div>
                                </div>
                                <i class="bi bi-download"></i>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-pdf"></i> Rapports PDF</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Générez des rapports professionnels au format PDF</p>

                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action" onclick="alert('Fonctionnalité premium - Disponible prochainement'); return false;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-graph-up text-success"></i>
                                    <strong>Rapport d'activité mensuel</strong>
                                    <div class="text-muted small">Vue d'ensemble de vos performances</div>
                                </div>
                                <span class="badge bg-warning">Premium</span>
                            </div>
                        </a>

                        <a href="#" class="list-group-item list-group-item-action" onclick="alert('Fonctionnalité premium - Disponible prochainement'); return false;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-currency-euro text-warning"></i>
                                    <strong>Rapport financier</strong>
                                    <div class="text-muted small">Revenus, dépenses, marges</div>
                                </div>
                                <span class="badge bg-warning">Premium</span>
                            </div>
                        </a>

                        <a href="#" class="list-group-item list-group-item-action" onclick="alert('Fonctionnalité premium - Disponible prochainement'); return false;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-star text-info"></i>
                                    <strong>Rapport de performance</strong>
                                    <div class="text-muted small">Notes, délais, satisfaction</div>
                                </div>
                                <span class="badge bg-warning">Premium</span>
                            </div>
                        </a>
                    </div>

                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle"></i>
                        Passez à <a href="/pricing.php">Premium</a> pour générer des rapports PDF personnalisés
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scheduled Reports -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="bi bi-calendar-check"></i> Rapports programmés</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Recevez automatiquement vos rapports par email</p>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="weeklyReport">
                        <label class="form-check-label" for="weeklyReport">
                            <strong>Rapport hebdomadaire</strong> - Tous les lundis à 9h
                        </label>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="monthlyReport">
                        <label class="form-check-label" for="monthlyReport">
                            <strong>Rapport mensuel</strong> - Le 1er de chaque mois
                        </label>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="quarterlyReport">
                        <label class="form-check-label" for="quarterlyReport">
                            <strong>Rapport trimestriel</strong> - Chaque début de trimestre
                        </label>
                    </div>

                    <button class="btn btn-secondary" disabled>
                        <i class="bi bi-check-circle"></i> Enregistrer les préférences (Premium)
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
