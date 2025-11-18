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

$pageTitle = 'Administration - Utilisateurs';

try {
    $db = Database::getInstance()->getConnection();

    // Gestion des actions
    if (isset($_GET['action']) && isset($_GET['id'])) {
        $userId = (int)$_GET['id'];
        $action = $_GET['action'];

        if ($action === 'delete' && verifyCsrfToken($_GET['token'] ?? '')) {
            $stmt = $db->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
            $stmt->execute([$userId]);
            setFlash('success', 'Utilisateur supprimé');
            redirect('/admin/users.php');
        }
    }

    // Filtres
    $search = $_GET['search'] ?? '';
    $type = $_GET['type'] ?? 'all';
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    // Construction de la requête
    $where = "deleted_at IS NULL";
    $params = [];

    if ($search) {
        $where .= " AND (company_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    if ($type !== 'all') {
        $where .= " AND user_type = ?";
        $params[] = $type;
    }

    // Compter le total
    $countStmt = $db->prepare("SELECT COUNT(*) FROM users WHERE $where");
    $countStmt->execute($params);
    $totalUsers = $countStmt->fetchColumn();
    $totalPages = ceil($totalUsers / $perPage);

    // Récupérer les utilisateurs
    $stmt = $db->prepare("
        SELECT id, user_type, company_name, email, phone, siret, rating, total_ratings, created_at
        FROM users
        WHERE $where
        ORDER BY created_at DESC
        LIMIT $perPage OFFSET $offset
    ");
    $stmt->execute($params);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    logError('Admin users error', ['error' => $e->getMessage()]);
    $users = [];
    $totalPages = 0;
}

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-people"></i> Gestion des utilisateurs</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard.php">Accueil</a></li>
                    <li class="breadcrumb-item"><a href="/admin/index.php">Administration</a></li>
                    <li class="breadcrumb-item active">Utilisateurs</li>
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
                <a href="/admin/users.php" class="btn btn-primary">
                    <i class="bi bi-people"></i> Utilisateurs
                </a>
                <a href="/admin/offers.php" class="btn btn-outline-primary">
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
                    <form method="GET" action="">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" name="search"
                                       placeholder="Rechercher par nom, email, téléphone..."
                                       value="<?= h($search) ?>">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" name="type">
                                    <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>Tous les types</option>
                                    <option value="transporter" <?= $type === 'transporter' ? 'selected' : '' ?>>Transporteurs</option>
                                    <option value="shipper" <?= $type === 'shipper' ? 'selected' : '' ?>>Chargeurs</option>
                                    <option value="both" <?= $type === 'both' ? 'selected' : '' ?>>Les deux</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Rechercher
                                </button>
                            </div>
                        </div>
                    </form>
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
                            <?= number_format($totalUsers) ?> utilisateur(s)
                        </h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($users)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-people text-muted" style="font-size: 4rem;"></i>
                            <p class="text-muted mt-3">Aucun utilisateur trouvé</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Entreprise</th>
                                        <th>Email</th>
                                        <th>Type</th>
                                        <th>Note</th>
                                        <th>Inscription</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td>
                                                <strong><?= h($user['company_name']) ?></strong><br>
                                                <small class="text-muted"><?= h($user['phone']) ?></small>
                                            </td>
                                            <td><?= h($user['email']) ?></td>
                                            <td>
                                                <?php
                                                $badgeClass = 'secondary';
                                                if ($user['user_type'] === 'transporter') $badgeClass = 'success';
                                                if ($user['user_type'] === 'shipper') $badgeClass = 'primary';
                                                if ($user['user_type'] === 'both') $badgeClass = 'info';
                                                ?>
                                                <span class="badge bg-<?= $badgeClass ?>">
                                                    <?= ucfirst($user['user_type']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <i class="bi bi-star-fill text-warning"></i>
                                                <?= number_format($user['rating'], 2) ?>
                                                <small class="text-muted">(<?= $user['total_ratings'] ?>)</small>
                                            </td>
                                            <td>
                                                <small><?= formatDate($user['created_at']) ?></small>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="/directory.php?user_id=<?= $user['id'] ?>"
                                                       class="btn btn-outline-primary" title="Voir profil">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <a href="?action=delete&id=<?= $user['id'] ?>&token=<?= getCsrfToken() ?>"
                                                       class="btn btn-outline-danger"
                                                       onclick="return confirm('Supprimer cet utilisateur ?')"
                                                       title="Supprimer">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
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
                        <nav aria-label="Navigation des utilisateurs">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&type=<?= $type ?>">
                                            Précédent
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if ($i == $page || $i == 1 || $i == $totalPages || abs($i - $page) <= 2): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&type=<?= $type ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php elseif (abs($i - $page) == 3): ?>
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    <?php endif; ?>
                                <?php endfor; ?>

                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&type=<?= $type ?>">
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
