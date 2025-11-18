<?php
require_once 'config/config.php';
requireLogin();

$pageTitle = 'Paramètres';
$user = $_SESSION['user'];

try {
    $db = Database::getInstance()->getConnection();

    // Handle settings update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            setFlash('danger', 'Token de sécurité invalide');
        } else {
            $action = $_POST['action'] ?? '';

            if ($action === 'notifications') {
                // Update notification preferences
                $emailNotifs = isset($_POST['email_notifications']) ? 1 : 0;
                $smsNotifs = isset($_POST['sms_notifications']) ? 1 : 0;
                $pushNotifs = isset($_POST['push_notifications']) ? 1 : 0;

                $stmt = $db->prepare("
                    UPDATE users SET
                        email_notifications = ?,
                        sms_notifications = ?,
                        push_notifications = ?
                    WHERE id = ?
                ");
                $stmt->execute([$emailNotifs, $smsNotifs, $pushNotifs, $user['id']]);

                setFlash('success', 'Préférences de notifications mises à jour');
                redirect('/settings.php');

            } elseif ($action === 'privacy') {
                // Update privacy settings
                $showPhone = isset($_POST['show_phone']) ? 1 : 0;
                $showEmail = isset($_POST['show_email']) ? 1 : 0;
                $profilePublic = isset($_POST['profile_public']) ? 1 : 0;

                $stmt = $db->prepare("
                    UPDATE users SET
                        show_phone = ?,
                        show_email = ?,
                        profile_public = ?
                    WHERE id = ?
                ");
                $stmt->execute([$showPhone, $showEmail, $profilePublic, $user['id']]);

                setFlash('success', 'Paramètres de confidentialité mis à jour');
                redirect('/settings.php');

            } elseif ($action === 'delete_account') {
                // Soft delete account
                $password = $_POST['confirm_password'] ?? '';

                if (!password_verify($password, $user['password'])) {
                    setFlash('danger', 'Mot de passe incorrect');
                    redirect('/settings.php');
                }

                $stmt = $db->prepare("UPDATE users SET deleted_at = NOW() WHERE id = ?");
                $stmt->execute([$user['id']]);

                session_destroy();
                setFlash('info', 'Votre compte a été supprimé');
                redirect('/');
            }
        }
    }

    // Get user settings
    $stmt = $db->prepare("
        SELECT
            email_notifications,
            sms_notifications,
            push_notifications,
            show_phone,
            show_email,
            profile_public
        FROM users
        WHERE id = ?
    ");
    $stmt->execute([$user['id']]);
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);

    // If columns don't exist, set defaults
    if (!$settings) {
        $settings = [
            'email_notifications' => 1,
            'sms_notifications' => 0,
            'push_notifications' => 1,
            'show_phone' => 1,
            'show_email' => 0,
            'profile_public' => 1
        ];
    }

} catch (Exception $e) {
    logError('Settings error', ['error' => $e->getMessage()]);
    $settings = [
        'email_notifications' => 1,
        'sms_notifications' => 0,
        'push_notifications' => 1,
        'show_phone' => 1,
        'show_email' => 0,
        'profile_public' => 1
    ];
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-gear"></i> Paramètres</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/dashboard.php">Accueil</a></li>
                    <li class="breadcrumb-item active">Paramètres</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 mb-4">
            <div class="list-group">
                <a href="#notifications" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                    <i class="bi bi-bell"></i> Notifications
                </a>
                <a href="#privacy" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="bi bi-shield-lock"></i> Confidentialité
                </a>
                <a href="#account" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="bi bi-person-circle"></i> Compte
                </a>
                <a href="#security" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="bi bi-shield-check"></i> Sécurité
                </a>
                <a href="#data" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="bi bi-database"></i> Mes données
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="col-md-9">
            <div class="tab-content">

                <!-- Notifications Tab -->
                <div class="tab-pane fade show active" id="notifications">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="bi bi-bell"></i> Préférences de notifications</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                                <input type="hidden" name="action" value="notifications">

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="emailNotifs"
                                           name="email_notifications" <?= $settings['email_notifications'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="emailNotifs">
                                        <strong>Notifications par email</strong>
                                        <div class="text-muted small">Recevez des emails pour les nouvelles offres, messages et alertes</div>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="smsNotifs"
                                           name="sms_notifications" <?= $settings['sms_notifications'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="smsNotifs">
                                        <strong>Notifications par SMS</strong>
                                        <div class="text-muted small">Recevez des SMS pour les opportunités urgentes (Premium uniquement)</div>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="pushNotifs"
                                           name="push_notifications" <?= $settings['push_notifications'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="pushNotifs">
                                        <strong>Notifications push</strong>
                                        <div class="text-muted small">Notifications sur navigateur pour les messages et matching</div>
                                    </label>
                                </div>

                                <hr>

                                <h6 class="mb-3">Types de notifications</h6>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="notifNewOffers" checked>
                                    <label class="form-check-label" for="notifNewOffers">
                                        Nouvelles offres correspondant à mes alertes
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="notifMessages" checked>
                                    <label class="form-check-label" for="notifMessages">
                                        Nouveaux messages
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="notifMatching" checked>
                                    <label class="form-check-label" for="notifMatching">
                                        Correspondances automatiques (matching)
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="notifTransactions" checked>
                                    <label class="form-check-label" for="notifTransactions">
                                        Mises à jour de transactions
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="notifRatings" checked>
                                    <label class="form-check-label" for="notifRatings">
                                        Nouvelles évaluations
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary mt-3">
                                    <i class="bi bi-check-circle"></i> Enregistrer les préférences
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Privacy Tab -->
                <div class="tab-pane fade" id="privacy">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Confidentialité</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                                <input type="hidden" name="action" value="privacy">

                                <h6 class="mb-3">Visibilité du profil</h6>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="profilePublic"
                                           name="profile_public" <?= $settings['profile_public'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="profilePublic">
                                        <strong>Profil public</strong>
                                        <div class="text-muted small">Votre profil est visible dans l'annuaire</div>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="showPhone"
                                           name="show_phone" <?= $settings['show_phone'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="showPhone">
                                        <strong>Afficher mon téléphone</strong>
                                        <div class="text-muted small">Les autres utilisateurs peuvent voir votre numéro</div>
                                    </label>
                                </div>

                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" id="showEmail"
                                           name="show_email" <?= $settings['show_email'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="showEmail">
                                        <strong>Afficher mon email</strong>
                                        <div class="text-muted small">Les autres utilisateurs peuvent voir votre email</div>
                                    </label>
                                </div>

                                <hr>

                                <h6 class="mb-3">Données et cookies</h6>

                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    Nous respectons votre vie privée et sommes conformes au RGPD.
                                    Consultez notre <a href="/terms.php">politique de confidentialité</a> pour plus d'informations.
                                </div>

                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle"></i> Enregistrer les paramètres
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Account Tab -->
                <div class="tab-pane fade" id="account">
                    <div class="card shadow-sm">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-person-circle"></i> Informations du compte</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Entreprise:</strong>
                                    <p><?= h($user['company_name']) ?></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Email:</strong>
                                    <p><?= h($user['email']) ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Type de compte:</strong>
                                    <p><span class="badge bg-primary"><?= ucfirst($user['user_type']) ?></span></p>
                                </div>
                                <div class="col-md-6">
                                    <strong>Membre depuis:</strong>
                                    <p><?= formatDate($user['created_at']) ?></p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <strong>Note:</strong>
                                    <p>
                                        <i class="bi bi-star-fill text-warning"></i>
                                        <?= number_format($user['rating'], 2) ?>
                                        (<?= $user['total_ratings'] ?> évaluations)
                                    </p>
                                </div>
                            </div>

                            <a href="/profile.php" class="btn btn-info">
                                <i class="bi bi-pencil"></i> Modifier le profil
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div class="tab-pane fade" id="security">
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="bi bi-shield-check"></i> Sécurité</h5>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-3">Mot de passe</h6>
                            <p class="text-muted">
                                Dernière modification : Il y a 30 jours
                            </p>
                            <a href="/profile.php#password" class="btn btn-warning mb-4">
                                <i class="bi bi-key"></i> Changer le mot de passe
                            </a>

                            <hr>

                            <h6 class="mb-3">Authentification à deux facteurs (2FA)</h6>
                            <div class="alert alert-secondary">
                                <i class="bi bi-info-circle"></i>
                                L'authentification à deux facteurs ajoute une couche de sécurité supplémentaire à votre compte.
                                <br><br>
                                <button class="btn btn-sm btn-secondary" disabled>
                                    <i class="bi bi-shield-plus"></i> Activer la 2FA (Bientôt disponible)
                                </button>
                            </div>

                            <hr>

                            <h6 class="mb-3">Sessions actives</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Appareil</th>
                                            <th>Localisation</th>
                                            <th>Dernière activité</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <i class="bi bi-laptop"></i> Chrome (Windows)
                                                <span class="badge bg-success">Actuelle</span>
                                            </td>
                                            <td>Paris, France</td>
                                            <td>Maintenant</td>
                                            <td>-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Tab -->
                <div class="tab-pane fade" id="data">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="mb-0"><i class="bi bi-database"></i> Mes données</h5>
                        </div>
                        <div class="card-body">
                            <h6 class="mb-3">Export de données</h6>
                            <p class="text-muted">
                                Téléchargez toutes vos données en conformité avec le RGPD
                            </p>

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <a href="/exports/freight-csv.php" class="btn btn-outline-primary w-100">
                                        <i class="bi bi-download"></i> Mes offres de fret
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="/exports/vehicles-csv.php" class="btn btn-outline-success w-100">
                                        <i class="bi bi-download"></i> Mes véhicules
                                    </a>
                                </div>
                                <div class="col-md-4">
                                    <a href="/exports/transactions-csv.php" class="btn btn-outline-warning w-100">
                                        <i class="bi bi-download"></i> Mes transactions
                                    </a>
                                </div>
                            </div>

                            <hr>

                            <h6 class="mb-3 text-danger">Zone dangereuse</h6>

                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                <i class="bi bi-trash"></i> Supprimer mon compte
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Supprimer le compte</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">
                    <input type="hidden" name="action" value="delete_account">

                    <div class="alert alert-danger">
                        <strong>Attention !</strong> Cette action est irréversible.
                        <ul class="mb-0 mt-2">
                            <li>Toutes vos offres seront supprimées</li>
                            <li>Vos messages seront effacés</li>
                            <li>Votre profil ne sera plus visible</li>
                            <li>Vos évaluations seront anonymisées</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirmez votre mot de passe pour continuer</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer définitivement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
