<?php
require_once 'config/config.php';
$pageTitle = 'Témoignages clients';

$testimonials = [
    [
        'name' => 'Jean-Pierre Martin',
        'company' => 'Transport Express SA',
        'role' => 'Directeur Logistique',
        'rating' => 5,
        'text' => 'Depuis que nous utilisons Teleroute Marketplace, nos trajets à vide ont diminué de 40%. Le système de matching automatique est incroyablement précis!',
        'image' => 'https://ui-avatars.com/api/?name=JP+Martin&background=0D6EFD&color=fff'
    ],
    [
        'name' => 'Marie Dubois',
        'company' => 'LogiFrance Distribution',
        'role' => 'Responsable Transport',
        'rating' => 5,
        'text' => 'La plateforme est très intuitive. Les alertes automatiques nous font gagner un temps précieux. Nous trouvons maintenant des transporteurs en quelques minutes!',
        'image' => 'https://ui-avatars.com/api/?name=M+Dubois&background=198754&color=fff'
    ],
    [
        'name' => 'Carlos Rodriguez',
        'company' => 'Iberica Transportes',
        'role' => 'CEO',
        'rating' => 4,
        'text' => 'Excellente plateforme pour le transport international. La carte interactive et le calculateur de distance sont des outils fantastiques pour planifier nos routes.',
        'image' => 'https://ui-avatars.com/api/?name=C+Rodriguez&background=FFC107&color=000'
    ],
    [
        'name' => 'Hans Mueller',
        'company' => 'Deutsche Transport GmbH',
        'role' => 'Gérant',
        'rating' => 5,
        'text' => 'Le système de notation et les vérifications de sécurité nous donnent confiance. Nous avons établi des partenariats durables grâce à cette plateforme.',
        'image' => 'https://ui-avatars.com/api/?name=H+Mueller&background=DC3545&color=fff'
    ],
    [
        'name' => 'Sophie Laurent',
        'company' => 'EcoTransit',
        'role' => 'Fondatrice',
        'rating' => 5,
        'text' => 'L\'optimisation de trajets nous a permis de réduire notre empreinte carbone de 25%. Un outil indispensable pour un transport responsable!',
        'image' => 'https://ui-avatars.com/api/?name=S+Laurent&background=28A745&color=fff'
    ],
    [
        'name' => 'Paolo Rossi',
        'company' => 'TransAlpes Italia',
        'role' => 'Directeur Commercial',
        'rating' => 4,
        'text' => 'Les analytics nous aident à prendre de meilleures décisions stratégiques. La visibilité sur nos performances est excellente.',
        'image' => 'https://ui-avatars.com/api/?name=P+Rossi&background=6610F2&color=fff'
    ],
    [
        'name' => 'Nathalie Bernard',
        'company' => 'Fret Services France',
        'role' => 'Responsable Exploitation',
        'rating' => 5,
        'text' => 'Le support client est réactif et professionnel. Toutes nos questions ont été rapidement résolues. Bravo!',
        'image' => 'https://ui-avatars.com/api/?name=N+Bernard&background=0DCAF0&color=000'
    ],
    [
        'name' => 'Thomas Van Der Berg',
        'company' => 'Benelux Cargo',
        'role' => 'Operations Manager',
        'rating' => 5,
        'text' => 'La messagerie instantanée facilite grandement la communication avec nos partenaires. Plus rapide et plus efficace que les emails!',
        'image' => 'https://ui-avatars.com/api/?name=T+Berg&background=FD7E14&color=000'
    ],
    [
        'name' => 'Isabelle Petit',
        'company' => 'AgroTransport Bretagne',
        'role' => 'Gérante',
        'rating' => 5,
        'text' => 'Parfait pour notre activité de transport frigorifique. Les filtres avancés nous permettent de trouver exactement ce que nous cherchons.',
        'image' => 'https://ui-avatars.com/api/?name=I+Petit&background=D63384&color=fff'
    ]
];

