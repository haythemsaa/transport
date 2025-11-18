<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Veuillez vous connecter');
    redirect('/login.php');
}

$pageTitle = 'Mes transactions';
$userId = $_SESSION['user_id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$transactionModel = new Transaction();
$results = $transactionModel->getUserTransactions($userId, $page);

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-receipt"></i> Mes transactions</h2>
        <div class="d-flex align-items-center gap-2">
            <?php if ($results['success'] && $results['total'] > 0): ?>
                <a href="/exports/transactions-csv.php" class="btn btn-outline-secondary">
                    <i class="bi bi-download"></i> Exporter CSV
                </a>
                <span class="badge bg-primary fs-6"><?= $results['total'] ?> transaction(s)</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($results['success'] && !empty($results['data'])): ?>
        <div class="row g-3">
            <?php foreach ($results['data'] as $transaction): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="mb-1">
                                                <?php if ($transaction['freight_title']): ?>
                                                    <i class="bi bi-box-seam text-primary"></i>
                                                    <?= h($transaction['freight_title']) ?>
                                                <?php else: ?>
                                                    <i class="bi bi-truck text-success"></i>
                                                    <?= h($transaction['vehicle_title']) ?>
                                                <?php endif; ?>
                                            </h5>
                                            <p class="text-muted mb-0">
                                                Transaction #<?= $transaction['id'] ?>
                                            </p>
                                        </div>
                                        <?= getStatusBadge($transaction['status']) ?>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <strong>
                                                <?= $transaction['transporter_id'] == $userId ? 'Chargeur:' : 'Transporteur:' ?>
                                            </strong>
                                            <?php if ($transaction['transporter_id'] == $userId): ?>
                                                <?= h($transaction['shipper_name']) ?>
                                            <?php else: ?>
                                                <?= h($transaction['transporter_name']) ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6">
                                            <i class="bi bi-calendar"></i>
                                            <?php if ($transaction['pickup_date']): ?>
                                                Chargement: <?= formatDate($transaction['pickup_date']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <?php if ($transaction['notes']): ?>
                                        <p class="small text-muted mb-0">
                                            <i class="bi bi-chat-left-text"></i>
                                            <?= h($transaction['notes']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="col-md-4 text-md-end">
                                    <h4 class="text-primary mb-2"><?= formatPrice($transaction['price']) ?></h4>
                                    <p class="mb-2">
                                        <span class="badge bg-<?= $transaction['payment_status'] === 'paid' ? 'success' : 'warning' ?>">
                                            <?php
                                                $paymentLabels = [
                                                    'pending' => 'Paiement en attente',
                                                    'paid' => 'Payé',
                                                    'failed' => 'Échec',
                                                    'refunded' => 'Remboursé'
                                                ];
                                                echo $paymentLabels[$transaction['payment_status']] ?? $transaction['payment_status'];
                                            ?>
                                        </span>
                                    </p>
                                    <small class="text-muted d-block mb-2">
                                        Créée: <?= timeAgo($transaction['created_at']) ?>
                                    </small>

                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#transactionModal<?= $transaction['id'] ?>">
                                            <i class="bi bi-eye"></i> Détails
                                        </button>
                                        <?php if ($transaction['transporter_id'] == $userId): ?>
                                            <a href="/messages.php?user_id=<?= $transaction['shipper_id'] ?>" class="btn btn-outline-secondary">
                                                <i class="bi bi-chat-dots"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="/messages.php?user_id=<?= $transaction['transporter_id'] ?>" class="btn btn-outline-secondary">
                                                <i class="bi bi-chat-dots"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal détails -->
                <div class="modal fade" id="transactionModal<?= $transaction['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Détails de la transaction #<?= $transaction['id'] ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <h6>Informations générales</h6>
                                        <p><strong>Statut:</strong> <?= getStatusBadge($transaction['status']) ?></p>
                                        <p><strong>Prix:</strong> <?= formatPrice($transaction['price']) ?></p>
                                        <p><strong>Paiement:</strong> <span class="badge bg-<?= $transaction['payment_status'] === 'paid' ? 'success' : 'warning' ?>"><?= $transaction['payment_status'] ?></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Dates</h6>
                                        <p><strong>Date de chargement:</strong> <?= formatDate($transaction['pickup_date']) ?></p>
                                        <p><strong>Date de livraison:</strong> <?= formatDate($transaction['delivery_date']) ?></p>
                                        <?php if ($transaction['actual_delivery_date']): ?>
                                            <p><strong>Livré le:</strong> <?= formatDateTime($transaction['actual_delivery_date']) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($transaction['notes']): ?>
                                    <hr>
                                    <h6>Notes</h6>
                                    <p><?= nl2br(h($transaction['notes'])) ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
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
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>

    <?php else: ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-receipt fs-1 text-muted d-block mb-3"></i>
                <h5>Aucune transaction</h5>
                <p class="text-muted">Vous n'avez pas encore de transaction</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
