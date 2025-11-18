<?php
/**
 * API pour la messagerie en temps réel
 * Permet de récupérer les nouveaux messages via AJAX
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    jsonResponse(['success' => false, 'error' => 'Non authentifié'], 401);
}

$userId = $_SESSION['user_id'];
$messageModel = new Message();

// Récupérer les nouveaux messages
if (isset($_GET['conversation_id']) && isset($_GET['last_message_id'])) {
    $conversationId = (int)$_GET['conversation_id'];
    $lastMessageId = (int)$_GET['last_message_id'];

    $messages = $messageModel->getNewMessages($conversationId, $lastMessageId);

    // Marquer comme lu
    if (!empty($messages)) {
        $messageModel->markAsRead($conversationId, $userId);
    }

    jsonResponse([
        'success' => true,
        'messages' => $messages
    ]);
}

// Récupérer le nombre de messages non lus
if (isset($_GET['unread_count'])) {
    $count = $messageModel->getUnreadCount($userId);

    jsonResponse([
        'success' => true,
        'unread_count' => $count
    ]);
}

jsonResponse(['success' => false, 'error' => 'Requête invalide'], 400);
