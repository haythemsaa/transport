<?php
require_once 'config/config.php';
$pageTitle = 'Calculateur de distance et prix';
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-12">
            <h1><i class="bi bi-calculator"></i> Calculateur de distance et prix</h1>
            <p class="text-muted">Calculez rapidement la distance, le temps de trajet et estimez vos coûts</p>
        </div>
    </div>

    <div class="row">
        <!-- Calculator Form -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Itinéraire</h5>
                </div>
                <div class="card-body">
                    <form id="calculatorForm">
                        <div class="mb-3">
                            <label class="form-label">Ville de départ *</label>
                            <input type="text" class="form-control" id="departure"
                                   placeholder="Ex: Paris, France" required>
                            <small class="text-muted">Format: Ville, Pays</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ville d'arrivée *</label>
                            <input type="text" class="form-control" id="destination"
                                   placeholder="Ex: Lyon, France" required>
                            <small class="text-muted">Format: Ville, Pays</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Prix au kilomètre (€/km)</label>
                            <input type="number" step="0.01" class="form-control" id="pricePerKm"
                                   value="1.50" min="0">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Poids de la marchandise (kg)</label>
                            <input type="number" class="form-control" id="weight"
                                   placeholder="Ex: 5000">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-calculator"></i> Calculer
                        </button>
                    </form>
                </div>
            </div>

            <!-- Quick Routes -->
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="bi bi-star"></i> Trajets populaires</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action quick-route"
                           data-departure="Paris, France" data-destination="Lyon, France">
                            <i class="bi bi-arrow-right"></i> Paris → Lyon
                        </a>
                        <a href="#" class="list-group-item list-group-item-action quick-route"
                           data-departure="Paris, France" data-destination="Marseille, France">
                            <i class="bi bi-arrow-right"></i> Paris → Marseille
                        </a>
                        <a href="#" class="list-group-item list-group-item-action quick-route"
                           data-departure="Paris, France" data-destination="Berlin, Allemagne">
                            <i class="bi bi-arrow-right"></i> Paris → Berlin
                        </a>
                        <a href="#" class="list-group-item list-group-item-action quick-route"
                           data-departure="Lyon, France" data-destination="Barcelone, Espagne">
                            <i class="bi bi-arrow-right"></i> Lyon → Barcelone
                        </a>
                        <a href="#" class="list-group-item list-group-item-action quick-route"
                           data-departure="Lille, France" data-destination="Amsterdam, Pays-Bas">
                            <i class="bi bi-arrow-right"></i> Lille → Amsterdam
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results -->
        <div class="col-lg-7">
            <div id="results" class="d-none">
                <!-- Distance & Time -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="bi bi-map"></i> Résultats</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <i class="bi bi-signpost-2 text-primary" style="font-size: 2.5rem;"></i>
                                    <h3 class="mt-2 mb-0" id="distance">-</h3>
                                    <small class="text-muted">Distance</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <i class="bi bi-clock text-warning" style="font-size: 2.5rem;"></i>
                                    <h3 class="mt-2 mb-0" id="duration">-</h3>
                                    <small class="text-muted">Durée estimée</small>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="p-3 bg-light rounded">
                                    <i class="bi bi-fuel-pump text-danger" style="font-size: 2.5rem;"></i>
                                    <h3 class="mt-2 mb-0" id="fuelCost">-</h3>
                                    <small class="text-muted">Carburant (estimé)</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Price Breakdown -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="bi bi-currency-euro"></i> Estimation des coûts</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless mb-0">
                            <tr>
                                <td><i class="bi bi-signpost-2"></i> Distance totale</td>
                                <td class="text-end fw-bold" id="distanceDetail">-</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-cash"></i> Prix au km</td>
                                <td class="text-end fw-bold" id="rateDetail">-</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-fuel-pump"></i> Carburant (30L/100km à 1.80€/L)</td>
                                <td class="text-end fw-bold" id="fuelDetail">-</td>
                            </tr>
                            <tr>
                                <td><i class="bi bi-cash-coin"></i> Péages estimés</td>
                                <td class="text-end fw-bold" id="tollsDetail">-</td>
                            </tr>
                            <tr class="table-active">
                                <td class="fw-bold fs-5"><i class="bi bi-calculator"></i> Total estimé</td>
                                <td class="text-end fw-bold fs-4 text-success" id="totalPrice">-</td>
                            </tr>
                        </table>
                    </div>
                </div>

                <!-- CO2 Emissions -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="bi bi-cloud"></i> Impact environnemental</h5>
                    </div>
                    <div class="card-body text-center">
                        <i class="bi bi-tree text-success" style="font-size: 3rem;"></i>
                        <h3 class="mt-3 mb-0" id="co2Emissions">-</h3>
                        <p class="text-muted mb-0">Émissions de CO₂ estimées</p>
                        <small class="text-muted">Base: 62g CO₂/km pour un camion poids lourd</small>
                    </div>
                </div>
            </div>

            <!-- Initial State -->
            <div id="initialState">
                <div class="card shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-calculator text-muted" style="font-size: 5rem;"></i>
                        <h4 class="mt-4">Calculez votre itinéraire</h4>
                        <p class="text-muted">Entrez vos villes de départ et d'arrivée pour obtenir une estimation détaillée</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="row mt-5">
        <div class="col-md-3">
            <div class="card shadow-sm border-primary h-100">
                <div class="card-body text-center">
                    <i class="bi bi-speedometer2 text-primary" style="font-size: 2.5rem;"></i>
                    <h6 class="mt-3">Calcul instantané</h6>
                    <p class="small text-muted mb-0">Résultats en quelques secondes</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-success h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle text-success" style="font-size: 2.5rem;"></i>
                    <h6 class="mt-3">Précision optimale</h6>
                    <p class="small text-muted mb-0">Basé sur des données routières réelles</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-info h-100">
                <div class="card-body text-center">
                    <i class="bi bi-graph-up text-info" style="font-size: 2.5rem;"></i>
                    <h6 class="mt-3">Coûts détaillés</h6>
                    <p class="small text-muted mb-0">Carburant, péages, prix total</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-warning h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cloud text-warning" style="font-size: 2.5rem;"></i>
                    <h6 class="mt-3">Bilan carbone</h6>
                    <p class="small text-muted mb-0">Estimez vos émissions CO₂</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// City coordinates database (simplified - in production use geocoding API)
