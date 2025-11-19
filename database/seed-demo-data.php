<?php
/**
 * Seed Demo Data - Teleroute Marketplace
 *
 * Generates realistic demo data for testing the application
 * Usage: php database/seed-demo-data.php [options]
 *
 * Options:
 *   --admin          Create only admin user
 *   --users=N        Number of users to create (default: 20)
 *   --freight=N      Number of freight offers (default: 50)
 *   --vehicles=N     Number of vehicle offers (default: 30)
 *   --full           Generate all data including messages, notifications
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../helpers/Database.php';

// Parse command line options
$options = getopt('', ['admin', 'admin-only', 'users:', 'freight:', 'vehicles:', 'full']);

$createAdmin = isset($options['admin']) || isset($options['admin-only']);
$adminOnly = isset($options['admin-only']);
$usersCount = isset($options['users']) ? (int)$options['users'] : 20;
$freightCount = isset($options['freight']) ? (int)$options['freight'] : 50;
$vehiclesCount = isset($options['vehicles']) ? (int)$options['vehicles'] : 30;
$fullMode = isset($options['full']);

// Colors for console output
define('COLOR_GREEN', "\033[0;32m");
define('COLOR_BLUE', "\033[0;34m");
define('COLOR_YELLOW', "\033[1;33m");
define('COLOR_NC', "\033[0m"); // No Color

function printMsg($msg) {
    echo COLOR_BLUE . "[" . date('Y-m-d H:i:s') . "]" . COLOR_NC . " $msg\n";
}

function printSuccess($msg) {
    echo COLOR_GREEN . "[✓]" . COLOR_NC . " $msg\n";
}

function printWarning($msg) {
    echo COLOR_YELLOW . "[!]" . COLOR_NC . " $msg\n";
}

printMsg("Starting demo data generation...");

try {
    $db = Database::getInstance()->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Sample data arrays
    $companyNames = [
        'Transport Express SA', 'Logistics Europe', 'Fast Cargo SARL', 'Euro Transport',
        'Quick Delivery', 'Road Masters', 'Continental Freight', 'Speed Logistics',
        'Premium Transport', 'Global Shipping Co', 'Trans European', 'Swift Cargo',
        'Reliable Transport', 'Express Freight Solutions', 'International Carriers',
        'Direct Logistics', 'National Transport', 'European Express', 'Fast Track',
        'Professional Movers', 'Cargo Masters', 'Transport Solutions Inc'
    ];

    $cities = [
        'Paris', 'Lyon', 'Marseille', 'Toulouse', 'Nice', 'Bordeaux', 'Lille',
        'Berlin', 'Hamburg', 'Munich', 'Frankfurt', 'Cologne',
        'Madrid', 'Barcelona', 'Valencia', 'Seville',
        'Rome', 'Milan', 'Naples', 'Turin',
        'Amsterdam', 'Rotterdam', 'Brussels', 'Antwerp',
        'London', 'Manchester', 'Birmingham'
    ];

    $countries = ['FR', 'DE', 'ES', 'IT', 'NL', 'BE', 'GB', 'PL', 'CZ', 'AT'];

    $cargoTypes = ['Palettes', 'Cartons', 'Vrac', 'Conteneur', 'Produits frais', 'Matériaux', 'Machines', 'Mobilier'];

    $vehicleTypes = [
        'Camion bâché' => ['Tautliner', 'Savoyarde'],
        'Camion frigorifique' => ['Frigo', 'Multi-température'],
        'Fourgon' => ['3.5T', '7.5T'],
        'Semi-remorque' => ['Standard', 'Mega']
    ];

    // Create Admin User
    if ($createAdmin) {
        printMsg("Creating admin user...");

        $adminEmail = 'admin@teleroute.com';
        $adminPassword = password_hash('Admin123!', PASSWORD_BCRYPT);

        $stmt = $db->prepare("
            INSERT INTO users (user_type, company_name, email, password, phone, siret, address, city, postal_code, country, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE email = email
        ");

        $stmt->execute([
            'both',
            'Teleroute Marketplace Admin',
            $adminEmail,
            $adminPassword,
            '+33123456789',
            '12345678901234',
            '123 Rue de la Paix',
            'Paris',
            '75001',
            'FR'
        ]);

        printSuccess("Admin user created: $adminEmail / Admin123!");

        if ($adminOnly) {
            printSuccess("Demo data generation completed (admin only)");
            exit(0);
        }
    }

    // Create Users
    printMsg("Creating $usersCount demo users...");
    $userIds = [];

    for ($i = 1; $i <= $usersCount; $i++) {
        $userType = ['carrier', 'shipper', 'both'][rand(0, 2)];
        $companyName = $companyNames[array_rand($companyNames)];
        $email = strtolower(str_replace(' ', '', $companyName)) . $i . '@example.com';
        $password = password_hash('Password123!', PASSWORD_BCRYPT);
        $phone = '+33' . rand(100000000, 999999999);
        $siret = rand(10000000000000, 99999999999999);
        $city = $cities[array_rand($cities)];
        $country = $countries[array_rand($countries)];

        $stmt = $db->prepare("
            INSERT INTO users (user_type, company_name, email, password, phone, siret, address, city, postal_code, country, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $userType,
            $companyName,
            $email,
            $password,
            $phone,
            $siret,
            rand(1, 999) . ' Avenue de Test',
            $city,
            rand(10000, 99999),
            $country
        ]);

        $userIds[] = ['id' => $db->lastInsertId(), 'type' => $userType];
    }

    printSuccess("Created $usersCount users");

    // Create Freight Offers
    printMsg("Creating $freightCount freight offers...");
    $freightIds = [];

    // Get shipper users
    $shippers = array_filter($userIds, function($user) {
        return in_array($user['type'], ['shipper', 'both']);
    });

    for ($i = 0; $i < $freightCount; $i++) {
        if (empty($shippers)) break;

        $shipper = $shippers[array_rand($shippers)];
        $originCity = $cities[array_rand($cities)];
        $destCity = $cities[array_rand($cities)];
        $cargoType = $cargoTypes[array_rand($cargoTypes)];
        $weight = rand(100, 24000); // kg
        $volume = rand(1, 90); // m3
        $price = rand(100, 5000);

        // Random date in next 60 days
        $pickupDate = date('Y-m-d', strtotime('+' . rand(1, 60) . ' days'));
        $deliveryDate = date('Y-m-d', strtotime($pickupDate . ' +' . rand(1, 7) . ' days'));

        $stmt = $db->prepare("
            INSERT INTO freight_offers (
                user_id, origin_city, origin_country, destination_city, destination_country,
                pickup_date, delivery_date, cargo_type, weight, volume, price, currency,
                description, status, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $shipper['id'],
            $originCity,
            $countries[array_rand($countries)],
            $destCity,
            $countries[array_rand($countries)],
            $pickupDate,
            $deliveryDate,
            $cargoType,
            $weight,
            $volume,
            $price,
            'EUR',
            "Transport de $cargoType de $originCity à $destCity. Chargement complet.",
            'active'
        ]);

        $freightIds[] = $db->lastInsertId();
    }

    printSuccess("Created $freightCount freight offers");

    // Create Vehicle Offers
    printMsg("Creating $vehiclesCount vehicle offers...");
    $vehicleIds = [];

    // Get carrier users
    $carriers = array_filter($userIds, function($user) {
        return in_array($user['type'], ['carrier', 'both']);
    });

    foreach ($vehicleTypes as $mainType => $subTypes) {
        for ($i = 0; $i < $vehiclesCount / 4; $i++) {
            if (empty($carriers)) break;

            $carrier = $carriers[array_rand($carriers)];
            $subType = $subTypes[array_rand($subTypes)];
            $originCity = $cities[array_rand($cities)];
            $destCity = $cities[array_rand($cities)];
            $capacity = rand(1000, 24000); // kg
            $volumeCapacity = rand(10, 90); // m3

            $availableDate = date('Y-m-d', strtotime('+' . rand(1, 30) . ' days'));

            $stmt = $db->prepare("
                INSERT INTO vehicle_offers (
                    user_id, vehicle_type, origin_city, origin_country, destination_city, destination_country,
                    available_date, capacity, volume_capacity, price, currency,
                    description, status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $carrier['id'],
                $subType,
                $originCity,
                $countries[array_rand($countries)],
                $destCity,
                $countries[array_rand($countries)],
                $availableDate,
                $capacity,
                $volumeCapacity,
                rand(200, 2000),
                'EUR',
                "$mainType - $subType disponible pour transport de $originCity à $destCity",
                'active'
            ]);

            $vehicleIds[] = $db->lastInsertId();
        }
    }

    printSuccess("Created " . count($vehicleIds) . " vehicle offers");

    // Create Messages (if full mode)
    if ($fullMode && !empty($userIds) && count($userIds) >= 2) {
        printMsg("Creating demo messages...");
        $messageCount = 0;

        for ($i = 0; $i < 30; $i++) {
            $sender = $userIds[array_rand($userIds)];
            $receiver = $userIds[array_rand($userIds)];

            if ($sender['id'] == $receiver['id']) continue;

            $messages = [
                "Bonjour, je suis intéressé par votre offre. Pouvez-vous me donner plus de détails ?",
                "Quelle est votre meilleure offre pour ce transport ?",
                "Je peux proposer un tarif de " . rand(500, 2000) . " EUR pour ce transport.",
                "Quand pouvez-vous effectuer le chargement ?",
                "Mon camion sera disponible à partir de " . date('d/m/Y', strtotime('+' . rand(1, 15) . ' days')),
                "Avez-vous les documents nécessaires pour le transport international ?",
                "Puis-je avoir une confirmation de votre disponibilité ?",
            ];

            $stmt = $db->prepare("
                INSERT INTO messages (sender_id, receiver_id, message, is_read, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $sender['id'],
                $receiver['id'],
                $messages[array_rand($messages)],
                rand(0, 1)
            ]);

            $messageCount++;
        }

        printSuccess("Created $messageCount demo messages");
    }

    // Create Notifications (if full mode)
    if ($fullMode && !empty($userIds)) {
        printMsg("Creating demo notifications...");
        $notificationCount = 0;

        $notificationTypes = [
            'new_message' => 'Vous avez reçu un nouveau message',
            'new_offer' => 'Nouvelle offre correspondant à vos critères',
            'offer_expired' => 'Votre offre expire bientôt',
            'profile_updated' => 'Votre profil a été mis à jour',
        ];

        foreach ($userIds as $user) {
            for ($i = 0; $i < rand(2, 5); $i++) {
                $type = array_rand($notificationTypes);
                $message = $notificationTypes[$type];

                $stmt = $db->prepare("
                    INSERT INTO notifications (user_id, type, message, is_read, created_at)
                    VALUES (?, ?, ?, ?, NOW() - INTERVAL " . rand(0, 72) . " HOUR)
                ");

                $stmt->execute([
                    $user['id'],
                    $type,
                    $message,
                    rand(0, 1)
                ]);

                $notificationCount++;
            }
        }

        printSuccess("Created $notificationCount notifications");
    }

    // Create Saved Searches (if full mode)
    if ($fullMode && !empty($userIds)) {
        printMsg("Creating saved searches...");
        $searchCount = 0;

        foreach (array_slice($userIds, 0, 10) as $user) {
            $criteria = json_encode([
                'origin_country' => $countries[array_rand($countries)],
                'destination_country' => $countries[array_rand($countries)],
                'cargo_type' => $cargoTypes[array_rand($cargoTypes)]
            ]);

            $stmt = $db->prepare("
                INSERT INTO saved_searches (user_id, name, criteria, alert_enabled, created_at)
                VALUES (?, ?, ?, ?, NOW())
            ");

            $stmt->execute([
                $user['id'],
                "Alerte " . $cities[array_rand($cities)] . " - " . $cities[array_rand($cities)],
                $criteria,
                rand(0, 1)
            ]);

            $searchCount++;
        }

        printSuccess("Created $searchCount saved searches");
    }

    // Create Favorites (if full mode)
    if ($fullMode && !empty($userIds) && !empty($freightIds)) {
        printMsg("Creating favorites...");
        $favCount = 0;

        foreach (array_slice($userIds, 0, 15) as $user) {
            for ($i = 0; $i < rand(1, 5); $i++) {
                $offerType = rand(0, 1) ? 'freight' : 'vehicle';
                $offerId = $offerType === 'freight' ?
                    $freightIds[array_rand($freightIds)] :
                    $vehicleIds[array_rand($vehicleIds)];

                try {
                    $stmt = $db->prepare("
                        INSERT INTO favorites (user_id, offer_type, offer_id, created_at)
                        VALUES (?, ?, ?, NOW())
                    ");

                    $stmt->execute([
                        $user['id'],
                        $offerType,
                        $offerId
                    ]);

                    $favCount++;
                } catch (PDOException $e) {
                    // Ignore duplicate favorites
                }
            }
        }

        printSuccess("Created $favCount favorites");
    }

    // Summary
    echo "\n";
    printSuccess("=========================================");
    printSuccess("  Demo Data Generation Complete!");
    printSuccess("=========================================");
    echo "\n";
    printMsg("Summary:");
    echo "  Users created: " . count($userIds) . "\n";
    echo "  Freight offers: " . count($freightIds) . "\n";
    echo "  Vehicle offers: " . count($vehicleIds) . "\n";

    if ($fullMode) {
        echo "  Messages: ~30\n";
        echo "  Notifications: ~" . (count($userIds) * 3) . "\n";
        echo "  Saved searches: ~10\n";
        echo "  Favorites: ~" . (15 * 3) . "\n";
    }

    echo "\n";
    printMsg("Test Accounts:");
    echo "  Admin: admin@teleroute.com / Admin123!\n";
    echo "  Users: <company><number>@example.com / Password123!\n";
    echo "  Example: transportexpresssa1@example.com\n";
    echo "\n";

} catch (PDOException $e) {
    printWarning("Error: " . $e->getMessage());
    exit(1);
}
