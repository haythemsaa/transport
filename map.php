<?php
require_once 'config/config.php';
$pageTitle = 'Carte Interactive';
include 'includes/header.php';
?>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="container-fluid my-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4"><i class="bi bi-geo-alt"></i> Carte Interactive des Offres</h1>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-3 mb-md-0">Filtrer les offres</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="btn-group w-100" role="group">
                                <input type="checkbox" class="btn-check" id="show-freight" checked autocomplete="off">
                                <label class="btn btn-outline-primary" for="show-freight">
                                    <i class="bi bi-box-seam"></i> Fret
                                </label>

                                <input type="checkbox" class="btn-check" id="show-vehicles" checked autocomplete="off">
                                <label class="btn btn-outline-success" for="show-vehicles">
                                    <i class="bi bi-truck"></i> Véhicules
                                </label>

                                <button type="button" class="btn btn-outline-secondary" id="reset-map">
                                    <i class="bi bi-arrow-clockwise"></i> Réinitialiser
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div id="map" style="height: 600px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Légende</h6>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-geo-alt-fill text-primary fs-4 me-2"></i>
                        <span>Offres de fret (départ)</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-geo-alt-fill text-danger fs-4 me-2"></i>
                        <span>Offres de fret (arrivée)</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-geo-alt-fill text-success fs-4 me-2"></i>
                        <span>Véhicules disponibles</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">Statistiques</h6>
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="fw-bold text-primary fs-4" id="freight-count">0</div>
                            <small class="text-muted">Offres de fret</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-success fs-4" id="vehicle-count">0</div>
                            <small class="text-muted">Véhicules</small>
                        </div>
                        <div class="col-4">
                            <div class="fw-bold text-info fs-4" id="total-count">0</div>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialize the map centered on Europe
var map = L.map('map').setView([48.8566, 2.3522], 5); // Paris, France

// Add OpenStreetMap tiles
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19
}).addTo(map);

// Layer groups for different marker types
var freightLayer = L.layerGroup().addTo(map);
var vehicleLayer = L.layerGroup().addTo(map);

// Custom icons
var freightDepartIcon = L.divIcon({
    html: '<i class="bi bi-geo-alt-fill text-primary" style="font-size: 2rem;"></i>',
    className: 'custom-marker',
    iconSize: [30, 30],
    iconAnchor: [15, 30],
    popupAnchor: [0, -30]
});

var freightArrivalIcon = L.divIcon({
    html: '<i class="bi bi-geo-alt-fill text-danger" style="font-size: 2rem;"></i>',
    className: 'custom-marker',
    iconSize: [30, 30],
    iconAnchor: [15, 30],
    popupAnchor: [0, -30]
});

var vehicleIcon = L.divIcon({
    html: '<i class="bi bi-geo-alt-fill text-success" style="font-size: 2rem;"></i>',
    className: 'custom-marker',
    iconSize: [30, 30],
    iconAnchor: [15, 30],
    popupAnchor: [0, -30]
});

