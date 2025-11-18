<?php
require_once 'config/config.php';
$pageTitle = 'Tarifs et Abonnements';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-5 text-center">
        <div class="col-12">
            <h1 class="mb-3">Tarifs simples et transparents</h1>
            <p class="lead text-muted">Choisissez la formule adaptée à vos besoins</p>
        </div>
    </div>

    <!-- Pricing Cards -->
    <div class="row g-4 mb-5">
        <!-- Free Plan -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="text-center mb-4">
                        <h3 class="card-title">Gratuit</h3>
                        <div class="display-4 my-3">0€</div>
                        <p class="text-muted">Pour découvrir</p>
                    </div>

                    <ul class="list-unstyled flex-grow-1">
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Inscription gratuite</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> 5 offres actives max</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Recherche basique</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Messagerie</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Profil public</li>
                        <li class="mb-2"><i class="bi bi-x-circle text-muted"></i> Alertes limitées (3 max)</li>
                        <li class="mb-2"><i class="bi bi-x-circle text-muted"></i> Matching automatique</li>
                        <li class="mb-2"><i class="bi bi-x-circle text-muted"></i> Analytics</li>
                    </ul>

                    <?php if (isLoggedIn()): ?>
                        <button class="btn btn-outline-secondary w-100" disabled>Votre formule actuelle</button>
                    <?php else: ?>
                        <a href="/register.php" class="btn btn-outline-primary w-100">
                            <i class="bi bi-person-plus"></i> Commencer gratuitement
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Starter Plan -->
        <div class="col-lg-3">
            <div class="card shadow h-100 border-primary">
                <div class="card-body d-flex flex-column">
                    <div class="text-center mb-4">
                        <span class="badge bg-primary mb-2">Populaire</span>
                        <h3 class="card-title">Starter</h3>
                        <div class="display-4 my-3">29€</div>
                        <p class="text-muted">par mois</p>
                    </div>

                    <ul class="list-unstyled flex-grow-1">
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <strong>20 offres actives</strong></li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Recherche avancée</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Messagerie illimitée</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <strong>10 alertes personnalisées</strong></li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Matching automatique</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Statistiques basiques</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Export CSV</li>
                        <li class="mb-2"><i class="bi bi-x-circle text-muted"></i> Badge vérifié</li>
                        <li class="mb-2"><i class="bi bi-x-circle text-muted"></i> Support prioritaire</li>
                    </ul>

                    <a href="/register.php" class="btn btn-primary w-100">
                        <i class="bi bi-rocket"></i> Commencer maintenant
                    </a>
                </div>
            </div>
        </div>

        <!-- Professional Plan -->
        <div class="col-lg-3">
            <div class="card shadow h-100 border-success">
                <div class="card-body d-flex flex-column">
                    <div class="text-center mb-4">
                        <span class="badge bg-success mb-2">Recommandé</span>
                        <h3 class="card-title">Professional</h3>
                        <div class="display-4 my-3">79€</div>
                        <p class="text-muted">par mois</p>
                    </div>

                    <ul class="list-unstyled flex-grow-1">
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <strong>Offres illimitées</strong></li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Recherche super avancée</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <strong>Alertes illimitées</strong></li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Matching automatique+</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <strong>Analytics complètes</strong></li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Optimisation de trajets</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Export PDF + Excel</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <strong>Badge vérifié</strong></li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Support prioritaire</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> API access</li>
                    </ul>

                    <a href="/register.php" class="btn btn-success w-100">
                        <i class="bi bi-star"></i> Souscrire
                    </a>
                </div>
            </div>
        </div>

        <!-- Enterprise Plan -->
        <div class="col-lg-3">
            <div class="card shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <div class="text-center mb-4">
                        <h3 class="card-title">Enterprise</h3>
                        <div class="display-4 my-3">Sur mesure</div>
                        <p class="text-muted">Contactez-nous</p>
                    </div>

                    <ul class="list-unstyled flex-grow-1">
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> <strong>Tout Professional +</strong></li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Comptes multi-utilisateurs</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Gestionnaire de compte dédié</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Formation personnalisée</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Intégration API complète</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> SLA garanti</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Rapports personnalisés</li>
                        <li class="mb-2"><i class="bi bi-check-circle text-success"></i> Facturation flexible</li>
                    </ul>

                    <a href="/contact.php" class="btn btn-outline-dark w-100">
                        <i class="bi bi-envelope"></i> Nous contacter
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Comparison Table -->
    <div class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Comparaison détaillée</h2>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Fonctionnalité</th>
                            <th class="text-center">Gratuit</th>
                            <th class="text-center bg-primary bg-opacity-10">Starter</th>
                            <th class="text-center bg-success bg-opacity-10">Professional</th>
                            <th class="text-center">Enterprise</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Offres actives simultanées</strong></td>
                            <td class="text-center">5</td>
                            <td class="text-center">20</td>
                            <td class="text-center">Illimitées</td>
                            <td class="text-center">Illimitées</td>
                        </tr>
                        <tr>
                            <td><strong>Alertes automatiques</strong></td>
                            <td class="text-center">3</td>
                            <td class="text-center">10</td>
                            <td class="text-center">Illimitées</td>
                            <td class="text-center">Illimitées</td>
                        </tr>
                        <tr>
                            <td><strong>Recherche avancée</strong></td>
                            <td class="text-center"><i class="bi bi-x text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-check text-success"></i></td>
                            <td class="text-center"><i class="bi bi-check text-success"></i></td>
                            <td class="text-center"><i class="bi bi-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Matching automatique</strong></td>
                            <td class="text-center"><i class="bi bi-x text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-check text-success"></i></td>
                            <td class="text-center"><i class="bi bi-check-circle text-success"></i> Avancé</td>
                            <td class="text-center"><i class="bi bi-check-circle text-success"></i> Avancé</td>
                        </tr>
                        <tr>
                            <td><strong>Analytics & Statistiques</strong></td>
                            <td class="text-center"><i class="bi bi-x text-danger"></i></td>
                            <td class="text-center">Basiques</td>
                            <td class="text-center">Complètes</td>
                            <td class="text-center">Personnalisées</td>
                        </tr>
                        <tr>
                            <td><strong>Optimisation de trajets</strong></td>
                            <td class="text-center"><i class="bi bi-x text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-x text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-check text-success"></i></td>
                            <td class="text-center"><i class="bi bi-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Export de données</strong></td>
                            <td class="text-center"><i class="bi bi-x text-danger"></i></td>
                            <td class="text-center">CSV</td>
                            <td class="text-center">CSV + PDF + Excel</td>
                            <td class="text-center">Tous formats</td>
                        </tr>
                        <tr>
                            <td><strong>Badge "Vérifié"</strong></td>
                            <td class="text-center"><i class="bi bi-x text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-x text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-check text-success"></i></td>
                            <td class="text-center"><i class="bi bi-check text-success"></i></td>
                        </tr>
                        <tr>
                            <td><strong>Support</strong></td>
                            <td class="text-center">Email</td>
                            <td class="text-center">Email + Chat</td>
                            <td class="text-center">Prioritaire 24/7</td>
                            <td class="text-center">Dédié + SLA</td>
                        </tr>
                        <tr>
                            <td><strong>API Access</strong></td>
                            <td class="text-center"><i class="bi bi-x text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-x text-danger"></i></td>
                            <td class="text-center"><i class="bi bi-check text-success"></i></td>
                            <td class="text-center"><i class="bi bi-check text-success"></i> + Webhooks</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <h2 class="text-center mb-4">Questions fréquentes sur les tarifs</h2>

            <div class="accordion" id="pricingFAQ">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            Puis-je changer de formule à tout moment ?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#pricingFAQ">
                        <div class="accordion-body">
                            Oui, absolument ! Vous pouvez upgrader ou downgrader votre formule à tout moment.
                            En cas d'upgrade, vous bénéficiez immédiatement des nouvelles fonctionnalités.
                            Le montant est calculé au prorata pour le mois en cours.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Y a-t-il un engagement minimum ?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                        <div class="accordion-body">
                            Non, nos abonnements sont sans engagement. Vous pouvez annuler à tout moment.
                            Bénéficiez de -15% sur un engagement annuel.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Quels moyens de paiement acceptez-vous ?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                        <div class="accordion-body">
                            Nous acceptons les cartes bancaires (Visa, Mastercard, American Express),
                            virements bancaires, et PayPal. Pour les formules Enterprise, nous proposons
                            également la facturation mensuelle ou trimestrielle.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            Proposez-vous une période d'essai ?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                        <div class="accordion-body">
                            Oui ! Toutes les formules payantes bénéficient de 14 jours d'essai gratuit,
                            sans engagement et sans carte bancaire requise.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm bg-primary text-white">
                <div class="card-body text-center py-5">
                    <h2 class="mb-3">Prêt à optimiser vos transports ?</h2>
                    <p class="lead mb-4">Rejoignez plus de 85,000 professionnels qui nous font confiance</p>
                    <a href="/register.php" class="btn btn-light btn-lg me-2">
                        <i class="bi bi-rocket"></i> Commencer gratuitement
                    </a>
                    <a href="/contact.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-telephone"></i> Contactez-nous
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
