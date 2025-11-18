<?php
require_once '../config/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

$user = $_SESSION['user'];

try {
    $db = Database::getInstance()->getConnection();

    // Get action
    $action = $_GET['action'] ?? 'count';

    switch ($action) {
        case 'count':
            // Get unread count
            $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$user['id']]);
            $count = $stmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'count' => (int)$count
            ]);
            break;

        case 'recent':
            // Get recent unread notifications
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;
            $stmt = $db->prepare("
                SELECT id, type, title, message, link, created_at
                FROM notifications
                WHERE user_id = ? AND is_read = 0
                ORDER BY created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$user['id'], $limit]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format dates
            foreach ($notifications as &$notification) {
                $notification['time_ago'] = timeAgo($notification['created_at']);
            }

            echo json_encode([
                'success' => true,
                'notifications' => $notifications
            ]);
            break;

        case 'mark_read':
            // Mark notification as read
            if (!isset($_POST['id'])) {
                throw new Exception('ID manquant');
            }

            $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$_POST['id'], $user['id']]);

            echo json_encode(['success' => true]);
            break;

        case 'mark_all_read':
            // Mark all as read
            $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$user['id']]);

            echo json_encode(['success' => true]);
            break;

        case 'delete':
            // Delete notification
            if (!isset($_POST['id'])) {
                throw new Exception('ID manquant');
            }

            $stmt = $db->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
            $stmt->execute([$_POST['id'], $user['id']]);

            echo json_encode(['success' => true]);
            break;

        default:
            throw new Exception('Action invalide');
    }

} catch (Exception $e) {
    logError('Notifications API error', ['error' => $e->getMessage()]);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
