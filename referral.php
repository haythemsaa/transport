<?php
require_once 'config/config.php';
requireLogin();

$pageTitle = 'Programme de Parrainage';
$user = $_SESSION['user'];

// Générer code de parrainage unique pour l'utilisateur
$referralCode = strtoupper(substr(md5($user['id'] . $user['email']), 0, 8));
$referralLink = "https://" . $_SERVER['HTTP_HOST'] . "/register.php?ref=" . $referralCode;

try {
    $db = Database::getInstance()->getConnection();

    // Statistiques de parrainage (simulées - en production, utiliser une vraie table referrals)
    $stmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM users
        WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $recentUsers = $stmt->fetchColumn();

    // Simulation de statistiques
    $referralStats = [
        'total_referrals' => rand(0, 15),
        'active_referrals' => rand(0, 10),
        'pending_reward' => rand(50, 500),
        'total_earned' => rand(100, 2000),
        'conversion_rate' => rand(20, 80)
    ];

} catch (Exception $e) {
    logError('Referral error', ['error' => $e->getMessage()]);
    $referralStats = [
        'total_referrals' => 0,
        'active_referrals' => 0,
        'pending_reward' => 0,
        'total_earned' => 0,
        'conversion_rate' => 0
    ];
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1><i class="bi bi-gift"></i> Programme de Parrainage</h1>
            <p class="lead text-muted">Gagnez 50€ pour chaque ami que vous parrainez</p>
        </div>
    </div>

    <!-- How it works -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="text-center mb-4">Comment ça marche ?</h4>
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="p-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
                                     style="width: 80px; height: 80px;">
                                    <i class="bi bi-share text-primary" style="font-size: 2.5rem;"></i>
                                </div>
                                <h5>1. Partagez</h5>
                                <p class="text-muted small">Envoyez votre lien de parrainage à vos amis professionnels du transport</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3">
                                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
                                     style="width: 80px; height: 80px;">
                                    <i class="bi bi-person-plus text-success" style="font-size: 2.5rem;"></i>
                                </div>
                                <h5>2. Inscription</h5>
                                <p class="text-muted small">Votre ami s'inscrit et souscrit à un abonnement payant</p>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="p-3">
                                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3"
                                     style="width: 80px; height: 80px;">
                                    <i class="bi bi-currency-euro text-warning" style="font-size: 2.5rem;"></i>
                                </div>
                                <h5>3. Gagnez</h5>
                                <p class="text-muted small">Recevez 50€ de crédit + votre ami reçoit -20% sur son premier mois</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-primary h-100">
                <div class="card-body text-center">
                    <i class="bi bi-people text-primary" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= $referralStats['total_referrals'] ?></h3>
                    <p class="text-muted mb-0">Parrainages total</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-success h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= $referralStats['active_referrals'] ?></h3>
                    <p class="text-muted mb-0">Actifs</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-warning h-100">
                <div class="card-body text-center">
                    <i class="bi bi-hourglass-split text-warning" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= formatPrice($referralStats['pending_reward']) ?></h3>
                    <p class="text-muted mb-0">En attente</p>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-info h-100">
                <div class="card-body text-center">
                    <i class="bi bi-trophy text-info" style="font-size: 2.5rem;"></i>
                    <h3 class="mt-2 mb-0"><?= formatPrice($referralStats['total_earned']) ?></h3>
                    <p class="text-muted mb-0">Total gagné</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral Link -->
    <div class="row mb-4">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-link-45deg"></i> Votre lien de parrainage</h5>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="referralLink" value="<?= $referralLink ?>" readonly>
                        <button class="btn btn-primary" type="button" onclick="copyReferralLink()">
                            <i class="bi bi-clipboard"></i> Copier
                        </button>
                    </div>

                    <p class="text-center mb-3"><strong>Votre code :</strong> <span class="badge bg-primary fs-5"><?= $referralCode ?></span></p>

                    <div class="text-center">
                        <p class="text-muted mb-3">Partagez sur les réseaux sociaux</p>
                        <div class="btn-group" role="group">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($referralLink) ?>"
                               target="_blank" class="btn btn-outline-primary">
                                <i class="bi bi-facebook"></i> Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?= urlencode($referralLink) ?>&text=Rejoignez%20Teleroute%20Marketplace"
                               target="_blank" class="btn btn-outline-info">
                                <i class="bi bi-twitter"></i> Twitter
                            </a>
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($referralLink) ?>"
                               target="_blank" class="btn btn-outline-primary">
                                <i class="bi bi-linkedin"></i> LinkedIn
                            </a>
                            <a href="mailto:?subject=Rejoignez%20Teleroute%20Marketplace&body=<?= urlencode($referralLink) ?>"
                               class="btn btn-outline-secondary">
                                <i class="bi bi-envelope"></i> Email
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Referral History -->
    <div class="row mb-4">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="bi bi-list-ul"></i> Historique des parrainages</h5>
                </div>
                <div class="card-body">
                    <?php if ($referralStats['total_referrals'] == 0): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">Aucun parrainage pour le moment</h4>
                            <p class="text-muted">Partagez votre lien pour commencer à gagner des récompenses !</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Filleul</th>
                                        <th>Statut</th>
                                        <th>Récompense</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Exemples de données (en production, depuis BDD)
                                    $sampleReferrals = [
                                        ['date' => '2024-11-10', 'name' => 'Transport ABC', 'status' => 'active', 'reward' => 50],
                                        ['date' => '2024-11-05', 'name' => 'LogiRoute SAS', 'status' => 'active', 'reward' => 50],
                                        ['date' => '2024-10-28', 'name' => 'EuroFret', 'status' => 'pending', 'reward' => 0],
                                    ];
                                    foreach (array_slice($sampleReferrals, 0, $referralStats['total_referrals']) as $ref):
                                    ?>
                                    <tr>
                                        <td><?= formatDate($ref['date']) ?></td>
                                        <td><?= h($ref['name']) ?></td>
                                        <td>
                                            <?php if ($ref['status'] == 'active'): ?>
                                                <span class="badge bg-success">Actif</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">En attente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($ref['reward'] > 0): ?>
                                                <strong class="text-success">+<?= $ref['reward'] ?>€</strong>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms & Conditions -->
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="mb-3">Conditions du programme</h6>
                    <ul class="small text-muted">
                        <li>Récompense de 50€ par filleul qui souscrit à un abonnement payant (Starter, Professional ou Enterprise)</li>
                        <li>Le filleul reçoit -20% de réduction sur son premier mois</li>
                        <li>La récompense est créditée après 30 jours d'abonnement actif du filleul</li>
                        <li>Pas de limite du nombre de parrainages</li>
                        <li>Le crédit peut être utilisé pour réduire votre abonnement ou retiré sur votre compte bancaire</li>
                        <li>Programme valable pour tous les abonnés (gratuits et payants)</li>
                        <li>Teleroute se réserve le droit de modifier ou arrêter le programme à tout moment</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralLink() {
    const input = document.getElementById('referralLink');
    input.select();
    document.execCommand('copy');

    // Show feedback
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="bi bi-check"></i> Copié !';
    btn.classList.add('btn-success');
    btn.classList.remove('btn-primary');

    setTimeout(() => {
        btn.innerHTML = originalHTML;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
    }, 2000);
}
</script>

<?php include 'includes/footer.php'; ?>
