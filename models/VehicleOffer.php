<?php
/**
 * Modèle VehicleOffer
 * Gestion des offres de véhicules
 */

class VehicleOffer {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Créer une nouvelle offre de véhicule
     */
    public function create($data) {
        $sql = "INSERT INTO vehicle_offers (
            user_id, title, description,
            departure_address, departure_city, departure_postal_code, departure_country,
            departure_lat, departure_lng, available_from,
            destination_address, destination_city, destination_postal_code, destination_country,
            destination_lat, destination_lng, available_until,
            vehicle_type, vehicle_brand, vehicle_model, registration_number,
            max_weight, max_volume, max_pallets,
            has_gps, has_refrigeration, has_tail_lift, has_adr, equipment_details,
            price_per_km, min_price, price_negotiable,
            expires_at
        ) VALUES (
            :user_id, :title, :description,
            :departure_address, :departure_city, :departure_postal_code, :departure_country,
            :departure_lat, :departure_lng, :available_from,
            :destination_address, :destination_city, :destination_postal_code, :destination_country,
            :destination_lat, :destination_lng, :available_until,
            :vehicle_type, :vehicle_brand, :vehicle_model, :registration_number,
            :max_weight, :max_volume, :max_pallets,
            :has_gps, :has_refrigeration, :has_tail_lift, :has_adr, :equipment_details,
            :price_per_km, :min_price, :price_negotiable,
            :expires_at
        )";

