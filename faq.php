<?php
require_once 'config/config.php';
$pageTitle = 'FAQ - Questions fréquentes';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-question-circle"></i> Questions Fréquentes (FAQ)</h1>
            <p class="text-muted">Trouvez rapidement des réponses à vos questions</p>
        </div>
    </div>

    <!-- Search -->
    <div class="row mb-4">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control" id="faqSearch" placeholder="Rechercher une question...">
                        <button class="btn btn-primary" type="button">
                            <i class="bi bi-search"></i> Rechercher
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group flex-wrap" role="group">
                <button type="button" class="btn btn-outline-primary active" data-category="all">
                    <i class="bi bi-grid"></i> Toutes
                </button>
                <button type="button" class="btn btn-outline-primary" data-category="general">
                    <i class="bi bi-info-circle"></i> Général
                </button>
                <button type="button" class="btn btn-outline-primary" data-category="account">
                    <i class="bi bi-person"></i> Compte
                </button>
                <button type="button" class="btn btn-outline-primary" data-category="offers">
                    <i class="bi bi-box-seam"></i> Offres
                </button>
                <button type="button" class="btn btn-outline-primary" data-category="payment">
                    <i class="bi bi-credit-card"></i> Paiement
                </button>
                <button type="button" class="btn btn-outline-primary" data-category="security">
                    <i class="bi bi-shield-check"></i> Sécurité
                </button>
            </div>
        </div>
    </div>

    <!-- FAQ Accordion -->
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="accordion" id="faqAccordion">

                <!-- GÉNÉRAL -->
                <div class="faq-item" data-category="general">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Qu'est-ce que <?= APP_NAME ?> ?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <?= APP_NAME ?> est la plateforme leader de mise en relation entre transporteurs et chargeurs en Europe.
                                Nous connectons plus de 85,000 professionnels et publions plus de 350,000 offres de fret par jour.
                                Notre mission est de faciliter le transport routier en optimisant la rencontre entre l'offre et la demande.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="general">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Comment fonctionne la plateforme ?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                <ol>
                                    <li><strong>Inscription gratuite</strong> - Créez votre compte en quelques minutes</li>
                                    <li><strong>Publiez vos offres</strong> - Fret pour les chargeurs, véhicules pour les transporteurs</li>
                                    <li><strong>Recherchez</strong> - Utilisez nos filtres avancés pour trouver ce qui vous convient</li>
                                    <li><strong>Contactez</strong> - Communiquez directement via notre messagerie sécurisée</li>
                                    <li><strong>Transactez</strong> - Finalisez votre accord et gérez vos transactions</li>
                                    <li><strong>Évaluez</strong> - Notez vos partenaires pour construire la confiance</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="general">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Quels pays sont couverts ?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Nous couvrons plus de 25 pays en Europe, notamment :
                                <ul>
                                    <li>France, Allemagne, Espagne, Italie, Belgique</li>
                                    <li>Pays-Bas, Pologne, République Tchèque, Autriche</li>
                                    <li>Portugal, Suisse, Danemark, Suède, Norvège</li>
                                    <li>Roumanie, Bulgarie, Hongrie, Slovaquie</li>
                                    <li>Et bien d'autres...</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- COMPTE -->
                <div class="faq-item" data-category="account">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Comment créer un compte ?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                C'est simple et rapide :
                                <ol>
                                    <li>Cliquez sur "Inscription" en haut de la page</li>
                                    <li>Choisissez votre type : Transporteur, Chargeur, ou Les deux</li>
                                    <li>Remplissez vos informations : entreprise, email, mot de passe</li>
                                    <li>Validez votre email</li>
                                    <li>Complétez votre profil avec SIRET, téléphone, etc.</li>
                                </ol>
                                L'inscription est 100% gratuite !
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="account">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                J'ai oublié mon mot de passe, que faire ?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Cliquez sur "Mot de passe oublié ?" sur la page de connexion.
                                Entrez votre email et vous recevrez un lien pour réinitialiser votre mot de passe.
                                Le lien est valable 24 heures.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="account">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                Comment améliorer ma note ?
                            </button>
                        </h2>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Votre note dépend des évaluations de vos partenaires. Pour l'améliorer :
                                <ul>
                                    <li>✅ Respectez vos engagements (dates, horaires)</li>
                                    <li>✅ Communiquez de manière professionnelle</li>
                                    <li>✅ Soyez ponctuel dans vos livraisons</li>
                                    <li>✅ Maintenez vos véhicules en bon état</li>
                                    <li>✅ Traitez les marchandises avec soin</li>
                                    <li>✅ Répondez rapidement aux messages</li>
                                </ul>
                                Plus vous avez de transactions réussies, meilleure sera votre réputation.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OFFRES -->
                <div class="faq-item" data-category="offers">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                                Comment publier une offre de fret ?
                            </button>
                        </h2>
                        <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                En tant que chargeur :
                                <ol>
                                    <li>Connectez-vous à votre compte</li>
                                    <li>Cliquez sur "Publier Fret" dans le menu</li>
                                    <li>Remplissez les informations : chargement, livraison, dates, marchandise</li>
                                    <li>Indiquez le type de véhicule requis et le prix</li>
                                    <li>Validez - votre offre est visible instantanément !</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="offers">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                                Combien de temps mon offre reste-t-elle visible ?
                            </button>
                        </h2>
                        <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Votre offre reste visible jusqu'à ce que :
                                <ul>
                                    <li>Vous la marquez comme "Assignée" ou "Terminée"</li>
                                    <li>Vous la supprimiez manuellement</li>
                                    <li>La date de chargement/disponibilité soit dépassée de plus de 7 jours</li>
                                </ul>
                                Vous pouvez modifier ou supprimer vos offres à tout moment depuis "Mes offres".
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="offers">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                                Comment fonctionne le matching automatique ?
                            </button>
                        </h2>
                        <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Notre algorithme intelligent analyse :
                                <ul>
                                    <li>🗺️ La proximité géographique (départ/arrivée)</li>
                                    <li>📅 La compatibilité des dates</li>
                                    <li>🚚 Le type de véhicule et la capacité</li>
                                    <li>⭐ La réputation des partenaires</li>
                                </ul>
                                Un score de 0 à 100% indique la compatibilité. Consultez "Outils → Matching automatique" pour voir vos correspondances !
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PAIEMENT -->
                <div class="faq-item" data-category="payment">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq10">
                                Comment se passe le paiement ?
                            </button>
                        </h2>
                        <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Le paiement se fait directement entre le chargeur et le transporteur.
                                <?= APP_NAME ?> n'intervient pas dans les transactions financières mais vous permet de :
                                <ul>
                                    <li>Suivre vos transactions dans "Transactions"</li>
                                    <li>Enregistrer le statut (en attente, payé, etc.)</li>
                                    <li>Télécharger des rapports pour votre comptabilité</li>
                                </ul>
                                Nous recommandons de définir clairement les conditions de paiement avant d'accepter une mission.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="payment">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq11">
                                Quels sont vos tarifs ?
                            </button>
                        </h2>
                        <div id="faq11" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                L'inscription et la consultation des offres sont <strong>100% gratuites</strong>.
                                Nous proposons des formules premium optionnelles pour :
                                <ul>
                                    <li>📢 Mettre vos offres en avant</li>
                                    <li>🔔 Alertes illimitées</li>
                                    <li>📊 Analytics avancées</li>
                                    <li>⭐ Badge "Vérifié"</li>
                                </ul>
                                Consultez notre <a href="/pricing.php">page Tarifs</a> pour plus de détails.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SÉCURITÉ -->
                <div class="faq-item" data-category="security">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq12">
                                Comment vérifiez-vous les utilisateurs ?
                            </button>
                        </h2>
                        <div id="faq12" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Nous vérifions tous nos membres professionnels :
                                <ul>
                                    <li>✅ Vérification email obligatoire</li>
                                    <li>✅ SIRET/numéro d'entreprise requis</li>
                                    <li>✅ Documents professionnels (licence transport, assurance)</li>
                                    <li>✅ Système de notation et évaluations</li>
                                    <li>✅ Modération des offres suspectes</li>
                                </ul>
                                Les utilisateurs vérifiés ont un badge <i class="bi bi-patch-check-fill text-primary"></i> sur leur profil.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="security">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq13">
                                Mes données sont-elles sécurisées ?
                            </button>
                        </h2>
                        <div id="faq13" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Absolument. Nous prenons la sécurité très au sérieux :
                                <ul>
                                    <li>🔒 Connexion HTTPS chiffrée (SSL)</li>
                                    <li>🔒 Mots de passe hashés (bcrypt)</li>
                                    <li>🔒 Protection CSRF sur tous les formulaires</li>
                                    <li>🔒 Conformité RGPD</li>
                                    <li>🔒 Sauvegardes quotidiennes</li>
                                    <li>🔒 Authentification à deux facteurs (bientôt)</li>
                                </ul>
                                Vos données ne sont jamais vendues à des tiers.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="faq-item" data-category="security">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq14">
                                Que faire en cas de problème avec un utilisateur ?
                            </button>
                        </h2>
                        <div id="faq14" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Si vous rencontrez un problème :
                                <ol>
                                    <li>Essayez d'abord de résoudre à l'amiable via la messagerie</li>
                                    <li>Documentez le problème (captures d'écran, messages)</li>
                                    <li>Contactez notre support : <a href="/contact.php">Formulaire de contact</a></li>
                                    <li>Nous enquêterons et prendrons les mesures appropriées</li>
                                </ol>
                                En cas de fraude avérée, l'utilisateur sera banni définitivement.
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Still have questions? -->
            <div class="card shadow-sm mt-5 bg-light">
                <div class="card-body text-center py-4">
                    <h4><i class="bi bi-question-circle"></i> Vous ne trouvez pas votre réponse ?</h4>
                    <p class="text-muted">Notre équipe support est là pour vous aider</p>
                    <a href="/contact.php" class="btn btn-primary">
                        <i class="bi bi-envelope"></i> Contactez-nous
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// FAQ Search
document.getElementById('faqSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.faq-item');

    items.forEach(item => {
        const text = item.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

// Category Filter
document.querySelectorAll('[data-category]').forEach(btn => {
    btn.addEventListener('click', function() {
        const category = this.dataset.category;

        // Update active button
        document.querySelectorAll('[data-category]').forEach(b => b.classList.remove('active'));
        this.classList.add('active');

        // Filter items
        const items = document.querySelectorAll('.faq-item');
        items.forEach(item => {
            if (category === 'all' || item.dataset.category === category) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
