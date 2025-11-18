<?php
require_once 'config/config.php';
require_once 'config/database.php';

$pageTitle = 'Inscription';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect('/dashboard.php');
}

$errors = [];
$formData = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le CSRF
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        // Récupérer les données du formulaire
        $formData = [
            'user_type' => $_POST['user_type'] ?? '',
            'company_name' => trim($_POST['company_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'phone' => trim($_POST['phone'] ?? ''),
            'siret' => trim($_POST['siret'] ?? ''),
            'vat_number' => trim($_POST['vat_number'] ?? ''),
            'address' => trim($_POST['address'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'postal_code' => trim($_POST['postal_code'] ?? ''),
            'country' => $_POST['country'] ?? 'France',
        ];

        // Validation
        if (empty($formData['user_type']) || !in_array($formData['user_type'], ['transporter', 'shipper', 'both'])) {
            $errors[] = 'Veuillez sélectionner un type de compte';
        }
        if (empty($formData['company_name'])) {
            $errors[] = 'Le nom de l\'entreprise est requis';
        }
        if (empty($formData['email']) || !isValidEmail($formData['email'])) {
            $errors[] = 'Email invalide';
        }
        if (empty($formData['password']) || !isStrongPassword($formData['password'])) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre';
        }
        if ($formData['password'] !== $formData['password_confirm']) {
            $errors[] = 'Les mots de passe ne correspondent pas';
        }
        if (empty($formData['phone'])) {
            $errors[] = 'Le numéro de téléphone est requis';
        }
        if (empty($formData['city'])) {
            $errors[] = 'La ville est requise';
        }

        // Si pas d'erreurs, créer le compte
        if (empty($errors)) {
            $userModel = new User();

            // Vérifier si l'email existe déjà
            if ($userModel->getByEmail($formData['email'])) {
                $errors[] = 'Un compte avec cet email existe déjà';
            } else {
                $result = $userModel->create($formData);

                if ($result['success']) {
                    // TODO: Envoyer un email de vérification
                    setFlash('success', 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.');
                    redirect('/login.php');
                } else {
                    $errors[] = $result['error'] ?? 'Erreur lors de la création du compte';
                }
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">
                        <i class="bi bi-person-plus-fill text-primary"></i>
                        Créer un compte
                    </h2>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= h($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

                        <!-- Type de compte -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Type de compte *</label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="form-check card p-3 h-100">
                                        <input class="form-check-input" type="radio" name="user_type" value="transporter" id="typeTransporter" <?= ($formData['user_type'] ?? '') === 'transporter' ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100" for="typeTransporter">
                                            <i class="bi bi-truck fs-2 d-block text-primary mb-2"></i>
                                            <strong>Transporteur</strong>
                                            <p class="small text-muted mb-0">Je cherche du fret à transporter</p>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check card p-3 h-100">
                                        <input class="form-check-input" type="radio" name="user_type" value="shipper" id="typeShipper" <?= ($formData['user_type'] ?? '') === 'shipper' ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100" for="typeShipper">
                                            <i class="bi bi-box-seam fs-2 d-block text-success mb-2"></i>
                                            <strong>Chargeur</strong>
                                            <p class="small text-muted mb-0">J'ai du fret à transporter</p>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check card p-3 h-100">
                                        <input class="form-check-input" type="radio" name="user_type" value="both" id="typeBoth" <?= ($formData['user_type'] ?? '') === 'both' ? 'checked' : '' ?>>
                                        <label class="form-check-label w-100" for="typeBoth">
                                            <i class="bi bi-arrow-left-right fs-2 d-block text-info mb-2"></i>
                                            <strong>Les deux</strong>
                                            <p class="small text-muted mb-0">Je cherche et propose</p>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Nom de l'entreprise *</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" value="<?= h($formData['company_name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= h($formData['email'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mot de passe *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <small class="text-muted">Min. 8 caractères, 1 majuscule, 1 minuscule, 1 chiffre</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">Confirmer le mot de passe *</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Téléphone *</label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="<?= h($formData['phone'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="siret" class="form-label">SIRET</label>
                                <input type="text" class="form-control" id="siret" name="siret" value="<?= h($formData['siret'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="vat_number" class="form-label">Numéro TVA</label>
                            <input type="text" class="form-control" id="vat_number" name="vat_number" value="<?= h($formData['vat_number'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Adresse</label>
                            <input type="text" class="form-control" id="address" name="address" value="<?= h($formData['address'] ?? '') ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">Ville *</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?= h($formData['city'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?= h($formData['postal_code'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="country" class="form-label">Pays</label>
                            <select class="form-select" id="country" name="country">
                                <option value="France" <?= ($formData['country'] ?? 'France') === 'France' ? 'selected' : '' ?>>France</option>
                                <option value="Belgique" <?= ($formData['country'] ?? '') === 'Belgique' ? 'selected' : '' ?>>Belgique</option>
                                <option value="Allemagne" <?= ($formData['country'] ?? '') === 'Allemagne' ? 'selected' : '' ?>>Allemagne</option>
                                <option value="Espagne" <?= ($formData['country'] ?? '') === 'Espagne' ? 'selected' : '' ?>>Espagne</option>
                                <option value="Italie" <?= ($formData['country'] ?? '') === 'Italie' ? 'selected' : '' ?>>Italie</option>
                                <option value="Pays-Bas" <?= ($formData['country'] ?? '') === 'Pays-Bas' ? 'selected' : '' ?>>Pays-Bas</option>
                            </select>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms" required>
                            <label class="form-check-label" for="terms">
                                J'accepte les <a href="/terms.php" target="_blank">conditions générales d'utilisation</a>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-person-plus"></i> Créer mon compte
                        </button>
                    </form>

                    <div class="text-center mt-4">
                        <p>Vous avez déjà un compte ?
                            <a href="/login.php">Se connecter</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
