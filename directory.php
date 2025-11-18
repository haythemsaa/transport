<?php
require_once 'config/config.php';
require_once 'config/database.php';

$pageTitle = 'Annuaire professionnel';

$filters = [
    'user_type' => $_GET['user_type'] ?? '',
    'city' => $_GET['city'] ?? '',
    'country' => $_GET['country'] ?? '',
    'min_rating' => $_GET['min_rating'] ?? '',
];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$userModel = new User();

// Si on affiche un utilisateur spécifique
if (isset($_GET['user'])) {
    $viewUser = $userModel->getById((int)$_GET['user']);
    if ($viewUser) {
        $stats = $userModel->getStats($viewUser['id']);
        $ratingModel = new Rating();
        $ratingsResult = $ratingModel->getUserRatings($viewUser['id']);
        $ratingStats = $ratingModel->getRatingStats($viewUser['id']);
    }
} else {
    $results = $userModel->search($filters, $page);
}

include 'includes/header.php';
?>

<div class="container my-4">
    <?php if (isset($viewUser) && $viewUser): ?>
        <!-- Profil utilisateur détaillé -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Accueil</a></li>
                <li class="breadcrumb-item"><a href="/directory.php">Annuaire</a></li>
                <li class="breadcrumb-item active"><?= h($viewUser['company_name']) ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-lg-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <?php if ($viewUser['profile_image']): ?>
                            <img src="<?= h($viewUser['profile_image']) ?>" alt="Profile" class="rounded-circle profile-img mb-3">
                        <?php else: ?>
                            <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px;">
                                <i class="bi bi-building fs-1"></i>
                            </div>
                        <?php endif; ?>

                        <h3><?= h($viewUser['company_name']) ?></h3>
                        <p class="text-muted mb-2">
                            <?php
                                $types = [
                                    'transporter' => 'Transporteur',
                                    'shipper' => 'Chargeur',
                                    'both' => 'Transporteur & Chargeur'
                                ];
                                echo $types[$viewUser['user_type']] ?? $viewUser['user_type'];
                            ?>
                        </p>

                        <p class="mb-2">
                            <i class="bi bi-geo-alt"></i>
                            <?= h($viewUser['city']) ?>, <?= h($viewUser['country']) ?>
                        </p>

                        <?php if ($viewUser['rating'] > 0): ?>
                            <div class="mb-3">
                                <div class="text-warning fs-4">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= round($viewUser['rating'])): ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php else: ?>
                                            <i class="bi bi-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <h4><?= number_format($viewUser['rating'], 2) ?></h4>
                                <small class="text-muted"><?= $viewUser['total_ratings'] ?> évaluation(s)</small>
                            </div>
                        <?php endif; ?>

                        <?= getStatusBadge($viewUser['status']) ?>

                        <?php if (isLoggedIn() && $viewUser['id'] != $_SESSION['user_id']): ?>
                            <hr>
                            <a href="/messages.php?user_id=<?= $viewUser['id'] ?>" class="btn btn-primary w-100">
                                <i class="bi bi-chat-dots"></i> Envoyer un message
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="bi bi-graph-up"></i> Statistiques</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Offres de fret</span>
                            <strong><?= $stats['total_freight_offers'] ?? 0 ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Offres de véhicules</span>
                            <strong><?= $stats['total_vehicle_offers'] ?? 0 ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Transactions</span>
                            <strong><?= $stats['total_transactions'] ?? 0 ?></strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Complétées</span>
                            <strong><?= $stats['completed_transactions'] ?? 0 ?></strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Détails de notation -->
                <?php if ($ratingStats && $ratingStats['total_ratings'] > 0): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-star"></i> Évaluations</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Répartition des notes</h6>
                                    <?php
                                        for ($stars = 5; $stars >= 1; $stars--) {
                                            $count = $ratingStats[$stars === 5 ? 'five_stars' : ($stars === 4 ? 'four_stars' : ($stars === 3 ? 'three_stars' : ($stars === 2 ? 'two_stars' : 'one_star')))];
                                            $percentage = $ratingStats['total_ratings'] > 0 ? ($count / $ratingStats['total_ratings']) * 100 : 0;
                                    ?>
                                        <div class="d-flex align-items-center mb-2">
                                            <span style="width: 80px;"><?= $stars ?> étoiles</span>
                                            <div class="progress flex-grow-1 mx-2" style="height: 20px;">
                                                <div class="progress-bar bg-warning" style="width: <?= $percentage ?>%"></div>
                                            </div>
                                            <span style="width: 50px;"><?= $count ?></span>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="col-md-6">
                                    <h6>Critères détaillés</h6>
                                    <div class="mb-2">
                                        <strong>Ponctualité:</strong>
                                        <span class="float-end"><?= number_format($ratingStats['avg_punctuality'], 1) ?>/5</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Communication:</strong>
                                        <span class="float-end"><?= number_format($ratingStats['avg_communication'], 1) ?>/5</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Professionnalisme:</strong>
                                        <span class="float-end"><?= number_format($ratingStats['avg_professionalism'], 1) ?>/5</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Commentaires -->
                <?php if ($ratingsResult['success'] && !empty($ratingsResult['data'])): ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-chat-quote"></i> Commentaires</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($ratingsResult['data'] as $rating): ?>
                                <div class="mb-3 pb-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1"><?= h($rating['rater_name']) ?></h6>
                                            <div class="text-warning">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= $rating['rating']): ?>
                                                        <i class="bi bi-star-fill"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-star"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted"><?= timeAgo($rating['created_at']) ?></small>
                                    </div>
                                    <?php if ($rating['comment']): ?>
                                        <p class="mb-0"><?= nl2br(h($rating['comment'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- Liste de l'annuaire -->
        <div class="row">
            <div class="col-lg-3">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtres</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="">
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select class="form-select form-select-sm" name="user_type">
                                    <option value="">Tous</option>
                                    <option value="transporter" <?= $filters['user_type'] === 'transporter' ? 'selected' : '' ?>>Transporteurs</option>
                                    <option value="shipper" <?= $filters['user_type'] === 'shipper' ? 'selected' : '' ?>>Chargeurs</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ville</label>
                                <input type="text" class="form-control form-control-sm" name="city"
                                       value="<?= h($filters['city']) ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Pays</label>
                                <select class="form-select form-select-sm" name="country">
                                    <option value="">Tous les pays</option>
                                    <option value="France" <?= $filters['country'] === 'France' ? 'selected' : '' ?>>France</option>
                                    <option value="Belgique" <?= $filters['country'] === 'Belgique' ? 'selected' : '' ?>>Belgique</option>
                                    <option value="Allemagne" <?= $filters['country'] === 'Allemagne' ? 'selected' : '' ?>>Allemagne</option>
                                    <option value="Espagne" <?= $filters['country'] === 'Espagne' ? 'selected' : '' ?>>Espagne</option>
                                    <option value="Italie" <?= $filters['country'] === 'Italie' ? 'selected' : '' ?>>Italie</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Note minimum</label>
                                <select class="form-select form-select-sm" name="min_rating">
                                    <option value="">Toutes</option>
                                    <option value="4" <?= $filters['min_rating'] == '4' ? 'selected' : '' ?>>4+ étoiles</option>
                                    <option value="3" <?= $filters['min_rating'] == '3' ? 'selected' : '' ?>>3+ étoiles</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 btn-sm">
                                <i class="bi bi-search"></i> Rechercher
                            </button>
                            <a href="/directory.php" class="btn btn-outline-secondary w-100 btn-sm mt-2">
                                Réinitialiser
                            </a>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-9">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="bi bi-people"></i> Annuaire professionnel</h2>
                    <?php if ($results['success']): ?>
                        <span class="text-muted"><?= number_format($results['total']) ?> professionnel(s)</span>
                    <?php endif; ?>
                </div>

                <?php if ($results['success'] && !empty($results['data'])): ?>
                    <div class="row g-3">
                        <?php foreach ($results['data'] as $member): ?>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start">
                                            <?php if ($member['profile_image']): ?>
                                                <img src="<?= h($member['profile_image']) ?>" class="rounded-circle me-3" width="60" height="60">
                                            <?php else: ?>
                                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                                    <i class="bi bi-building fs-4"></i>
                                                </div>
                                            <?php endif; ?>

                                            <div class="flex-grow-1">
                                                <h5 class="mb-1"><?= h($member['company_name']) ?></h5>
                                                <p class="text-muted small mb-2">
                                                    <?php
                                                        $types = [
                                                            'transporter' => 'Transporteur',
                                                            'shipper' => 'Chargeur',
                                                            'both' => 'Transporteur & Chargeur'
                                                        ];
                                                        echo $types[$member['user_type']] ?? $member['user_type'];
                                                    ?>
                                                </p>

                                                <p class="mb-2 small">
                                                    <i class="bi bi-geo-alt"></i>
                                                    <?= h($member['city']) ?>, <?= h($member['country']) ?>
                                                </p>

                                                <?php if ($member['rating'] > 0): ?>
                                                    <div class="mb-2">
                                                        <div class="text-warning">
                                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                <?php if ($i <= round($member['rating'])): ?>
                                                                    <i class="bi bi-star-fill"></i>
                                                                <?php else: ?>
                                                                    <i class="bi bi-star"></i>
                                                                <?php endif; ?>
                                                            <?php endfor; ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            <?= number_format($member['rating'], 1) ?> (<?= $member['total_ratings'] ?> avis)
                                                        </small>
                                                    </div>
                                                <?php endif; ?>

                                                <div class="d-flex gap-2">
                                                    <a href="/directory.php?user=<?= $member['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> Voir le profil
                                                    </a>
                                                    <?php if (isLoggedIn()): ?>
                                                        <a href="/messages.php?user_id=<?= $member['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-chat-dots"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
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

                <?php else: ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                            <h5>Aucun résultat</h5>
                            <p class="text-muted">Essayez de modifier vos critères de recherche</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
