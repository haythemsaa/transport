<?php
require_once 'config/config.php';
require_once 'config/database.php';

$pageTitle = 'Connexion';

// Rediriger si déjà connecté
if (isLoggedIn()) {
    redirect('/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $errors[] = 'Veuillez remplir tous les champs';
        } else {
            $userModel = new User();
            $result = $userModel->login($email, $password);

            if ($result['success']) {
                // Stocker les informations de l'utilisateur en session
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['user_data'] = $result['user'];

                // Logger l'activité
                logActivity("Connexion réussie", ['user_id' => $result['user']['id'], 'ip' => getClientIP()]);

                // Rediriger vers le tableau de bord
                setFlash('success', 'Bienvenue ' . $result['user']['company_name'] . ' !');
                redirect('/dashboard.php');
            } else {
                $errors[] = $result['error'];
                logActivity("Tentative de connexion échouée", ['email' => $email, 'ip' => getClientIP()]);
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">
                        <i class="bi bi-box-arrow-in-right text-primary"></i>
                        Connexion
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

                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" value="<?= h($_POST['email'] ?? '') ?>" required autofocus>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Se souvenir de moi
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right"></i> Se connecter
                        </button>

                        <div class="text-center">
                            <a href="/forgot-password.php" class="text-decoration-none">
                                Mot de passe oublié ?
                            </a>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="mb-0">Pas encore de compte ?</p>
                        <a href="/register.php" class="btn btn-outline-primary mt-2">
                            <i class="bi bi-person-plus"></i> Créer un compte
                        </a>
                    </div>
                </div>
            </div>

            <!-- Info Section -->
            <div class="card mt-4 bg-light border-0">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-info-circle text-primary"></i>
                        Pourquoi créer un compte ?
                    </h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Accédez à des milliers d'offres de fret et véhicules
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Publiez vos propres offres gratuitement
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Messagerie instantanée avec vos partenaires
                        </li>
                        <li class="mb-2">
                            <i class="bi bi-check-circle text-success"></i>
                            Système de notation et d'évaluations
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
