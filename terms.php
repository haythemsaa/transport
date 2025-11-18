<?php
require_once 'config/config.php';
$pageTitle = 'Conditions Générales d\'Utilisation';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h1 class="mb-4">Conditions Générales d'Utilisation</h1>
            <p class="text-muted">Dernière mise à jour : <?= date('d/m/Y') ?></p>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">1. Objet</h3>
                    <p>
                        Les présentes Conditions Générales d'Utilisation (CGU) régissent l'utilisation de la plateforme
                        <?= APP_NAME ?>, une bourse de fret en ligne permettant la mise en relation entre transporteurs
                        et chargeurs.
                    </p>
                    <p>
                        L'accès et l'utilisation de la plateforme impliquent l'acceptation pleine et entière des présentes CGU.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">2. Inscription et Compte Utilisateur</h3>
                    <h5>2.1 Conditions d'inscription</h5>
                    <p>
                        L'inscription sur <?= APP_NAME ?> est réservée aux professionnels du transport et de la logistique.
                        Vous devez être une entreprise légalement constituée et disposer de toutes les autorisations
                        nécessaires à l'exercice de votre activité.
                    </p>
                    <h5>2.2 Informations de compte</h5>
                    <p>
                        Vous vous engagez à fournir des informations exactes, complètes et à jour lors de votre inscription.
                        Vous êtes responsable de la confidentialité de vos identifiants de connexion.
                    </p>
                    <h5>2.3 Vérification</h5>
                    <p>
                        <?= APP_NAME ?> se réserve le droit de vérifier l'authenticité des informations fournies et de
                        demander des documents justificatifs (SIRET, licence de transport, assurance professionnelle, etc.).
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">3. Services Proposés</h3>
                    <h5>3.1 Bourse de fret</h5>
                    <p>
                        La plateforme permet aux chargeurs de publier leurs offres de fret et aux transporteurs de
                        proposer leurs véhicules disponibles. <?= APP_NAME ?> agit uniquement comme intermédiaire
                        et ne participe pas directement aux transactions de transport.
                    </p>
                    <h5>3.2 Messagerie</h5>
                    <p>
                        Un système de messagerie instantanée permet aux utilisateurs de communiquer directement pour
                        négocier les conditions de transport.
                    </p>
                    <h5>3.3 Système d'évaluation</h5>
                    <p>
                        Les utilisateurs peuvent s'évaluer mutuellement après chaque transaction pour construire une
                        réputation basée sur la qualité du service.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">4. Obligations des Utilisateurs</h3>
                    <h5>4.1 Usage conforme</h5>
                    <ul>
                        <li>Utiliser la plateforme uniquement à des fins professionnelles légales</li>
                        <li>Ne pas publier de fausses offres ou d'informations trompeuses</li>
                        <li>Respecter les engagements pris envers les autres utilisateurs</li>
                        <li>Ne pas utiliser la plateforme pour des activités frauduleuses</li>
                    </ul>
                    <h5>4.2 Contenu publié</h5>
                    <p>
                        Vous êtes seul responsable du contenu que vous publiez sur la plateforme. Vous garantissez
                        que ce contenu ne viole aucune loi ni aucun droit de tiers.
                    </p>
                    <h5>4.3 Assurances et licences</h5>
                    <p>
                        Les transporteurs doivent disposer de toutes les licences et assurances nécessaires à
                        l'exercice de leur activité conformément à la réglementation en vigueur.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">5. Transactions et Paiements</h3>
                    <h5>5.1 Relations contractuelles</h5>
                    <p>
                        Les contrats de transport sont conclus directement entre chargeurs et transporteurs.
                        <?= APP_NAME ?> n'est pas partie à ces contrats et n'assume aucune responsabilité quant à
                        leur exécution.
                    </p>
                    <h5>5.2 Conditions de paiement</h5>
                    <p>
                        Les conditions de paiement (montant, délais, modalités) sont librement négociées entre les parties.
                        <?= APP_NAME ?> peut proposer des services de paiement sécurisé optionnels.
                    </p>
                    <h5>5.3 Litiges</h5>
                    <p>
                        En cas de litige concernant une transaction, les parties s'engagent à tenter une résolution
                        amiable. <?= APP_NAME ?> peut proposer ses services de médiation sans toutefois être tenue
                        d'intervenir.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">6. Tarification</h3>
                    <h5>6.1 Abonnements</h5>
                    <p>
                        L'accès à <?= APP_NAME ?> peut être gratuit ou payant selon les formules d'abonnement proposées.
                        Les tarifs en vigueur sont affichés sur le site.
                    </p>
                    <h5>6.2 Commissions</h5>
                    <p>
                        <?= APP_NAME ?> peut prélever une commission sur les transactions réalisées via la plateforme.
                        Le montant de cette commission est communiqué avant toute transaction.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">7. Propriété Intellectuelle</h3>
                    <p>
                        Tous les éléments de la plateforme (logos, textes, graphismes, logiciels, bases de données)
                        sont la propriété exclusive de <?= APP_NAME ?> ou de ses partenaires.
                    </p>
                    <p>
                        Toute reproduction, représentation, modification ou exploitation non autorisée est strictement interdite.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">8. Données Personnelles</h3>
                    <p>
                        <?= APP_NAME ?> s'engage à protéger vos données personnelles conformément au Règlement Général
                        sur la Protection des Données (RGPD).
                    </p>
                    <p>
                        Vos données sont collectées pour les besoins du service et ne sont pas cédées à des tiers sans
                        votre consentement, sauf obligation légale.
                    </p>
                    <p>
                        Vous disposez d'un droit d'accès, de rectification, de suppression et d'opposition concernant
                        vos données personnelles.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">9. Responsabilité</h3>
                    <h5>9.1 Limitation de responsabilité</h5>
                    <p>
                        <?= APP_NAME ?> ne peut être tenue responsable :
                    </p>
                    <ul>
                        <li>De l'inexécution ou de la mauvaise exécution des contrats de transport</li>
                        <li>Des dommages causés aux marchandises transportées</li>
                        <li>Des pertes financières résultant de transactions entre utilisateurs</li>
                        <li>Des interruptions temporaires du service pour maintenance ou incidents techniques</li>
                    </ul>
                    <h5>9.2 Comportement des utilisateurs</h5>
                    <p>
                        Chaque utilisateur est responsable de ses actes et de son comportement sur la plateforme.
                        <?= APP_NAME ?> ne peut être tenue responsable des agissements de ses utilisateurs.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">10. Suspension et Résiliation</h3>
                    <h5>10.1 Par l'utilisateur</h5>
                    <p>
                        Vous pouvez résilier votre compte à tout moment en nous contactant. La résiliation prend effet
                        immédiatement, sans préjudice des obligations en cours.
                    </p>
                    <h5>10.2 Par <?= APP_NAME ?></h5>
                    <p>
                        <?= APP_NAME ?> se réserve le droit de suspendre ou de résilier un compte en cas de :
                    </p>
                    <ul>
                        <li>Violation des présentes CGU</li>
                        <li>Comportement frauduleux ou abusif</li>
                        <li>Non-paiement des sommes dues</li>
                        <li>Inactivité prolongée du compte</li>
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">11. Modification des CGU</h3>
                    <p>
                        <?= APP_NAME ?> se réserve le droit de modifier les présentes CGU à tout moment. Les utilisateurs
                        seront informés des modifications par email ou via la plateforme.
                    </p>
                    <p>
                        La poursuite de l'utilisation de la plateforme après modification vaut acceptation des nouvelles CGU.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="mb-3">12. Loi Applicable et Juridiction</h3>
                    <p>
                        Les présentes CGU sont régies par le droit français.
                    </p>
                    <p>
                        En cas de litige, les parties s'engagent à rechercher une solution amiable. À défaut, les
                        tribunaux français seront seuls compétents.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm bg-light">
                <div class="card-body p-4">
                    <h3 class="mb-3">13. Contact</h3>
                    <p>
                        Pour toute question concernant les présentes CGU, vous pouvez nous contacter :
                    </p>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-envelope"></i> Email : <a href="mailto:legal@teleroute-marketplace.com">legal@teleroute-marketplace.com</a></li>
                        <li><i class="bi bi-telephone"></i> Téléphone : +33 1 23 45 67 89</li>
                        <li><i class="bi bi-geo-alt"></i> Adresse : 123 Avenue de la Logistique, 75001 Paris, France</li>
                    </ul>
                </div>
            </div>

            <div class="text-center mt-5">
                <a href="/register.php" class="btn btn-primary">
                    <i class="bi bi-person-plus"></i> Accepter et créer un compte
                </a>
                <a href="/" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-arrow-left"></i> Retour à l'accueil
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
