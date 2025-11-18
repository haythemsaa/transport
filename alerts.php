<?php
require_once 'config/config.php';
requireLogin();

$pageTitle = 'Alertes automatiques';
$user = $_SESSION['user'];

try {
    $db = Database::getInstance()->getConnection();

    // Handle alert creation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_alert'])) {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Token de sécurité invalide');
        } else {
            $alertType = $_POST['alert_type'];
            $criteria = [
                'departure_city' => $_POST['departure_city'] ?? '',
                'destination_city' => $_POST['destination_city'] ?? '',
                'vehicle_type' => $_POST['vehicle_type'] ?? '',
                'min_weight' => $_POST['min_weight'] ?? '',
                'max_weight' => $_POST['max_weight'] ?? '',
                'min_price' => $_POST['min_price'] ?? '',
                'max_price' => $_POST['max_price'] ?? '',
            ];

            $stmt = $db->prepare("
                INSERT INTO saved_searches (user_id, search_type, criteria, alert_enabled, created_at)
                VALUES (?, ?, ?, 1, NOW())
            ");
            $stmt->execute([
                $user['id'],
                $alertType,
                json_encode($criteria)
            ]);

            setFlash('success', 'Alerte créée avec succès! Vous serez notifié des nouvelles offres correspondantes.');
            redirect('/alerts.php');
        }
    }

    // Handle alert deletion
    if (isset($_GET['delete'])) {
        $stmt = $db->prepare("DELETE FROM saved_searches WHERE id = ? AND user_id = ?");
        $stmt->execute([$_GET['delete'], $user['id']]);
        setFlash('success', 'Alerte supprimée');
        redirect('/alerts.php');
    }

    // Handle alert toggle
    if (isset($_GET['toggle'])) {
        $stmt = $db->prepare("
            UPDATE saved_searches
            SET alert_enabled = NOT alert_enabled
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$_GET['toggle'], $user['id']]);
        setFlash('success', 'Alerte mise à jour');
        redirect('/alerts.php');
    }

    // Get user's alerts
    $stmt = $db->prepare("
        SELECT * FROM saved_searches
        WHERE user_id = ?
        ORDER BY created_at DESC
    ");
    $stmt->execute([$user['id']]);
    $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    logError('Alerts error', ['error' => $e->getMessage()]);
    $alerts = [];
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-bell-fill"></i> Alertes automatiques</h1>
            <p class="text-muted">Recevez des notifications instantanées quand de nouvelles offres correspondent à vos critères</p>
        </div>
    </div>

    <!-- Create Alert Button -->
    <div class="row mb-4">
        <div class="col-12">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAlertModal">
                <i class="bi bi-plus-circle"></i> Créer une nouvelle alerte
            </button>
        </div>
    </div>

    <!-- Active Alerts -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Mes alertes (<?= count($alerts) ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($alerts)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-bell-slash text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">Aucune alerte configurée</h4>
                            <p class="text-muted">Créez votre première alerte pour être notifié des nouvelles offres</p>
                            <button type="button" class="btn btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#createAlertModal">
                                <i class="bi bi-plus-circle"></i> Créer une alerte
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($alerts as $alert): ?>
                                <?php $criteria = json_decode($alert['criteria'], true); ?>
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="d-flex align-items-start">
                                                <div class="form-check form-switch me-3">
                                                    <input class="form-check-input" type="checkbox"
                                                           <?= $alert['alert_enabled'] ? 'checked' : '' ?>
                                                           onchange="window.location.href='?toggle=<?= $alert['id'] ?>'">
                                                </div>
                                                <div>
                                                    <h6 class="mb-1">
                                                        <?php if ($alert['search_type'] === 'freight'): ?>
                                                            <i class="bi bi-box-seam text-primary"></i> Alerte Fret
                                                        <?php else: ?>
                                                            <i class="bi bi-truck text-success"></i> Alerte Véhicule
                                                        <?php endif; ?>
                                                        <?php if (!$alert['alert_enabled']): ?>
                                                            <span class="badge bg-secondary ms-2">Désactivée</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success ms-2">Active</span>
                                                        <?php endif; ?>
                                                    </h6>
                                                    <div class="text-muted small">
                                                        <?php if (!empty($criteria['departure_city'])): ?>
                                                            <i class="bi bi-geo-alt"></i> Départ: <?= h($criteria['departure_city']) ?><br>
                                                        <?php endif; ?>
                                                        <?php if (!empty($criteria['destination_city'])): ?>
                                                            <i class="bi bi-geo-alt-fill"></i> Destination: <?= h($criteria['destination_city']) ?><br>
                                                        <?php endif; ?>
                                                        <?php if (!empty($criteria['vehicle_type'])): ?>
                                                            <i class="bi bi-truck"></i> Type: <?= h($criteria['vehicle_type']) ?><br>
                                                        <?php endif; ?>
                                                        <?php if (!empty($criteria['min_weight']) || !empty($criteria['max_weight'])): ?>
                                                            <i class="bi bi-box"></i> Poids: <?= $criteria['min_weight'] ?? '0' ?> - <?= $criteria['max_weight'] ?? '∞' ?> kg<br>
                                                        <?php endif; ?>
                                                        <?php if (!empty($criteria['min_price']) || !empty($criteria['max_price'])): ?>
                                                            <i class="bi bi-currency-euro"></i> Prix: <?= $criteria['min_price'] ?? '0' ?> - <?= $criteria['max_price'] ?? '∞' ?> €
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        Créée <?= timeAgo($alert['created_at']) ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <a href="?delete=<?= $alert['id'] ?>"
                                               class="btn btn-outline-danger btn-sm"
                                               onclick="return confirm('Supprimer cette alerte ?')">
                                                <i class="bi bi-trash"></i> Supprimer
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Section -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-primary">
                <div class="card-body text-center">
                    <i class="bi bi-lightning-fill text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Notifications instantanées</h5>
                    <p class="text-muted small">Soyez alerté en temps réel dès qu'une offre correspond</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center">
                    <i class="bi bi-bullseye text-success" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Ciblage précis</h5>
                    <p class="text-muted small">Définissez vos critères pour des alertes pertinentes</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-info">
                <div class="card-body text-center">
                    <i class="bi bi-clock-history text-info" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Gain de temps</h5>
                    <p class="text-muted small">Plus besoin de chercher constamment de nouvelles offres</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Alert Modal -->
<div class="modal fade" id="createAlertModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Créer une alerte automatique</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                    <input type="hidden" name="create_alert" value="1">

                    <div class="mb-3">
                        <label class="form-label">Type d'alerte *</label>
                        <select class="form-select" name="alert_type" required>
                            <?php if (isShipper()): ?>
                                <option value="vehicle">Offres de véhicules</option>
                            <?php endif; ?>
                            <?php if (isTransporter()): ?>
                                <option value="freight">Offres de fret</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ville de départ</label>
                            <input type="text" class="form-control" name="departure_city"
                                   placeholder="Ex: Paris">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ville de destination</label>
                            <input type="text" class="form-control" name="destination_city"
                                   placeholder="Ex: Lyon">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type de véhicule</label>
                        <select class="form-select" name="vehicle_type">
                            <option value="">Tous les types</option>
                            <option value="Fourgon">Fourgon</option>
                            <option value="Camion bâché">Camion bâché</option>
                            <option value="Camion frigorifique">Camion frigorifique</option>
                            <option value="Semi-remorque">Semi-remorque</option>
                            <option value="Plateau">Plateau</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Poids minimum (kg)</label>
                            <input type="number" class="form-control" name="min_weight"
                                   placeholder="Ex: 1000">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Poids maximum (kg)</label>
                            <input type="number" class="form-control" name="max_weight"
                                   placeholder="Ex: 10000">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prix minimum (€)</label>
                            <input type="number" class="form-control" name="min_price"
                                   placeholder="Ex: 500">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prix maximum (€)</label>
                            <input type="number" class="form-control" name="max_price"
                                   placeholder="Ex: 2000">
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i>
                        <strong>Astuce :</strong> Laissez les champs vides pour ne pas filtrer sur ce critère
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Créer l'alerte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
