<?php
require_once '../config/config.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();

    // Fetch active freight offers with coordinates
    $freightStmt = $db->prepare("
        SELECT
            fo.id,
            fo.loading_city,
            fo.loading_country,
            fo.loading_lat,
            fo.loading_lng,
            fo.delivery_city,
            fo.delivery_country,
            fo.delivery_lat,
            fo.delivery_lng,
            fo.loading_date,
            fo.delivery_date,
            fo.weight,
            fo.price,
            fo.cargo_description
        FROM freight_offers fo
        WHERE fo.status = 'active'
            AND fo.deleted_at IS NULL
            AND (fo.loading_lat IS NOT NULL AND fo.loading_lng IS NOT NULL
                 OR fo.delivery_lat IS NOT NULL AND fo.delivery_lng IS NOT NULL)
        ORDER BY fo.created_at DESC
        LIMIT 100
    ");
    $freightStmt->execute();
    $freight = $freightStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch available vehicle offers with coordinates
    $vehicleStmt = $db->prepare("
        SELECT
            vo.id,
            vo.vehicle_type,
            vo.departure_city,
            vo.departure_country,
            vo.departure_lat,
            vo.departure_lng,
            vo.destination_city,
            vo.destination_country,
            vo.destination_lat,
            vo.destination_lng,
            vo.available_date,
            vo.max_weight,
            vo.price_per_km
        FROM vehicle_offers vo
        WHERE vo.status = 'available'
            AND vo.deleted_at IS NULL
            AND (vo.departure_lat IS NOT NULL AND vo.departure_lng IS NOT NULL)
        ORDER BY vo.created_at DESC
        LIMIT 100
    ");
    $vehicleStmt->execute();
    $vehicles = $vehicleStmt->fetchAll(PDO::FETCH_ASSOC);

    // Format dates and numbers
    foreach ($freight as &$offer) {
        $offer['loading_date'] = formatDate($offer['loading_date']);
        $offer['delivery_date'] = formatDate($offer['delivery_date']);
        $offer['weight'] = number_format($offer['weight'], 0, ',', ' ');
        $offer['price'] = number_format($offer['price'], 2, ',', ' ');

        // Convert to float for JavaScript
        $offer['loading_lat'] = (float)$offer['loading_lat'];
        $offer['loading_lng'] = (float)$offer['loading_lng'];
        $offer['delivery_lat'] = (float)$offer['delivery_lat'];
        $offer['delivery_lng'] = (float)$offer['delivery_lng'];
    }

    foreach ($vehicles as &$vehicle) {
        $vehicle['available_date'] = formatDate($vehicle['available_date']);
        $vehicle['max_weight'] = number_format($vehicle['max_weight'], 0, ',', ' ');
        $vehicle['price_per_km'] = number_format($vehicle['price_per_km'], 2, ',', ' ');

        // Convert to float for JavaScript
        $vehicle['departure_lat'] = (float)$vehicle['departure_lat'];
        $vehicle['departure_lng'] = (float)$vehicle['departure_lng'];
        if ($vehicle['destination_lat']) {
            $vehicle['destination_lat'] = (float)$vehicle['destination_lat'];
            $vehicle['destination_lng'] = (float)$vehicle['destination_lng'];
        }
    }

    echo json_encode([
        'success' => true,
        'freight' => $freight,
        'vehicles' => $vehicles
    ]);

} catch (Exception $e) {
    logError('Map data error', ['error' => $e->getMessage()]);

    echo json_encode([
        'success' => false,
        'freight' => [],
        'vehicles' => [],
        'error' => 'Erreur lors du chargement des données'
    ]);
}
