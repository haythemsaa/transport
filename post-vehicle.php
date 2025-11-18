<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Veuillez vous connecter pour publier une offre');
    redirect('/login.php');
}

if (!isTransporter()) {
    setFlash('danger', 'Vous devez être transporteur pour publier un véhicule');
    redirect('/dashboard.php');
}

$pageTitle = 'Publier une offre de véhicule';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        $data = [
            'user_id' => $_SESSION['user_id'],
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),

            'departure_address' => trim($_POST['departure_address'] ?? ''),
            'departure_city' => trim($_POST['departure_city'] ?? ''),
            'departure_postal_code' => trim($_POST['departure_postal_code'] ?? ''),
            'departure_country' => $_POST['departure_country'] ?? 'France',
            'departure_lat' => null,
            'departure_lng' => null,
            'available_from' => $_POST['available_from'] ?? '',

            'destination_address' => trim($_POST['destination_address'] ?? ''),
            'destination_city' => trim($_POST['destination_city'] ?? ''),
            'destination_postal_code' => trim($_POST['destination_postal_code'] ?? ''),
            'destination_country' => $_POST['destination_country'] ?? '',
            'destination_lat' => null,
            'destination_lng' => null,
            'available_until' => !empty($_POST['available_until']) ? $_POST['available_until'] : null,

            'vehicle_type' => $_POST['vehicle_type'] ?? '',
            'vehicle_brand' => trim($_POST['vehicle_brand'] ?? ''),
            'vehicle_model' => trim($_POST['vehicle_model'] ?? ''),
            'registration_number' => trim($_POST['registration_number'] ?? ''),

            'max_weight' => floatval($_POST['max_weight'] ?? 0),
            'max_volume' => !empty($_POST['max_volume']) ? floatval($_POST['max_volume']) : null,
            'max_pallets' => !empty($_POST['max_pallets']) ? intval($_POST['max_pallets']) : null,

            'has_gps' => isset($_POST['has_gps']) ? 1 : 0,
            'has_refrigeration' => isset($_POST['has_refrigeration']) ? 1 : 0,
            'has_tail_lift' => isset($_POST['has_tail_lift']) ? 1 : 0,
            'has_adr' => isset($_POST['has_adr']) ? 1 : 0,
            'equipment_details' => trim($_POST['equipment_details'] ?? ''),

            'price_per_km' => !empty($_POST['price_per_km']) ? floatval($_POST['price_per_km']) : null,
            'min_price' => !empty($_POST['min_price']) ? floatval($_POST['min_price']) : null,
            'price_negotiable' => isset($_POST['price_negotiable']) ? 1 : 0,
        ];

        // Validation
        if (empty($data['title'])) $errors[] = 'Le titre est requis';
        if (empty($data['departure_city'])) $errors[] = 'La ville de départ est requise';
        if (empty($data['available_from'])) $errors[] = 'La date de disponibilité est requise';
        if (empty($data['vehicle_type'])) $errors[] = 'Le type de véhicule est requis';
        if ($data['max_weight'] <= 0) $errors[] = 'La capacité maximale doit être supérieure à 0';

        if (empty($errors)) {
            $vehicleModel = new VehicleOffer();
            $result = $vehicleModel->create($data);

            if ($result['success']) {
                setFlash('success', 'Votre offre de véhicule a été publiée avec succès !');
                redirect('/view-vehicle.php?id=' . $result['offer_id']);
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
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0">
                        <i class="bi bi-plus-circle"></i> Publier une offre de véhicule
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
                                   value="<?= h($_POST['title'] ?? '') ?>" required
                                   placeholder="Ex: Semi-remorque 25t disponible Paris → Lyon">
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= h($_POST['description'] ?? '') ?></textarea>
                        </div>

                        <hr>

                        <!-- Informations du véhicule -->
                        <h5 class="mb-3"><i class="bi bi-truck"></i> Informations du véhicule</h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="vehicle_type" class="form-label">Type de véhicule *</label>
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
                            <div class="col-md-4 mb-3">
                                <label for="vehicle_brand" class="form-label">Marque</label>
                                <input type="text" class="form-control" id="vehicle_brand" name="vehicle_brand"
                                       value="<?= h($_POST['vehicle_brand'] ?? '') ?>" placeholder="Ex: Mercedes, Volvo...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="vehicle_model" class="form-label">Modèle</label>
                                <input type="text" class="form-control" id="vehicle_model" name="vehicle_model"
                                       value="<?= h($_POST['vehicle_model'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="registration_number" class="form-label">Numéro d'immatriculation</label>
                            <input type="text" class="form-control" id="registration_number" name="registration_number"
                                   value="<?= h($_POST['registration_number'] ?? '') ?>">
                        </div>

                        <hr>

                        <!-- Localisation et disponibilité -->
                        <h5 class="mb-3"><i class="bi bi-geo-alt"></i> Localisation et disponibilité</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="departure_city" class="form-label">Ville de départ *</label>
                                <input type="text" class="form-control" id="departure_city" name="departure_city"
                                       value="<?= h($_POST['departure_city'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="departure_postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="departure_postal_code" name="departure_postal_code"
                                       value="<?= h($_POST['departure_postal_code'] ?? '') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="departure_country" class="form-label">Pays</label>
                                <select class="form-select" id="departure_country" name="departure_country">
                                    <option value="France">France</option>
                                    <option value="Belgique">Belgique</option>
                                    <option value="Allemagne">Allemagne</option>
                                    <option value="Espagne">Espagne</option>
                                    <option value="Italie">Italie</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="departure_address" class="form-label">Adresse de départ</label>
                            <input type="text" class="form-control" id="departure_address" name="departure_address"
                                   value="<?= h($_POST['departure_address'] ?? '') ?>">
                        </div>

                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle"></i> <strong>Destination (optionnel)</strong>
                            <p class="mb-0 small">Laissez vide si vous êtes flexible sur la destination</p>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="destination_city" class="form-label">Ville de destination</label>
                                <input type="text" class="form-control" id="destination_city" name="destination_city"
                                       value="<?= h($_POST['destination_city'] ?? '') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="destination_postal_code" class="form-label">Code postal</label>
                                <input type="text" class="form-control" id="destination_postal_code" name="destination_postal_code"
                                       value="<?= h($_POST['destination_postal_code'] ?? '') ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="destination_country" class="form-label">Pays</label>
                                <select class="form-select" id="destination_country" name="destination_country">
                                    <option value="">Flexible</option>
                                    <option value="France">France</option>
                                    <option value="Belgique">Belgique</option>
                                    <option value="Allemagne">Allemagne</option>
                                    <option value="Espagne">Espagne</option>
                                    <option value="Italie">Italie</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="available_from" class="form-label">Disponible à partir du *</label>
                                <input type="date" class="form-control" id="available_from" name="available_from"
                                       value="<?= h($_POST['available_from'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="available_until" class="form-label">Disponible jusqu'au</label>
                                <input type="date" class="form-control" id="available_until" name="available_until"
                                       value="<?= h($_POST['available_until'] ?? '') ?>">
                            </div>
                        </div>

                        <hr>

                        <!-- Capacités -->
                        <h5 class="mb-3"><i class="bi bi-boxes"></i> Capacités</h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="max_weight" class="form-label">Poids max (tonnes) *</label>
                                <input type="number" class="form-control" id="max_weight" name="max_weight" step="0.1"
                                       value="<?= h($_POST['max_weight'] ?? '') ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="max_volume" class="form-label">Volume max (m³)</label>
                                <input type="number" class="form-control" id="max_volume" name="max_volume" step="0.1"
                                       value="<?= h($_POST['max_volume'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="max_pallets" class="form-label">Palettes max</label>
                                <input type="number" class="form-control" id="max_pallets" name="max_pallets"
                                       value="<?= h($_POST['max_pallets'] ?? '') ?>">
                            </div>
                        </div>

                        <hr>

                        <!-- Équipements -->
                        <h5 class="mb-3"><i class="bi bi-gear"></i> Équipements</h5>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="has_gps" name="has_gps"
                                           <?= isset($_POST['has_gps']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="has_gps">
                                        <i class="bi bi-geo"></i> GPS
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="has_refrigeration" name="has_refrigeration"
                                           <?= isset($_POST['has_refrigeration']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="has_refrigeration">
                                        <i class="bi bi-snow"></i> Réfrigération
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="has_tail_lift" name="has_tail_lift"
                                           <?= isset($_POST['has_tail_lift']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="has_tail_lift">
                                        <i class="bi bi-arrow-down-up"></i> Hayon élévateur
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="has_adr" name="has_adr"
                                           <?= isset($_POST['has_adr']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="has_adr">
                                        <i class="bi bi-exclamation-triangle"></i> ADR
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="equipment_details" class="form-label">Détails supplémentaires</label>
                            <textarea class="form-control" id="equipment_details" name="equipment_details" rows="2"><?= h($_POST['equipment_details'] ?? '') ?></textarea>
                        </div>

                        <hr>

                        <!-- Tarification -->
                        <h5 class="mb-3"><i class="bi bi-currency-euro"></i> Tarification</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price_per_km" class="form-label">Prix par km (€)</label>
                                <input type="number" class="form-control" id="price_per_km" name="price_per_km" step="0.01"
                                       value="<?= h($_POST['price_per_km'] ?? '') ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="min_price" class="form-label">Prix minimum (€)</label>
                                <input type="number" class="form-control" id="min_price" name="min_price" step="0.01"
                                       value="<?= h($_POST['min_price'] ?? '') ?>">
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
                            <button type="submit" class="btn btn-success btn-lg">
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
