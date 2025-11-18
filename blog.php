<?php
require_once 'config/config.php';
$pageTitle = 'Blog - Actualités Transport';

// Articles de blog (en production, ces données viendraient de la BDD)
$articles = [
    [
        'id' => 1,
        'title' => '10 conseils pour optimiser vos trajets de transport',
        'excerpt' => 'Découvrez comment réduire vos coûts de transport de 30% grâce à l\'optimisation de trajets et la réduction des kilomètres à vide.',
        'content' => 'Lorem ipsum...',
        'category' => 'Conseils',
        'author' => 'Équipe Teleroute',
        'date' => '2024-11-15',
        'image' => 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?w=800&h=400&fit=crop',
        'tags' => ['Optimisation', 'Économies', 'Conseils']
    ],
    [
        'id' => 2,
        'title' => 'Nouvelle réglementation transport 2024 : ce qui change',
        'excerpt' => 'Tour d\'horizon des nouvelles réglementations européennes sur le transport routier de marchandises pour 2024.',
        'content' => 'Lorem ipsum...',
        'category' => 'Réglementation',
        'author' => 'Marie Dupont',
        'date' => '2024-11-10',
        'image' => 'https://images.unsplash.com/photo-1568605117036-5fe5e7bab0b7?w=800&h=400&fit=crop',
        'tags' => ['Réglementation', 'Europe', '2024']
    ],
    [
        'id' => 3,
        'title' => 'Comment notre algorithme de matching fonctionne',
        'excerpt' => 'Plongée technique dans notre système de matching automatique qui connecte transporteurs et chargeurs avec 95% de précision.',
        'content' => 'Lorem ipsum...',
        'category' => 'Technologie',
        'author' => 'Pierre Martin',
        'date' => '2024-11-05',
        'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=800&h=400&fit=crop',
        'tags' => ['IA', 'Algorithme', 'Innovation']
    ],
    [
        'id' => 4,
        'title' => 'Transport éco-responsable : réduire son empreinte carbone',
        'excerpt' => 'Les meilleures pratiques pour un transport routier plus vert et plus durable.',
        'content' => 'Lorem ipsum...',
        'category' => 'Environnement',
        'author' => 'Sophie Laurent',
        'date' => '2024-10-28',
        'image' => 'https://images.unsplash.com/photo-1532996122724-e3c354a0b15b?w=800&h=400&fit=crop',
        'tags' => ['Écologie', 'CO2', 'Durable']
    ],
    [
        'id' => 5,
        'title' => 'Success Story : Transport Express Europe',
        'excerpt' => 'Comment TEE a augmenté sa rentabilité de 40% en utilisant notre plateforme.',
        'content' => 'Lorem ipsum...',
        'category' => 'Success Stories',
        'author' => 'Équipe Teleroute',
        'date' => '2024-10-20',
        'image' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800&h=400&fit=crop',
        'tags' => ['Success Story', 'Témoignage', 'ROI']
    ],
    [
        'id' => 6,
        'title' => 'Les tendances du transport routier en 2025',
        'excerpt' => 'Digitalisation, automatisation, électrification : découvrez les tendances qui transforment le secteur.',
        'content' => 'Lorem ipsum...',
        'category' => 'Tendances',
        'author' => 'Jean Leroy',
        'date' => '2024-10-15',
        'image' => 'https://images.unsplash.com/photo-1519003722824-194d4455a60c?w=800&h=400&fit=crop',
        'tags' => ['Tendances', 'Innovation', '2025']
    ]
];

include 'includes/header.php';
?>

<div class="container my-5">
    <!-- Header -->
    <div class="row mb-5 text-center">
        <div class="col-12">
            <h1 class="mb-3">Blog & Actualités</h1>
            <p class="lead text-muted">Restez informé des dernières actualités du transport routier</p>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-funnel"></i> Catégories</h6>
                </div>
                <div class="list-group list-group-flush">
                    <a href="#" class="list-group-item list-group-item-action active">
                        Toutes les catégories
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-lightbulb"></i> Conseils <span class="badge bg-secondary float-end">12</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-newspaper"></i> Actualités <span class="badge bg-secondary float-end">8</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-graph-up"></i> Tendances <span class="badge bg-secondary float-end">6</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-shield-check"></i> Réglementation <span class="badge bg-secondary float-end">5</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-cpu"></i> Technologie <span class="badge bg-secondary float-end">10</span>
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="bi bi-trophy"></i> Success Stories <span class="badge bg-secondary float-end">7</span>
                    </a>
                </div>
            </div>

            <!-- Newsletter -->
            <div class="card shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="mb-3"><i class="bi bi-envelope"></i> Newsletter</h6>
                    <p class="small text-muted">Recevez nos derniers articles par email</p>
                    <form>
                        <div class="mb-2">
                            <input type="email" class="form-control form-control-sm" placeholder="Votre email">
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-send"></i> S'abonner
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Articles -->
        <div class="col-lg-9">
            <!-- Featured Article -->
            <?php $featured = $articles[0]; ?>
            <div class="card shadow-sm mb-4">
                <img src="<?= $featured['image'] ?>" class="card-img-top" alt="<?= h($featured['title']) ?>" style="height: 400px; object-fit: cover;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-primary"><?= $featured['category'] ?></span>
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i> <?= formatDate($featured['date']) ?>
                        </small>
                    </div>
                    <h2 class="card-title"><?= h($featured['title']) ?></h2>
                    <p class="card-text text-muted"><?= h($featured['excerpt']) ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="bi bi-person-circle"></i> <?= h($featured['author']) ?>
                        </small>
                        <a href="#" class="btn btn-primary">
                            Lire la suite <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Articles Grid -->
            <div class="row g-4">
                <?php foreach (array_slice($articles, 1) as $article): ?>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100">
                        <img src="<?= $article['image'] ?>" class="card-img-top" alt="<?= h($article['title']) ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-secondary"><?= $article['category'] ?></span>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> <?= formatDate($article['date']) ?>
                                </small>
                            </div>
                            <h5 class="card-title"><?= h($article['title']) ?></h5>
                            <p class="card-text text-muted flex-grow-1"><?= h($article['excerpt']) ?></p>
                            <div class="mb-2">
                                <?php foreach ($article['tags'] as $tag): ?>
                                    <span class="badge bg-light text-dark me-1">#<?= $tag ?></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> <?= h($article['author']) ?>
                                </small>
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    Lire <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <nav aria-label="Navigation blog" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <span class="page-link">Précédent</span>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Suivant</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- CTA -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm bg-primary text-white">
                <div class="card-body text-center py-5">
                    <h3 class="mb-3">Vous aimez nos contenus ?</h3>
                    <p class="lead mb-4">Rejoignez notre communauté de 85,000+ professionnels du transport</p>
                    <a href="/register.php" class="btn btn-light btn-lg">
                        <i class="bi bi-rocket"></i> Inscription gratuite
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
