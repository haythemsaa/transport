<?php
require_once 'config/config.php';
require_once 'config/database.php';

if (!isLoggedIn()) {
    setFlash('warning', 'Veuillez vous connecter');
    redirect('/login.php');
}

if (!isTransporter()) {
    setFlash('danger', 'Accès refusé');
    redirect('/dashboard.php');
}

$offerId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId = $_SESSION['user_id'];

if (!$offerId) {
    setFlash('danger', 'Offre introuvable');
    redirect('/my-offers.php?type=vehicle');
}

$vehicleModel = new VehicleOffer();
$offer = $vehicleModel->getById($offerId);

if (!$offer || $offer['user_id'] != $userId) {
    setFlash('danger', 'Offre introuvable ou accès refusé');
    redirect('/my-offers.php?type=vehicle');
}

$pageTitle = 'Modifier l\'offre de véhicule';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token de sécurité invalide';
    } else {
        $data = [
            'title' => trim($_POST['title'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'departure_city' => trim($_POST['departure_city'] ?? ''),
            'departure_postal_code' => trim($_POST['departure_postal_code'] ?? ''),
            'departure_country' => $_POST['departure_country'] ?? 'France',
            'departure_address' => trim($_POST['departure_address'] ?? ''),
            'available_from' => $_POST['available_from'] ?? '',
            'destination_city' => trim($_POST['destination_city'] ?? ''),
            'destination_postal_code' => trim($_POST['destination_postal_code'] ?? ''),
            'destination_country' => $_POST['destination_country'] ?? '',
            'destination_address' => trim($_POST['destination_address'] ?? ''),
            'available_until' => !empty($_POST['available_until']) ? $_POST['available_until'] : null,
            'vehicle_type' => $_POST['vehicle_type'] ?? '',
            'vehicle_brand' => trim($_POST['vehicle_brand'] ?? ''),
            'vehicle_model' => trim($_POST['vehicle_model'] ?? ''),
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
            'status' => $_POST['status'] ?? 'available',
        ];

        if (empty($data['title'])) $errors[] = 'Le titre est requis';
        if (empty($data['departure_city'])) $errors[] = 'La ville de départ est requise';
        if ($data['max_weight'] <= 0) $errors[] = 'La capacité doit être supérieure à 0';

        if (empty($errors)) {
            $result = $vehicleModel->update($offerId, $userId, $data);

            if ($result['success']) {
                setFlash('success', 'Offre mise à jour avec succès !');
                redirect('/view-vehicle.php?id=' . $offerId);
            } else {
                $errors[] = $result['error'];
            }
        }
    }
} else {
    $_POST = $offer;
}

include 'includes/header.php';
?>

<div class="container my-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/dashboard.php">Tableau de bord</a></li>
            <li class="breadcrumb-item"><a href="/my-offers.php?type=vehicle">Mes offres</a></li>
            <li class="breadcrumb-item active">Modifier</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h3 class="mb-0">
                        <i class="bi bi-pencil"></i> Modifier l'offre de véhicule
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

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?= getCsrfToken() ?>">

                        <div class="mb-3">
                            <label class="form-label">Statut</label>
                            <select class="form-select" name="status">
                                <option value="available" <?= ($offer['status'] ?? 'available') === 'available' ? 'selected' : '' ?>>Disponible</option>
                                <option value="assigned" <?= ($offer['status'] ?? '') === 'assigned' ? 'selected' : '' ?>>Assigné</option>
                                <option value="in_transit" <?= ($offer['status'] ?? '') === 'in_transit' ? 'selected' : '' ?>>En transit</option>
                                <option value="unavailable" <?= ($offer['status'] ?? '') === 'unavailable' ? 'selected' : '' ?>>Non disponible</option>
                            </select>
                        </div>

                        <hr>

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

                        <h5 class="mb-3"><i class="bi bi-truck"></i> Informations du véhicule</h5>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="vehicle_type" class="form-label">Type de véhicule *</label>
                                <select class="form-select" id="vehicle_type" name="vehicle_type" required>
                                    <option value="van" <?= ($_POST['vehicle_type'] ?? '') === 'van' ? 'selected' : '' ?>>Fourgon</option>
                                    <option value="truck" <?= ($_POST['vehicle_type'] ?? '') === 'truck' ? 'selected' : '' ?>>Camion</option>
                                    <option value="semi_trailer" <?= ($_POST['vehicle_type'] ?? '') === 'semi_trailer' ? 'selected' : '' ?>>Semi-remorque</option>
                                    <option value="container_truck" <?= ($_POST['vehicle_type'] ?? '') === 'container_truck' ? 'selected' : '' ?>>Porte-conteneur</option>
                                    <option value="refrigerated_truck" <?= ($_POST['vehicle_type'] ?? '') === 'refrigerated_truck' ? 'selected' : '' ?>>Camion frigorifique</option>
                                    <option value="flatbed" <?= ($_POST['vehicle_type'] ?? '') === 'flatbed' ? 'selected' : '' ?>>Plateau</option>
                                    <option value="tanker" <?= ($_POST['vehicle_type'] ?? '') === 'tanker' ? 'selected' : '' ?>>Citerne</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="vehicle_brand" class="form-label">Marque</label>
                                <input type="text" class="form-control" id="vehicle_brand" name="vehicle_brand"
                                       value="<?= h($_POST['vehicle_brand'] ?? '') ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="vehicle_model" class="form-label">Modèle</label>
                                <input type="text" class="form-control" id="vehicle_model" name="vehicle_model"
                                       value="<?= h($_POST['vehicle_model'] ?? '') ?>">
                            </div>
                        </div>

                        <hr>

                        <h5 class="mb-3"><i class="bi bi-geo-alt"></i> Localisation</h5>

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
                                    <option value="France" <?= ($_POST['departure_country'] ?? 'France') === 'France' ? 'selected' : '' ?>>France</option>
                                    <option value="Belgique" <?= ($_POST['departure_country'] ?? '') === 'Belgique' ? 'selected' : '' ?>>Belgique</option>
                                    <option value="Allemagne" <?= ($_POST['departure_country'] ?? '') === 'Allemagne' ? 'selected' : '' ?>>Allemagne</option>
                                    <option value="Espagne" <?= ($_POST['departure_country'] ?? '') === 'Espagne' ? 'selected' : '' ?>>Espagne</option>
                                    <option value="Italie" <?= ($_POST['departure_country'] ?? '') === 'Italie' ? 'selected' : '' ?>>Italie</option>
                                </select>
                            </div>
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
                                    <option value="France" <?= ($_POST['destination_country'] ?? '') === 'France' ? 'selected' : '' ?>>France</option>
                                    <option value="Belgique" <?= ($_POST['destination_country'] ?? '') === 'Belgique' ? 'selected' : '' ?>>Belgique</option>
                                    <option value="Allemagne" <?= ($_POST['destination_country'] ?? '') === 'Allemagne' ? 'selected' : '' ?>>Allemagne</option>
                                    <option value="Espagne" <?= ($_POST['destination_country'] ?? '') === 'Espagne' ? 'selected' : '' ?>>Espagne</option>
                                    <option value="Italie" <?= ($_POST['destination_country'] ?? '') === 'Italie' ? 'selected' : '' ?>>Italie</option>
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

                        <h5 class="mb-3"><i class="bi bi-gear"></i> Équipements</h5>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="has_gps" name="has_gps"
                                           <?= ($_POST['has_gps'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="has_gps">
                                        <i class="bi bi-geo"></i> GPS
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="has_refrigeration" name="has_refrigeration"
                                           <?= ($_POST['has_refrigeration'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="has_refrigeration">
                                        <i class="bi bi-snow"></i> Réfrigération
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="has_tail_lift" name="has_tail_lift"
                                           <?= ($_POST['has_tail_lift'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="has_tail_lift">
                                        <i class="bi bi-arrow-down-up"></i> Hayon
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="has_adr" name="has_adr"
                                           <?= ($_POST['has_adr'] ?? 0) ? 'checked' : '' ?>>
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
                                   <?= ($_POST['price_negotiable'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="price_negotiable">
                                Prix négociable
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle"></i> Enregistrer les modifications
                            </button>
                            <a href="/view-vehicle.php?id=<?= $offerId ?>" class="btn btn-outline-secondary btn-lg">
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