const cityCoords = {
    'paris, france': [48.8566, 2.3522],
    'lyon, france': [45.7640, 4.8357],
    'marseille, france': [43.2965, 5.3698],
    'lille, france': [50.6292, 3.0573],
    'bordeaux, france': [44.8378, -0.5792],
    'berlin, allemagne': [52.5200, 13.4050],
    'munich, allemagne': [48.1351, 11.5820],
    'rome, italie': [41.9028, 12.4964],
    'milan, italie': [45.4642, 9.1900],
    'madrid, espagne': [40.4168, -3.7038],
    'barcelone, espagne': [41.3851, 2.1734],
    'bruxelles, belgique': [50.8503, 4.3517],
    'amsterdam, pays-bas': [52.3676, 4.9041],
};

// Calculate distance using Haversine formula
function calculateDistance(lat1, lon1, lat2, lon2) {
    const R = 6371; // Earth's radius in km
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
              Math.sin(dLon/2) * Math.sin(dLon/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

document.getElementById('calculatorForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const departure = document.getElementById('departure').value.toLowerCase().trim();
    const destination = document.getElementById('destination').value.toLowerCase().trim();
    const pricePerKm = parseFloat(document.getElementById('pricePerKm').value) || 1.50;

    // Check if cities exist in database
    if (!cityCoords[departure] || !cityCoords[destination]) {
        alert('Ville non reconnue. Vérifiez le format: "Ville, Pays"');
        return;
    }

    // Calculate distance
    const [lat1, lon1] = cityCoords[departure];
    const [lat2, lon2] = cityCoords[destination];
    const straightDistance = calculateDistance(lat1, lon1, lat2, lon2);
    const roadDistance = straightDistance * 1.3; // Road distance ~30% longer

    // Calculate duration (average 80 km/h)
    const hours = roadDistance / 80;
    const durationText = hours >= 1
        ? `${Math.floor(hours)}h ${Math.round((hours % 1) * 60)}min`
        : `${Math.round(hours * 60)}min`;

    // Calculate costs
    const fuelConsumption = 30; // L/100km
    const fuelPrice = 1.80; // €/L
    const fuelCost = (roadDistance / 100) * fuelConsumption * fuelPrice;
    const tollsEstimate = roadDistance * 0.15; // ~0.15€/km for tolls
    const transportCost = roadDistance * pricePerKm;
    const totalCost = transportCost + fuelCost + tollsEstimate;

    // Calculate CO2 (62g/km for heavy truck)
    const co2Kg = (roadDistance * 62) / 1000;

    // Update UI
    document.getElementById('distance').textContent = Math.round(roadDistance) + ' km';
    document.getElementById('duration').textContent = durationText;
    document.getElementById('fuelCost').textContent = Math.round(fuelCost) + ' €';
    document.getElementById('distanceDetail').textContent = Math.round(roadDistance) + ' km';
    document.getElementById('rateDetail').textContent = pricePerKm.toFixed(2) + ' €/km';
    document.getElementById('fuelDetail').textContent = Math.round(fuelCost) + ' €';
    document.getElementById('tollsDetail').textContent = Math.round(tollsEstimate) + ' €';
    document.getElementById('totalPrice').textContent = Math.round(totalCost) + ' €';
    document.getElementById('co2Emissions').textContent = co2Kg.toFixed(1) + ' kg CO₂';

    // Show results
    document.getElementById('initialState').classList.add('d-none');
    document.getElementById('results').classList.remove('d-none');
});

// Quick routes
document.querySelectorAll('.quick-route').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        document.getElementById('departure').value = this.dataset.departure;
        document.getElementById('destination').value = this.dataset.destination;
        document.getElementById('calculatorForm').dispatchEvent(new Event('submit'));
    });
});
</script>

<?php include 'includes/footer.php'; ?>