// Load map data
function loadMapData() {
    fetch('/api/map-data.php')
        .then(response => response.json())
        .then(data => {
            // Clear existing markers
            freightLayer.clearLayers();
            vehicleLayer.clearLayers();

            // Add freight offers
            data.freight.forEach(offer => {
                // Departure marker
                if (offer.loading_lat && offer.loading_lng) {
                    var departMarker = L.marker([offer.loading_lat, offer.loading_lng], {
                        icon: freightDepartIcon
                    }).addTo(freightLayer);

                    departMarker.bindPopup(`
                        <div class="p-2">
                            <h6 class="mb-2"><i class="bi bi-box-seam"></i> Offre de fret</h6>
                            <p class="mb-1"><strong>Départ:</strong> ${offer.loading_city}, ${offer.loading_country}</p>
                            <p class="mb-1"><strong>Arrivée:</strong> ${offer.delivery_city}, ${offer.delivery_country}</p>
                            <p class="mb-1"><strong>Date:</strong> ${offer.loading_date}</p>
                            <p class="mb-1"><strong>Poids:</strong> ${offer.weight} kg</p>
                            <p class="mb-2"><strong>Prix:</strong> ${offer.price} €</p>
                            <a href="/view-freight.php?id=${offer.id}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> Voir détails
                            </a>
                        </div>
                    `);
                }

                // Arrival marker
                if (offer.delivery_lat && offer.delivery_lng) {
                    var arrivalMarker = L.marker([offer.delivery_lat, offer.delivery_lng], {
                        icon: freightArrivalIcon
                    }).addTo(freightLayer);

                    arrivalMarker.bindPopup(`
                        <div class="p-2">
                            <h6 class="mb-2"><i class="bi bi-box-seam"></i> Destination</h6>
                            <p class="mb-1"><strong>Arrivée:</strong> ${offer.delivery_city}, ${offer.delivery_country}</p>
                            <p class="mb-2"><strong>Date:</strong> ${offer.delivery_date}</p>
                            <a href="/view-freight.php?id=${offer.id}" class="btn btn-sm btn-primary">
                                <i class="bi bi-eye"></i> Voir détails
                            </a>
                        </div>
                    `);
                }

                // Draw line between departure and arrival
                if (offer.loading_lat && offer.loading_lng && offer.delivery_lat && offer.delivery_lng) {
                    var polyline = L.polyline([
                        [offer.loading_lat, offer.loading_lng],
                        [offer.delivery_lat, offer.delivery_lng]
                    ], {
                        color: '#0d6efd',
                        weight: 2,
                        opacity: 0.5,
                        dashArray: '5, 10'
                    }).addTo(freightLayer);
                }
            });

            // Add vehicle offers
            data.vehicles.forEach(vehicle => {
                if (vehicle.departure_lat && vehicle.departure_lng) {
                    var marker = L.marker([vehicle.departure_lat, vehicle.departure_lng], {
                        icon: vehicleIcon
                    }).addTo(vehicleLayer);

                    marker.bindPopup(`
                        <div class="p-2">
                            <h6 class="mb-2"><i class="bi bi-truck"></i> Véhicule disponible</h6>
                            <p class="mb-1"><strong>Type:</strong> ${vehicle.vehicle_type}</p>
                            <p class="mb-1"><strong>Départ:</strong> ${vehicle.departure_city}, ${vehicle.departure_country}</p>
                            <p class="mb-1"><strong>Destination:</strong> ${vehicle.destination_city}, ${vehicle.destination_country}</p>
                            <p class="mb-1"><strong>Date:</strong> ${vehicle.available_date}</p>
                            <p class="mb-1"><strong>Capacité:</strong> ${vehicle.max_weight} kg</p>
                            <p class="mb-2"><strong>Prix:</strong> ${vehicle.price_per_km} €/km</p>
                            <a href="/view-vehicle.php?id=${vehicle.id}" class="btn btn-sm btn-success">
                                <i class="bi bi-eye"></i> Voir détails
                            </a>
                        </div>
                    `);
                }
            });

            // Update statistics
            document.getElementById('freight-count').textContent = data.freight.length;
            document.getElementById('vehicle-count').textContent = data.vehicles.length;
            document.getElementById('total-count').textContent = data.freight.length + data.vehicles.length;
        })
        .catch(error => {
            console.error('Erreur de chargement des données:', error);
        });
}

// Filter controls
document.getElementById('show-freight').addEventListener('change', function() {
    if (this.checked) {
        map.addLayer(freightLayer);
    } else {
        map.removeLayer(freightLayer);
    }
});

document.getElementById('show-vehicles').addEventListener('change', function() {
    if (this.checked) {
        map.addLayer(vehicleLayer);
    } else {
        map.removeLayer(vehicleLayer);
    }
});

document.getElementById('reset-map').addEventListener('click', function() {
    map.setView([48.8566, 2.3522], 5);
    document.getElementById('show-freight').checked = true;
    document.getElementById('show-vehicles').checked = true;
    map.addLayer(freightLayer);
    map.addLayer(vehicleLayer);
});

// Load data on page load
loadMapData();

// Refresh data every 30 seconds
setInterval(loadMapData, 30000);
</script>

<style>
.custom-marker {
    background: none;
    border: none;
}

.leaflet-popup-content-wrapper {
    border-radius: 8px;
}

.leaflet-popup-content {
    margin: 0;
    min-width: 250px;
}
</style>

<?php include 'includes/footer.php'; ?>
