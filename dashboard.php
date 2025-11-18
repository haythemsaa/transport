<?php
require_once 'config/config.php';
require_once 'config/database.php';

// Vérifier l'authentification
if (!isLoggedIn()) {
    setFlash('warning', 'Veuillez vous connecter pour accéder à votre tableau de bord');
    redirect('/login.php');
}

$pageTitle = 'Tableau de bord';
$userId = $_SESSION['user_id'];

// Obtenir les modèles
$userModel = new User();
$freightModel = new FreightOffer();
$vehicleModel = new VehicleOffer();
$messageModel = new Message();
$transactionModel = new Transaction();

// Obtenir les statistiques de l'utilisateur
$userStats = $userModel->getStats($userId);
$unreadMessages = $messageModel->getUnreadCount($userId);

// Obtenir les dernières offres de l'utilisateur
$myFreightOffers = $freightModel->getByUser($userId, 1, 5);
$myVehicleOffers = $vehicleModel->getByUser($userId, 1, 5);

// Obtenir les dernières transactions
$myTransactions = $transactionModel->getUserTransactions($userId, 1, 5);

// Obtenir les données de l'utilisateur
$user = $userModel->getById($userId);

include 'includes/header.php';
?>

<div class="container my-4">
    <!-- En-tête du tableau de bord -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="bi bi-speedometer2 text-primary"></i>
                Tableau de bord
            </h2>
            <p class="text-muted">
                Bienvenue, <strong><?= h($user['company_name']) ?></strong>
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="btn-group" role="group">
                <?php if (isShipper()): ?>
                    <a href="/post-freight.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Publier du fret
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

    <!-- Cartes statistiques -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Offres de fret</h6>
                            <h2 class="mb-0"><?= number_format($userStats['total_freight_offers'] ?? 0) ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-box-seam fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-primary bg-opacity-25 border-0">
                    <a href="/my-offers.php?type=freight" class="text-white text-decoration-none small">
                        <i class="bi bi-eye"></i> Voir toutes mes offres
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Offres véhicules</h6>
                            <h2 class="mb-0"><?= number_format($userStats['total_vehicle_offers'] ?? 0) ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-truck fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-success bg-opacity-25 border-0">
                    <a href="/my-offers.php?type=vehicle" class="text-white text-decoration-none small">
                        <i class="bi bi-eye"></i> Voir tous mes véhicules
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Transactions</h6>
                            <h2 class="mb-0"><?= number_format($userStats['total_transactions'] ?? 0) ?></h2>
                        </div>
                        <div>
                            <i class="bi bi-receipt fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-info bg-opacity-25 border-0">
                    <a href="/transactions.php" class="text-white text-decoration-none small">
                        <i class="bi bi-eye"></i> Voir toutes les transactions
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-uppercase mb-1">Messages</h6>
                            <h2 class="mb-0"><?= number_format($unreadMessages) ?></h2>
                            <small>Non lus</small>
                        </div>
                        <div>
                            <i class="bi bi-chat-dots fs-1 opacity-75"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-warning bg-opacity-25 border-0">
                    <a href="/messages.php" class="text-white text-decoration-none small">
                        <i class="bi bi-envelope"></i> Voir tous les messages
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Profil et Évaluation -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-person-badge"></i> Mon profil
                    </h5>
                </div>
                <div class="card-body text-center">
                    <?php if ($user['profile_image']): ?>
                        <img src="<?= h($user['profile_image']) ?>" alt="Profile" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                    <?php else: ?>
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                            <i class="bi bi-building fs-1"></i>
                        </div>
                    <?php endif; ?>

                    <h5><?= h($user['company_name']) ?></h5>
                    <p class="text-muted mb-2">
                        <?php
                            $types = [
                                'transporter' => 'Transporteur',
                                'shipper' => 'Chargeur',
                                'both' => 'Transporteur & Chargeur'
                            ];
                            echo $types[$user['user_type']] ?? $user['user_type'];
                        ?>
                    </p>

                    <div class="mb-3">
                        <?php if ($user['rating'] > 0): ?>
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <div class="text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= round($user['rating'])): ?>
                                            <i class="bi bi-star-fill"></i>
                                        <?php else: ?>
                                            <i class="bi bi-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <strong><?= number_format($user['rating'], 1) ?></strong>
                            </div>
                            <small class="text-muted"><?= $user['total_ratings'] ?> évaluation(s)</small>
                        <?php else: ?>
                            <p class="text-muted mb-0">Pas encore d'évaluations</p>
                        <?php endif; ?>
                    </div>

                    <div class="mb-2">
                        <i class="bi bi-geo-alt text-primary"></i>
                        <?= h($user['city']) ?>, <?= h($user['country']) ?>
                    </div>

                    <?php if ($user['status'] !== 'active'): ?>
                        <div class="alert alert-warning mt-3">
                            <i class="bi bi-exclamation-triangle"></i>
                            Votre compte est en attente de vérification
                        </div>
                    <?php endif; ?>

                    <a href="/profile.php" class="btn btn-outline-primary w-100 mt-3">
                        <i class="bi bi-pencil"></i> Modifier mon profil
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-activity"></i> Activité récente
                    </h5>
                </div>
                <div class="card-body">
                    <?php if ($myTransactions['success'] && !empty($myTransactions['data'])): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach (array_slice($myTransactions['data'], 0, 5) as $transaction): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">
                                                <?php if ($transaction['freight_title']): ?>
                                                    <i class="bi bi-box-seam text-primary"></i>
                                                    <?= h($transaction['freight_title']) ?>
                                                <?php else: ?>
                                                    <i class="bi bi-truck text-success"></i>
                                                    <?= h($transaction['vehicle_title']) ?>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="mb-1 small text-muted">
                                                <?php if ($transaction['transporter_id'] == $userId): ?>
                                                    Avec: <?= h($transaction['shipper_name']) ?>
                                                <?php else: ?>
                                                    Avec: <?= h($transaction['transporter_name']) ?>
                                                <?php endif; ?>
                                            </p>
                                            <small class="text-muted">
                                                <?= timeAgo($transaction['created_at']) ?>
                                            </small>
                                        </div>
                                        <div class="text-end">
                                            <?= getStatusBadge($transaction['status']) ?>
                                            <div class="mt-1">
                                                <strong><?= formatPrice($transaction['price']) ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="/transactions.php" class="btn btn-sm btn-outline-primary w-100 mt-3">
                            Voir toutes les transactions
                        </a>
                    <?php else: ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                            <p>Aucune transaction pour le moment</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mes offres récentes -->
    <div class="row">
        <?php if (isShipper() && $myFreightOffers['success'] && !empty($myFreightOffers['data'])): ?>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-box-seam text-primary"></i> Mes offres de fret
                    </h5>
                    <a href="/my-offers.php?type=freight" class="btn btn-sm btn-primary">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($myFreightOffers['data'] as $offer): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= h($offer['title']) ?></h6>
                                        <p class="mb-1 small">
                                            <i class="bi bi-geo-alt"></i>
                                            <?= h($offer['loading_city']) ?> → <?= h($offer['delivery_city']) ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i>
                                            <?= formatDate($offer['loading_date']) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <?= getStatusBadge($offer['status']) ?>
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-eye"></i> <?= $offer['views'] ?> vues
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (isTransporter() && $myVehicleOffers['success'] && !empty($myVehicleOffers['data'])): ?>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-truck text-success"></i> Mes offres de véhicules
                    </h5>
                    <a href="/my-offers.php?type=vehicle" class="btn btn-sm btn-success">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($myVehicleOffers['data'] as $offer): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1"><?= h($offer['title']) ?></h6>
                                        <p class="mb-1 small">
                                            <i class="bi bi-truck"></i>
                                            <?= getVehicleTypeLabel($offer['vehicle_type']) ?>
                                        </p>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i>
                                            Dispo: <?= formatDate($offer['available_from']) ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <?= getStatusBadge($offer['status']) ?>
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-eye"></i> <?= $offer['views'] ?> vues
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
