<?php
/**
 * Script de génération de données de test
 *
 * Ce script génère des données de test réalistes pour la plateforme Teleroute Marketplace
 * ATTENTION: À utiliser uniquement en environnement de développement/test
 *
 * Usage: php generate-test-data.php
 */

require_once 'config/config.php';

// Vérification de l'environnement
if (APP_ENV === 'production') {
    die("❌ Ce script ne peut pas être exécuté en production!\n");
}

echo "🚀 Génération de données de test pour Teleroute Marketplace\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

try {
    $db = Database::getInstance()->getConnection();

    // ========== UTILISATEURS ==========
    echo "👥 Création d'utilisateurs...\n";

    $users = [
        // Transporteurs
        ['type' => 'transporter', 'company' => 'Transport Express Europe', 'email' => 'contact@tee.fr', 'phone' => '+33 1 23 45 67 01', 'siret' => '12345678900001'],
        ['type' => 'transporter', 'company' => 'LogiRoute International', 'email' => 'info@logiroute.com', 'phone' => '+33 1 23 45 67 02', 'siret' => '12345678900002'],
        ['type' => 'transporter', 'company' => 'EuroFret Services', 'email' => 'contact@eurofret.fr', 'phone' => '+33 1 23 45 67 03', 'siret' => '12345678900003'],
        ['type' => 'transporter', 'company' => 'TransAlpes', 'email' => 'info@transalpes.it', 'phone' => '+39 02 1234567', 'siret' => 'IT12345678900'],
        ['type' => 'transporter', 'company' => 'Iberica Transportes', 'email' => 'contacto@iberica.es', 'phone' => '+34 91 1234567', 'siret' => 'ES12345678900'],

        // Chargeurs
        ['type' => 'shipper', 'company' => 'Industrie Française SA', 'email' => 'logistique@if-sa.fr', 'phone' => '+33 1 34 56 78 01', 'siret' => '98765432100001'],
        ['type' => 'shipper', 'company' => 'Distribution Nord Europe', 'email' => 'shipping@dne.com', 'phone' => '+33 3 12 34 56 78', 'siret' => '98765432100002'],
        ['type' => 'shipper', 'company' => 'Agroalimentaire Bretagne', 'email' => 'export@agrobretagne.fr', 'phone' => '+33 2 98 76 54 32', 'siret' => '98765432100003'],
        ['type' => 'shipper', 'company' => 'Textile Import Export', 'email' => 'logistics@tie.fr', 'phone' => '+33 4 56 78 90 12', 'siret' => '98765432100004'],

        // Les deux
        ['type' => 'both', 'company' => 'TransLog Solutions', 'email' => 'info@translog.fr', 'phone' => '+33 1 45 67 89 01', 'siret' => '55555555500001'],
    ];

    $userIds = [];
    foreach ($users as $userData) {
        $hashedPassword = password_hash('password123', PASSWORD_DEFAULT);

        $stmt = $db->prepare("
            INSERT INTO users (user_type, company_name, email, phone, siret, password, rating, total_ratings, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $rating = rand(35, 50) / 10; // 3.5 à 5.0
        $totalRatings = rand(5, 50);

        $stmt->execute([
            $userData['type'],
            $userData['company'],
            $userData['email'],
            $userData['phone'],
            $userData['siret'],
            $hashedPassword,
            $rating,
            $totalRatings
        ]);

        $userIds[$userData['type']][] = $db->lastInsertId();
        echo "  ✓ {$userData['company']} ({$userData['email']})\n";
    }

    echo "  → " . count($users) . " utilisateurs créés\n\n";

    // ========== OFFRES DE FRET ==========
    echo "📦 Création d'offres de fret...\n";

    $cities = [
        ['Paris', 'France', '75001', 48.8566, 2.3522],
        ['Lyon', 'France', '69001', 45.7640, 4.8357],
        ['Marseille', 'France', '13001', 43.2965, 5.3698],
        ['Lille', 'France', '59000', 50.6292, 3.0573],
        ['Bordeaux', 'France', '33000', 44.8378, -0.5792],
        ['Berlin', 'Allemagne', '10115', 52.5200, 13.4050],
        ['Munich', 'Allemagne', '80331', 48.1351, 11.5820],
        ['Rome', 'Italie', '00100', 41.9028, 12.4964],
        ['Milan', 'Italie', '20100', 45.4642, 9.1900],
        ['Madrid', 'Espagne', '28001', 40.4168, -3.7038],
        ['Barcelona', 'Espagne', '08001', 41.3851, 2.1734],
        ['Bruxelles', 'Belgique', '1000', 50.8503, 4.3517],
        ['Amsterdam', 'Pays-Bas', '1012', 52.3676, 4.9041],
    ];

    $cargoTypes = ['Palettes', 'Colis', 'Vrac', 'Conteneur', 'Matières premières', 'Produits finis', 'Équipement'];
    $vehicleTypes = ['Fourgon', 'Camion bâché', 'Camion frigorifique', 'Semi-remorque', 'Plateau'];
    $statuses = ['active', 'active', 'active', 'active', 'assigned', 'completed'];

    $freightCount = 0;
    foreach ($userIds['shipper'] ?? [] as $shipperId) {
        for ($i = 0; $i < rand(3, 8); $i++) {
            $loading = $cities[array_rand($cities)];
            $delivery = $cities[array_rand($cities)];

            // S'assurer que chargement ≠ livraison
            while ($loading[0] === $delivery[0]) {
                $delivery = $cities[array_rand($cities)];
            }

            $loadingDate = date('Y-m-d', strtotime('+' . rand(1, 30) . ' days'));
            $deliveryDate = date('Y-m-d', strtotime($loadingDate . ' +' . rand(1, 5) . ' days'));

            $stmt = $db->prepare("
                INSERT INTO freight_offers (
                    user_id, loading_city, loading_country, loading_postal_code, loading_lat, loading_lng,
                    delivery_city, delivery_country, delivery_postal_code, delivery_lat, delivery_lng,
                    loading_date, delivery_date, cargo_description, cargo_type, weight, volume,
                    vehicle_type, price, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $weight = rand(500, 20000);
            $volume = rand(5, 80);
            $price = rand(300, 3000);

            $stmt->execute([
                $shipperId,
                $loading[0], $loading[1], $loading[2], $loading[3], $loading[4],
                $delivery[0], $delivery[1], $delivery[2], $delivery[3], $delivery[4],
                $loadingDate, $deliveryDate,
                'Transport de ' . $cargoTypes[array_rand($cargoTypes)] . ' - Livraison rapide requise',
                $cargoTypes[array_rand($cargoTypes)],
                $weight, $volume,
                $vehicleTypes[array_rand($vehicleTypes)],
                $price,
                $statuses[array_rand($statuses)]
            ]);

            $freightCount++;
        }
    }

    // Offres pour les utilisateurs "both"
    foreach ($userIds['both'] ?? [] as $userId) {
        for ($i = 0; $i < rand(2, 5); $i++) {
            $loading = $cities[array_rand($cities)];
            $delivery = $cities[array_rand($cities)];

            while ($loading[0] === $delivery[0]) {
                $delivery = $cities[array_rand($cities)];
            }

            $loadingDate = date('Y-m-d', strtotime('+' . rand(1, 30) . ' days'));
            $deliveryDate = date('Y-m-d', strtotime($loadingDate . ' +' . rand(1, 5) . ' days'));

            $stmt = $db->prepare("
                INSERT INTO freight_offers (
                    user_id, loading_city, loading_country, loading_postal_code, loading_lat, loading_lng,
                    delivery_city, delivery_country, delivery_postal_code, delivery_lat, delivery_lng,
                    loading_date, delivery_date, cargo_description, cargo_type, weight, volume,
                    vehicle_type, price, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $userId,
                $loading[0], $loading[1], $loading[2], $loading[3], $loading[4],
                $delivery[0], $delivery[1], $delivery[2], $delivery[3], $delivery[4],
                $loadingDate, $deliveryDate,
                'Transport de ' . $cargoTypes[array_rand($cargoTypes)],
                $cargoTypes[array_rand($cargoTypes)],
                rand(500, 20000), rand(5, 80),
                $vehicleTypes[array_rand($vehicleTypes)],
                rand(300, 3000),
                $statuses[array_rand($statuses)]
            ]);

            $freightCount++;
        }
    }

    echo "  → $freightCount offres de fret créées\n\n";

    // ========== OFFRES DE VÉHICULES ==========
    echo "🚚 Création d'offres de véhicules...\n";

    $equipments = ['GPS', 'Hayon', 'Sangles', 'Bâche', 'Température contrôlée'];
    $vehicleStatuses = ['available', 'available', 'available', 'assigned', 'in_transit'];

    $vehicleCount = 0;
    foreach ($userIds['transporter'] ?? [] as $transporterId) {
        for ($i = 0; $i < rand(2, 6); $i++) {
            $departure = $cities[array_rand($cities)];
            $destination = $cities[array_rand($cities)];

            while ($departure[0] === $destination[0]) {
                $destination = $cities[array_rand($cities)];
            }

            $availableDate = date('Y-m-d', strtotime('+' . rand(1, 20) . ' days'));

            $selectedEquipment = [];
            for ($j = 0; $j < rand(1, 4); $j++) {
                $selectedEquipment[] = $equipments[array_rand($equipments)];
            }

            $stmt = $db->prepare("
                INSERT INTO vehicle_offers (
                    user_id, vehicle_type, departure_city, departure_country, departure_postal_code,
                    departure_lat, departure_lng, destination_city, destination_country,
                    destination_postal_code, destination_lat, destination_lng, available_date,
                    length, width, height, max_weight, volume, equipment, price_per_km, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $transporterId,
                $vehicleTypes[array_rand($vehicleTypes)],
                $departure[0], $departure[1], $departure[2], $departure[3], $departure[4],
                $destination[0], $destination[1], $destination[2], $destination[3], $destination[4],
                $availableDate,
                rand(6, 13), rand(2, 3), rand(2, 3),
                rand(5000, 24000), rand(30, 100),
                implode(', ', array_unique($selectedEquipment)),
                rand(15, 35) / 10, // 1.5 à 3.5 €/km
                $vehicleStatuses[array_rand($vehicleStatuses)]
            ]);

            $vehicleCount++;
        }
    }

    // Offres pour les utilisateurs "both"
    foreach ($userIds['both'] ?? [] as $userId) {
        for ($i = 0; $i < rand(2, 4); $i++) {
            $departure = $cities[array_rand($cities)];
            $destination = $cities[array_rand($cities)];

            while ($departure[0] === $destination[0]) {
                $destination = $cities[array_rand($cities)];
            }

            $availableDate = date('Y-m-d', strtotime('+' . rand(1, 20) . ' days'));

            $selectedEquipment = [];
            for ($j = 0; $j < rand(1, 4); $j++) {
                $selectedEquipment[] = $equipments[array_rand($equipments)];
            }

            $stmt = $db->prepare("
                INSERT INTO vehicle_offers (
                    user_id, vehicle_type, departure_city, departure_country, departure_postal_code,
                    departure_lat, departure_lng, destination_city, destination_country,
                    destination_postal_code, destination_lat, destination_lng, available_date,
                    length, width, height, max_weight, volume, equipment, price_per_km, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $userId,
                $vehicleTypes[array_rand($vehicleTypes)],
                $departure[0], $departure[1], $departure[2], $departure[3], $departure[4],
                $destination[0], $destination[1], $destination[2], $destination[3], $destination[4],
                $availableDate,
                rand(6, 13), rand(2, 3), rand(2, 3),
                rand(5000, 24000), rand(30, 100),
                implode(', ', array_unique($selectedEquipment)),
                rand(15, 35) / 10,
                $vehicleStatuses[array_rand($vehicleStatuses)]
            ]);

            $vehicleCount++;
        }
    }

    echo "  → $vehicleCount offres de véhicules créées\n\n";

    // ========== NOTIFICATIONS ==========
    echo "🔔 Création de notifications...\n";

    $notificationTypes = [
        ['type' => 'message', 'title' => 'Nouveau message', 'message' => 'Vous avez reçu un nouveau message'],
        ['type' => 'offer', 'title' => 'Nouvelle offre correspondante', 'message' => 'Une nouvelle offre correspond à vos critères'],
        ['type' => 'transaction', 'title' => 'Transaction mise à jour', 'message' => 'Le statut de votre transaction a changé'],
        ['type' => 'rating', 'title' => 'Nouvelle évaluation', 'message' => 'Vous avez reçu une nouvelle évaluation'],
        ['type' => 'system', 'title' => 'Mise à jour système', 'message' => 'La plateforme a été mise à jour'],
    ];

    $notifCount = 0;
    $allUserIds = array_merge(
        $userIds['shipper'] ?? [],
        $userIds['transporter'] ?? [],
        $userIds['both'] ?? []
    );

    foreach ($allUserIds as $userId) {
        for ($i = 0; $i < rand(2, 8); $i++) {
            $notif = $notificationTypes[array_rand($notificationTypes)];

            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $userId,
                $notif['type'],
                $notif['title'],
                $notif['message'],
                $notif['type'] === 'message' ? '/messages.php' : '/dashboard.php',
                rand(0, 1) // Certaines lues, d'autres non
            ]);

            $notifCount++;
        }
    }

    echo "  → $notifCount notifications créées\n\n";

    // ========== STATISTIQUES ==========
    echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "✅ Génération terminée avec succès!\n\n";
    echo "📊 Résumé:\n";
    echo "  • Utilisateurs: " . count($users) . "\n";
    echo "  • Offres de fret: $freightCount\n";
    echo "  • Offres de véhicules: $vehicleCount\n";
    echo "  • Notifications: $notifCount\n";
    echo "\n";
    echo "🔑 Identifiants de test:\n";
    echo "  • Email: {$users[0]['email']}\n";
    echo "  • Mot de passe: password123\n";
    echo "\n";
    echo "💡 Tous les utilisateurs utilisent le mot de passe: password123\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

} catch (Exception $e) {
    echo "\n❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}
