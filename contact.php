<?php
require_once 'config/config.php';
$pageTitle = 'Contact';
$success = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if (empty($name)) $errors[] = 'Le nom est requis';
        if (empty($email) || !isValidEmail($email)) $errors[] = 'Email invalide';
        if (empty($subject)) $errors[] = 'Le sujet est requis';
        if (empty($message)) $errors[] = 'Le message est requis';

        if (empty($errors)) {
            // TODO: Envoyer l'email
            logActivity("Formulaire de contact soumis", ['email' => $email, 'subject' => $subject]);
            $success = true;
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8">
            <h1 class="mb-4">Contactez-nous</h1>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <h4 class="alert-heading"><i class="bi bi-check-circle"></i> Message envoyé !</h4>
                    <p class="mb-0">Merci de nous avoir contactés. Nous vous répondrons dans les plus brefs délais.</p>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= h($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="name" name="name"
                                       value="<?= h($_POST['name'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email *</label>
                                <input type="email" class="form-control" id="email" name="email"
                                       value="<?= h($_POST['email'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Sujet *</label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="">Sélectionner...</option>
                                <option value="question">Question générale</option>
                                <option value="support">Support technique</option>
                                <option value="partnership">Partenariat</option>
                                <option value="other">Autre</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message *</label>
                            <textarea class="form-control" id="message" name="message" rows="6" required><?= h($_POST['message'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Envoyer le message
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-envelope"></i> Email</h5>
                    <p class="card-text">
                        <a href="mailto:contact@teleroute-marketplace.com">contact@teleroute-marketplace.com</a>
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-telephone"></i> Téléphone</h5>
                    <p class="card-text">
                        +33 1 23 45 67 89<br>
                        <small class="text-muted">Lundi - Vendredi: 9h - 18h</small>
                    </p>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title"><i class="bi bi-geo-alt"></i> Adresse</h5>
                    <p class="card-text">
                        123 Avenue de la Logistique<br>
                        75001 Paris<br>
                        France
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mt-4 bg-light">
                <div class="card-body">
                    <h5 class="card-title">Besoin d'aide ?</h5>
                    <p class="small">Consultez notre <a href="/faq.php">FAQ</a> pour trouver rapidement des réponses à vos questions.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