include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-5 text-center">
        <div class="col-12">
            <h1 class="mb-3">Ce que disent nos clients</h1>
            <p class="lead text-muted">Plus de 85,000 professionnels nous font confiance</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="display-3 text-primary mb-2">4.8</div>
                    <div class="mb-2">
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-fill text-warning"></i>
                        <i class="bi bi-star-half text-warning"></i>
                    </div>
                    <p class="text-muted mb-0">Note moyenne</p>
                    <small class="text-muted">Sur 12,847 avis</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="display-3 text-success mb-2">97%</div>
                    <p class="text-muted mb-0">Satisfaction client</p>
                    <small class="text-muted">Recommanderaient notre service</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="display-3 text-info mb-2">85K+</div>
                    <p class="text-muted mb-0">Utilisateurs actifs</p>
                    <small class="text-muted">Transporteurs et chargeurs</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center">
                    <div class="display-3 text-warning mb-2">350K+</div>
                    <p class="text-muted mb-0">Offres par jour</p>
                    <small class="text-muted">Millions de transactions réussies</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Testimonials Grid -->
    <div class="row g-4">
        <?php foreach ($testimonials as $testimonial): ?>
        <div class="col-lg-4 col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?= $testimonial['image'] ?>" alt="<?= h($testimonial['name']) ?>"
                             class="rounded-circle me-3" width="60" height="60">
                        <div>
                            <h6 class="mb-0"><?= h($testimonial['name']) ?></h6>
                            <small class="text-muted"><?= h($testimonial['role']) ?></small>
                            <div class="text-primary small fw-bold"><?= h($testimonial['company']) ?></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <?php for ($i = 0; $i < 5; $i++): ?>
                            <i class="bi bi-star<?= $i < $testimonial['rating'] ? '-fill' : '' ?> text-warning"></i>
                        <?php endfor; ?>
                    </div>

                    <p class="text-muted mb-0">
                        <i class="bi bi-quote text-primary fs-4"></i>
                        <?= h($testimonial['text']) ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Trust Badges -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm bg-light">
                <div class="card-body py-5">
                    <h3 class="text-center mb-4">Ils nous font confiance</h3>
                    <div class="row text-center g-4">
                        <div class="col-md-2 col-4">
                            <div class="p-3">
                                <i class="bi bi-shield-fill-check text-success" style="font-size: 3rem;"></i>
                                <p class="small mt-2 mb-0">Certifié sécurisé</p>
                            </div>
                        </div>
                        <div class="col-md-2 col-4">
                            <div class="p-3">
                                <i class="bi bi-award-fill text-warning" style="font-size: 3rem;"></i>
                                <p class="small mt-2 mb-0">Récompensé 2024</p>
                            </div>
                        </div>
                        <div class="col-md-2 col-4">
                            <div class="p-3">
                                <i class="bi bi-patch-check-fill text-primary" style="font-size: 3rem;"></i>
                                <p class="small mt-2 mb-0">RGPD Conforme</p>
                            </div>
                        </div>
                        <div class="col-md-2 col-4">
                            <div class="p-3">
                                <i class="bi bi-globe-europe-africa text-info" style="font-size: 3rem;"></i>
                                <p class="small mt-2 mb-0">25+ Pays</p>
                            </div>
                        </div>
                        <div class="col-md-2 col-4">
                            <div class="p-3">
                                <i class="bi bi-headset text-success" style="font-size: 3rem;"></i>
                                <p class="small mt-2 mb-0">Support 24/7</p>
                            </div>
                        </div>
                        <div class="col-md-2 col-4">
                            <div class="p-3">
                                <i class="bi bi-lightning-charge-fill text-warning" style="font-size: 3rem;"></i>
                                <p class="small mt-2 mb-0">Instantané</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="row mt-5">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body text-center py-5">
                    <h2 class="mb-3">Rejoignez des milliers de professionnels satisfaits</h2>
                    <p class="lead mb-4">Commencez à optimiser vos transports dès aujourd'hui</p>
                    <a href="/register.php" class="btn btn-light btn-lg me-2">
                        <i class="bi bi-rocket"></i> Inscription gratuite
                    </a>
                    <a href="/about.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-info-circle"></i> En savoir plus
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
