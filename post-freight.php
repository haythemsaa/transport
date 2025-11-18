<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Veuillez vous connecter pour publier une offre');
    redirect('/login.php');
}

if (!isShipper()) {
    setFlash('danger', 'Vous devez être chargeur pour publier du fret');
    redirect('/dashboard.php');
}

$pageTitle = 'Publier une offre de fret';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        $data = [
            'user_id' => $_SESSION['user_id'],
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),

            'loading_address' => trim($_POST['loading_address'] ?? ''),
            'loading_city' => trim($_POST['loading_city'] ?? ''),
            'loading_postal_code' => trim($_POST['loading_postal_code'] ?? ''),
            'loading_country' => $_POST['loading_country'] ?? 'France',
            'loading_lat' => null, // TODO: Géocoder
            'loading_lng' => null,
            'loading_date' => $_POST['loading_date'] ?? '',
            'loading_time_start' => $_POST['loading_time_start'] ?? null,
            'loading_time_end' => $_POST['loading_time_end'] ?? null,

            'delivery_address' => trim($_POST['delivery_address'] ?? ''),
            'delivery_city' => trim($_POST['delivery_city'] ?? ''),
            'delivery_postal_code' => trim($_POST['delivery_postal_code'] ?? ''),
            'delivery_country' => $_POST['delivery_country'] ?? 'France',
            'delivery_lat' => null,
            'delivery_lng' => null,
            'delivery_date' => $_POST['delivery_date'] ?? '',
            'delivery_time_start' => $_POST['delivery_time_start'] ?? null,
            'delivery_time_end' => $_POST['delivery_time_end'] ?? null,

            'cargo_type' => $_POST['cargo_type'] ?? '',
            'cargo_description' => trim($_POST['cargo_description'] ?? ''),
            'weight' => floatval($_POST['weight'] ?? 0),
            'volume' => !empty($_POST['volume']) ? floatval($_POST['volume']) : null,
            'quantity' => !empty($_POST['quantity']) ? intval($_POST['quantity']) : 1,

            'vehicle_type' => $_POST['vehicle_type'] ?? '',
            'special_requirements' => trim($_POST['special_requirements'] ?? ''),

            'price' => !empty($_POST['price']) ? floatval($_POST['price']) : null,
            'price_negotiable' => isset($_POST['price_negotiable']) ? 1 : 0,
            'payment_terms' => trim($_POST['payment_terms'] ?? ''),
        ];

        // Validation
        if (empty($data['title'])) $errors[] = 'Le titre est requis';
        if (empty($data['loading_city'])) $errors[] = 'La ville de chargement est requise';
        if (empty($data['delivery_city'])) $errors[] = 'La ville de livraison est requise';
        if (empty($data['loading_date'])) $errors[] = 'La date de chargement est requise';
        if (empty($data['delivery_date'])) $errors[] = 'La date de livraison est requise';
        if (empty($data['cargo_type'])) $errors[] = 'Le type de cargo est requis';
        if (empty($data['vehicle_type'])) $errors[] = 'Le type de véhicule est requis';
        if ($data['weight'] <= 0) $errors[] = 'Le poids doit être supérieur à 0';

        if (empty($errors)) {
            $freightModel = new FreightOffer();
            $result = $freightModel->create($data);

            if ($result['success']) {
                setFlash('success', 'Votre offre de fret a été publiée avec succès !');
                redirect('/view-freight.php?id=' . $result['offer_id']);
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="bi bi-plus-circle"></i> Publier une offre de fret
                    </h3>
                </div>
                <div class="card-body p-4">
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= h($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="needs-validation" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

                        <!-- Informations générales -->
                        <h5 class="mb-3"><i class="bi bi-info-circle"></i> Informations générales</h5>

                        <div class="mb-3">
                            <label for="title" class="form-label">Titre de l'offre *</label>
                            <input type="text" class="form-control" id="title" name="title"
                                   value="<?= h($_POST['title'] ?? '') ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= h($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <hr>

                        <!-- Point de chargement -->
                        <h5 class="mb-3"><i class="bi bi-geo-alt text-success"></i> Point de chargement</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="loading_city" class="form-label">Ville *</label>
                                <input type="text" class="form-control" id="loading_city" name="loading_city"
                                       value="<?= h($_POST['loading_city'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="loading_postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="loading_postal_code" name="loading_postal_code"
                                       value="<?= h($_POST['loading_postal_code'] ?? '') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="loading_country" class="form-label">Pays</label>
                                <select class="form-select" id="loading_country" name="loading_country">
                                    <option value="France">France</option>
                                    <option value="Belgique">Belgique</option>
                                    <option value="Allemagne">Allemagne</option>
                                    <option value="Espagne">Espagne</option>
                                    <option value="Italie">Italie</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="loading_address" class="form-label">Adresse complète</label>
                            <input type="text" class="form-control" id="loading_address" name="loading_address"
                                   value="<?= h($_POST['loading_address'] ?? '') ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="loading_date" class="form-label">Date de chargement *</label>
                                <input type="date" class="form-control" id="loading_date" name="loading_date"
                                       value="<?= h($_POST['loading_date'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="loading_time_start" class="form-label">Heure de début</label>
                                <input type="time" class="form-control" id="loading_time_start" name="loading_time_start"
                                       value="<?= h($_POST['loading_time_start'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="loading_time_end" class="form-label">Heure de fin</label>
                                <input type="time" class="form-control" id="loading_time_end" name="loading_time_end"
                                       value="<?= h($_POST['loading_time_end'] ?? '') ?>">
                            </div>
                        </div>

                        <hr>

                        <!-- Point de livraison -->
                        <h5 class="mb-3"><i class="bi bi-geo-alt-fill text-danger"></i> Point de livraison</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="delivery_city" class="form-label">Ville *</label>
                                <input type="text" class="form-control" id="delivery_city" name="delivery_city"
                                       value="<?= h($_POST['delivery_city'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="delivery_postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="delivery_postal_code" name="delivery_postal_code"
                                       value="<?= h($_POST['delivery_postal_code'] ?? '') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="delivery_country" class="form-label">Pays</label>
                                <select class="form-select" id="delivery_country" name="delivery_country">
                                    <option value="France">France</option>
                                    <option value="Belgique">Belgique</option>
                                    <option value="Allemagne">Allemagne</option>
                                    <option value="Espagne">Espagne</option>
                                    <option value="Italie">Italie</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="delivery_address" class="form-label">Adresse complète</label>
                            <input type="text" class="form-control" id="delivery_address" name="delivery_address"
                                   value="<?= h($_POST['delivery_address'] ?? '') ?>">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="delivery_date" class="form-label">Date de livraison *</label>
                                <input type="date" class="form-control" id="delivery_date" name="delivery_date"
                                       value="<?= h($_POST['delivery_date'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="delivery_time_start" class="form-label">Heure de début</label>
                                <input type="time" class="form-control" id="delivery_time_start" name="delivery_time_start"
                                       value="<?= h($_POST['delivery_time_start'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="delivery_time_end" class="form-label">Heure de fin</label>
                                <input type="time" class="form-control" id="delivery_time_end" name="delivery_time_end"
                                       value="<?= h($_POST['delivery_time_end'] ?? '') ?>">
                            </div>
                        </div>

                        <hr>

                        <!-- Détails du fret -->
                        <h5 class="mb-3"><i class="bi bi-box-seam"></i> Détails du fret</h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="cargo_type" class="form-label">Type de cargo *</label>
                                <select class="form-select" id="cargo_type" name="cargo_type" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="pallets">Palettes</option>
                                    <option value="containers">Conteneurs</option>
                                    <option value="bulk">Vrac</option>
                                    <option value="vehicles">Véhicules</option>
                                    <option value="refrigerated">Réfrigéré</option>
                                    <option value="dangerous">Matières dangereuses</option>
                                    <option value="other">Autre</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="weight" class="form-label">Poids (tonnes) *</label>
                                <input type="number" class="form-control" id="weight" name="weight" step="0.1"
                                       value="<?= h($_POST['weight'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="volume" class="form-label">Volume (m³)</label>
                                <input type="number" class="form-control" id="volume" name="volume" step="0.1"
                                       value="<?= h($_POST['volume'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quantity" class="form-label">Quantité</label>
                                <input type="number" class="form-control" id="quantity" name="quantity"
                                       value="<?= h($_POST['quantity'] ?? '1') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="vehicle_type" class="form-label">Type de véhicule requis *</label>
                                <select class="form-select" id="vehicle_type" name="vehicle_type" required>
                                    <option value="">Sélectionner...</option>
                                    <option value="van">Fourgon</option>
                                    <option value="truck">Camion</option>
                                    <option value="semi_trailer">Semi-remorque</option>
                                    <option value="container_truck">Porte-conteneur</option>
                                    <option value="refrigerated_truck">Camion frigorifique</option>
                                    <option value="flatbed">Plateau</option>
                                    <option value="tanker">Citerne</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="cargo_description" class="form-label">Description du cargo</label>
                            <textarea class="form-control" id="cargo_description" name="cargo_description" rows="2"><?= h($_POST['cargo_description'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="special_requirements" class="form-label">Exigences particulières</label>
                            <textarea class="form-control" id="special_requirements" name="special_requirements" rows="2"
                                      placeholder="Ex: Chauffeur parlant anglais, assurance spécifique..."><?= h($_POST['special_requirements'] ?? '') ?></textarea>
                        </div>

                        <hr>

                        <!-- Prix et conditions -->
                        <h5 class="mb-3"><i class="bi bi-currency-euro"></i> Prix et conditions</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">Prix proposé (€)</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01"
                                       value="<?= h($_POST['price'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="payment_terms" class="form-label">Conditions de paiement</label>
                                <input type="text" class="form-control" id="payment_terms" name="payment_terms"
                                       value="<?= h($_POST['payment_terms'] ?? '') ?>"
                                       placeholder="Ex: 30 jours fin de mois">
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="price_negotiable" name="price_negotiable"
                                   <?= isset($_POST['price_negotiable']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="price_negotiable">
                                Prix négociable
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle"></i> Publier l'offre
                            </button>
                            <a href="/dashboard.php" class="btn btn-outline-secondary btn-lg">
                                Annuler
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
