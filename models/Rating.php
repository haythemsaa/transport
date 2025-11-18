<?php
/**
 * Modèle Rating
 * Gestion des évaluations/notations
 */

class Rating {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Créer une évaluation
     */
    public function create($data) {
        $sql = "INSERT INTO ratings (
            rated_user_id, rater_user_id, transaction_type, offer_id,
            rating, comment, criteria_punctuality, criteria_communication, criteria_professionalism
        ) VALUES (
            :rated_user_id, :rater_user_id, :transaction_type, :offer_id,
            :rating, :comment, :criteria_punctuality, :criteria_communication, :criteria_professionalism
        )";

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':rated_user_id', $data['rated_user_id']);
            $stmt->bindParam(':rater_user_id', $data['rater_user_id']);
            $stmt->bindParam(':transaction_type', $data['transaction_type']);
            $stmt->bindParam(':offer_id', $data['offer_id']);
            $stmt->bindParam(':rating', $data['rating']);
            $stmt->bindParam(':comment', $data['comment']);
            $stmt->bindParam(':criteria_punctuality', $data['criteria_punctuality']);
            $stmt->bindParam(':criteria_communication', $data['criteria_communication']);
            $stmt->bindParam(':criteria_professionalism', $data['criteria_professionalism']);
            $stmt->execute();

            // Mettre à jour la note moyenne de l'utilisateur
            $userModel = new User();
            $userModel->updateRating($data['rated_user_id']);

            $this->db->commit();

            return ['success' => true, 'rating_id' => $this->db->lastInsertId()];
        } catch (PDOException $e) {
            $this->db->rollBack();
            logError("Erreur création évaluation: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la création'];
        }
    }

    /**
     * Obtenir les évaluations d'un utilisateur
     */
    public function getUserRatings($userId, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;

        $countSql = "SELECT COUNT(*) as total FROM ratings WHERE rated_user_id = :user_id";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->bindParam(':user_id', $userId);
        $countStmt->execute();
        $total = $countStmt->fetch()['total'];

        $sql = "SELECT r.*, u.company_name as rater_name
                FROM ratings r
                INNER JOIN users u ON r.rater_user_id = u.id
                WHERE r.rated_user_id = :user_id
                ORDER BY r.created_at DESC
                LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'success' => true,
                'data' => $stmt->fetchAll(),
                'total' => $total,
                'pages' => ceil($total / $limit)
            ];
        } catch (PDOException $e) {
            logError("Erreur récupération évaluations: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur'];
        }
    }

    /**
     * Vérifier si un utilisateur peut évaluer une transaction
     */
    public function canRate($raterUserId, $transactionType, $offerId) {
        $sql = "SELECT COUNT(*) as count FROM ratings
                WHERE rater_user_id = :rater_id
                AND transaction_type = :type
                AND offer_id = :offer_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':rater_id', $raterUserId);
            $stmt->bindParam(':type', $transactionType);
            $stmt->bindParam(':offer_id', $offerId);
            $stmt->execute();

            return $stmt->fetch()['count'] == 0;
        } catch (PDOException $e) {
            logError("Erreur vérification évaluation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtenir les statistiques détaillées des évaluations
     */
    public function getRatingStats($userId) {
        $sql = "SELECT
                    COUNT(*) as total_ratings,
                    AVG(rating) as avg_rating,
                    AVG(criteria_punctuality) as avg_punctuality,
                    AVG(criteria_communication) as avg_communication,
                    AVG(criteria_professionalism) as avg_professionalism,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_stars,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_stars,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_stars,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_stars,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM ratings
                WHERE rated_user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            logError("Erreur stats évaluations: " . $e->getMessage());
            return null;
        }
    }
}
