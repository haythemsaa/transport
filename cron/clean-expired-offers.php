#!/usr/bin/env php
<?php
/**
 * Clean Expired Offers
 * Automatically marks old offers as expired
 * Run daily via cron: 0 7 * * * php cron/clean-expired-offers.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/Logger.php';

$logger = Logger::getInstance();
$logger->info('Starting expired offers cleanup', [], 'cron');

try {
    $db = Database::getInstance()->getConnection();

    // Mark expired freight offers
    $stmt = $db->prepare("
        UPDATE freight_offers
        SET status = 'expired'
        WHERE delivery_date < CURDATE()
        AND status = 'active'
        AND deleted_at IS NULL
    ");
    $stmt->execute();
    $freightExpired = $stmt->rowCount();

    // Mark expired vehicle offers
    $stmt = $db->prepare("
        UPDATE vehicle_offers
        SET status = 'expired'
        WHERE departure_date < CURDATE()
        AND status = 'active'
        AND deleted_at IS NULL
    ");
    $stmt->execute();
    $vehicleExpired = $stmt->rowCount();

    // Delete very old offers (soft deleted > 90 days)
    $stmt = $db->prepare("
        DELETE FROM freight_offers
        WHERE deleted_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
    ");
    $stmt->execute();
    $freightDeleted = $stmt->rowCount();

    $stmt = $db->prepare("
        DELETE FROM vehicle_offers
        WHERE deleted_at < DATE_SUB(NOW(), INTERVAL 90 DAY)
    ");
    $stmt->execute();
    $vehicleDeleted = $stmt->rowCount();

    $logger->info('Expired offers cleanup completed', [
        'freight_expired' => $freightExpired,
        'vehicle_expired' => $vehicleExpired,
        'freight_deleted' => $freightDeleted,
        'vehicle_deleted' => $vehicleDeleted
    ], 'cron');

    echo "Cleanup completed:\n";
    echo "- Freight offers expired: $freightExpired\n";
    echo "- Vehicle offers expired: $vehicleExpired\n";
    echo "- Freight offers permanently deleted: $freightDeleted\n";
    echo "- Vehicle offers permanently deleted: $vehicleDeleted\n";

} catch (Exception $e) {
    $logger->error('Expired offers cleanup failed', ['error' => $e->getMessage()], 'cron');
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
