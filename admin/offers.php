<?php
require_once '../config/config.php';
requireLogin();

// Simple admin check
$adminEmails = ['admin@teleroute-marketplace.com', 'contact@tee.fr'];
if (!in_array($_SESSION['user']['email'], $adminEmails)) {
    http_response_code(403);
    setFlash('danger', 'Accès administrateur requis');
    redirect('/dashboard.php');
}

$pageTitle = 'Administration - Offres';

try {
    $db = Database::getInstance()->getConnection();

    // Type d'offre
    $offerType = $_GET['offer_type'] ?? 'freight';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    if ($offerType === 'freight') {
        // Compter les offres de fret
        $countStmt = $db->query("SELECT COUNT(*) FROM freight_offers WHERE deleted_at IS NULL");
        $totalOffers = $countStmt->fetchColumn();
        $totalPages = ceil($totalOffers / $perPage);

        // Récupérer les offres de fret
        $stmt = $db->prepare("
            SELECT
                fo.*,
                u.company_name as user_company
            FROM freight_offers fo
            JOIN users u ON fo.user_id = u.id
            WHERE fo.deleted_at IS NULL
            ORDER BY fo.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute();
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // Compter les offres de véhicules
        $countStmt = $db->query("SELECT COUNT(*) FROM vehicle_offers WHERE deleted_at IS NULL");
        $totalOffers = $countStmt->fetchColumn();
        $totalPages = ceil($totalOffers / $perPage);

        // Récupérer les offres de véhicules
        $stmt = $db->prepare("
            SELECT
                vo.*,
                u.company_name as user_company
            FROM vehicle_offers vo
            JOIN users u ON vo.user_id = u.id
            WHERE vo.deleted_at IS NULL
            ORDER BY vo.created_at DESC
            LIMIT $perPage OFFSET $offset
        ");
        $stmt->execute();
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (Exception $e) {
    logError('Admin offers error', ['error' => $e->getMessage()]);
    $offers = [];
    $totalPages = 0;
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-list-ul"></i> Gestion des offres</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard.php">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="/admin/index.php">Administration</a></li>
                    <li class="breadcrumb-item active">Offres</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Navigation admin -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group" role="group">
                <a href="/admin/index.php" class="btn btn-outline-primary">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <a href="/admin/users.php" class="btn btn-outline-primary">
                    <i class="bi bi-people"></i> Utilisateurs
                </a>
                <a href="/admin/offers.php" class="btn btn-primary">
                    <i class="bi bi-list-ul"></i> Offres
                </a>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="btn-group w-100" role="group">
                        <a href="?offer_type=freight" class="btn btn-<?= $offerType === 'freight' ? 'primary' : 'outline-primary' ?>">
                            <i class="bi bi-box-seam"></i> Offres de fret
                        </a>
                        <a href="?offer_type=vehicle" class="btn btn-<?= $offerType === 'vehicle' ? 'success' : 'outline-success' ?>">
                            <i class="bi bi-truck"></i> Offres de véhicules
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Résultats -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <?= number_format($totalOffers) ?> offre(s)
                        </h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($offers)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-3">Aucune offre trouvée</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Entreprise</th>
                                        <?php if ($offerType === 'freight'): ?>
                                            <th>Chargement</th>
                                            <th>Livraison</th>
                                            <th>Poids</th>
                                        <?php else: ?>
                                            <th>Départ</th>
                                            <th>Destination</th>
                                            <th>Type</th>
                                        <?php endif; ?>
                                        <th>Prix</th>
                                        <th>Statut</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($offers as $offer): ?>
                                        <tr>
                                            <td><?= $offer['id'] ?></td>
                                            <td>
                                                <strong><?= h($offer['user_company']) ?></strong>
                                            </td>
                                            <?php if ($offerType === 'freight'): ?>
                                                <td>
                                                    <?= h($offer['loading_city']) ?>, <?= h($offer['loading_country']) ?><br>
                                                    <small class="text-muted"><?= formatDate($offer['loading_date']) ?></small>
                                                </td>
                                                <td>
                                                    <?= h($offer['delivery_city']) ?>, <?= h($offer['delivery_country']) ?><br>
                                                    <small class="text-muted"><?= formatDate($offer['delivery_date']) ?></small>
                                                </td>
                                                <td><?= number_format($offer['weight']) ?> kg</td>
                                            <?php else: ?>
                                                <td>
                                                    <?= h($offer['departure_city']) ?>, <?= h($offer['departure_country']) ?><br>
                                                    <small class="text-muted"><?= formatDate($offer['available_date']) ?></small>
                                                </td>
                                                <td>
                                                    <?= h($offer['destination_city']) ?>, <?= h($offer['destination_country']) ?>
                                                </td>
                                                <td><?= h($offer['vehicle_type']) ?></td>
                                            <?php endif; ?>
                                            <td>
                                                <?php if ($offerType === 'freight'): ?>
                                                    <?= formatPrice($offer['price']) ?>
                                                <?php else: ?>
                                                    <?= formatPrice($offer['price_per_km']) ?>/km
                                                <?php endif; ?>
                                            </td>
                                            <td><?= getStatusBadge($offer['status']) ?></td>
                                            <td>
                                                <small><?= timeAgo($offer['created_at']) ?></small>
                                            </td>
                                            <td>
                                                <?php if ($offerType === 'freight'): ?>
                                                    <a href="/view-freight.php?id=<?= $offer['id'] ?>"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Voir l'offre">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="/view-vehicle.php?id=<?= $offer['id'] ?>"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Voir l'offre">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="card-footer">
                        <nav aria-label="Navigation des offres">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&offer_type=<?= $offerType ?>">
                                            Précédent
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if ($i == $page || $i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&offer_type=<?= $offerType ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php elseif (abs($i - $page) == 3): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&offer_type=<?= $offerType ?>">
                                            Suivant
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
