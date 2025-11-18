<?php
/**
 * Modèle FreightOffer
 * Gestion des offres de fret
 */

class FreightOffer {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Créer une nouvelle offre de fret
     */
    public function create($data) {
        $sql = "INSERT INTO freight_offers (
            user_id, title, description,
            loading_address, loading_city, loading_postal_code, loading_country, loading_lat, loading_lng,
            loading_date, loading_time_start, loading_time_end,
            delivery_address, delivery_city, delivery_postal_code, delivery_country, delivery_lat, delivery_lng,
            delivery_date, delivery_time_start, delivery_time_end,
            cargo_type, cargo_description, weight, volume, quantity,
            vehicle_type, special_requirements,
            price, price_negotiable, payment_terms,
            expires_at
        ) VALUES (
            :user_id, :title, :description,
            :loading_address, :loading_city, :loading_postal_code, :loading_country, :loading_lat, :loading_lng,
            :loading_date, :loading_time_start, :loading_time_end,
            :delivery_address, :delivery_city, :delivery_postal_code, :delivery_country, :delivery_lat, :delivery_lng,
            :delivery_date, :delivery_time_start, :delivery_time_end,
            :cargo_type, :cargo_description, :weight, :volume, :quantity,
            :vehicle_type, :special_requirements,
            :price, :price_negotiable, :payment_terms,
            :expires_at
        )";

        try {
            $stmt = $this->db->prepare($sql);

            // Binding des paramètres
            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);

            // Chargement
            $stmt->bindParam(':loading_address', $data['loading_address']);
            $stmt->bindParam(':loading_city', $data['loading_city']);
            $stmt->bindParam(':loading_postal_code', $data['loading_postal_code']);
            $stmt->bindParam(':loading_country', $data['loading_country']);
            $stmt->bindParam(':loading_lat', $data['loading_lat']);
            $stmt->bindParam(':loading_lng', $data['loading_lng']);
            $stmt->bindParam(':loading_date', $data['loading_date']);
            $stmt->bindParam(':loading_time_start', $data['loading_time_start']);
            $stmt->bindParam(':loading_time_end', $data['loading_time_end']);

            // Livraison
            $stmt->bindParam(':delivery_address', $data['delivery_address']);
            $stmt->bindParam(':delivery_city', $data['delivery_city']);
            $stmt->bindParam(':delivery_postal_code', $data['delivery_postal_code']);
            $stmt->bindParam(':delivery_country', $data['delivery_country']);
            $stmt->bindParam(':delivery_lat', $data['delivery_lat']);
            $stmt->bindParam(':delivery_lng', $data['delivery_lng']);
            $stmt->bindParam(':delivery_date', $data['delivery_date']);
            $stmt->bindParam(':delivery_time_start', $data['delivery_time_start']);
            $stmt->bindParam(':delivery_time_end', $data['delivery_time_end']);

            // Détails cargo
            $stmt->bindParam(':cargo_type', $data['cargo_type']);
            $stmt->bindParam(':cargo_description', $data['cargo_description']);
            $stmt->bindParam(':weight', $data['weight']);
            $stmt->bindParam(':volume', $data['volume']);
            $stmt->bindParam(':quantity', $data['quantity']);

            // Véhicule
            $stmt->bindParam(':vehicle_type', $data['vehicle_type']);
            $stmt->bindParam(':special_requirements', $data['special_requirements']);

            // Prix
            $stmt->bindParam(':price', $data['price']);
            $stmt->bindParam(':price_negotiable', $data['price_negotiable']);
            $stmt->bindParam(':payment_terms', $data['payment_terms']);

