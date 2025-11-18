<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Veuillez vous connecter');
    redirect('/login.php');
}

$pageTitle = 'Mon profil';
$userId = $_SESSION['user_id'];
$userModel = new User();
$user = $userModel->getById($userId);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        $data = [
            'company_name' => trim($_POST['company_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'siret' => trim($_POST['siret'] ?? ''),
            'vat_number' => trim($_POST['vat_number'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'postal_code' => trim($_POST['postal_code'] ?? ''),
            'country' => $_POST['country'] ?? 'France',
        ];

        if (empty($data['company_name'])) $errors[] = 'Le nom de l\'entreprise est requis';
        if (empty($data['phone'])) $errors[] = 'Le téléphone est requis';

        if (empty($errors)) {
            $result = $userModel->update($userId, $data);

            if ($result['success']) {
                $_SESSION['user_data'] = $userModel->getById($userId);
                $user = $_SESSION['user_data'];
                setFlash('success', 'Profil mis à jour avec succès !');
                redirect('/profile.php');
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}

// Changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (verifyCsrfToken($_POST['csrf_token_password'] ?? '')) {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $errors[] = 'Tous les champs sont requis';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Les mots de passe ne correspondent pas';
        } elseif (!isStrongPassword($newPassword)) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre';
        } else {
            $result = $userModel->changePassword($userId, $currentPassword, $newPassword);

            if ($result['success']) {
                setFlash('success', 'Mot de passe modifié avec succès !');
                redirect('/profile.php');
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}

$stats = $userModel->getStats($userId);

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <?php if ($user['profile_image']): ?>
                        <img src="<?= h($user['profile_image']) ?>" alt="Profile" class="rounded-circle profile-img mb-3">
                    <?php else: ?>
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px;">
                            <i class="bi bi-building fs-1"></i>
                        </div>
                    <?php endif; ?>

                    <h4><?= h($user['company_name']) ?></h4>
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

                    <?php if ($user['rating'] > 0): ?>
                        <div class="mb-3">
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
                            <small class="text-muted d-block"><?= $user['total_ratings'] ?> évaluation(s)</small>
                        </div>
                    <?php endif; ?>

                    <?= getStatusBadge($user['status']) ?>
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

        <!-- Contenu principal -->
        <div class="col-lg-9">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= h($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Informations du profil -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Informations du profil</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Nom de l'entreprise *</label>
                                <input type="text" class="form-control" id="company_name" name="company_name"
                                       value="<?= h($user['company_name']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?= h($user['email']) ?>" disabled>
                                <small class="text-muted">L'email ne peut pas être modifié</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Téléphone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                       value="<?= h($user['phone']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="siret" class="form-label">SIRET</label>
                                <input type="text" class="form-control" id="siret" name="siret"
                                       value="<?= h($user['siret']) ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="vat_number" class="form-label">Numéro TVA</label>
                            <input type="text" class="form-control" id="vat_number" name="vat_number"
                                   value="<?= h($user['vat_number']) ?>">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="address" name="address"
                                   value="<?= h($user['address']) ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">Ville</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       value="<?= h($user['city']) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code"
                                       value="<?= h($user['postal_code']) ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="country" class="form-label">Pays</label>
                                <select class="form-select" id="country" name="country">
                                    <option value="France" <?= $user['country'] === 'France' ? 'selected' : '' ?>>France</option>
                                    <option value="Belgique" <?= $user['country'] === 'Belgique' ? 'selected' : '' ?>>Belgique</option>
                                    <option value="Allemagne" <?= $user['country'] === 'Allemagne' ? 'selected' : '' ?>>Allemagne</option>
                                    <option value="Espagne" <?= $user['country'] === 'Espagne' ? 'selected' : '' ?>>Espagne</option>
                                    <option value="Italie" <?= $user['country'] === 'Italie' ? 'selected' : '' ?>>Italie</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>

            <!-- Changer le mot de passe -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-key"></i> Changer le mot de passe</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token_password" value="<?= getCsrfToken() ?>">
                        <input type="hidden" name="change_password" value="1">

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="current_password" class="form-label">Mot de passe actuel</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                                <small class="text-muted">Min. 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-key"></i> Changer le mot de passe
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
