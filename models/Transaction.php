<?php
/**
 * Modèle Transaction
 * Gestion des transactions/contrats de transport
 */

class Transaction {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Créer une nouvelle transaction
     */
    public function create($data) {
        $sql = "INSERT INTO transactions (
            freight_offer_id, vehicle_offer_id, transporter_id, shipper_id,
            price, pickup_date, delivery_date, notes
        ) VALUES (
            :freight_offer_id, :vehicle_offer_id, :transporter_id, :shipper_id,
            :price, :pickup_date, :delivery_date, :notes
        )";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':freight_offer_id', $data['freight_offer_id']);
            $stmt->bindParam(':vehicle_offer_id', $data['vehicle_offer_id']);
            $stmt->bindParam(':transporter_id', $data['transporter_id']);
            $stmt->bindParam(':shipper_id', $data['shipper_id']);
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':pickup_date', $data['pickup_date']);
            $stmt->bindParam(':delivery_date', $data['delivery_date']);
            $stmt->bindParam(':notes', $data['notes']);
            $stmt->execute();

            return [
                'success' => true,
                'transaction_id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            logError("Erreur création transaction: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la création'];
        }
    }

    /**
     * Obtenir une transaction par ID
     */
    public function getById($id) {
        $sql = "SELECT t.*,
                       u1.company_name as transporter_name,
                       u2.company_name as shipper_name
                FROM transactions t
                INNER JOIN users u1 ON t.transporter_id = u1.id
                INNER JOIN users u2 ON t.shipper_id = u2.id
                WHERE t.id = :id
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            logError("Erreur récupération transaction: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtenir les transactions d'un utilisateur
     */
    public function getUserTransactions($userId, $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;

        $countSql = "SELECT COUNT(*) as total FROM transactions
                    WHERE transporter_id = :user_id OR shipper_id = :user_id";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->bindParam(':user_id', $userId);
        $countStmt->execute();
        $total = $countStmt->fetch()['total'];

        $sql = "SELECT t.*,
                       u1.company_name as transporter_name,
                       u2.company_name as shipper_name,
                       fo.title as freight_title,
                       vo.title as vehicle_title
                FROM transactions t
                INNER JOIN users u1 ON t.transporter_id = u1.id
                INNER JOIN users u2 ON t.shipper_id = u2.id
                LEFT JOIN freight_offers fo ON t.freight_offer_id = fo.id
                LEFT JOIN vehicle_offers vo ON t.vehicle_offer_id = vo.id
                WHERE t.transporter_id = :user_id OR t.shipper_id = :user_id
                ORDER BY t.created_at DESC
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
            logError("Erreur récupération transactions: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur'];
        }
    }

    /**
     * Mettre à jour le statut
     */
    public function updateStatus($id, $status, $userId) {
        // Vérifier que l'utilisateur est impliqué dans la transaction
        $check = "SELECT * FROM transactions WHERE id = :id
                 AND (transporter_id = :user_id OR shipper_id = :user_id)";
        $checkStmt = $this->db->prepare($check);
        $checkStmt->bindParam(':id', $id);
        $checkStmt->bindParam(':user_id', $userId);
        $checkStmt->execute();

        if (!$checkStmt->fetch()) {
            return ['success' => false, 'error' => 'Non autorisé'];
        }

        $sql = "UPDATE transactions SET status = :status WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            return ['success' => true];
        } catch (PDOException $e) {
            logError("Erreur mise à jour statut: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur'];
        }
    }

    /**
     * Ajouter une mise à jour de tracking
     */
    public function addTrackingUpdate($id, $update) {
        $sql = "SELECT tracking_updates FROM transactions WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $transaction = $stmt->fetch();

        $updates = $transaction['tracking_updates'] ? json_decode($transaction['tracking_updates'], true) : [];
        $updates[] = array_merge($update, ['timestamp' => date('Y-m-d H:i:s')]);

        $updateSql = "UPDATE transactions SET tracking_updates = :updates WHERE id = :id";
        $updateStmt = $this->db->prepare($updateSql);
        $updatesJson = json_encode($updates);
        $updateStmt->bindParam(':updates', $updatesJson);
        $updateStmt->bindParam(':id', $id);

        try {
            $updateStmt->execute();
            return ['success' => true];
        } catch (PDOException $e) {
            logError("Erreur tracking: " . $e->getMessage());
            return ['success' => false];
        }
    }
}
