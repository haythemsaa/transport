<?php
/**
 * Modèle Message
 * Gestion de la messagerie instantanée
 */

class Message {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Créer ou récupérer une conversation
     */
    public function getOrCreateConversation($user1Id, $user2Id, $offerType = 'general', $offerId = null) {
        // S'assurer que user1_id < user2_id pour éviter les doublons
        if ($user1Id > $user2Id) {
            $temp = $user1Id;
            $user1Id = $user2Id;
            $user2Id = $temp;
        }

        // Chercher une conversation existante
        $sql = "SELECT * FROM conversations
                WHERE user1_id = :user1_id AND user2_id = :user2_id
                AND offer_type = :offer_type
                AND (offer_id = :offer_id OR (offer_id IS NULL AND :offer_id IS NULL))
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user1_id', $user1Id);
            $stmt->bindParam(':user2_id', $user2Id);
            $stmt->bindParam(':offer_type', $offerType);
            $stmt->bindParam(':offer_id', $offerId);
            $stmt->execute();

            $conversation = $stmt->fetch();

            if ($conversation) {
                return $conversation;
            }

            // Créer une nouvelle conversation
            $insertSql = "INSERT INTO conversations (user1_id, user2_id, offer_type, offer_id)
                         VALUES (:user1_id, :user2_id, :offer_type, :offer_id)";

            $insertStmt = $this->db->prepare($insertSql);
            $insertStmt->bindParam(':user1_id', $user1Id);
            $insertStmt->bindParam(':user2_id', $user2Id);
            $insertStmt->bindParam(':offer_type', $offerType);
            $insertStmt->bindParam(':offer_id', $offerId);
            $insertStmt->execute();

            return [
                'id' => $this->db->lastInsertId(),
                'user1_id' => $user1Id,
                'user2_id' => $user2Id,
                'offer_type' => $offerType,
                'offer_id' => $offerId
            ];
        } catch (PDOException $e) {
            logError("Erreur conversation: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Envoyer un message
     */
    public function sendMessage($conversationId, $senderId, $message, $attachmentUrl = null) {
        $sql = "INSERT INTO messages (conversation_id, sender_id, message, attachment_url)
                VALUES (:conversation_id, :sender_id, :message, :attachment_url)";

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':conversation_id', $conversationId);
            $stmt->bindParam(':sender_id', $senderId);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':attachment_url', $attachmentUrl);
            $stmt->execute();

            // Mettre à jour last_message_at dans conversations
            $updateSql = "UPDATE conversations SET last_message_at = NOW()
                         WHERE id = :conversation_id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->bindParam(':conversation_id', $conversationId);
            $updateStmt->execute();

            $this->db->commit();

            return [
                'success' => true,
                'message_id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            $this->db->rollBack();
            logError("Erreur envoi message: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de l\'envoi'];
        }
    }

    /**
     * Récupérer les messages d'une conversation
     */
    public function getMessages($conversationId, $limit = 50, $offset = 0) {
        $sql = "SELECT m.*, u.company_name, u.profile_image
                FROM messages m
                INNER JOIN users u ON m.sender_id = u.id
                WHERE m.conversation_id = :conversation_id
                ORDER BY m.created_at DESC
                LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':conversation_id', $conversationId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();

            return array_reverse($stmt->fetchAll());
        } catch (PDOException $e) {
            logError("Erreur récupération messages: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les conversations d'un utilisateur
     */
    public function getUserConversations($userId) {
        $sql = "SELECT c.*,
                       CASE
                           WHEN c.user1_id = :user_id THEN c.user2_id
                           ELSE c.user1_id
                       END as other_user_id,
                       u.company_name as other_user_name,
                       u.profile_image as other_user_image,
                       (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != :user_id AND is_read = 0) as unread_count,
                       (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) as last_message
                FROM conversations c
                INNER JOIN users u ON (CASE WHEN c.user1_id = :user_id THEN c.user2_id ELSE c.user1_id END = u.id)
                WHERE c.user1_id = :user_id OR c.user2_id = :user_id
                ORDER BY c.last_message_at DESC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            logError("Erreur récupération conversations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Marquer les messages comme lus
     */
    public function markAsRead($conversationId, $userId) {
        $sql = "UPDATE messages SET is_read = 1
                WHERE conversation_id = :conversation_id
                AND sender_id != :user_id
                AND is_read = 0";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':conversation_id', $conversationId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            return true;
        } catch (PDOException $e) {
            logError("Erreur marquage lecture: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Compter les messages non lus
     */
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as total
                FROM messages m
                INNER JOIN conversations c ON m.conversation_id = c.id
                WHERE (c.user1_id = :user_id OR c.user2_id = :user_id)
                AND m.sender_id != :user_id
                AND m.is_read = 0";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            return $stmt->fetch()['total'];
        } catch (PDOException $e) {
            logError("Erreur comptage non lus: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupérer les nouveaux messages (pour polling/AJAX)
     */
    public function getNewMessages($conversationId, $lastMessageId) {
        $sql = "SELECT m.*, u.company_name, u.profile_image
                FROM messages m
                INNER JOIN users u ON m.sender_id = u.id
                WHERE m.conversation_id = :conversation_id
                AND m.id > :last_message_id
                ORDER BY m.created_at ASC";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':conversation_id', $conversationId);
            $stmt->bindParam(':last_message_id', $lastMessageId);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $e) {
            logError("Erreur nouveaux messages: " . $e->getMessage());
            return [];
        }
    }
}
