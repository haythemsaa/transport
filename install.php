<?php
/**
 * Script d'installation de Teleroute Marketplace
 * Ce script doit être supprimé après l'installation !
 */

$errors = [];
$success = [];
$step = $_GET['step'] ?? 1;

// Vérifier si l'installation est déjà effectuée
if (file_exists('config/.installed')) {
    die('L\'application est déjà installée. Supprimez le fichier config/.installed pour réinstaller.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step == 2) {
    // Récupérer les données du formulaire
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    $appUrl = $_POST['app_url'] ?? '';

    // Tester la connexion
    try {
        $conn = new PDO("mysql:host=$dbHost", $dbUser, $dbPass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Créer la base de données si elle n'existe pas
        $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->exec("USE `$dbName`");

        // Importer le schéma
        $schema = file_get_contents('database/schema.sql');
        $conn->exec($schema);

        $success[] = "Base de données créée avec succès !";

        // Mettre à jour le fichier de configuration
        $configContent = file_get_contents('config/database.php');
        $configContent = str_replace("'localhost'", "'$dbHost'", $configContent);
        $configContent = str_replace("'teleroute_marketplace'", "'$dbName'", $configContent);
        $configContent = str_replace("'root'", "'$dbUser'", $configContent);
        $configContent = str_replace("private \$password = '';", "private \$password = '$dbPass';", $configContent);
        file_put_contents('config/database.php', $configContent);

        // Mettre à jour l'URL de base
        $configFileContent = file_get_contents('config/config.php');
        $configFileContent = str_replace("'http://localhost/transport'", "'$appUrl'", $configFileContent);
        file_put_contents('config/config.php', $configFileContent);

        // Créer les dossiers nécessaires
        if (!is_dir('uploads')) mkdir('uploads', 0755, true);
        if (!is_dir('logs')) mkdir('logs', 0755, true);

        // Marquer l'installation comme terminée
        file_put_contents('config/.installed', date('Y-m-d H:i:s'));

        $success[] = "Configuration mise à jour !";
        $success[] = "Installation terminée avec succès !";
        $step = 3;

    } catch (PDOException $e) {
        $errors[] = "Erreur de connexion : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Teleroute Marketplace</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="bi bi-truck"></i>
                        Installation de Teleroute Marketplace
                    </h3>
                </div>
                <div class="card-body p-5">

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success">
                            <ul class="mb-0">
                                <?php foreach ($success as $msg): ?>
                                    <li><?= htmlspecialchars($msg) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($step == 1): ?>
                        <!-- Étape 1: Bienvenue -->
                        <h4 class="mb-4">Bienvenue dans l'installation</h4>

                        <div class="alert alert-info">
                            <h5><i class="bi bi-info-circle"></i> Prérequis</h5>
                            <ul class="mb-0">
                                <li>PHP 7.4 ou supérieur</li>
                                <li>MySQL 5.7 ou supérieur</li>
                                <li>Extensions PHP: PDO, mysqli, json, mbstring</li>
                                <li>Apache avec mod_rewrite</li>
                            </ul>
                        </div>

                        <div class="mb-4">
                            <h5>Vérification de la configuration</h5>
                            <table class="table table-sm">
                                <tr>
                                    <td>Version PHP</td>
                                    <td><strong><?= phpversion() ?></strong></td>
                                    <td>
                                        <?php if (version_compare(phpversion(), '7.4.0', '>=')): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> OK</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Non compatible</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Extension PDO</td>
                                    <td>-</td>
                                    <td>
                                        <?php if (extension_loaded('pdo')): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Installée</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Non installée</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Extension mbstring</td>
                                    <td>-</td>
                                    <td>
                                        <?php if (extension_loaded('mbstring')): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Installée</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Non installée</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Dossier uploads/ accessible en écriture</td>
                                    <td>-</td>
                                    <td>
                                        <?php if (is_writable('.')): ?>
                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> OK</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning"><i class="bi bi-exclamation-triangle"></i> Vérifier les permissions</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <a href="?step=2" class="btn btn-primary btn-lg w-100">
                            Continuer l'installation
                            <i class="bi bi-arrow-right"></i>
                        </a>

                    <?php elseif ($step == 2): ?>
                        <!-- Étape 2: Configuration -->
                        <h4 class="mb-4">Configuration de la base de données</h4>

                        <form method="POST" action="?step=2">
                            <div class="mb-3">
                                <label class="form-label">Hôte de la base de données</label>
                                <input type="text" class="form-control" name="db_host" value="localhost" required>
                                <small class="text-muted">Généralement "localhost"</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nom de la base de données</label>
                                <input type="text" class="form-control" name="db_name" value="teleroute_marketplace" required>
                                <small class="text-muted">Elle sera créée si elle n'existe pas</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Utilisateur MySQL</label>
                                <input type="text" class="form-control" name="db_user" value="root" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mot de passe MySQL</label>
                                <input type="password" class="form-control" name="db_pass">
                            </div>

                            <hr class="my-4">

                            <div class="mb-3">
                                <label class="form-label">URL de l'application</label>
                                <input type="text" class="form-control" name="app_url"
                                       value="http://<?= $_SERVER['HTTP_HOST'] ?><?= dirname($_SERVER['PHP_SELF']) ?>"
                                       required>
                                <small class="text-muted">L'URL complète de votre application (sans slash à la fin)</small>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-cloud-download"></i>
                                Installer maintenant
                            </button>
                        </form>

                    <?php elseif ($step == 3): ?>
                        <!-- Étape 3: Terminé -->
                        <div class="text-center">
                            <div class="mb-4">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                            </div>

                            <h4 class="mb-4">Installation terminée avec succès !</h4>

                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Important :</strong> Pour des raisons de sécurité, supprimez le fichier <code>install.php</code> immédiatement !
                            </div>

                            <div class="mb-4">
                                <h5>Prochaines étapes :</h5>
                                <ol class="text-start">
                                    <li>Supprimer le fichier <code>install.php</code></li>
                                    <li>Créer votre premier compte utilisateur</li>
                                    <li>Configurer la clé API Google Maps dans <code>config/config.php</code></li>
                                    <li>Configurer les paramètres SMTP pour l'envoi d'emails</li>
                                </ol>
                            </div>

                            <a href="/" class="btn btn-primary btn-lg">
                                <i class="bi bi-house"></i>
                                Accéder à l'application
                            </a>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="card-footer text-center text-muted">
                    <small>Teleroute Marketplace v1.0.0</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
