-- ============================================
-- TELEROUTE MARKETPLACE - DATABASE SCHEMA
-- Marketplace de Transport Routier
-- ============================================

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_type ENUM('transporter', 'shipper', 'both') NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    siret VARCHAR(50),
    vat_number VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(20),
    country VARCHAR(100) DEFAULT 'France',
    is_verified BOOLEAN DEFAULT FALSE,
    verification_token VARCHAR(255),
    rating DECIMAL(3,2) DEFAULT 0.00,
    total_ratings INT DEFAULT 0,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    status ENUM('active', 'suspended', 'pending') DEFAULT 'pending',
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des offres de fret (chargeurs)
CREATE TABLE IF NOT EXISTS freight_offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,

    -- Lieu de chargement
    loading_address TEXT NOT NULL,
    loading_city VARCHAR(100) NOT NULL,
    loading_postal_code VARCHAR(20) NOT NULL,
    loading_country VARCHAR(100) NOT NULL,
    loading_lat DECIMAL(10, 8),
    loading_lng DECIMAL(11, 8),
    loading_date DATE NOT NULL,
    loading_time_start TIME,
    loading_time_end TIME,

    -- Lieu de livraison
    delivery_address TEXT NOT NULL,
    delivery_city VARCHAR(100) NOT NULL,
    delivery_postal_code VARCHAR(20) NOT NULL,
    delivery_country VARCHAR(100) NOT NULL,
    delivery_lat DECIMAL(10, 8),
    delivery_lng DECIMAL(11, 8),
    delivery_date DATE NOT NULL,
    delivery_time_start TIME,
    delivery_time_end TIME,

    -- Détails du fret
    cargo_type ENUM('pallets', 'containers', 'bulk', 'vehicles', 'refrigerated', 'dangerous', 'other') NOT NULL,
    cargo_description TEXT,
    weight DECIMAL(10, 2) NOT NULL COMMENT 'En tonnes',
    volume DECIMAL(10, 2) COMMENT 'En m3',
    quantity INT DEFAULT 1,

    -- Véhicule requis
    vehicle_type ENUM('van', 'truck', 'semi_trailer', 'container_truck', 'refrigerated_truck', 'flatbed', 'tanker') NOT NULL,
    special_requirements TEXT,

    -- Prix et conditions
    price DECIMAL(10, 2),
    price_negotiable BOOLEAN DEFAULT TRUE,
    payment_terms VARCHAR(255),

    -- Statut
    status ENUM('active', 'assigned', 'in_transit', 'completed', 'cancelled') DEFAULT 'active',
    views INT DEFAULT 0,

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_loading_city (loading_city),
    INDEX idx_delivery_city (delivery_city),
    INDEX idx_loading_date (loading_date),
    INDEX idx_cargo_type (cargo_type),
    INDEX idx_vehicle_type (vehicle_type),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des offres de véhicules (transporteurs)
