#!/usr/bin/env php
<?php
/**
 * Process Saved Searches / Alerts
 * Checks for new matches and sends notifications
 * Run hourly via cron: 0 * * * * php cron/process-alerts.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/Logger.php';

$logger = Logger::getInstance();
$logger->info('Starting alerts processing', [], 'cron');

try {
    $db = Database::getInstance()->getConnection();

    // Get all active saved searches
    $stmt = $db->query("
        SELECT *
        FROM saved_searches
        WHERE is_active = 1
        AND deleted_at IS NULL
    ");
    $searches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalNotifications = 0;

    foreach ($searches as $search) {
        $criteria = json_decode($search['criteria'], true);
        $userId = $search['user_id'];
        $searchType = $search['search_type']; // 'freight' or 'vehicle'

        // Build query based on criteria
        if ($searchType === 'freight') {
            $query = "SELECT * FROM freight_offers WHERE 1=1 AND status = 'active' AND deleted_at IS NULL";
            $params = [];

            if (!empty($criteria['loading_city'])) {
                $query .= " AND loading_city = ?";
                $params[] = $criteria['loading_city'];
            }
            if (!empty($criteria['delivery_city'])) {
                $query .= " AND delivery_city = ?";
                $params[] = $criteria['delivery_city'];
            }
            if (!empty($criteria['vehicle_type'])) {
                $query .= " AND vehicle_type = ?";
                $params[] = $criteria['vehicle_type'];
            }
            if (!empty($criteria['min_weight'])) {
                $query .= " AND weight >= ?";
                $params[] = $criteria['min_weight'];
            }
            if (!empty($criteria['max_weight'])) {
                $query .= " AND weight <= ?";
                $params[] = $criteria['max_weight'];
            }

            // Only get offers created since last check (1 hour ago)
            $query .= " AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";

            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } else { // vehicle
            $query = "SELECT * FROM vehicle_offers WHERE 1=1 AND status = 'active' AND deleted_at IS NULL";
            $params = [];

            if (!empty($criteria['departure_city'])) {
                $query .= " AND departure_city = ?";
                $params[] = $criteria['departure_city'];
            }
            if (!empty($criteria['destination_city'])) {
                $query .= " AND destination_city = ?";
                $params[] = $criteria['destination_city'];
            }
            if (!empty($criteria['vehicle_type'])) {
                $query .= " AND vehicle_type = ?";
                $params[] = $criteria['vehicle_type'];
            }

            $query .= " AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)";

            $stmt = $db->prepare($query);
            $stmt->execute($params);
            $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // Create notifications for matches
        if (!empty($matches)) {
            foreach ($matches as $match) {
                // Check if notification already sent
                $checkStmt = $db->prepare("
                    SELECT COUNT(*) FROM notifications
                    WHERE user_id = ?
                    AND notification_type = 'alert_match'
                    AND metadata LIKE ?
                ");
                $checkStmt->execute([$userId, '%"offer_id":' . $match['id'] . '%']);

                if ($checkStmt->fetchColumn() == 0) {
                    // Create notification
                    $title = 'Nouvelle offre correspondant à votre alerte';
                    $message = $searchType === 'freight'
                        ? "Nouvelle offre de fret: {$match['loading_city']} → {$match['delivery_city']}, {$match['weight']}kg, " . formatPrice($match['price'])
                        : "Nouveau véhicule disponible: {$match['departure_city']} → {$match['destination_city']}, {$match['vehicle_type']}";

                    $insertStmt = $db->prepare("
                        INSERT INTO notifications (user_id, notification_type, title, message, metadata, created_at)
                        VALUES (?, 'alert_match', ?, ?, ?, NOW())
                    ");
                    $insertStmt->execute([
                        $userId,
                        $title,
                        $message,
                        json_encode([
                            'search_id' => $search['id'],
                            'offer_type' => $searchType,
                            'offer_id' => $match['id']
                        ])
                    ]);

                    $totalNotifications++;
                }
            }

            // Update last_matched timestamp
            $updateStmt = $db->prepare("
                UPDATE saved_searches
                SET last_matched = NOW()
                WHERE id = ?
            ");
            $updateStmt->execute([$search['id']]);
        }
    }

    $logger->info('Alerts processing completed', [
        'searches_processed' => count($searches),
        'notifications_created' => $totalNotifications
    ], 'cron');

    echo "Alerts processing completed:\n";
    echo "- Saved searches processed: " . count($searches) . "\n";
    echo "- Notifications created: $totalNotifications\n";

} catch (Exception $e) {
    $logger->error('Alerts processing failed', ['error' => $e->getMessage()], 'cron');
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
