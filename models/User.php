<?php
/**
 * Modèle User
 * Gestion des utilisateurs
 */

class User {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    /**
     * Créer un nouvel utilisateur
     */
    public function create($data) {
        $sql = "INSERT INTO users (
            user_type, company_name, email, password, phone, siret, vat_number,
            address, city, postal_code, country, verification_token, status
        ) VALUES (
            :user_type, :company_name, :email, :password, :phone, :siret, :vat_number,
            :address, :city, :postal_code, :country, :verification_token, 'pending'
        )";

        try {
            $stmt = $this->db->prepare($sql);

            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT);
            $verificationToken = generateToken();

            $stmt->bindParam(':user_type', $data['user_type']);
            $stmt->bindParam(':company_name', $data['company_name']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':phone', $data['phone']);
            $stmt->bindParam(':siret', $data['siret']);
            $stmt->bindParam(':vat_number', $data['vat_number']);
            $stmt->bindParam(':address', $data['address']);
            $stmt->bindParam(':city', $data['city']);
            $stmt->bindParam(':postal_code', $data['postal_code']);
            $stmt->bindParam(':country', $data['country']);
            $stmt->bindParam(':verification_token', $verificationToken);

            $stmt->execute();

            return [
                'success' => true,
                'user_id' => $this->db->lastInsertId(),
                'verification_token' => $verificationToken
            ];
        } catch (PDOException $e) {
            logError("Erreur lors de la création de l'utilisateur: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la création du compte'];
        }
    }

    /**
     * Authentifier un utilisateur
     */
    public function login($email, $password) {
        $sql = "SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Mettre à jour la dernière connexion
                $updateSql = "UPDATE users SET last_login = NOW() WHERE id = :id";
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->bindParam(':id', $user['id']);
                $updateStmt->execute();

                // Supprimer le mot de passe avant de retourner
                unset($user['password']);

                return ['success' => true, 'user' => $user];
            }

            return ['success' => false, 'error' => 'Email ou mot de passe incorrect'];
        } catch (PDOException $e) {
            logError("Erreur lors de la connexion: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la connexion'];
        }
    }

    /**
     * Obtenir un utilisateur par ID
     */
    public function getById($id) {
        $sql = "SELECT * FROM users WHERE id = :id LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            $user = $stmt->fetch();
            if ($user) {
                unset($user['password']);
            }

            return $user;
        } catch (PDOException $e) {
            logError("Erreur lors de la récupération de l'utilisateur: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtenir un utilisateur par email
     */
    public function getByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            return $stmt->fetch();
        } catch (PDOException $e) {
            logError("Erreur lors de la récupération de l'utilisateur: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mettre à jour un utilisateur
     */
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];

        $allowedFields = [
            'company_name', 'phone', 'siret', 'vat_number', 'address',
            'city', 'postal_code', 'country', 'profile_image'
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

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return ['success' => true];
        } catch (PDOException $e) {
            logError("Erreur lors de la mise à jour de l'utilisateur: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
        }
    }

    /**
     * Vérifier le compte avec le token
     */
    public function verifyAccount($token) {
        $sql = "UPDATE users SET status = 'active', is_verified = 1, verification_token = NULL
                WHERE verification_token = :token";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            logError("Erreur lors de la vérification du compte: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Changer le mot de passe
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        // Vérifier le mot de passe actuel
        $sql = "SELECT password FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $userId);
        $stmt->execute();

        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'error' => 'Mot de passe actuel incorrect'];
        }

        // Mettre à jour le mot de passe
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $updateSql = "UPDATE users SET password = :password WHERE id = :id";

        try {
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->bindParam(':password', $hashedPassword);
            $updateStmt->bindParam(':id', $userId);
            $updateStmt->execute();

            return ['success' => true];
        } catch (PDOException $e) {
            logError("Erreur lors du changement de mot de passe: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors du changement de mot de passe'];
        }
    }

    /**
     * Mettre à jour la note moyenne d'un utilisateur
     */
    public function updateRating($userId) {
        $sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_ratings
                FROM ratings
                WHERE rated_user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            $result = $stmt->fetch();

            $updateSql = "UPDATE users SET rating = :rating, total_ratings = :total
                         WHERE id = :user_id";
            $updateStmt = $this->db->prepare($updateSql);
            $updateStmt->bindParam(':rating', $result['avg_rating']);
            $updateStmt->bindParam(':total', $result['total_ratings']);
            $updateStmt->bindParam(':user_id', $userId);
            $updateStmt->execute();

            return true;
        } catch (PDOException $e) {
            logError("Erreur lors de la mise à jour de la note: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Rechercher des utilisateurs
     */
    public function search($filters = [], $page = 1, $limit = ITEMS_PER_PAGE) {
        $where = ['status = "active"'];
        $params = [];

        if (!empty($filters['user_type'])) {
            $where[] = "(user_type = :user_type OR user_type = 'both')";
            $params[':user_type'] = $filters['user_type'];
        }

        if (!empty($filters['city'])) {
            $where[] = "city LIKE :city";
            $params[':city'] = '%' . $filters['city'] . '%';
        }

        if (!empty($filters['country'])) {
            $where[] = "country = :country";
            $params[':country'] = $filters['country'];
        }

        if (!empty($filters['min_rating'])) {
            $where[] = "rating >= :min_rating";
            $params[':min_rating'] = $filters['min_rating'];
        }

        $whereClause = implode(' AND ', $where);
        $offset = ($page - 1) * $limit;

        // Compter le total
        $countSql = "SELECT COUNT(*) as total FROM users WHERE $whereClause";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = $countStmt->fetch()['total'];

        // Récupérer les résultats
        $sql = "SELECT id, user_type, company_name, email, phone, city, postal_code, country,
                       rating, total_ratings, profile_image, created_at
                FROM users
                WHERE $whereClause
                ORDER BY rating DESC, total_ratings DESC
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
            logError("Erreur lors de la recherche d'utilisateurs: " . $e->getMessage());
            return ['success' => false, 'error' => 'Erreur lors de la recherche'];
        }
    }

    /**
     * Obtenir les statistiques d'un utilisateur
     */
    public function getStats($userId) {
        try {
            $stats = [];

            // Total offres de fret
            $sql = "SELECT COUNT(*) as total FROM freight_offers WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $stats['total_freight_offers'] = $stmt->fetch()['total'];

            // Total offres de véhicules
            $sql = "SELECT COUNT(*) as total FROM vehicle_offers WHERE user_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $stats['total_vehicle_offers'] = $stmt->fetch()['total'];

            // Total transactions
            $sql = "SELECT COUNT(*) as total FROM transactions
                   WHERE transporter_id = :user_id OR shipper_id = :user_id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $stats['total_transactions'] = $stmt->fetch()['total'];

            // Transactions complétées
            $sql = "SELECT COUNT(*) as total FROM transactions
                   WHERE (transporter_id = :user_id OR shipper_id = :user_id)
                   AND status = 'delivered'";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            $stats['completed_transactions'] = $stmt->fetch()['total'];

            return $stats;
        } catch (PDOException $e) {
            logError("Erreur lors de la récupération des statistiques: " . $e->getMessage());
            return [];
        }
    }
}
