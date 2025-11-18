<?php
require_once '../config/config.php';
requireLogin();

$user = $_SESSION['user'];

try {
    $db = Database::getInstance()->getConnection();

    // Get filter from query params
    $status = $_GET['status'] ?? 'all';
    $type = $_GET['type'] ?? 'all';

    // Build query
    $where = "(t.shipper_id = ? OR t.transporter_id = ?)";
    $params = [$user['id'], $user['id']];

    if ($status !== 'all') {
        $where .= " AND t.status = ?";
        $params[] = $status;
    }

    if ($type !== 'all') {
        $where .= " AND t.offer_type = ?";
        $params[] = $type;
    }

    $stmt = $db->prepare("
        SELECT
            t.id,
            t.offer_type,
            t.offer_id,
            t.shipper_id,
            us.company_name as shipper_name,
            t.transporter_id,
            ut.company_name as transporter_name,
            t.amount,
            t.status,
            t.created_at,
            t.completed_at
        FROM transactions t
        JOIN users us ON t.shipper_id = us.id
        JOIN users ut ON t.transporter_id = ut.id
        WHERE $where
        ORDER BY t.created_at DESC
    ");
    $stmt->execute($params);
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="transactions_' . date('Y-m-d_His') . '.csv"');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Write CSV header
    fputcsv($output, [
        'ID Transaction',
        'Type d\'offre',
        'ID Offre',
        'Chargeur',
        'Transporteur',
        'Montant (EUR)',
        'Statut',
        'Date de création',
        'Date de complétion',
        'Mon rôle'
    ], ';');

    // Write data rows
    foreach ($transactions as $transaction) {
        $myRole = ($transaction['shipper_id'] == $user['id']) ? 'Chargeur' : 'Transporteur';

        fputcsv($output, [
            $transaction['id'],
            $transaction['offer_type'] === 'freight' ? 'Fret' : 'Véhicule',
            $transaction['offer_id'],
            $transaction['shipper_name'],
            $transaction['transporter_name'],
            $transaction['amount'],
            $transaction['status'],
            $transaction['created_at'],
            $transaction['completed_at'] ?? 'N/A',
            $myRole
        ], ';');
    }

    fclose($output);

    logActivity('Export des transactions', ['format' => 'CSV', 'count' => count($transactions)]);

} catch (Exception $e) {
    logError('Export transactions error', ['error' => $e->getMessage()]);
    die('Erreur lors de l\'export');
}