CREATE TABLE IF NOT EXISTS vehicle_offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,

    -- Localisation de départ
    departure_address TEXT,
    departure_city VARCHAR(100) NOT NULL,
    departure_postal_code VARCHAR(20) NOT NULL,
    departure_country VARCHAR(100) NOT NULL,
    departure_lat DECIMAL(10, 8),
    departure_lng DECIMAL(11, 8),
    available_from DATE NOT NULL,

    -- Destination souhaitée
    destination_address TEXT,
    destination_city VARCHAR(100),
    destination_postal_code VARCHAR(20),
    destination_country VARCHAR(100),
    destination_lat DECIMAL(10, 8),
    destination_lng DECIMAL(11, 8),
    available_until DATE,

    -- Détails du véhicule
    vehicle_type ENUM('van', 'truck', 'semi_trailer', 'container_truck', 'refrigerated_truck', 'flatbed', 'tanker') NOT NULL,
    vehicle_brand VARCHAR(100),
    vehicle_model VARCHAR(100),
    registration_number VARCHAR(50),

    -- Capacités
    max_weight DECIMAL(10, 2) NOT NULL COMMENT 'En tonnes',
    max_volume DECIMAL(10, 2) COMMENT 'En m3',
    max_pallets INT,

    -- Équipements
    has_gps BOOLEAN DEFAULT TRUE,
    has_refrigeration BOOLEAN DEFAULT FALSE,
    has_tail_lift BOOLEAN DEFAULT FALSE,
    has_adr BOOLEAN DEFAULT FALSE COMMENT 'Transport matières dangereuses',
    equipment_details TEXT,

    -- Prix et conditions
    price_per_km DECIMAL(10, 2),
    min_price DECIMAL(10, 2),
    price_negotiable BOOLEAN DEFAULT TRUE,

    -- Statut
    status ENUM('available', 'assigned', 'in_transit', 'unavailable') DEFAULT 'available',
    views INT DEFAULT 0,

    -- Metadata
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_departure_city (departure_city),
    INDEX idx_destination_city (destination_city),
    INDEX idx_available_from (available_from),
    INDEX idx_vehicle_type (vehicle_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des conversations/messagerie
CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    offer_type ENUM('freight', 'vehicle', 'general') DEFAULT 'general',
    offer_id INT,
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_users (user1_id, user2_id),
    INDEX idx_last_message (last_message_at),
    UNIQUE KEY unique_conversation (user1_id, user2_id, offer_type, offer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    attachment_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation (conversation_id),
    INDEX idx_sender (sender_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des évaluations/notations
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    rated_user_id INT NOT NULL,
    rater_user_id INT NOT NULL,
    transaction_type ENUM('freight', 'vehicle') NOT NULL,
    offer_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    criteria_punctuality INT CHECK (criteria_punctuality >= 1 AND criteria_punctuality <= 5),
    criteria_communication INT CHECK (criteria_communication >= 1 AND criteria_communication <= 5),
    criteria_professionalism INT CHECK (criteria_professionalism >= 1 AND criteria_professionalism <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (rated_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (rater_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_rated_user (rated_user_id),
    INDEX idx_rater (rater_user_id),
    UNIQUE KEY unique_rating (rater_user_id, transaction_type, offer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des transactions/contrats
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    freight_offer_id INT,
    vehicle_offer_id INT,
    transporter_id INT NOT NULL,
    shipper_id INT NOT NULL,

    -- Détails du transport
    status ENUM('pending', 'confirmed', 'in_transit', 'delivered', 'cancelled', 'disputed') DEFAULT 'pending',
    price DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',

    -- Dates importantes
    pickup_date DATE,
    delivery_date DATE,
    actual_pickup_date DATETIME,
    actual_delivery_date DATETIME,

    -- Documents
    contract_pdf VARCHAR(255),
    cmr_document VARCHAR(255) COMMENT 'Lettre de voiture CMR',
    pod_document VARCHAR(255) COMMENT 'Proof of Delivery',

    -- Suivi
    tracking_updates TEXT COMMENT 'JSON des updates de position',
    notes TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (freight_offer_id) REFERENCES freight_offers(id) ON DELETE SET NULL,
    FOREIGN KEY (vehicle_offer_id) REFERENCES vehicle_offers(id) ON DELETE SET NULL,
    FOREIGN KEY (transporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (shipper_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_transporter (transporter_id),
    INDEX idx_shipper (shipper_id),
    INDEX idx_payment (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des favoris/signets
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    offer_type ENUM('freight', 'vehicle') NOT NULL,
    offer_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_offer (offer_type, offer_id),
    UNIQUE KEY unique_favorite (user_id, offer_type, offer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des recherches sauvegardées
CREATE TABLE IF NOT EXISTS saved_searches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    search_type ENUM('freight', 'vehicle') NOT NULL,
    search_name VARCHAR(255) NOT NULL,
    search_criteria JSON NOT NULL,
    email_alerts BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des documents de l'utilisateur
CREATE TABLE IF NOT EXISTS user_documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    document_type ENUM('insurance', 'license', 'registration', 'certification', 'other') NOT NULL,
    document_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    verified BOOLEAN DEFAULT FALSE,
    expiry_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_type (document_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table d'audit/logs
CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
