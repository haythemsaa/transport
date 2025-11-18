<?php
require_once 'config/config.php';
requireLogin();

$pageTitle = 'Notifications';
$user = $_SESSION['user'];

try {
    $db = Database::getInstance()->getConnection();

    // Mark all as read if requested
    if (isset($_GET['mark_all_read'])) {
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
        $stmt->execute([$user['id']]);
        redirect('/notifications.php');
    }

    // Mark single notification as read
    if (isset($_GET['mark_read'])) {
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['mark_read'], $user['id']]);
        redirect('/notifications.php');
    }

    // Delete notification
    if (isset($_GET['delete'])) {
        $stmt = $db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['delete'], $user['id']]);
        redirect('/notifications.php');
    }

    // Get filter
    $filter = $_GET['filter'] ?? 'all';

    // Build query
    $where = "user_id = ?";
    $params = [$user['id']];

    if ($filter === 'unread') {
        $where .= " AND is_read = 0";
    }

    // Pagination
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    // Get total count
    $countStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE $where");
    $countStmt->execute($params);
    $totalNotifications = $countStmt->fetchColumn();
    $totalPages = ceil($totalNotifications / $perPage);

    // Get notifications
    $stmt = $db->prepare("
        SELECT * FROM notifications
        WHERE $where
        ORDER BY created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get unread count
    $unreadStmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $unreadStmt->execute([$user['id']]);
    $unreadCount = $unreadStmt->fetchColumn();

} catch (Exception $e) {
    logError('Notifications error', ['error' => $e->getMessage()]);
    $notifications = [];
    $totalPages = 0;
    $unreadCount = 0;
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-bell"></i> Notifications</h1>
                <?php if ($unreadCount > 0): ?>
                    <a href="?mark_all_read=1" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-check-all"></i> Tout marquer comme lu
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="btn-group" role="group">
                                <a href="?filter=all" class="btn btn-sm <?= $filter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                    Toutes (<?= $totalNotifications ?>)
                                </a>
                                <a href="?filter=unread" class="btn btn-sm <?= $filter === 'unread' ? 'btn-primary' : 'btn-outline-primary' ?>">
                                    Non lues (<?= $unreadCount ?>)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php if (empty($notifications)): ?>
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                        <h4 class="mt-3">Aucune notification</h4>
                        <p class="text-muted">Vous n'avez pas de notifications pour le moment.</p>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="card shadow-sm mb-3 <?= !$notification['is_read'] ? 'border-primary' : '' ?>">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-1 text-center">
                                    <?php
                                    $iconClass = 'bi-info-circle text-info';
                                    switch ($notification['type']) {
                                        case 'message':
                                            $iconClass = 'bi-chat-dots text-primary';
                                            break;
                                        case 'offer':
                                            $iconClass = 'bi-box-seam text-success';
                                            break;
                                        case 'transaction':
                                            $iconClass = 'bi-credit-card text-warning';
                                            break;
                                        case 'rating':
                                            $iconClass = 'bi-star text-warning';
                                            break;
                                        case 'system':
                                            $iconClass = 'bi-gear text-secondary';
                                            break;
                                    }
                                    ?>
                                    <i class="bi <?= $iconClass ?>" style="font-size: 2rem;"></i>
                                </div>
                                <div class="col-md-9">
                                    <h6 class="mb-1">
                                        <?= h($notification['title']) ?>
                                        <?php if (!$notification['is_read']): ?>
                                            <span class="badge bg-primary ms-2">Nouveau</span>
                                        <?php endif; ?>
                                    </h6>
                                    <p class="mb-1 text-muted"><?= h($notification['message']) ?></p>
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> <?= timeAgo($notification['created_at']) ?>
                                    </small>
                                </div>
                                <div class="col-md-2 text-end">
                                    <?php if ($notification['link']): ?>
                                        <a href="<?= h($notification['link']) ?>" class="btn btn-sm btn-outline-primary mb-2">
                                            <i class="bi bi-eye"></i> Voir
                                        </a>
                                    <?php endif; ?>
                                    <div class="btn-group-vertical w-100" role="group">
                                        <?php if (!$notification['is_read']): ?>
                                            <a href="?mark_read=<?= $notification['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                                <i class="bi bi-check"></i> Marquer lu
                                            </a>
                                        <?php endif; ?>
                                        <a href="?delete=<?= $notification['id'] ?>" class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Supprimer cette notification ?')">
                                            <i class="bi bi-trash"></i> Supprimer
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Navigation des notifications">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page - 1 ?>&filter=<?= $filter ?>">Précédent</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $page || $i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
                                    <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                        <a class="page-link" href="?page=<?= $i ?>&filter=<?= $filter ?>"><?= $i ?></a>
                                    </li>
                                <?php elseif (abs($i - $page) == 3): ?>
                                    <li class="page-item disabled"><span class="page-link">...</span></li>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page + 1 ?>&filter=<?= $filter ?>">Suivant</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
