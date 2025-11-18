<?php
require_once '../config/config.php';
requireLogin();

$user = $_SESSION['user'];

try {
    $db = Database::getInstance()->getConnection();

    // Get filter from query params
    $status = $_GET['status'] ?? 'all';

    // Build query
    $where = "fo.user_id = ? AND fo.deleted_at IS NULL";
    $params = [$user['id']];

    if ($status !== 'all') {
        $where .= " AND fo.status = ?";
        $params[] = $status;
    }

    $stmt = $db->prepare("
        SELECT
            fo.id,
            fo.loading_city,
            fo.loading_country,
            fo.loading_postal_code,
            fo.loading_date,
            fo.delivery_city,
            fo.delivery_country,
            fo.delivery_postal_code,
            fo.delivery_date,
            fo.cargo_description,
            fo.cargo_type,
            fo.weight,
            fo.volume,
            fo.vehicle_type,
            fo.price,
            fo.status,
            fo.created_at
        FROM freight_offers fo
        WHERE $where
        ORDER BY fo.created_at DESC
    ");
    $stmt->execute($params);
    $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="offres_fret_' . date('Y-m-d_His') . '.csv"');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Write CSV header
    fputcsv($output, [
        'ID',
        'Ville de chargement',
        'Pays de chargement',
        'Code postal chargement',
        'Date de chargement',
        'Ville de livraison',
        'Pays de livraison',
        'Code postal livraison',
        'Date de livraison',
        'Description',
        'Type de marchandise',
        'Poids (kg)',
        'Volume (m³)',
        'Type de véhicule',
        'Prix (EUR)',
        'Statut',
        'Date de création'
    ], ';');

    // Write data rows
    foreach ($offers as $offer) {
        fputcsv($output, [
            $offer['id'],
            $offer['loading_city'],
            $offer['loading_country'],
            $offer['loading_postal_code'],
            $offer['loading_date'],
            $offer['delivery_city'],
            $offer['delivery_country'],
            $offer['delivery_postal_code'],
            $offer['delivery_date'],
            $offer['cargo_description'],
            $offer['cargo_type'],
            $offer['weight'],
            $offer['volume'],
            $offer['vehicle_type'],
            $offer['price'],
            $offer['status'],
            $offer['created_at']
        ], ';');
    }

    fclose($output);

    logActivity('Export des offres de fret', ['format' => 'CSV', 'count' => count($offers)]);

} catch (Exception $e) {
    logError('Export freight error', ['error' => $e->getMessage()]);
    die('Erreur lors de l\'export');
}
