<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? h($pageTitle) . ' - ' : '' ?><?= APP_NAME ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= ASSETS_URL ?>/css/style.css">

    <?php if (isset($extraCSS)): ?>
        <?= $extraCSS ?>
    <?php endif; ?>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="/">
            <i class="bi bi-truck"></i> <?= APP_NAME ?>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="/search-freight.php">
                        <i class="bi bi-box-seam"></i> Rechercher Fret
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/search-vehicles.php">
                        <i class="bi bi-truck-front"></i> Rechercher Véhicules
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <?php if (isShipper()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/post-freight.php">
                            <i class="bi bi-plus-circle"></i> Publier Fret
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php if (isTransporter()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/post-vehicle.php">
                            <i class="bi bi-plus-circle"></i> Publier Véhicule
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/directory.php">
                            <i class="bi bi-people"></i> Annuaire
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <?php
                        $messageModel = new Message();
                        $unreadCount = $messageModel->getUnreadCount($_SESSION['user_id']);
                    ?>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="/messages.php">
                            <i class="bi bi-chat-dots"></i> Messages
                            <?php if ($unreadCount > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= $unreadCount ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                            <?= h(getCurrentUser()['company_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="/dashboard.php"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                            <li><a class="dropdown-item" href="/my-offers.php"><i class="bi bi-list-ul"></i> Mes offres</a></li>
                            <li><a class="dropdown-item" href="/transactions.php"><i class="bi bi-receipt"></i> Transactions</a></li>
                            <li><a class="dropdown-item" href="/profile.php"><i class="bi bi-person"></i> Mon profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/logout.php"><i class="bi bi-box-arrow-right"></i> Déconnexion</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/login.php">
                            <i class="bi bi-box-arrow-in-right"></i> Connexion
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light btn-sm ms-2" href="/register.php">
                            Inscription
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<?php $flash = getFlash(); ?>
<?php if ($flash): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show" role="alert">
            <?= h($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
<?php endif; ?>

<!-- Main Content -->
<main>
