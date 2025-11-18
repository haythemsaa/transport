</main>

<!-- Footer -->
<footer class="bg-dark text-light mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5><i class="bi bi-truck"></i> <?= APP_NAME ?></h5>
                <p class="text-muted">La plateforme n°1 de mise en relation pour le transport routier en Europe.</p>
            </div>
            <div class="col-md-2">
                <h6>Liens rapides</h6>
                <ul class="list-unstyled">
                    <li><a href="/search-freight.php" class="text-light text-decoration-none">Rechercher Fret</a></li>
                    <li><a href="/search-vehicles.php" class="text-light text-decoration-none">Rechercher Véhicules</a></li>
                    <li><a href="/directory.php" class="text-light text-decoration-none">Annuaire</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <h6>Informations</h6>
                <ul class="list-unstyled">
                    <li><a href="/about.php" class="text-light text-decoration-none">À propos</a></li>
                    <li><a href="/contact.php" class="text-light text-decoration-none">Contact</a></li>
                    <li><a href="/terms.php" class="text-light text-decoration-none">CGU</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6>Statistiques</h6>
                <div class="text-muted">
                    <?php
                        $freightModel = new FreightOffer();
                        $vehicleModel = new VehicleOffer();
                        $freightStats = $freightModel->getStats();
                        $vehicleStats = $vehicleModel->getStats();
                    ?>
                    <p class="mb-1">
                        <i class="bi bi-box-seam"></i>
                        <strong><?= number_format($freightStats['active_offers'] ?? 0) ?></strong> offres de fret
                    </p>
                    <p class="mb-1">
                        <i class="bi bi-truck-front"></i>
                        <strong><?= number_format($vehicleStats['available_vehicles'] ?? 0) ?></strong> véhicules disponibles
                    </p>
                </div>
            </div>
        </div>
        <hr class="bg-light">
        <div class="text-center text-muted">
            <small>&copy; <?= date('Y') ?> <?= APP_NAME ?>. Tous droits réservés. Version <?= APP_VERSION ?></small>
        </div>
    </div>
</footer>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery (pour compatibilité et facilité d'utilisation) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Custom JS -->
<script src="<?= ASSETS_URL ?>/js/main.js"></script>

<?php if (isset($extraJS)): ?>
    <?= $extraJS ?>
<?php endif; ?>

</body>
</html>