            // Expiration (30 jours par défaut)
            $expiresAt = $data['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+30 days'));
            $stmt->bindParam(':expires_at', $expiresAt);

            $stmt->execute();

            return [
                'success' => true,
                'offer_id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            logError("Erreur lors de la création de l'offre de fret: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la création de l\'offre'];
        }
    }

    /**
     * Obtenir une offre par ID
     */
    public function getById($id) {
        $sql = "SELECT fo.*, u.company_name, u.rating, u.total_ratings, u.phone, u.email
                FROM freight_offers fo
                INNER JOIN users u ON fo.user_id = u.id
                WHERE fo.id = :id
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $offer = $stmt->fetch();

            if ($offer) {
                // Incrémenter les vues
                $this->incrementViews($id);
            }

            return $offer;
        } catch (PDOException $e) {
            logError("Erreur lors de la récupération de l'offre: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Rechercher des offres de fret
     */
    public function search($filters = [], $page = 1, $limit = ITEMS_PER_PAGE) {
        $where = ['fo.status = "active"'];
        $params = [];

        // Filtres de localisation
        if (!empty($filters['loading_city'])) {
            $where[] = "fo.loading_city LIKE :loading_city";
            $params[':loading_city'] = '%' . $filters['loading_city'] . '%';
        }

        if (!empty($filters['delivery_city'])) {
            $where[] = "fo.delivery_city LIKE :delivery_city";
            $params[':delivery_city'] = '%' . $filters['delivery_city'] . '%';
        }

        if (!empty($filters['loading_country'])) {
            $where[] = "fo.loading_country = :loading_country";
            $params[':loading_country'] = $filters['loading_country'];
        }

        if (!empty($filters['delivery_country'])) {
            $where[] = "fo.delivery_country = :delivery_country";
            $params[':delivery_country'] = $filters['delivery_country'];
        }

        // Filtres de date
        if (!empty($filters['loading_date_from'])) {
            $where[] = "fo.loading_date >= :loading_date_from";
            $params[':loading_date_from'] = $filters['loading_date_from'];
        }

        if (!empty($filters['loading_date_to'])) {
            $where[] = "fo.loading_date <= :loading_date_to";
            $params[':loading_date_to'] = $filters['loading_date_to'];
        }

        // Filtres de cargo
        if (!empty($filters['cargo_type'])) {
            $where[] = "fo.cargo_type = :cargo_type";
            $params[':cargo_type'] = $filters['cargo_type'];
        }

        if (!empty($filters['vehicle_type'])) {
            $where[] = "fo.vehicle_type = :vehicle_type";
            $params[':vehicle_type'] = $filters['vehicle_type'];
        }

        if (!empty($filters['min_weight'])) {
            $where[] = "fo.weight >= :min_weight";
            $params[':min_weight'] = $filters['min_weight'];
        }

        if (!empty($filters['max_weight'])) {
            $where[] = "fo.weight <= :max_weight";
            $params[':max_weight'] = $filters['max_weight'];
        }

        // Filtres de prix
        if (!empty($filters['min_price'])) {
            $where[] = "fo.price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }

        if (!empty($filters['max_price'])) {
            $where[] = "fo.price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $limit;

        // Tri
        $orderBy = "fo.created_at DESC";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $orderBy = "fo.price ASC";
                    break;
                case 'price_desc':
                    $orderBy = "fo.price DESC";
                    break;
                case 'date_asc':
                    $orderBy = "fo.loading_date ASC";
                    break;
                case 'date_desc':
                    $orderBy = "fo.loading_date DESC";
                    break;
            }
        }

        // Compter le total
        $countSql = "SELECT COUNT(*) as total
                    FROM freight_offers fo
                    WHERE $whereClause";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];

        // Récupérer les résultats
        $sql = "SELECT fo.*, u.company_name, u.rating, u.total_ratings
                FROM freight_offers fo
                INNER JOIN users u ON fo.user_id = u.id
                WHERE $whereClause
                ORDER BY $orderBy
                LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'success' => true,
                'data' => $stmt->fetchAll(),
                'total' => $total,
                'page' => $page,
                'pages' => ceil($total / $limit)
            ];
        } catch (PDOException $e) {
            logError("Erreur lors de la recherche d'offres: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la recherche'];
        }
    }

    /**
     * Mettre à jour une offre
     */
    public function update($id, $userId, $data) {
        // Vérifier que l'utilisateur est le propriétaire
        $check = "SELECT user_id FROM freight_offers WHERE id = :id";
        $checkStmt = $this->db->prepare($check);
        $checkStmt->bindParam(':id', $id);
        $checkStmt->execute();
        $offer = $checkStmt->fetch();

        if (!$offer || $offer['user_id'] != $userId) {
            return ['success' => false, 'error' => 'Non autorisé'];
        }

        $fields = [];
        $params = [':id' => $id];

        $allowedFields = [
            'title', 'description', 'loading_address', 'loading_city', 'loading_postal_code',
            'loading_country', 'loading_date', 'loading_time_start', 'loading_time_end',
            'delivery_address', 'delivery_city', 'delivery_postal_code', 'delivery_country',
            'delivery_date', 'delivery_time_start', 'delivery_time_end',
            'cargo_type', 'cargo_description', 'weight', 'volume', 'quantity',
            'vehicle_type', 'special_requirements', 'price', 'price_negotiable',
            'payment_terms', 'status'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }

        if (empty($fields)) {
            return ['success' => false, 'error' => 'Aucune donnée à mettre à jour'];
        }

        $sql = "UPDATE freight_offers SET " . implode(', ', $fields) . " WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return ['success' => true];
        } catch (PDOException $e) {
            logError("Erreur lors de la mise à jour de l'offre: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        }
    }

    /**
     * Supprimer une offre
     */
    public function delete($id, $userId) {
        $sql = "DELETE FROM freight_offers WHERE id = :id AND user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return ['success' => true];
            }

            return ['success' => false, 'error' => 'Offre non trouvée ou non autorisée'];
        } catch (PDOException $e) {
            logError("Erreur lors de la suppression de l'offre: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la suppression'];
        }
    }

    /**
     * Obtenir les offres d'un utilisateur
     */
    public function getByUser($userId, $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;

        // Compter le total
        $countSql = "SELECT COUNT(*) as total FROM freight_offers WHERE user_id = :user_id";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->bindParam(':user_id', $userId);
        $countStmt->execute();
        $total = $countStmt->fetch()['total'];

        // Récupérer les offres
        $sql = "SELECT * FROM freight_offers
                WHERE user_id = :user_id
                ORDER BY created_at DESC
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
                'page' => $page,
                'pages' => ceil($total / $limit)
            ];
        } catch (PDOException $e) {
            logError("Erreur lors de la récupération des offres: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la récupération'];
        }
    }

    /**
     * Incrémenter le nombre de vues
     */
    private function incrementViews($id) {
        $sql = "UPDATE freight_offers SET views = views + 1 WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            logError("Erreur lors de l'incrémentation des vues: " . $e->getMessage());
        }
    }

    /**
     * Obtenir les statistiques des offres
     */
    public function getStats() {
        try {
            $stats = [];

            // Total offres actives
            $sql = "SELECT COUNT(*) as total FROM freight_offers WHERE status = 'active'";
            $stmt = $this->db->query($sql);
            $stats['active_offers'] = $stmt->fetch()['total'];

            // Offres ajoutées aujourd'hui
            $sql = "SELECT COUNT(*) as total FROM freight_offers
                   WHERE DATE(created_at) = CURDATE()";
            $stmt = $this->db->query($sql);
            $stats['today_offers'] = $stmt->fetch()['total'];

            return $stats;
        } catch (PDOException $e) {
            logError("Erreur lors de la récupération des statistiques: " . $e->getMessage());
            return [];
        }
    }
}
