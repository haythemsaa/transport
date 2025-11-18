<?php
require_once '../config/config.php';
requireLogin();

// Simple admin check - in production, add an is_admin column to users table
$adminEmails = ['admin@teleroute-marketplace.com', 'contact@tee.fr'];
if (!in_array($_SESSION['user']['email'], $adminEmails)) {
    http_response_code(403);
    setFlash('danger', 'Accès administrateur requis');
    redirect('/dashboard.php');
}

$pageTitle = 'Administration - Dashboard';

try {
    $db = Database::getInstance()->getConnection();

    // Statistiques générales
    $stats = [];

    // Total utilisateurs
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE deleted_at IS NULL");
    $stats['total_users'] = $stmt->fetchColumn();

    // Nouveaux utilisateurs ce mois
    $stmt = $db->query("SELECT COUNT(*) FROM users WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
    $stats['new_users_month'] = $stmt->fetchColumn();

    // Total offres de fret
    $stmt = $db->query("SELECT COUNT(*) FROM freight_offers WHERE deleted_at IS NULL");
    $stats['total_freight'] = $stmt->fetchColumn();

    // Offres de fret actives
    $stmt = $db->query("SELECT COUNT(*) FROM freight_offers WHERE status = 'active' AND deleted_at IS NULL");
    $stats['active_freight'] = $stmt->fetchColumn();

    // Total offres de véhicules
    $stmt = $db->query("SELECT COUNT(*) FROM vehicle_offers WHERE deleted_at IS NULL");
    $stats['total_vehicles'] = $stmt->fetchColumn();

    // Offres de véhicules disponibles
    $stmt = $db->query("SELECT COUNT(*) FROM vehicle_offers WHERE status = 'available' AND deleted_at IS NULL");
    $stats['available_vehicles'] = $stmt->fetchColumn();

    // Total transactions
    $stmt = $db->query("SELECT COUNT(*) FROM transactions");
    $stats['total_transactions'] = $stmt->fetchColumn();

    // Total messages
    $stmt = $db->query("SELECT COUNT(*) FROM messages");
    $stats['total_messages'] = $stmt->fetchColumn();

    // Activité récente
    $recentActivity = $db->query("
        SELECT * FROM activity_logs
        ORDER BY created_at DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Utilisateurs récents
    $recentUsers = $db->query("
        SELECT id, company_name, email, user_type, rating, created_at
        FROM users
        WHERE deleted_at IS NULL
        ORDER BY created_at DESC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    logError('Admin dashboard error', ['error' => $e->getMessage()]);
    $stats = [];
    $recentActivity = [];
    $recentUsers = [];
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-shield-lock"></i> Administration</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard.php">Accueil</a></li>
                    <li class="breadcrumb-item active">Administration</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Navigation admin -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group" role="group">
                <a href="/admin/index.php" class="btn btn-primary">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="/admin/users.php" class="btn btn-outline-primary">
                    <i class="bi bi-people"></i> Utilisateurs
                </a>
                <a href="/admin/offers.php" class="btn btn-outline-primary">
                    <i class="bi bi-list-ul"></i> Offres
                </a>
            </div>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-people text-primary" style="font-size: 3rem;"></i>
                    <h3 class="mt-3 mb-0"><?= number_format($stats['total_users']) ?></h3>
                    <p class="text-muted mb-0">Utilisateurs</p>
                    <small class="text-success">+<?= $stats['new_users_month'] ?> ce mois</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam text-success" style="font-size: 3rem;"></i>
                    <h3 class="mt-3 mb-0"><?= number_format($stats['total_freight']) ?></h3>
                    <p class="text-muted mb-0">Offres de fret</p>
                    <small class="text-success"><?= $stats['active_freight'] ?> actives</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-info">
                <div class="card-body text-center">
                    <i class="bi bi-truck text-info" style="font-size: 3rem;"></i>
                    <h3 class="mt-3 mb-0"><?= number_format($stats['total_vehicles']) ?></h3>
                    <p class="text-muted mb-0">Véhicules</p>
                    <small class="text-success"><?= $stats['available_vehicles'] ?> disponibles</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-receipt text-warning" style="font-size: 3rem;"></i>
                    <h3 class="mt-3 mb-0"><?= number_format($stats['total_transactions']) ?></h3>
                    <p class="text-muted mb-0">Transactions</p>
                    <small class="text-muted"><?= number_format($stats['total_messages']) ?> messages</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Utilisateurs récents -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Utilisateurs récents</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Entreprise</th>
                                    <th>Type</th>
                                    <th>Note</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentUsers as $user): ?>
                                    <tr>
                                        <td>
                                            <strong><?= h($user['company_name']) ?></strong><br>
                                            <small class="text-muted"><?= h($user['email']) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?= ucfirst($user['user_type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <i class="bi bi-star-fill text-warning"></i>
                                            <?= number_format($user['rating'], 2) ?>
                                        </td>
                                        <td>
                                            <small><?= timeAgo($user['created_at']) ?></small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="/admin/users.php" class="btn btn-sm btn-primary">
                        Voir tous les utilisateurs <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Activité récente -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-activity"></i> Activité récente</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($recentActivity)): ?>
                        <p class="text-muted text-center my-4">Aucune activité récente</p>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentActivity as $activity): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= h($activity['action']) ?></strong>
                                            <?php if ($activity['details']): ?>
                                                <br>
                                                <small class="text-muted">
                                                    <?php
                                                    $details = json_decode($activity['details'], true);
                                                    if ($details) {
                                                        echo h(implode(', ', array_map(
                                                            function($k, $v) { return "$k: $v"; },
                                                            array_keys($details),
                                                            array_values($details)
                                                        )));
                                                    }
                                                    ?>
                                                </small>
                                            <?php endif; ?>
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

<?php include '../includes/footer.php'; ?>
