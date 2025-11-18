<?php
require_once '../config/config.php';
require_once '../helpers/Logger.php';
requireLogin();

// Admin check
$adminEmails = ['admin@teleroute-marketplace.com', 'contact@tee.fr'];
if (!in_array($_SESSION['user']['email'], $adminEmails)) {
    http_response_code(403);
    redirect('/dashboard.php');
}

$pageTitle = 'Logs Système';

$logger = Logger::getInstance();

// Get parameters
$channel = $_GET['channel'] ?? 'app';
$action = $_GET['action'] ?? 'view';
$search = $_GET['search'] ?? '';
$lines = (int)($_GET['lines'] ?? 100);

// Available channels
$channels = ['app', 'security', 'database', 'api'];

// Handle actions
if ($action === 'clear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $logger->clear($channel);
        $_SESSION['success'] = "Logs du canal '$channel' effacés avec succès";
        redirect('/admin/logs.php?channel=' . $channel);
    }
}

// Get logs
if (!empty($search)) {
    $logs = $logger->search($search, $channel, $lines);
} else {
    $logs = $logger->getRecentLogs($channel, $lines);
}

// Get statistics
$stats = $logger->getStats($channel);

include '../includes/header.php';
?>

<div class="container-fluid my-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-file-text"></i> Logs Système</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/">Admin</a></li>
                    <li class="breadcrumb-item active">Logs</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-list-ul text-primary" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0"><?= number_format($stats['total_entries']) ?></h3>
                    <p class="text-muted mb-0 small">Entrées totales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0"><?= number_format($stats['by_level']['error'] + $stats['by_level']['critical']) ?></h3>
                    <p class="text-muted mb-0 small">Erreurs</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-circle text-warning" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0"><?= number_format($stats['by_level']['warning']) ?></h3>
                    <p class="text-muted mb-0 small">Avertissements</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="bi bi-hdd text-info" style="font-size: 2rem;"></i>
                    <h3 class="mt-2 mb-0"><?= number_format($stats['file_size'] / 1024, 2) ?> KB</h3>
                    <p class="text-muted mb-0 small">Taille du fichier</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Controls -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Canal</label>
                            <select name="channel" class="form-select" onchange="this.form.submit()">
                                <?php foreach ($channels as $ch): ?>
                                    <option value="<?= $ch ?>" <?= $ch === $channel ? 'selected' : '' ?>>
                                        <?= ucfirst($ch) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Nombre de lignes</label>
                            <select name="lines" class="form-select" onchange="this.form.submit()">
                                <option value="50" <?= $lines === 50 ? 'selected' : '' ?>>50</option>
                                <option value="100" <?= $lines === 100 ? 'selected' : '' ?>>100</option>
                                <option value="500" <?= $lines === 500 ? 'selected' : '' ?>>500</option>
                                <option value="1000" <?= $lines === 1000 ? 'selected' : '' ?>>1000</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Rechercher</label>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Rechercher dans les logs..."
                                   value="<?= h($search) ?>">
                        </div>

                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Filtrer
                            </button>
                        </div>
                    </form>

                    <div class="mt-3">
                        <a href="/admin/logs.php?channel=<?= $channel ?>" class="btn btn-sm btn-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Rafraîchir
                        </a>

                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
                            <i class="bi bi-trash"></i> Effacer les logs
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-terminal"></i>
                        Logs - <?= ucfirst($channel) ?>
                        <?php if (!empty($search)): ?>
                            <span class="badge bg-info">Recherche: <?= h($search) ?></span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($logs)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">Aucun log trouvé</h4>
                            <p class="text-muted">
                                <?php if (!empty($search)): ?>
                                    Aucun résultat pour "<?= h($search) ?>"
                                <?php else: ?>
                                    Aucune entrée de log pour ce canal
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0" style="font-family: 'Courier New', monospace; font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th width="150">Timestamp</th>
                                        <th width="100">Niveau</th>
                                        <th width="80">User</th>
                                        <th width="120">IP</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                        <?php
                                        $levelClass = 'text-muted';
                                        $levelIcon = 'info-circle';

                                        if (isset($log['level'])) {
                                            $level = strtolower($log['level']);
                                            switch ($level) {
                                                case 'emergency':
                                                case 'alert':
                                                case 'critical':
                                                    $levelClass = 'text-danger fw-bold';
                                                    $levelIcon = 'exclamation-triangle-fill';
                                                    break;
                                                case 'error':
                                                    $levelClass = 'text-danger';
                                                    $levelIcon = 'exclamation-circle';
                                                    break;
                                                case 'warning':
                                                    $levelClass = 'text-warning';
                                                    $levelIcon = 'exclamation-triangle';
                                                    break;
                                                case 'info':
                                                    $levelClass = 'text-info';
                                                    $levelIcon = 'info-circle';
                                                    break;
                                                case 'debug':
                                                    $levelClass = 'text-secondary';
                                                    $levelIcon = 'bug';
                                                    break;
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td class="text-muted small"><?= $log['timestamp'] ?? '-' ?></td>
                                            <td>
                                                <?php if (isset($log['level'])): ?>
                                                    <span class="<?= $levelClass ?>">
                                                        <i class="bi bi-<?= $levelIcon ?>"></i>
                                                        <?= strtoupper($log['level']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="small"><?= $log['user_id'] ?? '-' ?></td>
                                            <td class="small"><?= $log['ip'] ?? '-' ?></td>
                                            <td class="small">
                                                <?php
                                                // Highlight search terms
                                                $message = $log['message'] ?? $log['raw'];
                                                if (!empty($search)) {
                                                    $message = str_ireplace(
                                                        $search,
                                                        '<mark>' . $search . '</mark>',
                                                        h($message)
                                                    );
                                                    echo $message;
                                                } else {
                                                    echo h($message);
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer text-muted small">
                            Affichage de <?= count($logs) ?> entrées
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Clear Logs Modal -->
<div class="modal fade" id="clearLogsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Confirmer la suppression
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="?action=clear&channel=<?= $channel ?>">
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir effacer tous les logs du canal <strong><?= ucfirst($channel) ?></strong> ?</p>
                    <p class="text-danger"><strong>Cette action est irréversible !</strong></p>
                    <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Effacer les logs
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
mark {
    background-color: #fff3cd;
    padding: 2px 4px;
    border-radius: 2px;
}
</style>

<?php include '../includes/footer.php'; ?>
