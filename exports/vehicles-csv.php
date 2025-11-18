<?php
require_once '../config/config.php';
requireLogin();

$user = $_SESSION['user'];

try {
    $db = Database::getInstance()->getConnection();

    // Get filter from query params
    $status = $_GET['status'] ?? 'all';

    // Build query
    $where = "vo.user_id = ? AND vo.deleted_at IS NULL";
    $params = [$user['id']];

    if ($status !== 'all') {
        $where .= " AND vo.status = ?";
        $params[] = $status;
    }

    $stmt = $db->prepare("
        SELECT
            vo.id,
            vo.vehicle_type,
            vo.departure_city,
            vo.departure_country,
            vo.departure_postal_code,
            vo.destination_city,
            vo.destination_country,
            vo.destination_postal_code,
            vo.available_date,
            vo.length,
            vo.width,
            vo.height,
            vo.max_weight,
            vo.volume,
            vo.equipment,
            vo.price_per_km,
            vo.status,
            vo.created_at
        FROM vehicle_offers vo
        WHERE $where
        ORDER BY vo.created_at DESC
    ");
    $stmt->execute($params);
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="offres_vehicules_' . date('Y-m-d_His') . '.csv"');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Write CSV header
    fputcsv($output, [
        'ID',
        'Type de véhicule',
        'Ville de départ',
        'Pays de départ',
        'Code postal départ',
        'Ville de destination',
        'Pays de destination',
        'Code postal destination',
        'Date de disponibilité',
        'Longueur (m)',
        'Largeur (m)',
        'Hauteur (m)',
        'Poids max (kg)',
        'Volume (m³)',
        'Équipements',
        'Prix par km (EUR)',
        'Statut',
        'Date de création'
    ], ';');

    // Write data rows
    foreach ($vehicles as $vehicle) {
        fputcsv($output, [
            $vehicle['id'],
            $vehicle['vehicle_type'],
            $vehicle['departure_city'],
            $vehicle['departure_country'],
            $vehicle['departure_postal_code'],
            $vehicle['destination_city'],
            $vehicle['destination_country'],
            $vehicle['destination_postal_code'],
            $vehicle['available_date'],
            $vehicle['length'],
            $vehicle['width'],
            $vehicle['height'],
            $vehicle['max_weight'],
            $vehicle['volume'],
            $vehicle['equipment'],
            $vehicle['price_per_km'],
            $vehicle['status'],
            $vehicle['created_at']
        ], ';');
    }

    fclose($output);

    logActivity('Export des offres de véhicules', ['format' => 'CSV', 'count' => count($vehicles)]);

} catch (Exception $e) {
    logError('Export vehicles error', ['error' => $e->getMessage()]);
    die('Erreur lors de l\'export');
}