        try {
            $stmt = $this->db->prepare($sql);

            $stmt->bindParam(':user_id', $data['user_id']);
            $stmt->bindParam(':title', $data['title']);
            $stmt->bindParam(':description', $data['description']);

            // Départ
            $stmt->bindParam(':departure_address', $data['departure_address']);
            $stmt->bindParam(':departure_city', $data['departure_city']);
            $stmt->bindParam(':departure_postal_code', $data['departure_postal_code']);
            $stmt->bindParam(':departure_country', $data['departure_country']);
            $stmt->bindParam(':departure_lat', $data['departure_lat']);
            $stmt->bindParam(':departure_lng', $data['departure_lng']);
            $stmt->bindParam(':available_from', $data['available_from']);

            // Destination
            $stmt->bindParam(':destination_address', $data['destination_address']);
            $stmt->bindParam(':destination_city', $data['destination_city']);
            $stmt->bindParam(':destination_postal_code', $data['destination_postal_code']);
            $stmt->bindParam(':destination_country', $data['destination_country']);
            $stmt->bindParam(':destination_lat', $data['destination_lat']);
            $stmt->bindParam(':destination_lng', $data['destination_lng']);
            $stmt->bindParam(':available_until', $data['available_until']);

            // Véhicule
            $stmt->bindParam(':vehicle_type', $data['vehicle_type']);
            $stmt->bindParam(':vehicle_brand', $data['vehicle_brand']);
            $stmt->bindParam(':vehicle_model', $data['vehicle_model']);
            $stmt->bindParam(':registration_number', $data['registration_number']);

            // Capacités
            $stmt->bindParam(':max_weight', $data['max_weight']);
            $stmt->bindParam(':max_volume', $data['max_volume']);
            $stmt->bindParam(':max_pallets', $data['max_pallets']);

            // Équipements
            $stmt->bindParam(':has_gps', $data['has_gps']);
            $stmt->bindParam(':has_refrigeration', $data['has_refrigeration']);
            $stmt->bindParam(':has_tail_lift', $data['has_tail_lift']);
            $stmt->bindParam(':has_adr', $data['has_adr']);
            $stmt->bindParam(':equipment_details', $data['equipment_details']);

            // Prix
            $stmt->bindParam(':price_per_km', $data['price_per_km']);
            $stmt->bindParam(':min_price', $data['min_price']);
            $stmt->bindParam(':price_negotiable', $data['price_negotiable']);

            // Expiration
            $expiresAt = $data['expires_at'] ?? date('Y-m-d H:i:s', strtotime('+30 days'));
            $stmt->bindParam(':expires_at', $expiresAt);

            $stmt->execute();

            return [
                'success' => true,
                'offer_id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            logError("Erreur lors de la création de l'offre de véhicule: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la création de l\'offre'];
        }
    }

    /**
     * Obtenir une offre par ID
     */
    public function getById($id) {
        $sql = "SELECT vo.*, u.company_name, u.rating, u.total_ratings, u.phone, u.email
                FROM vehicle_offers vo
                INNER JOIN users u ON vo.user_id = u.id
                WHERE vo.id = :id
                LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $offer = $stmt->fetch();

            if ($offer) {
                $this->incrementViews($id);
            }

            return $offer;
        } catch (PDOException $e) {
            logError("Erreur lors de la récupération de l'offre: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Rechercher des offres de véhicules
     */
    public function search($filters = [], $page = 1, $limit = ITEMS_PER_PAGE) {
        $where = ['vo.status = "available"'];
        $params = [];

        if (!empty($filters['departure_city'])) {
            $where[] = "vo.departure_city LIKE :departure_city";
            $params[':departure_city'] = '%' . $filters['departure_city'] . '%';
        }

        if (!empty($filters['destination_city'])) {
            $where[] = "vo.destination_city LIKE :destination_city";
            $params[':destination_city'] = '%' . $filters['destination_city'] . '%';
        }

        if (!empty($filters['departure_country'])) {
            $where[] = "vo.departure_country = :departure_country";
            $params[':departure_country'] = $filters['departure_country'];
        }

        if (!empty($filters['destination_country'])) {
            $where[] = "vo.destination_country = :destination_country";
            $params[':destination_country'] = $filters['destination_country'];
        }

        if (!empty($filters['available_from'])) {
            $where[] = "vo.available_from >= :available_from";
            $params[':available_from'] = $filters['available_from'];
        }

        if (!empty($filters['vehicle_type'])) {
            $where[] = "vo.vehicle_type = :vehicle_type";
            $params[':vehicle_type'] = $filters['vehicle_type'];
        }

        if (!empty($filters['min_weight'])) {
            $where[] = "vo.max_weight >= :min_weight";
            $params[':min_weight'] = $filters['min_weight'];
        }

        if (!empty($filters['has_refrigeration'])) {
            $where[] = "vo.has_refrigeration = 1";
        }

        if (!empty($filters['has_tail_lift'])) {
            $where[] = "vo.has_tail_lift = 1";
        }

        if (!empty($filters['has_adr'])) {
            $where[] = "vo.has_adr = 1";
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $limit;

        $orderBy = "vo.created_at DESC";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_asc':
                    $orderBy = "vo.price_per_km ASC";
                    break;
                case 'price_desc':
                    $orderBy = "vo.price_per_km DESC";
                    break;
                case 'date_asc':
                    $orderBy = "vo.available_from ASC";
                    break;
            }
        }

        $countSql = "SELECT COUNT(*) as total FROM vehicle_offers vo WHERE $whereClause";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];

        $sql = "SELECT vo.*, u.company_name, u.rating, u.total_ratings
                FROM vehicle_offers vo
                INNER JOIN users u ON vo.user_id = u.id
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
            logError("Erreur lors de la recherche: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la recherche'];
        }
    }

    /**
     * Mettre à jour une offre
     */
    public function update($id, $userId, $data) {
        $check = "SELECT user_id FROM vehicle_offers WHERE id = :id";
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
            'title', 'description', 'status', 'available_from', 'available_until',
            'departure_address', 'departure_city', 'departure_postal_code', 'departure_country',
            'destination_address', 'destination_city', 'destination_postal_code', 'destination_country',
            'vehicle_type', 'vehicle_brand', 'vehicle_model',
            'max_weight', 'max_volume', 'max_pallets',
            'has_gps', 'has_refrigeration', 'has_tail_lift', 'has_adr',
            'equipment_details', 'price_per_km', 'min_price', 'price_negotiable'
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

        $sql = "UPDATE vehicle_offers SET " . implode(', ', $fields) . " WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return ['success' => true];
        } catch (PDOException $e) {
            logError("Erreur lors de la mise à jour: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        }
    }

    /**
     * Supprimer une offre
     */
    public function delete($id, $userId) {
        $sql = "DELETE FROM vehicle_offers WHERE id = :id AND user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            return $stmt->rowCount() > 0
                ? ['success' => true]
                : ['success' => false, 'error' => 'Offre non trouvée'];
        } catch (PDOException $e) {
            logError("Erreur lors de la suppression: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la suppression'];
        }
    }

    /**
     * Obtenir les offres d'un utilisateur
     */
    public function getByUser($userId, $page = 1, $limit = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $limit;

        $countSql = "SELECT COUNT(*) as total FROM vehicle_offers WHERE user_id = :user_id";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->bindParam(':user_id', $userId);
        $countStmt->execute();
        $total = $countStmt->fetch()['total'];

        $sql = "SELECT * FROM vehicle_offers
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
            logError("Erreur lors de la récupération: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur'];
        }
    }

    /**
     * Incrémenter les vues
     */
    private function incrementViews($id) {
        $sql = "UPDATE vehicle_offers SET views = views + 1 WHERE id = :id";
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        } catch (PDOException $e) {
            logError("Erreur incrémentation vues: " . $e->getMessage());
        }
    }

    /**
     * Statistiques
     */
    public function getStats() {
        try {
            $stats = [];

            $sql = "SELECT COUNT(*) as total FROM vehicle_offers WHERE status = 'available'";
            $stmt = $this->db->query($sql);
            $stats['available_vehicles'] = $stmt->fetch()['total'];

            $sql = "SELECT COUNT(*) as total FROM vehicle_offers WHERE DATE(created_at) = CURDATE()";
            $stmt = $this->db->query($sql);
            $stats['today_vehicles'] = $stmt->fetch()['total'];

            return $stats;
        } catch (PDOException $e) {
            logError("Erreur stats: " . $e->getMessage());
            return [];
        }
    }
}
